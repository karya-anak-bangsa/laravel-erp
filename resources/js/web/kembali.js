// Tombol Kembali di halaman error publik. Halaman yang dibuka di tab baru (history.length 1) tidak punya tujuan
// kembali, jadi tombolnya tetap tersembunyi; tanpa JavaScript pun tetap tersembunyi.
export function pasangTombolKembali() {
    const tombol = document.querySelector('[data-kembali]');
    if (!tombol || history.length < 2) {
        return;
    }

    tombol.hidden = false;
    tombol.addEventListener('click', () => history.back());
}
