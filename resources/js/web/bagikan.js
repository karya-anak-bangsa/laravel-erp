// Tombol salin tautan di <x-web.bagikan> (detail artikel). Tanpa JavaScript tombol ini tidak berfungsi, jadi baru
// ditampilkan di sini; tombol WhatsApp, Facebook, dan LinkedIn berupa tautan biasa.
const LAMA_PESAN = 2500;

export function pasangTombolBagikan() {
    document.querySelectorAll('[data-salin-tautan]').forEach((tombol) => {
        const status = tombol.closest('.bagikan')?.querySelector('[data-status-salin]');
        let jeda;

        tombol.closest('li').hidden = false;
        tombol.addEventListener('click', async () => {
            const berhasil = await salin(tombol.dataset.salinTautan);
            // Cadangan salin memindahkan fokus ke isian sementara; kembalikan ke tombol.
            tombol.focus({ preventScroll: true });

            tombol.toggleAttribute('data-tersalin', berhasil);
            if (status) {
                status.textContent = berhasil ? 'Tautan disalin' : 'Tautan gagal disalin. Salin dari bilah alamat browser.';
                status.toggleAttribute('data-gagal', !berhasil);
            }

            clearTimeout(jeda);
            jeda = setTimeout(() => {
                tombol.removeAttribute('data-tersalin');
                if (status) {
                    status.textContent = '';
                }
            }, LAMA_PESAN);
        });
    });
}

async function salin(teks) {
    try {
        await navigator.clipboard.writeText(teks);

        return true;
    } catch {
        // Clipboard API hanya tersedia di HTTPS/localhost dan bisa ditolak izinnya: salin lewat isian sementara.
        const isian = Object.assign(document.createElement('textarea'), { value: teks, readOnly: true });
        isian.style.cssText = 'position:fixed;top:0;opacity:0';
        document.body.append(isian);
        isian.select();

        let berhasil = false;
        try {
            berhasil = document.execCommand('copy');
        } catch {
            berhasil = false;
        }
        isian.remove();

        return berhasil;
    }
}
