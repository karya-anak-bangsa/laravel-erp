import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/admin.scss', 'resources/js/admin.js'],
            refresh: true,
            // Font Inter dipakai Gentelella; di-host sendiri saat build agar tidak bergantung CDN font.
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
