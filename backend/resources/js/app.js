// Aurum web runtime: Livewire + Alpine (bundled with Livewire), theme, toasts,
// command palette, cookie consent and the public calculators.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm'
import { applyTheme, storedTheme, themeStore } from './theme'
import { calculators } from './calculators'

applyTheme(storedTheme())

Alpine.store('theme', themeStore())

Alpine.store('toasts', {
    items: [],
    seq: 0,
    push({ type = 'success', message = '', timeout = 4500 }) {
        if (!message) return
        const id = ++this.seq
        this.items.push({ id, type, message })
        if (timeout > 0) setTimeout(() => this.dismiss(id), timeout)
    },
    dismiss(id) {
        this.items = this.items.filter((t) => t.id !== id)
    },
})

window.addEventListener('toast', (e) => {
    const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail
    Alpine.store('toasts').push(detail || {})
})

// ⌘K / Ctrl+K command palette (items are rendered server-side as JSON).
Alpine.data('commandPalette', (items = []) => ({
    open: false,
    query: '',
    active: 0,
    items,
    get results() {
        const q = this.query.trim().toLowerCase()
        const list = q ? this.items.filter((i) => (i.label + ' ' + (i.group || '')).toLowerCase().includes(q)) : this.items
        return list.slice(0, 12)
    },
    toggle() {
        this.open ? this.close() : this.show()
    },
    show() {
        this.open = true
        this.query = ''
        this.active = 0
        this.$nextTick(() => this.$refs.search?.focus())
    },
    close() {
        this.open = false
    },
    move(step) {
        const n = this.results.length
        if (n === 0) return
        this.active = (this.active + step + n) % n
    },
    go(item) {
        const target = item || this.results[this.active]
        if (target && target.href && target.href !== '#') window.location.href = target.href
    },
}))

// Privacy-preserving consent: nothing optional runs until the visitor opts in.
Alpine.data('cookieConsent', () => ({
    visible: false,
    analytics: false,
    details: false,
    init() {
        let stored = null
        try {
            stored = JSON.parse(localStorage.getItem('au-consent') || 'null')
        } catch (e) {
            stored = null
        }
        if (stored) {
            this.analytics = !!stored.analytics
            this.emit()
        } else {
            setTimeout(() => (this.visible = true), 600)
        }
        window.addEventListener('au-open-consent', () => {
            this.visible = true
            this.details = true
        })
    },
    save(analytics) {
        this.analytics = analytics
        try {
            localStorage.setItem('au-consent', JSON.stringify({ analytics, at: new Date().toISOString(), v: 1 }))
        } catch (e) {
            // Storage unavailable: the choice applies to this page view.
        }
        this.visible = false
        this.emit()
    },
    emit() {
        window.dispatchEvent(new CustomEvent('au-consent', { detail: { analytics: this.analytics } }))
    },
}))

// Pricing page: billing interval + display currency conversion (amounts in USD minor units).
Alpine.data('pricing', (config = {}) => ({
    interval: 'month',
    currency: config.currency || 'USD',
    rates: config.rates || { USD: 1 },
    symbols: config.symbols || {},
    locale: config.locale || 'en',
    price(monthlyMinor, annualMinor) {
        const minor = this.interval === 'year' ? annualMinor / 12 : monthlyMinor
        const value = (minor / 100) * (this.rates[this.currency] || 1)
        return this.format(value)
    },
    yearly(annualMinor) {
        return this.format((annualMinor / 100) * (this.rates[this.currency] || 1))
    },
    format(value) {
        try {
            return new Intl.NumberFormat(this.locale, {
                style: 'currency',
                currency: this.currency,
                maximumFractionDigits: value % 1 === 0 || value >= 100 ? 0 : 2,
            }).format(Math.round(value))
        } catch (e) {
            return (this.symbols[this.currency] || this.currency + ' ') + Math.round(value)
        }
    },
}))

// Coach profile contact/booking request → public API.
Alpine.data('coachLead', (endpoint) => ({
    name: '',
    email: '',
    phone: '',
    message: '',
    sending: false,
    sent: false,
    error: '',
    async submit() {
        this.sending = true
        this.error = ''
        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'Accept-Language': document.documentElement.lang },
                body: JSON.stringify({ name: this.name, email: this.email, phone: this.phone || null, message: this.message, source: 'website' }),
            })
            if (!res.ok) {
                const body = await res.json().catch(() => ({}))
                this.error = body?.errors?.[0]?.message || this.$el.dataset.error
            } else {
                this.sent = true
            }
        } catch (e) {
            this.error = this.$el.dataset.error
        } finally {
            this.sending = false
        }
    },
}))

Object.entries(calculators).forEach(([name, factory]) => Alpine.data(name, factory))

Alpine.data('copy', (text) => ({
    copied: false,
    async copy() {
        try {
            await navigator.clipboard.writeText(text)
            this.copied = true
            setTimeout(() => (this.copied = false), 1800)
        } catch (e) {
            this.copied = false
        }
    },
}))

window.Alpine = Alpine
Livewire.start()
