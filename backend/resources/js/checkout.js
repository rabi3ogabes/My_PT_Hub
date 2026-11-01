// Public branded checkout: quote → create order (idempotent) → gateway redirect → poll status.
const PAID = ['paid']
const FAILED = ['failed', 'cancelled', 'refunded']

export function checkoutPage(config, t) {
    return {
        state: 'loading',
        quotes: [],
        priceId: null,
        name: '',
        email: '',
        phone: '',
        coupon: '',
        error: '',
        busy: false,
        orderId: null,
        idempotencyKey: null,
        storageKey: 'au-checkout:' + config.quoteUrl,
        get quote() {
            return this.quotes.find((q) => q.price_id === this.priceId) || null
        },
        init() {
            this.idempotencyKey = this.newKey()
            let saved = null
            try {
                saved = JSON.parse(sessionStorage.getItem(this.storageKey) || 'null')
            } catch (e) {
                saved = null
            }
            const order = config.order || (config.returned && saved ? saved.order : null)
            if (order && config.returned !== 'cancelled') {
                this.orderId = order
                this.poll()
                return
            }
            this.load()
        },
        newKey() {
            return window.crypto && crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2)
        },
        async load() {
            this.error = ''
            try {
                const url = new URL(config.quoteUrl)
                if (this.coupon.trim()) url.searchParams.set('coupon', this.coupon.trim().toUpperCase())
                const res = await fetch(url, { headers: { Accept: 'application/json', 'Accept-Language': config.locale } })
                const body = await res.json().catch(() => ({}))
                if (!res.ok) {
                    this.error = body?.errors?.[0]?.message || t.generic
                    this.state = this.quotes.length ? 'form' : 'error'
                    return
                }
                this.quotes = body.data?.quotes || []
                if (!this.quotes.find((q) => q.price_id === this.priceId)) this.priceId = this.quotes[0]?.price_id || null
                this.state = 'form'
            } catch (e) {
                this.error = t.generic
                this.state = this.quotes.length ? 'form' : 'error'
            }
        },
        label(q) {
            return (config.labels || {})[q.price_id] || t.once
        },
        money(minor, currency) {
            try {
                const f = new Intl.NumberFormat(config.locale, { style: 'currency', currency })
                const digits = f.resolvedOptions().maximumFractionDigits
                return f.format(minor / Math.pow(10, digits))
            } catch (e) {
                return (minor / 100).toFixed(2) + ' ' + currency
            }
        },
        async pay() {
            if (!this.quote) return
            this.busy = true
            this.error = ''
            try {
                const res = await fetch(config.quoteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'Accept-Language': config.locale, 'Idempotency-Key': this.idempotencyKey },
                    body: JSON.stringify({
                        price_id: this.priceId,
                        name: this.name,
                        email: this.email,
                        phone: this.phone || null,
                        coupon: this.coupon.trim() ? this.coupon.trim().toUpperCase() : null,
                        locale: config.locale,
                        success_url: config.successUrl,
                        cancel_url: config.cancelUrl,
                    }),
                })
                const body = await res.json().catch(() => ({}))
                if (!res.ok) {
                    const fields = body?.errors?.[0]?.field_errors
                    this.error = (fields && Object.values(fields)[0]?.[0]) || body?.errors?.[0]?.message || t.generic
                    this.busy = false
                    if (res.status !== 409) this.idempotencyKey = this.newKey()
                    return
                }
                this.orderId = body.data.order_id
                try {
                    sessionStorage.setItem(this.storageKey, JSON.stringify({ order: this.orderId }))
                } catch (e) {
                    // Storage blocked: the return URL still works through polling below.
                }
                if (body.data.checkout_url) {
                    window.location.assign(body.data.checkout_url)
                    return
                }
                this.poll()
            } catch (e) {
                this.error = t.generic
                this.busy = false
            }
        },
        async poll() {
            this.state = 'polling'
            const url = config.statusUrl.replace('__ID__', encodeURIComponent(this.orderId))
            for (let i = 0; i < 30; i++) {
                try {
                    const res = await fetch(url, { headers: { Accept: 'application/json' } })
                    const body = await res.json().catch(() => ({}))
                    const status = body.data?.status
                    if (PAID.includes(status) || body.data?.fulfilled) {
                        this.state = 'paid'
                        this.forget()
                        return
                    }
                    if (FAILED.includes(status) || res.status === 404) {
                        this.state = 'failed'
                        this.forget()
                        return
                    }
                } catch (e) {
                    // Network hiccup: keep polling.
                }
                await new Promise((r) => setTimeout(r, i < 10 ? 2000 : 4000))
            }
            this.state = 'pending'
        },
        forget() {
            try {
                sessionStorage.removeItem(this.storageKey)
            } catch (e) {
                // ignore
            }
        },
        reset() {
            this.forget()
            this.orderId = null
            this.busy = false
            this.idempotencyKey = this.newKey()
            this.state = 'loading'
            const clean = window.location.pathname
            window.history.replaceState({}, '', clean)
            config.returned = null
            config.order = null
            this.load()
        },
    }
}
