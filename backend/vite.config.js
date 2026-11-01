import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Fonts (Manrope + IBM Plex Sans Arabic) are served by Bunny Fonts (GDPR-friendly CDN,
// font-display: swap, unicode-range subsets incl. Arabic) — see layouts/partials/head.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/echo.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
