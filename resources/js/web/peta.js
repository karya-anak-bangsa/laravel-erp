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

    const peta = L.map(elPeta, { scrollWheelZoom: false, zoomControl: false, dragging: !seluler, tap: !seluler })
        .setView(titik, parseInt(d.zoom, 10) || 16);
    L.control.zoom({ position: 'bottomright', zoomInTitle: 'Perbesar', zoomOutTitle: 'Perkecil' }).addTo(peta);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">Kontributor OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(peta);

    const ikon = L.divIcon({ className: 'penanda-peta', html: ikonGedung, iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -40] });

    // Isi popup dirakit lewat DOM (textContent) agar data alamat tidak pernah ditafsirkan sebagai HTML.
    const isi = document.createElement('div');
    const nama = document.createElement('strong');
    nama.textContent = d.nama;
    const alamat = document.createElement('span');
    alamat.textContent = d.alamat;
    isi.append(nama, document.createElement('br'), alamat);

    const penanda = L.marker(titik, { icon: ikon, title: d.nama, alt: 'Lokasi ' + d.nama }).addTo(peta).bindPopup(isi);
    if (matchMedia('(min-width: 640px)').matches) {
        penanda.openPopup();
    }
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
