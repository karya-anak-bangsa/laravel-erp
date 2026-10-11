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
                // Font frontend publik: halaman hanya mencetak @font-face font template aktif (@fonts di head) dan tidak
                // di-preload. Kedua template bawaan kini memakai Geist + Geist Mono; Plus Jakarta Sans tetap dibangun
                // sebagai pilihan font Manajemen Template (Fase 4 langkah 3) dan tidak diunduh pengunjung.
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
