// Ganti tema Full Color ↔ Monochrome. Pilihan disimpan di cookie 'tema' (dikecualikan dari enkripsi di
// bootstrap/app.php) agar server merender tema yang sama pada kunjungan berikutnya tanpa kedipan.
const SETAHUN = 60 * 60 * 24 * 365;

const simpanCookie = (tema) => {
    const aman = location.protocol === 'https:' ? '; secure' : '';
    document.cookie = `tema=${tema}; path=/; max-age=${SETAHUN}; samesite=lax${aman}`;
};

export function pasangTema() {
    const akar = document.documentElement;
    const tombol = [...document.querySelectorAll('[data-pilih-tema]')];
    if (tombol.length === 0) {
        return;
    }

    const perbarui = () => tombol.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.pilihTema === akar.dataset.tema)));

    const ganti = (tema) => {
        if (akar.dataset.tema === tema) {
            return;
        }

        const ubah = () => {
            akar.dataset.tema = tema;
            perbarui();
        };
        const tenang = matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (document.startViewTransition && !tenang) {
            document.startViewTransition(ubah);
        } else {
            ubah();
        }

        simpanCookie(tema);
    };

    tombol.forEach((b) => b.addEventListener('click', () => ganti(b.dataset.pilihTema)));
    perbarui();
}
