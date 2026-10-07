// Sidebar & topbar dirender Blade. mountShell() hanya memasang perilakunya
// (drawer mobile, mode rail, submenu) karena markup sudah ada.
import { mountShell } from 'gentelella/v4/shell';
import { openPanel } from 'gentelella/v4/menus';
// Toast & konfirmasi memakai Simple Notify & SweetAlert2 (pilihan pemilik), bukan
// komponen Gentelella. Build ESM tanpa CSS bawaan; CSS-nya dimuat di admin.scss.
import Notify from 'simple-notify';
import Swal from 'sweetalert2/dist/sweetalert2.esm.js';

mountShell();

// Menu pengguna di topbar: isinya <template> Blade (layouts/partials/topbar).
document.querySelectorAll('[data-menu]').forEach((pemicu) => {
    const templat = document.getElementById(pemicu.dataset.menu);
    if (!(templat instanceof HTMLTemplateElement)) {
        return;
    }

    pemicu.addEventListener('click', (event) => {
        event.stopPropagation();
        openPanel(pemicu, templat.content.cloneNode(true), { className: pemicu.dataset.menu });
    });
});

// Toggle tema dari menu pengguna. Tombol .theme-toggle bawaan Gentelella tidak dipakai,
// jadi penyimpanan pilihan & mengikuti tema OS ditangani di sini.
const terapkanTema = (tema) => document.documentElement.setAttribute('data-theme', tema);

document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element) || !event.target.closest('[data-aksi="ganti-tema"]')) {
        return;
    }

    const tema = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    try {
        localStorage.setItem('theme', tema);
    } catch {
        // Mode privat: tema tetap berganti, hanya tidak diingat.
    }
    terapkanTema(tema);
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    let tersimpan = null;
    try {
        tersimpan = localStorage.getItem('theme');
    } catch {
        // Abaikan; anggap belum ada pilihan.
    }
    if (!tersimpan) {
        terapkanTema(event.matches ? 'dark' : 'light');
    }
});

// Flash message dari Laravel (layouts/partials/flash.blade.php) → toast.
const judulToast = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Informasi' };

// Simple Notify memasukkan "text" sebagai HTML; pesan bisa memuat input pengguna
// (mis. nama akun), jadi di-escape lebih dulu agar tidak menjadi celah XSS.
const escapeHtml = (teks) => {
    const div = document.createElement('div');
    div.textContent = teks;
    return div.innerHTML;
};

const flash = document.getElementById('flash-toast');
if (flash) {
    try {
        JSON.parse(flash.textContent).forEach(({ jenis, pesan }) => {
            new Notify({
                status: jenis,
                title: judulToast[jenis] ?? judulToast.info,
                text: escapeHtml(pesan),
                effect: 'slide',
                autotimeout: 4000,
                position: 'right bottom',
            });
        });
    } catch {
        // JSON rusak tidak boleh menghentikan skrip halaman.
    }
}

// Input nominal rupiah (atribut data-rupiah): tampil "1.250.000,5" saat diketik, tetapi
// yang dikirim ke server angka mentah "1250000.5" agar validasi numeric & kolom DECIMAL
// tidak berubah. Nilai awal dari server (model/old()) juga berformat mentah.
const formatRibuan = (digit) => digit.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

const rapikanRupiah = (teks) => {
    const [bulat = '', ...pecahan] = teks.replace(/[^\d,]/g, '').split(',');
    const hasil = formatRibuan(bulat.replace(/^0+(?=\d)/, ''));

    return pecahan.length ? `${hasil},${pecahan.join('').slice(0, 2)}` : hasil;
};

const keAngkaMentah = (teks) => teks.replace(/\./g, '').replace(',', '.');

// Posisi kursor dihitung dari jumlah digit/koma di depannya, karena titik ribuan
// yang disisipkan menggeser posisi karakter.
const posisiKursor = (teks, jumlahKarakter) => {
    let terhitung = 0;
    for (let i = 0; i < teks.length; i++) {
        if (terhitung === jumlahKarakter) {
            return i;
        }
        if (/[\d,]/.test(teks[i])) {
            terhitung++;
        }
    }

    return teks.length;
};

document.querySelectorAll('input[data-rupiah]').forEach((input) => {
    // "1250000.00" → "1.250.000"; desimal nol tidak perlu ditampilkan.
    const [bulat, pecahan = ''] = input.value.split('.');
    const pecahanBermakna = pecahan.replace(/0+$/, '');
    input.value = rapikanRupiah(pecahanBermakna ? `${bulat},${pecahanBermakna}` : bulat);

    input.addEventListener('input', () => {
        const kursor = input.selectionStart ?? input.value.length;
        const sebelumKursor = input.value.slice(0, kursor).replace(/[^\d,]/g, '').length;

        input.value = rapikanRupiah(input.value);

        const posisi = posisiKursor(input.value, sebelumKursor);
        input.setSelectionRange(posisi, posisi);
    });

    // formdata juga terpicu oleh form.submit() setelah konfirmasi SweetAlert2.
    input.form?.addEventListener('formdata', (event) => {
        event.formData.set(input.name, keAngkaMentah(input.value));
    });
});

// Form dengan data-confirm minta konfirmasi lewat SweetAlert2: hapus (<x-admin.delete-button>,
// varian danger) maupun simpan tambah/ubah (data-confirm-variant="primary").
// Tanpa JavaScript form tetap terkirim, hanya tanpa konfirmasi.
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    event.preventDefault();

    const varian = form.dataset.confirmVariant || 'danger';
    const berbahaya = varian === 'danger';

    // titleText & text dirender sebagai teks biasa (bukan HTML) oleh SweetAlert2.
    const { isConfirmed } = await Swal.fire({
        icon: berbahaya ? 'warning' : 'question',
        titleText: form.dataset.confirmTitle || 'Lanjutkan?',
        text: form.dataset.confirm,
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmLabel || 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        // Untuk aksi berbahaya fokus awal di Batal agar Enter tidak langsung menghapus data.
        focusCancel: berbahaya,
        // Tombol memakai kelas Gentelella agar warnanya sama dengan tombol di halaman.
        buttonsStyling: false,
        customClass: {
            confirmButton: `btn btn-${varian}`,
            cancelButton: 'btn btn-outline',
        },
    });

    if (isConfirmed) {
        // form.submit() tidak memicu event submit lagi, jadi tidak berulang.
        form.submit();
    }
});
