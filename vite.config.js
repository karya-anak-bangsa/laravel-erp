import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/admin.scss',
                'resources/js/admin.js',
                'resources/js/auth.js',
                'resources/css/web.css',
                'resources/js/web.js',
            ],
            refresh: true,
            // Semua font di-host sendiri saat build agar tidak bergantung CDN font.
            fonts: [
                // Inter dipakai Gentelella (panel admin).
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
                // Font frontend publik: kedua tema dimuat @font-face-nya agar ganti tema tanpa memuat ulang,
                // tetapi tidak di-preload supaya pengunjung hanya mengunduh font tema yang sedang tampil.
                bunny('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                bunny('Geist', {
                    weights: [400, 500, 600, 700],
                    preload: false,
                }),
                bunny('Geist Mono', {
                    weights: [400, 500, 600],
                    preload: false,
                }),
            ],
        }),
        // Plugin Tailwind hanya memproses berkas .css (frontend publik); admin.scss tetap lewat Sass.
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
