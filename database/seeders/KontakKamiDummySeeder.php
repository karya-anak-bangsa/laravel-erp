<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\KontakKami;
use Illuminate\Database\Seeder;

class KontakKamiDummySeeder extends Seeder
{
    private const JUMLAH = 30;

    /**
     * Pesan contoh untuk mencoba kotak masuk (filter, tandai dibaca, paginasi) sebelum
     * form kontak publik dibuat di Fase 4. Dijalankan otomatis hanya di lokal.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menggandakan pesan.
        if (KontakKami::withTrashed()->exists()) {
            return;
        }

        $daftarPesan = $this->daftarPesan();

        for ($i = 0; $i < self::JUMLAH; $i++) {
            [$subjek, $pesan] = $daftarPesan[$i % count($daftarPesan)];
            $nama = fake('id_ID')->name();

            KontakKami::create([
                'nama' => $nama,
                'email' => str($nama)->slug('.').'@contoh.id',
                'subjek' => $subjek,
                'pesan' => $pesan,
                // Berjarak ±1 hari agar urutan terbaru di atas mudah dicek.
                'tanggal' => now()->subHours($i * 23 + fake()->numberBetween(0, 5)),
                // Pesan terbaru belum dibaca, sisanya acak.
                'status_baca' => $i >= 5 && fake()->boolean(70),
            ]);
        }
    }

    /**
     * @return list<array{string, string}> [subjek, pesan]
     */
    private function daftarPesan(): array
    {
        return [
            ['Permintaan penawaran website sekolah', "Selamat siang,\n\nKami dari SMK Bina Karya ingin membuat website profil sekolah lengkap dengan halaman PPDB. Mohon informasi estimasi biaya dan lama pengerjaannya.\n\nTerima kasih."],
            ['Pelatihan Laravel untuk tim internal', 'Apakah tersedia pelatihan Laravel untuk 10 orang pengembang di kantor kami? Kami lebih memilih pelatihan tatap muka di Jakarta selama satu minggu.'],
            ['Jadwal bootcamp angkatan berikutnya', 'Halo, saya mahasiswa semester 6 Teknik Informatika. Kapan pendaftaran bootcamp angkatan berikutnya dibuka dan apa saja persyaratannya?'],
            ['Pembuatan aplikasi Android untuk koperasi', "Koperasi kami membutuhkan aplikasi Android untuk cek saldo simpanan dan pengajuan pinjaman anggota.\nBisakah dijadwalkan diskusi minggu depan?"],
            ['Sertifikasi junior web developer', 'Saya ingin mengikuti sertifikasi junior web developer. Apakah ada kelas persiapan sebelum uji kompetensi?'],
            ['Kerja sama program magang', 'Kampus kami tertarik menjalin kerja sama program magang bersertifikat untuk mahasiswa tingkat akhir. Siapa yang bisa kami hubungi untuk pembahasan lebih lanjut?'],
            ['Perbaikan website yang sudah ada', 'Website toko kami sering lambat dan tampilannya berantakan di ponsel. Apakah bisa diperbaiki tanpa membuat ulang dari awal?'],
            ['Pertanyaan biaya pelatihan daring', 'Berapa biaya pelatihan daring untuk perorangan? Apakah peserta mendapat sertifikat setelah selesai?'],
        ];
    }
}
