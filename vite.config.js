import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

/*
 * Plugin font laravel-vite-plugin 3.2 menulis woff2 dan woff sebagai @font-face terpisah berdeskriptor sama, sehingga
 * browser memakai woff (blok terakhir) yang lebih besar dan preload woff2 terbuang. Semua browser modern membaca woff2,
 * jadi woff dibuang dari hasil build: berkasnya, entri manifest font, dan sumbernya di @font-face.
 */
function fontWoff2Saja() {
    const tanpaWoff = (css) =>
        css.replace(/@font-face\s*\{[^}]*\}\s*/g, (blok) => {
            const src = /src:\s*([^;]+);/.exec(blok);
            const sumber = src ? src[1].split(/,\s*/) : [];
            const sisa = sumber.filter((s) => !s.includes('format("woff")'));

            if (sisa.length === sumber.length) {
                return blok;
            }

            return sisa.length === 0 ? '' : blok.replace(src[1], sisa.join(', '));
        });
    const teks = (aset) => (typeof aset.source === 'string' ? aset.source : new TextDecoder().decode(aset.source));

    return {
        name: 'font-woff2-saja',
        apply: 'build',
        enforce: 'post',
        generateBundle(_, bundle) {
            const manifest = bundle['fonts-manifest.json'];

            if (!manifest) {
                return;
            }

            const data = JSON.parse(teks(manifest));

            for (const keluarga of Object.values(data.families)) {
                for (const varian of Object.values(keluarga.variants)) {
                    varian.files = varian.files.filter((berkas) => {
                        if (berkas.format === 'woff') {
                            delete bundle[berkas.file];
                        }

                        return berkas.format !== 'woff';
                    });
                }
            }

            for (const alias of Object.keys(data.style.familyStyles)) {
                data.style.familyStyles[alias] = tanpaWoff(data.style.familyStyles[alias]);
            }

            bundle[data.style.file].source = tanpaWoff(teks(bundle[data.style.file]));
            manifest.source = JSON.stringify(data, null, 2);
        },
    };
}

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
        fontWoff2Saja(),
        // Plugin Tailwind hanya memproses berkas .css (frontend publik); admin.scss tetap lewat Sass.
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
