// Form template (Pengaturan Sistem › Manajemen Template): isian khusus jenis, pengaturan Default/Custom, pemilih warna,
// dan tombol pratinjau.
const KUNCI_PRATINJAU = ['nama', 'jenis', 'pengaturan', 'warna_utama', 'warna_aksen', 'nada_dasar', 'font', 'sudut', 'skala', 'lebar_konten', 'ketebalan_judul', 'bayangan'];

export function pasangFormTemplate() {
    const form = document.querySelector('form[data-form-template]');
    if (!form) {
        return;
    }

    const pilihJenis = form.querySelector('[data-pilih-jenis]');
    const pilihPengaturan = form.querySelector('[data-pilih-pengaturan]');
    const isianTampilan = form.querySelector('[data-isian-tampilan]');
    const bawaan = JSON.parse(isianTampilan?.dataset.bawaan || '{}');
    // Nilai isian sebelum Default dipilih, dikembalikan bila admin kembali ke Custom sebelum menyimpan.
    let nilaiKustom = null;

    const isi = (nilai) => {
        Object.entries(nilai).forEach(([nama, isiNilai]) => {
            const isian = isianTampilan.querySelector(`[name="${nama}"]`);
            if (isian) {
                isian.value = isiNilai;
                // Kotak hex menyamakan pemilih warna di sebelahnya.
                isian.dispatchEvent(new Event('input'));
            }
        });
    };

    // Default: isian diisi nilai bawaan jenis terpilih lalu dikunci. Fieldset disabled tidak ikut terkirim, jadi
    // server mengisinya lagi dari nilai bawaan yang sama.
    const terapkanPengaturan = () => {
        if (!pilihPengaturan || !isianTampilan) {
            return;
        }

        const pakaiBawaan = pilihPengaturan.value === 'bawaan';
        if (pakaiBawaan) {
            isi(bawaan[pilihJenis?.value] ?? {});
        }
        isianTampilan.disabled = pakaiBawaan;
    };

    // Hanya kelompok isian jenis terpilih yang tampil; isian jenis lain tetap terkirim tetapi dibuang server.
    pilihJenis?.addEventListener('change', () => {
        form.querySelectorAll('[data-khusus-jenis]').forEach((kelompok) => {
            kelompok.hidden = kelompok.dataset.khususJenis !== pilihJenis.value;
        });
        terapkanPengaturan();
    });

    pilihPengaturan?.addEventListener('change', () => {
        if (pilihPengaturan.value === 'bawaan') {
            nilaiKustom = Object.fromEntries([...isianTampilan.querySelectorAll('[name]')].map((isian) => [isian.name, isian.value]));
        } else if (nilaiKustom) {
            isi(nilaiKustom);
            nilaiKustom = null;
        }
        terapkanPengaturan();
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

    // Setelah gagal validasi, isian Default yang tidak terkirim tampil dengan nilai lama; samakan lagi dengan bawaan.
    // Dipanggil setelah pemilih warna terpasang agar ikut tersamakan.
    terapkanPengaturan();

    // Pratinjau: beranda dengan nilai isian saat ini di tab baru, tanpa menyimpan. Isian Default yang terkunci tidak
    // ada di FormData; server mengisinya dari nilai bawaan.
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
