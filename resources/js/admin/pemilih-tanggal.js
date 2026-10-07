// Pemilih tanggal seragam untuk semua <input type="date">. Input bawaan browser
// menampilkan format menurut bahasa browser (Chrome yyyy-mm-dd, Firefox mm/dd/yyyy),
// jadi tampilannya diganti flatpickr berformat dd/mm/yyyy. Nilai yang dikirim ke
// server tetap Y-m-d, sehingga validasi & filter tidak berubah. Tanpa JavaScript
// input bawaan browser tetap berfungsi. Sengaja diimpor statis (±14 kB gzip): impor
// dinamis membuat input bawaan sempat tampil sekejap sebelum diganti.
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/esm/l10n/id.js';

export function pasangPemilihTanggal() {
    document.querySelectorAll('input[type="date"]').forEach((input) => {
        const pemilih = flatpickr(input, {
            locale: Indonesian,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            // Tanggal boleh diketik (dd/mm/yyyy), bukan hanya dipilih dari kalender.
            allowInput: true,
            // Di ponsel flatpickr bawaannya kembali ke input browser; dimatikan agar format tetap sama.
            disableMobile: true,
            minDate: input.min || null,
            maxDate: input.max || null,
        });

        // flatpickr hanya menyalin kelas & required ke input tampilan; sisanya disalin di sini
        // agar ukuran inline, label (for=id), dan teks aksesibilitas tetap berlaku.
        const tampilan = pemilih.altInput;
        tampilan.style.cssText = input.style.cssText;
        tampilan.placeholder = 'dd/mm/yyyy';
        tampilan.autocomplete = 'off';
        ['aria-label', 'title'].forEach((atribut) => {
            if (input.hasAttribute(atribut)) {
                tampilan.setAttribute(atribut, input.getAttribute(atribut));
            }
        });
        if (input.id) {
            tampilan.id = input.id;
            input.removeAttribute('id');
        }
    });
}
