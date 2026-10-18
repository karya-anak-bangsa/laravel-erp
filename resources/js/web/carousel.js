// Carousel gambar detail portofolio: jalur scroll-snap sehingga bisa digeser langsung di layar sentuh, ditambah panah dan
// titik. Panah di slide pertama dan terakhir disembunyikan (pilihan pemilik 2026-10-18).
export function pasangCarousel() {
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const jalur = carousel.querySelector('[data-carousel-jalur]');
        const jumlah = jalur.children.length;
        const sebelumnya = carousel.querySelector('[data-carousel-sebelumnya]');
        const berikutnya = carousel.querySelector('[data-carousel-berikutnya]');
        const titik = [...carousel.querySelectorAll('[data-carousel-titik]')];
        // Keadaan slide pertama sudah dirender server.
        let aktif = 0;
        let menunggu = false;

        const ke = (nomor) => {
            const tujuan = Math.min(Math.max(nomor, 0), jumlah - 1);
            const gerak = matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth';
            jalur.scrollTo({ left: tujuan * jalur.clientWidth, behavior: gerak });
        };

        const tandai = (nomor) => {
            if (nomor === aktif) {
                return;
            }
            aktif = nomor;
            titik.forEach((t, i) => {
                if (i === nomor) {
                    t.setAttribute('aria-current', 'true');
                } else {
                    t.removeAttribute('aria-current');
                }
            });

            // Panah yang akan disembunyikan tidak boleh memegang fokus: fokus pindah ke panah lawannya.
            const diAwal = nomor === 0;
            const diAkhir = nomor === jumlah - 1;
            if (diAwal && document.activeElement === sebelumnya) {
                berikutnya.focus();
            }
            if (diAkhir && document.activeElement === berikutnya) {
                sebelumnya.focus();
            }
            sebelumnya.hidden = diAwal;
            berikutnya.hidden = diAkhir;
        };

        // Slide aktif dihitung dari posisi gulir (sekali per bingkai), juga saat digeser dengan jari atau trackpad.
        // Bukan IntersectionObserver: saat tata letak berubah drastis, pengamat itu bisa melaporkan semua slide terlihat.
        jalur.addEventListener('scroll', () => {
            if (menunggu) {
                return;
            }
            menunggu = true;
            requestAnimationFrame(() => {
                menunggu = false;
                if (jalur.clientWidth > 0) {
                    tandai(Math.min(Math.round(jalur.scrollLeft / jalur.clientWidth), jumlah - 1));
                }
            });
        }, { passive: true });

        sebelumnya.addEventListener('click', () => ke(aktif - 1));
        berikutnya.addEventListener('click', () => ke(aktif + 1));
        titik.forEach((t, i) => t.addEventListener('click', () => ke(i)));
        carousel.dataset.siap = '';
    });
}
