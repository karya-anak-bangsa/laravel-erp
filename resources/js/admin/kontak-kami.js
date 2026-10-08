// Kotak masuk Kontak Kami: pesan yang belum dibaca otomatis ditandai dibaca saat modal
// detailnya dibuka (event detail-dibuka dari admin.js), lalu status baris, isi modal,
// tombol tandai, dan badge sidebar diperbarui tanpa memuat ulang halaman.

const BADGE = 'kontak-kami-belum-dibaca';

const tampilkanDibaca = (akar) => {
    akar?.querySelectorAll('[data-status-baca]').forEach((el) => {
        el.className = 'status status-abu';
        el.textContent = 'Dibaca';
    });
};

// Tombol tandai di baris berganti menjadi "Tandai belum dibaca", sama seperti render server.
const ubahTombolTandai = (baris) => {
    const form = baris?.querySelector('[data-form-status-baca]');
    if (!form) {
        return;
    }

    form.elements.status_baca.value = '0';
    const tombol = form.querySelector('button');
    tombol.title = 'Tandai belum dibaca';
    tombol.setAttribute('aria-label', 'Tandai belum dibaca');
    tombol.querySelector('.icon')?.classList.replace('fa-envelope-open', 'fa-envelope');
};

const perbaruiBadge = (jumlah) => {
    document.querySelectorAll(`[data-badge="${BADGE}"]`).forEach((badge) => {
        badge.textContent = String(jumlah);
        badge.hidden = jumlah === 0;
    });
};

export function pasangTandaiBaca() {
    document.addEventListener('detail-dibuka', async (event) => {
        const tombol = event.target;
        const url = tombol.dataset.tandaiBaca;
        if (!url) {
            return;
        }

        // Dihapus lebih dulu agar membuka modal berulang tidak mengirim request ganda.
        delete tombol.dataset.tandaiBaca;

        try {
            const respons = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({ status_baca: true }),
            });

            if (!respons.ok) {
                throw new Error(`HTTP ${respons.status}`);
            }

            const { belum_dibaca: belumDibaca } = await respons.json();
            const baris = tombol.closest('tr');

            baris?.classList.remove('belum-dibaca');
            tampilkanDibaca(baris);
            // Isi <template> tidak ikut dijangkau querySelectorAll baris, jadi diperbarui terpisah.
            tampilkanDibaca(document.getElementById(tombol.dataset.detail)?.content);
            tampilkanDibaca(event.detail.popup);
            ubahTombolTandai(baris);
            perbaruiBadge(belumDibaca);
        } catch {
            // Gagal (mis. sesi habis): status tetap belum dibaca dan dicoba lagi saat modal dibuka ulang.
            tombol.dataset.tandaiBaca = url;
        }
    });
}
