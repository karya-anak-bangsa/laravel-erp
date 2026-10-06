// Sidebar & topbar dirender Blade. mountShell() hanya memasang perilakunya
// (drawer mobile, mode rail, submenu, toggle tema) karena markup sudah ada.
import { mountShell } from 'gentelella/v4/shell';
import { showModal } from 'gentelella/v4/modal';
import { showToast } from 'gentelella/v4/toast';

mountShell();

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
