// Menu ponsel: panel melayang di bawah header agar tinggi header (dan tujuan gulir anchor) tetap.
export function pasangMenu() {
    const tombol = document.getElementById('tombol-menu');
    const panel = document.getElementById('menu-ponsel');
    const latar = document.getElementById('latar-menu');
    if (!tombol || !panel) {
        return;
    }

    const atur = (buka) => {
        panel.hidden = !buka;
        if (latar) {
            latar.hidden = !buka;
        }
        tombol.setAttribute('aria-expanded', String(buka));
        tombol.setAttribute('aria-label', buka ? 'Tutup menu' : 'Buka menu');
        tombol.querySelector('.ikon-buka')?.classList.toggle('hidden', buka);
        tombol.querySelector('.ikon-tutup')?.classList.toggle('hidden', !buka);
    };

    tombol.addEventListener('click', () => atur(panel.hidden));
    panel.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
            atur(false);
        }
    });
    document.addEventListener('click', (e) => {
        if (!panel.hidden && !e.target.closest('.kepala-situs')) {
            atur(false);
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) {
            atur(false);
            tombol.focus();
        }
    });
    matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
        if (e.matches) {
            atur(false);
        }
    });
}
