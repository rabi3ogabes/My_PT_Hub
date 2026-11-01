// Realtime client (Laravel Reverb via the Pusher protocol). Loaded only inside the coach app shell.
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

const key = import.meta.env.VITE_REVERB_APP_KEY

if (key) {
    window.Pusher = Pusher
    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https'
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
    })

    const userId = document.querySelector('meta[name="au-user"]')?.getAttribute('content')
    if (userId) {
        window.Echo.private(`user.${userId}`).notification((notification) => {
            window.dispatchEvent(new CustomEvent('au-notification', { detail: notification }))
            if (notification.title) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: notification.title } }))
            }
        })
    }
}
