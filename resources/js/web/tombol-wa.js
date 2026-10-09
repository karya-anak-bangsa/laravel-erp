// Tombol WhatsApp melayang disembunyikan saat ajakan kontak lain terlihat (hero, band ajakan, kontak, footer)
// agar tidak menutupi tombol lain. Tanpa JavaScript tombol tetap tampil.
export function pasangTombolWa() {
    const tombol = document.getElementById('tombol-wa');
    if (!tombol || !('IntersectionObserver' in window)) {
        return;
    }

    const terlihat = new Set();
    const pengamat = new IntersectionObserver((entri) => {
        entri.forEach((e) => (e.isIntersecting ? terlihat.add(e.target) : terlihat.delete(e.target)));
        const sembunyi = terlihat.size > 0;
        tombol.toggleAttribute('data-sembunyi', sembunyi);
        tombol.setAttribute('aria-hidden', String(sembunyi));
        tombol.tabIndex = sembunyi ? -1 : 0;
    }, { rootMargin: '0px 0px -12% 0px' });

    document.querySelectorAll('[data-sembunyikan-wa]').forEach((el) => pengamat.observe(el));
}
