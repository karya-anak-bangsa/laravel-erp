// Sidebar & topbar dirender Blade. mountShell() hanya memasang perilakunya
// (drawer mobile, mode rail, submenu) karena markup sudah ada.
import { mountShell } from 'gentelella/v4/shell';
import { openPanel } from 'gentelella/v4/menus';
import { showModal } from 'gentelella/v4/modal';
import { showToast } from 'gentelella/v4/toast';

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
const flash = document.getElementById('flash-toast');
if (flash) {
    try {
        JSON.parse(flash.textContent).forEach(({ jenis, pesan }) => {
            showToast(pesan, { variant: jenis, duration: 4000 });
        });
    } catch {
        // JSON rusak tidak boleh menghentikan skrip halaman.
    }
}

// Form dengan data-confirm (mis. <x-admin.delete-button>) minta konfirmasi lewat modal.
// Tanpa JavaScript form tetap terkirim, hanya tanpa konfirmasi.
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    event.preventDefault();

    const isi = document.createElement('p');
    isi.style.margin = '0';
    isi.textContent = form.dataset.confirm;

    showModal({
        title: form.dataset.confirmTitle || 'Lanjutkan?',
        size: 'sm',
        body: isi,
        actions: [
            { label: 'Batal', variant: 'ghost' },
            {
                label: form.dataset.confirmLabel || 'Ya, lanjutkan',
                variant: 'danger',
                // form.submit() tidak memicu event submit lagi, jadi tidak berulang.
                action: () => form.submit(),
            },
        ],
    });
});
