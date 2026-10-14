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
                // Inter dipakai Gentelella (panel admin) sekaligus pilihan font template. Bobot 800 hanya untuk judul
                // extrabold di frontend, jadi tidak di-preload agar admin tidak ikut mengunduhnya.
                bunny('Inter', {
                    weights: [400, 500, 600, 700, 800],
                    preload: [{ weight: 400 }, { weight: 500 }, { weight: 600 }, { weight: 700 }],
                }),
                // Font frontend publik = semua pilihan FontTemplate (dijaga test FontTemplateTest), bobot 400–800 agar
                // ketebalan judul extrabold tidak dipalsukan browser. Halaman hanya mencetak @font-face font kedua
                // template aktif (@fonts di head) tanpa preload; font lain dibangun tetapi tidak diunduh pengunjung.
                bunny('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                bunny('Geist', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                bunny('Poppins', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                bunny('Manrope', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                bunny('DM Sans', {
                    weights: [400, 500, 600, 700, 800],
                    preload: false,
                }),
                // Label kecil bergaya mono di kedua tema.
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
