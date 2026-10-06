// Tombol tampil/sembunyikan kata sandi. Tombol diberi atribut hidden di Blade
// dan baru ditampilkan di sini, agar tanpa JavaScript tidak ada tombol mati.
document.querySelectorAll('[data-toggle-password]').forEach((tombol) => {
    const input = document.getElementById(tombol.dataset.togglePassword);
    const ikon = tombol.querySelector('i');
    if (!input || !ikon) {
        return;
    }

    tombol.hidden = false;

    tombol.addEventListener('click', () => {
        const tampil = input.type === 'password';

        input.type = tampil ? 'text' : 'password';
        ikon.classList.toggle('fa-eye', !tampil);
        ikon.classList.toggle('fa-eye-slash', tampil);
        tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        tombol.setAttribute('aria-pressed', String(tampil));
        input.focus();
    });
});
