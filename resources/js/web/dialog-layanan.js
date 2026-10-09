// Modal detail layanan (Full Color): satu <dialog> bawaan browser yang diisi dari kartu yang diklik.
// Kunci fokus, Esc, latar inert, dan pengembalian fokus ke pemicu sudah ditangani browser.
const salinIsi = (sumber, tujuan) => {
    tujuan.replaceChildren(...(sumber ? [...sumber.childNodes].map((n) => n.cloneNode(true)) : []));
};

export function pasangDialogLayanan() {
    const dialog = document.getElementById('dialog-layanan');
    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const tautanWa = dialog.querySelector('[data-wa-layanan]');

    document.querySelectorAll('[data-buka-layanan]').forEach((tombol) => tombol.addEventListener('click', () => {
        const kartu = tombol.closest('.kartu-layanan');
        const gaya = getComputedStyle(kartu);
        ['--corak', '--corak-lembut', '--corak-teks', '--corak-cincin'].forEach((p) => dialog.style.setProperty(p, gaya.getPropertyValue(p)));

        const gambar = kartu.querySelector('img');
        const gambarDialog = dialog.querySelector('.dialog-layanan-gambar img');
        gambarDialog.src = gambar.currentSrc || gambar.src;
        gambarDialog.alt = gambar.alt;

        const judul = kartu.querySelector('.judul-layanan').textContent.trim();
        dialog.querySelector('.dialog-layanan-nomor').textContent = kartu.querySelector('.nomor-layanan').textContent;
        dialog.querySelector('.dialog-layanan-judul').textContent = judul;
        salinIsi(kartu.querySelector('.isi-layanan-deskripsi'), dialog.querySelector('.dialog-layanan-deskripsi'));
        salinIsi(kartu.querySelector('.isi-layanan-rinci'), dialog.querySelector('.dialog-layanan-rinci'));

        // data-wa kosong bila identitas tidak punya link WhatsApp, maka tombol WA disembunyikan.
        const wa = dialog.dataset.wa;
        if (tautanWa) {
            tautanWa.hidden = !wa;
            if (wa) {
                tautanWa.href = wa + (wa.includes('?') ? '&' : '?') + 'text=' + encodeURIComponent('Halo, saya ingin bertanya tentang layanan ' + judul);
            }
        }

        dialog.showModal();
    }));

    dialog.addEventListener('click', (e) => {
        if (e.target === dialog) {
            dialog.close();
        }
    });
    dialog.querySelectorAll('[data-tutup-dialog]').forEach((b) => b.addEventListener('click', () => dialog.close()));
}
