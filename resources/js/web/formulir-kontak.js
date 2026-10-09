// Formulir kontak divalidasi server. Setelah kembali dari server, fokus dipindah ke isian salah pertama
// (atau ke kotak galat/sukses) agar hasilnya diumumkan pembaca layar; pesan yang sudah ada sejak halaman
// dimuat tidak diumumkan wilayah role=status/alert. Tombol kirim dikunci agar pesan tidak terkirim dua kali.
export function pasangFormulirKontak() {
    const form = document.getElementById('form-kontak');
    const tombol = form?.querySelector('[type=submit]');
    if (!form || !tombol) {
        return;
    }

    const tujuanFokus = form.querySelector('[aria-invalid=true]') ?? form.querySelector('.kotak-galat, .kotak-sukses');
    if (tujuanFokus) {
        // Ditunda sampai browser selesai menggulir ke anchor formulir: langkah itu memindahkan fokus ke viewport.
        window.addEventListener('load', () => setTimeout(() => tujuanFokus.focus()), { once: true });
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
