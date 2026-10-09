// Filter portofolio: Basecoat mengurus aria-selected & navigasi panah; kartu disaring setiap tab aktif berubah.
export function pasangPortofolio() {
    const akar = document.getElementById('filter-portofolio');
    const daftarTab = akar?.querySelector('[role=tablist]');
    const panel = akar?.querySelector('[role=tabpanel]');
    const daftar = panel?.querySelector('.daftar-portofolio');
    if (!daftarTab || !panel || !daftar) {
        return;
    }

    const kartu = [...panel.querySelectorAll('[data-kategori]')];
    const info = document.getElementById('info-filter');
    const petunjuk = document.getElementById('petunjuk-geser');
    let filterSebelumnya = null;

    const terapkan = () => {
        const aktif = daftarTab.querySelector('[role=tab][aria-selected=true]') || daftarTab.querySelector('[role=tab]');
        const filter = aktif.dataset.filter;
        if (filter === filterSebelumnya) {
            panel.hidden = false;
            return;
        }

        let jumlah = 0;
        kartu.forEach((el) => {
            const tampil = filter === 'semua' || el.dataset.kategori === filter;
            el.hidden = !tampil;
            if (tampil) {
                jumlah++;
                if (filterSebelumnya !== null) {
                    el.classList.remove('muncul-ulang');
                    void el.offsetWidth;
                    el.classList.add('muncul-ulang');
                }
            }
        });

        panel.setAttribute('aria-labelledby', aktif.id);
        panel.hidden = false;
        daftar.dataset.jumlah = String(jumlah);
        if (petunjuk) {
            petunjuk.hidden = jumlah < 2;
        }

        // Hasil diumumkan hanya saat pengunjung mengganti tab, bukan saat halaman dimuat.
        if (filterSebelumnya !== null) {
            daftar.scrollLeft = 0;
            if (info) {
                info.textContent = `Menampilkan ${jumlah} portofolio${filter === 'semua' ? '' : ' kategori ' + aktif.textContent.trim()}.`;
            }
        }

        filterSebelumnya = filter;
    };

    new MutationObserver(terapkan).observe(daftarTab, { subtree: true, attributes: true, attributeFilter: ['aria-selected'] });

    // Cadangan bila skrip tab Basecoat gagal dijalankan.
    daftarTab.addEventListener('click', (e) => {
        const tab = e.target.closest('[role=tab]');
        if (!tab || window.basecoat) {
            return;
        }

        daftarTab.querySelectorAll('[role=tab]').forEach((t) => {
            t.setAttribute('aria-selected', String(t === tab));
            t.tabIndex = t === tab ? 0 : -1;
        });
    });

    terapkan();
}
