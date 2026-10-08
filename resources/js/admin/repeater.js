// Repeater: daftar baris isian yang bisa ditambah/dihapus (mis. keyword & CTA hero).
// Markup (lihat admin/company-profile/hero/_form.blade.php):
//   [data-repeater][data-repeater-maks="10"]
//     [data-repeater-daftar] > [data-repeater-baris] (berisi tombol [data-repeater-hapus])
//     [data-repeater-kosong] (opsional, tampil saat tidak ada baris)
//     <template data-repeater-templat> satu baris kosong, indeks ditulis __i__
//     [data-repeater-tambah]
// Nama input berbentuk nama[indeks]...; indeks diurutkan ulang setiap ada perubahan
// agar sama dengan urutan tampil, sehingga pesan error server jatuh ke baris yang benar.
export function pasangRepeater(akar = document) {
    akar.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const daftar = repeater.querySelector('[data-repeater-daftar]');
        const templat = repeater.querySelector('template[data-repeater-templat]');
        const tombolTambah = repeater.querySelector('[data-repeater-tambah]');
        const kosong = repeater.querySelector('[data-repeater-kosong]');
        const maks = Number(repeater.dataset.repeaterMaks) || Infinity;

        if (!daftar || !templat || !tombolTambah) {
            return;
        }

        const perbarui = () => {
            const semuaBaris = daftar.querySelectorAll(':scope > [data-repeater-baris]');

            semuaBaris.forEach((baris, indeks) => {
                baris.querySelectorAll('[name]').forEach((isian) => {
                    isian.name = isian.name.replace(/^([^[]+)\[[^\]]*\]/, `$1[${indeks}]`);
                });
            });

            tombolTambah.disabled = semuaBaris.length >= maks;
            if (kosong) {
                kosong.hidden = semuaBaris.length > 0;
            }
        };

        tombolTambah.addEventListener('click', () => {
            const baris = templat.content.firstElementChild.cloneNode(true);
            daftar.append(baris);
            perbarui();
            baris.querySelector('input, select, textarea')?.focus();
        });

        daftar.addEventListener('click', (event) => {
            const tombolHapus = event.target.closest('[data-repeater-hapus]');
            if (!tombolHapus) {
                return;
            }

            tombolHapus.closest('[data-repeater-baris]').remove();
            perbarui();
        });

        perbarui();
    });
}
