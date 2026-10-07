import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/admin.scss', 'resources/js/admin.js', 'resources/js/auth.js'],
            refresh: true,
            // Font Inter dipakai Gentelella; di-host sendiri saat build agar tidak bergantung CDN font.
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
    ],
    build: {
        // Chunk ECharts (±520 kB, ±176 kB gzip) sudah minimal (hanya grafik batang) dan
        // dimuat dinamis khusus di dashboard, jadi batas peringatan dinaikkan sedikit.
        chunkSizeWarningLimit: 600,
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
