// Peta lokasi kantor (Leaflet + tile OpenStreetMap). Leaflet dibundel dari npm tetapi baru diunduh saat seksi
// kontak mendekati layar; sebelum itu (atau bila gagal) ilustrasi peta cadangan yang tampil.
const ikonGedung = '<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg></span>';

const buatPeta = (L, elPeta) => {
    const d = elPeta.dataset;
    const titik = [parseFloat(d.lat), parseFloat(d.lng)];
    if (titik.some(Number.isNaN)) {
        return;
    }

    const seluler = L.Browser.mobile;
    elPeta.hidden = false;

    const peta = L.map(elPeta, { scrollWheelZoom: false, zoomControl: false, dragging: !seluler })
        .setView(titik, parseInt(d.zoom, 10) || 16);
    L.control.zoom({ position: 'bottomright', zoomInTitle: 'Perbesar', zoomOutTitle: 'Perkecil' }).addTo(peta);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">Kontributor OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(peta);

    const ikon = L.divIcon({ className: 'penanda-peta', html: ikonGedung, iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -40] });

    // Popup: nama + tautan Google Maps untuk petunjuk arah (pilihan pemilik 2026-10-11; alamat lengkap sudah ada di
    // daftar kontak). Dirakit lewat DOM (textContent) agar data tidak pernah ditafsirkan sebagai HTML.
    const isi = document.createElement('div');
    const nama = document.createElement('strong');
    nama.textContent = d.nama;
    const tautan = document.createElement('a');
    tautan.href = d.tautan;
    tautan.target = '_blank';
    tautan.rel = 'noopener';
    tautan.className = 'tautan-peta';
    tautan.textContent = 'Buka di Google Maps';
    const keterangan = document.createElement('span');
    keterangan.className = 'sr-only';
    keterangan.textContent = ' (membuka tab baru)';
    tautan.append(keterangan);
    isi.append(nama, document.createElement('br'), tautan);

    // Popup cukup ringkas (dua baris) untuk langsung dibuka di semua layar, agar tautan Google Maps terlihat tanpa
    // mengetuk penanda. Lebar popup dihitung ulang setelah font tema termuat agar nama tidak patah baris.
    const penanda = L.marker(titik, { icon: ikon, title: d.nama, alt: 'Lokasi ' + d.nama }).addTo(peta).bindPopup(isi).openPopup();
    document.fonts?.ready.then(() => penanda.getPopup()?.update());
};

export function pasangPeta() {
    const wadah = document.getElementById('wadah-peta');
    const elPeta = document.getElementById('peta-lokasi');
    if (!wadah || !elPeta) {
        return;
    }

    const muat = () => import('leaflet/dist/leaflet-src.esm.js')
        .then((L) => buatPeta(L, elPeta))
        .catch(() => {
            // Gagal memuat: peta cadangan tetap tampil.
            elPeta.hidden = true;
        });

    if (!('IntersectionObserver' in window)) {
        window.addEventListener('load', muat, { once: true });
        return;
    }

    new IntersectionObserver(([e], pengamat) => {
        if (e.isIntersecting) {
            pengamat.disconnect();
            muat();
        }
    }, { rootMargin: '600px 0px' }).observe(wadah);
}
