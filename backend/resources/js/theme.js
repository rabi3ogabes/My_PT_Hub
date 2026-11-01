// Theme persistence. Dark is the brand default; "system" follows the OS.
const KEY = 'au-theme'

export function storedTheme() {
    try {
        return localStorage.getItem(KEY) || 'dark'
    } catch (e) {
        return 'dark'
    }
}

export function resolveTheme(choice) {
    if (choice === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
    return choice === 'light' ? 'light' : 'dark'
}

export function applyTheme(choice) {
    const resolved = resolveTheme(choice)
    document.documentElement.classList.toggle('dark', resolved === 'dark')
    document.documentElement.style.colorScheme = resolved
    const meta = document.querySelector('meta[name="theme-color"]')
    if (meta) meta.setAttribute('content', resolved === 'dark' ? '#0B0B0F' : '#FAF8F4')
}

export function themeStore() {
    return {
        choice: storedTheme(),
        get resolved() {
            return resolveTheme(this.choice)
        },
        set(choice) {
            this.choice = choice
            try {
                localStorage.setItem(KEY, choice)
            } catch (e) {
                // Private mode: keep the choice for this page view only.
            }
            applyTheme(choice)
        },
        toggle() {
            this.set(this.resolved === 'dark' ? 'light' : 'dark')
        },
        init() {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.choice === 'system') applyTheme('system')
            })
        },
    }
}
