// Form template (Pengaturan Sistem › Manajemen Template): isian khusus jenis, pemilih warna, dan tombol pratinjau.
const KUNCI_PRATINJAU = ['nama', 'jenis', 'warna_utama', 'warna_aksen', 'nada_dasar', 'font', 'sudut', 'skala', 'lebar_konten', 'ketebalan_judul'];

export function pasangFormTemplate() {
    const form = document.querySelector('form[data-form-template]');
    if (!form) {
        return;
    }

    // Hanya kelompok isian jenis terpilih yang tampil; isian jenis lain tetap terkirim tetapi dibuang server.
    const pilihJenis = form.querySelector('[data-pilih-jenis]');
    pilihJenis?.addEventListener('change', () => {
        form.querySelectorAll('[data-khusus-jenis]').forEach((kelompok) => {
            kelompok.hidden = kelompok.dataset.khususJenis !== pilihJenis.value;
        });
    });

    // Pemilih warna browser dan kotak hex saling mengisi; yang terkirim kotak hex-nya.
    form.querySelectorAll('[data-pilih-warna]').forEach((pemilih) => {
        const kotak = document.getElementById(pemilih.dataset.pilihWarna);
        if (!kotak) {
            return;
        }

        pemilih.addEventListener('input', () => {
            kotak.value = pemilih.value;
        });
        kotak.addEventListener('input', () => {
            const hex = kotak.value.trim().toLowerCase();
            if (/^#[0-9a-f]{6}$/.test(hex)) {
                pemilih.value = hex;
            }
        });
    });

    // Pratinjau: beranda dengan nilai isian saat ini di tab baru, tanpa menyimpan.
    form.querySelector('[data-pratinjau-template]')?.addEventListener('click', (event) => {
        const data = new FormData(form);
        const parameter = new URLSearchParams();
        KUNCI_PRATINJAU.forEach((kunci) => {
            const nilai = data.get(kunci);
            if (typeof nilai === 'string' && nilai !== '') {
                parameter.set(kunci, nilai);
            }
        });

        window.open(`${event.currentTarget.dataset.pratinjauTemplate}?${parameter}`, '_blank', 'noopener');
    });
}
