// Formulir kontak divalidasi server. Setelah kembali dengan galat, fokus dipindah ke isian salah pertama;
// tombol kirim dikunci saat mengirim agar pesan tidak terkirim dua kali.
export function pasangFormulirKontak() {
    const form = document.getElementById('form-kontak');
    const tombol = form?.querySelector('[type=submit]');
    if (!form || !tombol) {
        return;
    }

    const galatPertama = form.querySelector('[aria-invalid=true]');
    if (galatPertama) {
        // Ditunda sampai browser selesai menggulir ke #kontak: langkah itu memindahkan fokus ke viewport.
        window.addEventListener('load', () => setTimeout(() => galatPertama.focus()), { once: true });
    }

    const kunci = (ya) => {
        tombol.disabled = ya;
        tombol.toggleAttribute('aria-busy', ya);
    };

    // Ditunda satu tick agar penguncian tidak mengganggu pengiriman yang sedang berjalan.
    form.addEventListener('submit', () => setTimeout(() => kunci(true)));
    // Kembali lewat tombol Back (bfcache): tombol dibuka lagi.
    window.addEventListener('pageshow', () => kunci(false));
}
