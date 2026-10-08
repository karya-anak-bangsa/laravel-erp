<?php

namespace Database\Seeders;

use App\Models\CompanyProfile\Faq;
use App\Support\TeksHtml;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Lima FAQ awal seputar layanan perusahaan; isinya bisa diubah admin lewat panel.
     */
    public function run(): void
    {
        // Termasuk yang terhapus agar seeder aman dijalankan ulang tanpa menimpa isian admin.
        if (Faq::withTrashed()->exists()) {
            return;
        }

        foreach ($this->daftarFaq() as $urutan => [$pertanyaan, $jawaban]) {
            Faq::create([
                'pertanyaan' => $pertanyaan,
                'jawaban' => TeksHtml::dariTeksPolos($jawaban),
                'urutan_ke' => $urutan + 1,
            ]);
        }
    }

    /**
     * @return list<array{string, string}> [pertanyaan, jawaban]
     */
    private function daftarFaq(): array
    {
        return [
            [
                'Bagaimana cara memesan pembuatan website atau mobile apps?',
                'Hubungi kami melalui halaman kontak atau WhatsApp, lalu ceritakan kebutuhan Anda. Tim kami akan menjadwalkan diskusi, menyusun penawaran beserta lingkup pekerjaan, dan memulai pengerjaan setelah penawaran disetujui.',
            ],
            [
                'Berapa lama proses pembuatan website?',
                'Lama pengerjaan bergantung pada jumlah halaman dan fitur yang dibutuhkan. Perkiraan waktu dicantumkan di penawaran sehingga Anda dapat merencanakan peluncuran sejak awal.',
            ],
            [
                'Apakah pelatihan IT bisa diselenggarakan khusus untuk perusahaan atau instansi?',
                'Bisa. Materi, jadwal, dan tempat pelatihan dapat disesuaikan dengan kebutuhan tim Anda, baik secara tatap muka maupun daring.',
            ],
            [
                'Apa yang didapatkan peserta sertifikasi IT?',
                'Peserta mendapatkan materi persiapan, simulasi uji, dan pendampingan hingga pelaksanaan uji kompetensi. Peserta yang dinyatakan kompeten memperoleh sertifikat keahlian sesuai skema yang diikuti.',
            ],
            [
                'Siapa saja yang dapat mengikuti bootcamp mahasiswa?',
                'Bootcamp terbuka untuk mahasiswa aktif yang ingin menyiapkan diri berkarier di bidang IT. Informasi jadwal angkatan dan persyaratan pendaftaran diumumkan melalui website dan media sosial kami.',
            ],
        ];
    }
}
