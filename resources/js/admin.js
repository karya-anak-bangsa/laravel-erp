// Sidebar & topbar dirender Blade. mountShell() hanya memasang perilakunya
// (drawer mobile, mode rail, submenu) karena markup sudah ada.
import { mountShell } from 'gentelella/v4/shell';
import { openPanel } from 'gentelella/v4/menus';
// Toast & konfirmasi memakai Simple Notify & SweetAlert2 (pilihan pemilik), bukan
// komponen Gentelella. Build ESM tanpa CSS bawaan; CSS-nya dimuat di admin.scss.
import Notify from 'simple-notify';
import Swal from 'sweetalert2/dist/sweetalert2.esm.js';
import { pasangRepeater } from './admin/repeater.js';

mountShell();
pasangRepeater();

// Editor WYSIWYG cukup berat (TipTap + ProseMirror), jadi hanya dimuat di halaman form yang memakainya.
if (document.querySelector('[data-editor]')) {
    import('./admin/editor.js').then(({ pasangEditor }) => pasangEditor());
}

// Menu dropdown data-menu (mis. menu pengguna di sidebar-footer): isinya <template> Blade (layouts/partials/sidebar).
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

// Flash message dari Laravel (layouts/partials/flash.blade.php) → toast.
const judulToast = { success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Informasi' };

// Simple Notify memasukkan "text" sebagai HTML; pesan bisa memuat input pengguna
// (mis. nama data yang baru disimpan), jadi di-escape lebih dulu agar tidak menjadi celah XSS.
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

// Tombol Lihat (<x-admin.detail-button>) membuka rincian data dalam modal SweetAlert2.
// Isinya disalin sebagai elemen DOM dari <template> Blade yang sudah di-escape, bukan
// string HTML, sehingga input pengguna tidak pernah diparse ulang sebagai HTML di sini.
document.addEventListener('click', (event) => {
    const tombol = event.target instanceof Element ? event.target.closest('[data-detail]') : null;
    const templat = tombol ? document.getElementById(tombol.dataset.detail) : null;
    if (!(templat instanceof HTMLTemplateElement) || !templat.content.firstElementChild) {
        return;
    }

    Swal.fire({
        titleText: tombol.dataset.detailTitle || 'Detail data',
        html: templat.content.firstElementChild.cloneNode(true),
        showCloseButton: true,
        confirmButtonText: 'Tutup',
        buttonsStyling: false,
        customClass: {
            popup: 'swal-detail',
            confirmButton: 'btn btn-secondary',
        },
    });
});

// Form dengan data-confirm minta konfirmasi lewat SweetAlert2: hapus (<x-admin.delete-button>,
// varian danger) maupun simpan tambah/ubah (data-confirm-variant="success").
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
        // Tanpa reverseButtons: aksi di kiri & Batal di kanan, sama dengan urutan tombol form (pilihan pemilik).
        // Untuk aksi berbahaya fokus awal di Batal agar Enter tidak langsung menghapus data.
        focusCancel: berbahaya,
        // Tombol memakai kelas Gentelella agar warnanya sama dengan tombol di halaman.
        buttonsStyling: false,
        customClass: {
            confirmButton: `btn btn-${varian}`,
            cancelButton: 'btn btn-secondary',
        },
    });

    if (isConfirmed) {
        // form.submit() tidak memicu event submit lagi, jadi tidak berulang.
        form.submit();
    }
});
