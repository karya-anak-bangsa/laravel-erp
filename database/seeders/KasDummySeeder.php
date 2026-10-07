<?php

namespace Database\Seeders;

use App\Enums\Kas\JenisAkunKas;
use App\Enums\Kas\JenisTransaksi;
use App\Models\Kas\AkunKas;
use App\Models\Kas\KategoriTransaksi;
use App\Models\Kas\TransaksiKas;
use App\Models\Pengguna;
use App\Services\Kas\TransaksiKasService;
use App\Support\FormatRupiah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * SEMENTARA — data dummy Kas Perusahaan untuk melihat tampilan saat data sudah banyak.
 * Hanya untuk lokal, tidak didaftarkan di DatabaseSeeder. Dihapus bersama datanya
 * setelah Fase 3 selesai (lihat docs/ROADMAP.md).
 *
 * php artisan db:seed --class=KasDummySeeder
 */
class KasDummySeeder extends Seeder
{
    // Penanda akun dummy; dipakai juga untuk mencegah data digandakan saat seeder dijalankan ulang.
    public const PENANDA = 'Data dummy untuk uji tampilan — dihapus setelah Fase 3 selesai.';

    // Berkas bukti dummy diberi awalan ini agar mudah dibersihkan dari storage.
    public const AWALAN_BUKTI = 'dummy-';

    private const JUMLAH_TERHAPUS = 5;

    private TransaksiKasService $transaksiKasService;

    /** @var array<string, AkunKas> */
    private array $akun = [];

    /** @var array<string, KategoriTransaksi> */
    private array $kategori = [];

    /** @var list<array{tanggal: Carbon, akun: string, kategori: string, sen: int, pihak: ?string, keterangan: string}> */
    private array $rencana = [];

    // Omzet jasa per bulan (Ym → sen), dasar setoran PPh Final bulan berikutnya.
    /** @var array<string, int> */
    private array $omzet = [];

    public function run(TransaksiKasService $transaksiKasService): void
    {
        // Data dummy tidak boleh tercampur dengan catatan keuangan asli di server.
        if (app()->isProduction()) {
            throw new RuntimeException('KasDummySeeder hanya untuk lingkungan lokal, tidak boleh dijalankan di produksi.');
        }

        if (AkunKas::withTrashed()->where('keterangan', self::PENANDA)->exists()) {
            $this->command->warn('Data dummy kas sudah ada; seeder tidak dijalankan ulang.');

            return;
        }

        $admin = Pengguna::query()->orderBy('id_pengguna')->first()
            ?? throw new RuntimeException('Belum ada pengguna. Jalankan PenggunaSeeder lebih dulu.');

        $this->transaksiKasService = $transaksiKasService;
        $this->call(KategoriTransaksiSeeder::class);
        $this->kategori = KategoriTransaksi::query()->get()->keyBy('nama_kategori')->all();

        // Seed tetap agar data sama setiap kali dibuat ulang setelah migrate:fresh.
        fake()->seed(2026);

        $mulai = today()->subYear()->startOfMonth();
        $this->buatAkun($mulai);

        for ($bulan = $mulai->copy(), $ke = 0; $bulan->lte(today()); $bulan->addMonth(), $ke++) {
            $this->rencanakanBulan($bulan->copy(), $ke);
        }

        $dibuat = $this->simpanRencana($admin);

        // Transaksi terhapus untuk memastikan soft delete tidak ikut tampil/terhitung.
        // Dipilih dari pengeluaran agar saldo akun tidak menjadi minus.
        $terhapus = collect($dibuat)
            ->filter(fn (TransaksiKas $transaksi) => $transaksi->jenis_transaksi === JenisTransaksi::Pengeluaran)
            ->random(self::JUMLAH_TERHAPUS);
        $terhapus->each(fn (TransaksiKas $transaksi) => $this->transaksiKasService->hapus($transaksi, $admin));

        // Akun lama ditutup setelah transaksinya tercatat (transaksi baru hanya boleh di akun aktif).
        $this->akun['bri']->update(['status_aktif' => false]);

        $jumlahBukti = collect($dibuat)->whereNotNull('bukti_transaksi')->count();
        $this->command->info(sprintf(
            'Dibuat %d akun dan %d transaksi dummy (%d terhapus, %d dengan bukti).',
            count($this->akun), count($dibuat), self::JUMLAH_TERHAPUS, $jumlahBukti,
        ));
    }

    private function buatAkun(Carbon $tanggalSaldoAwal): void
    {
        $daftar = [
            'bca' => ['Rekening BCA Operasional', JenisAkunKas::Bank, 'BCA', '4371029581', 0],
            'mandiri' => ['Rekening Mandiri Pelatihan', JenisAkunKas::Bank, 'Mandiri', '1300028457193', 5_000_000],
            'tunai' => ['Kas Kecil Kantor', JenisAkunKas::Tunai, null, null, 2_500_000],
            'dana' => ['DANA Operasional', JenisAkunKas::EWallet, 'DANA', '081234567890', 1_000_000],
            'bri' => ['Rekening BRI Lama', JenisAkunKas::Bank, 'BRI', '012301004567509', 3_250_000],
        ];

        foreach ($daftar as $kunci => [$nama, $jenis, $bank, $rekening, $saldoAwal]) {
            $this->akun[$kunci] = AkunKas::create([
                'nama_akun' => $nama,
                'jenis_akun' => $jenis,
                'nama_bank' => $bank,
                'nomor_rekening' => $rekening,
                'saldo_awal' => $saldoAwal,
                'tanggal_saldo_awal' => $tanggalSaldoAwal,
                'status_aktif' => true,
                'keterangan' => self::PENANDA,
            ]);
        }
    }

    private function rencanakanBulan(Carbon $bulan, int $ke): void
    {
        $namaBulan = $bulan->translatedFormat('F Y');
        $hariAcak = fn (int $dari = 1, ?int $sampai = null) => fake()->numberBetween($dari, $sampai ?? $bulan->daysInMonth);

        if ($ke === 0) {
            $this->rencanakanBulanPertama($bulan);
        }

        if ($ke === 6) {
            $this->tambah($bulan, 3, 'bca', 'Setoran Modal Pemilik', 25_000_000, 'Pemegang saham', 'Tambahan modal kerja semester kedua');
        }

        // Pengeluaran rutin bulanan.
        $this->tambah($bulan, 5, 'bca', 'Operasional Kantor', 3_500_000, 'Hendra Gunawan', "Sewa ruang kantor & kelas pelatihan bulan {$namaBulan}");
        $this->tambah($bulan, 10, 'bca', 'Operasional Kantor', fake()->numberBetween(45, 85) * 10_000, 'PLN', "Tagihan listrik kantor bulan {$namaBulan}");
        $this->tambah($bulan, 10, 'bca', 'Operasional Kantor', 385_000, 'Biznet', 'Langganan internet kantor 100 Mbps');
        $this->tambah($bulan, 15, 'bca', 'Perangkat Lunak & Lisensi', 525_000, 'Google', 'Google Workspace Business Starter 5 pengguna');
        $this->tambah($bulan, 15, 'bca', 'Perangkat Lunak & Lisensi', fake()->numberBetween(250, 275) * 1_000, 'GitHub', 'GitHub Team 4 pengguna (kurs USD berubah tiap bulan)');
        $this->tambah($bulan, 25, 'bca', 'Honor & Gaji', 4_500_000, 'Siti Rahmawati', "Gaji staf administrasi bulan {$namaBulan}");
        $this->tambah($bulan, 25, 'bca', 'Honor & Gaji', 6_000_000, 'Rizky Pratama', "Gaji programmer junior bulan {$namaBulan}");
        $this->tambah($bulan, $hariAcak(), 'tunai', 'Operasional Kantor', fake()->numberBetween(80, 250) * 1_000, null, 'Air galon & kebutuhan dapur kantor');

        // Biaya admin & jasa giro dicatat di akhir bulan sesuai rekening koran.
        $akhir = $bulan->daysInMonth;
        $this->tambah($bulan, $akhir, 'bca', 'Biaya Administrasi Bank', 17_000, 'Bank BCA', 'Biaya administrasi rekening bulanan');
        $this->tambah($bulan, $akhir, 'mandiri', 'Biaya Administrasi Bank', 12_500, 'Bank Mandiri', 'Biaya administrasi rekening bulanan');
        if ($ke < 4) {
            $this->tambah($bulan, $akhir, 'bri', 'Biaya Administrasi Bank', 6_500, 'Bank BRI', 'Biaya administrasi rekening bulanan');
        }
        // Jasa giro bersen untuk memastikan nominal desimal tampil benar.
        $this->tambah($bulan, $akhir, 'bca', 'Pendapatan Lain-lain', fake()->randomFloat(2, 3_000, 25_000), 'Bank BCA', "Jasa giro rekening bulan {$namaBulan}");

        $omzetBulanLalu = $this->omzet[$bulan->copy()->subMonth()->format('Ym')] ?? 0;
        if ($omzetBulanLalu > 0) {
            $this->tambah($bulan, 15, 'bca', 'Pajak', (int) round($omzetBulanLalu * 0.005) / 100, 'KPP Pratama Bandung Cibeunying',
                'Setoran PPh Final 0,5% masa '.$bulan->copy()->subMonth()->translatedFormat('F Y'));
        }

        // Pemasukan jasa.
        for ($i = fake()->numberBetween(1, 3); $i > 0; $i--) {
            $this->tambahOmzet($bulan, $hariAcak(), 'bca', 'Jasa Pembuatan Website', fake()->numberBetween(30, 250) * 100_000,
                fake()->randomElement(['CV Maju Bersama', 'Klinik Sehat Sentosa', 'Toko Batik Larasati', 'SMK Negeri 2 Bandung', 'Koperasi Sejahtera Mandiri', 'PT Sinar Logistik Nusantara']),
                fake()->randomElement(['Pelunasan pembuatan website company profile', 'DP 50% pembuatan website toko online', 'Termin 2 pengembangan sistem informasi sekolah', 'Maintenance website & perpanjangan dukungan teknis']));
        }

        if (fake()->boolean(40)) {
            $this->tambahOmzet($bulan, $hariAcak(), 'bca', 'Jasa Mobile Apps', fake()->numberBetween(10, 40) * 1_000_000,
                fake()->randomElement(['PT Agro Tani Makmur', 'Yayasan Pendidikan Insan Mulia', 'Rumah Makan Sunda Saung Kuring']),
                fake()->randomElement(['Termin 1 aplikasi Android pemesanan', 'Pelunasan aplikasi mobile absensi karyawan', 'Pengembangan fitur notifikasi aplikasi']));
        }

        for ($i = fake()->numberBetween(1, 2); $i > 0; $i--) {
            $hari = $hariAcak(1, $bulan->daysInMonth - 2);
            $nilai = fake()->numberBetween(20, 120) * 100_000;
            $pelatihan = fake()->randomElement(['Laravel untuk Pemula', 'UI/UX dengan Figma', 'Dasar Jaringan Komputer', 'Microsoft Excel Lanjutan', 'Flutter Mobile Development']);
            $this->tambahOmzet($bulan, $hari, 'mandiri', 'Pelatihan IT', $nilai,
                fake()->randomElement(['Dinas Kominfo Kota Bandung', 'PT Bank Perkreditan Rakyat Jabar', 'Universitas Pasundan', 'PT Inti Persada Teknik']),
                "Pelatihan {$pelatihan} untuk staf");
            $this->tambah($bulan, $hari + 2, 'mandiri', 'Honor & Gaji', (int) round($nilai * 0.3 / 1_000) * 1_000,
                fake()->randomElement(['Dimas Saputra', 'Ayu Lestari', 'Fajar Nugroho']), "Honor instruktur pelatihan {$pelatihan}");
        }

        for ($i = fake()->numberBetween(0, 2); $i > 0; $i--) {
            $this->tambahOmzet($bulan, $hariAcak(), fake()->randomElement(['tunai', 'mandiri']), 'Sertifikasi IT', fake()->numberBetween(15, 100) * 50_000,
                fake()->name(), fake()->randomElement(['Biaya uji kompetensi BNSP Junior Web Developer', 'Sertifikasi Junior Network Administrator', 'Voucher ujian sertifikasi internasional']));
        }

        for ($i = fake()->numberBetween(0, 2); $i > 0; $i--) {
            $peserta = fake()->numberBetween(1, 8);
            $this->tambahOmzet($bulan, $hariAcak(), fake()->randomElement(['mandiri', 'dana']), 'Bootcamp', $peserta * 750_000,
                fake()->randomElement(['Universitas Komputer Indonesia', 'Politeknik Negeri Bandung', 'Telkom University', fake()->name()]),
                'Pembayaran bootcamp '.fake()->randomElement(['Full Stack Web', 'Data Analyst', 'Mobile Developer'])." batch {$bulan->month} — {$peserta} peserta");
        }

        // Pengeluaran tidak rutin.
        for ($i = fake()->numberBetween(1, 3); $i > 0; $i--) {
            $this->tambah($bulan, $hariAcak(), fake()->randomElement(['tunai', 'dana']), 'Transportasi', fake()->numberBetween(25, 350) * 1_000,
                fake()->randomElement(['Gojek', 'Grab', 'SPBU Pertamina', null]),
                fake()->randomElement(['Transport survei lokasi klien', 'Bensin kendaraan operasional', 'Ojek online antar dokumen ke notaris', 'Transport instruktur ke lokasi pelatihan']));
        }

        if (fake()->boolean(50)) {
            [$pihak, $keterangan] = fake()->randomElement([
                ['Meta Platforms', 'Iklan Instagram promosi bootcamp'],
                ['Google Ads', 'Iklan pencarian jasa pembuatan website'],
                ['Percetakan Sinar Grafika', 'Cetak brosur & roll banner pelatihan'],
            ]);
            $this->tambah($bulan, $hariAcak(), 'bca', 'Pemasaran', fake()->numberBetween(5, 30) * 50_000, $pihak, $keterangan);
        }

        if ($ke > 0 && fake()->boolean(20)) {
            [$nilai, $keterangan] = fake()->randomElement([
                [1_250_000, 'SSD eksternal 1 TB untuk backup'],
                [4_800_000, 'Proyektor ruang pelatihan'],
                [850_000, 'Router & switch jaringan kelas'],
            ]);
            $this->tambah($bulan, $hariAcak(), 'bca', 'Perangkat Keras', $nilai, 'Toko Komputer Mega Jaya', $keterangan);
        }

        if (fake()->boolean(30)) {
            $this->tambah($bulan, $hariAcak(), 'bca', 'Domain & Hosting', 189_000, 'Hostinger', 'Domain .com untuk proyek website klien');
        }
    }

    private function rencanakanBulanPertama(Carbon $bulan): void
    {
        $this->tambah($bulan, 1, 'bca', 'Setoran Modal Pemilik', 50_000_000, 'Pemegang saham', 'Setoran modal awal pendirian perusahaan');
        $this->tambah($bulan, 2, 'bca', 'Perizinan & Legalitas', 7_500_000, 'Kantor Notaris Rina Kusuma, S.H., M.Kn.', 'Akta pendirian PT dan pengesahan SK Kemenkumham');
        $this->tambah($bulan, 3, 'bca', 'Perizinan & Legalitas', 1_250_000, 'Konsultan Perizinan OSS', 'Pengurusan NIB dan izin usaha melalui OSS RBA');
        $this->tambah($bulan, 4, 'bca', 'Domain & Hosting', 389_000, 'Hostinger', 'Registrasi domain karyaanakbangsa.co.id 1 tahun');
        $this->tambah($bulan, 4, 'bca', 'Domain & Hosting', 1_899_000, 'Hostinger', 'Hosting Business 12 bulan untuk website perusahaan');
        $this->tambah($bulan, 6, 'bca', 'Perangkat Keras', 14_750_000, 'Toko Komputer Mega Jaya', 'Pembelian 2 unit laptop untuk tim pengembang');
        // Keterangan panjang & tanpa nama pihak untuk menguji tampilan tabel dan detail.
        $this->tambah($bulan, 8, 'tunai', 'Operasional Kantor', 1_150_000, null,
            'Pembelian perlengkapan kantor awal: kertas HVS A4 5 rim, map dan ordner arsip, alat tulis, tinta printer hitam & warna, '
            .'stempel perusahaan, papan tulis kaca untuk ruang pelatihan, serta kotak P3K. Nota dari beberapa toko digabung dalam satu catatan.');
    }

    private function tambahOmzet(Carbon $bulan, int $hari, string $akun, string $kategori, int $jumlah, string $pihak, string $keterangan): void
    {
        if ($this->tambah($bulan, $hari, $akun, $kategori, $jumlah, $pihak, $keterangan)) {
            $kunci = $bulan->format('Ym');
            $this->omzet[$kunci] = ($this->omzet[$kunci] ?? 0) + $jumlah * 100;
        }
    }

    // Mengembalikan false bila tanggalnya belum terjadi (bulan berjalan).
    private function tambah(Carbon $bulan, int $hari, string $akun, string $kategori, int|float $jumlah, ?string $pihak, string $keterangan): bool
    {
        $tanggal = $bulan->copy()->day(min($hari, $bulan->daysInMonth));

        if ($tanggal->gt(today())) {
            return false;
        }

        $this->rencana[] = [
            'tanggal' => $tanggal,
            'akun' => $akun,
            'kategori' => $kategori,
            'sen' => (int) round($jumlah * 100),
            'pihak' => $pihak,
            'keterangan' => $keterangan,
        ];

        return true;
    }

    /**
     * @return list<TransaksiKas>
     */
    private function simpanRencana(Pengguna $admin): array
    {
        // Disimpan urut tanggal agar nomor KM/KK berurutan seperti pencatatan sungguhan.
        usort($this->rencana, fn (array $a, array $b) => $a['tanggal'] <=> $b['tanggal']);

        // Saldo berjalan dalam sen (integer) agar tidak ada galat pembulatan float.
        $saldo = array_map(fn (AkunKas $akun) => (int) round((float) $akun->saldo_awal * 100), $this->akun);
        $dibuat = [];

        foreach ($this->rencana as $r) {
            $kategori = $this->kategori[$r['kategori']]
                ?? throw new RuntimeException("Kategori \"{$r['kategori']}\" tidak ditemukan atau sudah dihapus.");
            $masuk = $kategori->jenis_transaksi === JenisTransaksi::Pemasukan;

            // Pengeluaran yang melebihi saldo akun dilewati agar saldo tidak pernah minus.
            if (! $masuk && $saldo[$r['akun']] < $r['sen']) {
                continue;
            }

            $transaksi = $this->transaksiKasService->simpan([
                'id_akun_kas' => $this->akun[$r['akun']]->id_akun_kas,
                'id_kategori_transaksi' => $kategori->id_kategori_transaksi,
                'tanggal_transaksi' => $r['tanggal']->toDateString(),
                'jumlah' => sprintf('%d.%02d', intdiv($r['sen'], 100), $r['sen'] % 100),
                'nama_pihak' => $r['pihak'],
                'keterangan' => $r['keterangan'],
            ], null, $admin);

            $saldo[$r['akun']] += $masuk ? $r['sen'] : -$r['sen'];

            // Sebagian transaksi berpihak diberi bukti, bergantian gambar & PDF.
            if ($r['pihak'] !== null && fake()->boolean(20)) {
                $this->lampirkanBukti($transaksi, $r['pihak']);
            }

            $dibuat[] = $transaksi;
        }

        return $dibuat;
    }

    private function lampirkanBukti(TransaksiKas $transaksi, string $pihak): void
    {
        $baris = [
            'BUKTI TRANSAKSI (DATA DUMMY)',
            $transaksi->nomor_transaksi,
            'Tanggal : '.$transaksi->tanggal_transaksi->format('d-m-Y'),
            'Pihak   : '.Str::ascii($pihak),
            'Jumlah  : '.FormatRupiah::format($transaksi->jumlah),
            'Bukan dokumen asli - hanya untuk uji tampilan.',
        ];

        $pdf = $transaksi->id_transaksi_kas % 2 === 0;
        $path = TransaksiKasService::FOLDER_BUKTI.'/'.self::AWALAN_BUKTI.Str::uuid().($pdf ? '.pdf' : '.png');

        Storage::disk(TransaksiKasService::DISK_BUKTI)->put($path, $pdf ? $this->isiPdf($baris) : $this->isiPng($baris));
        $transaksi->update(['bukti_transaksi' => $path]);
    }

    /**
     * @param  list<string>  $baris
     */
    private function isiPng(array $baris): string
    {
        $gambar = imagecreatetruecolor(480, 320) ?: throw new RuntimeException('GD gagal membuat gambar bukti.');
        $warna = fn (int $r, int $g, int $b): int => (int) imagecolorallocate($gambar, $r, $g, $b);

        imagefill($gambar, 0, 0, $warna(255, 253, 245));
        imagerectangle($gambar, 10, 10, 469, 309, $warna(180, 170, 150));
        foreach ($baris as $i => $teks) {
            imagestring($gambar, $i === 0 ? 5 : 4, 30, 30 + $i * 40, $teks, $warna(40, 40, 40));
        }

        ob_start();
        imagepng($gambar);

        return (string) ob_get_clean();
    }

    /**
     * PDF satu halaman yang ditulis manual agar tidak perlu paket tambahan.
     *
     * @param  list<string>  $baris
     */
    private function isiPdf(array $baris): string
    {
        $teks = 'BT /F1 14 Tf 60 760 Td 24 TL';
        foreach ($baris as $isi) {
            $teks .= ' ('.addcslashes(Str::ascii($isi), '()\\').') Tj T*';
        }
        $teks .= ' ET';

        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($teks)." >>\nstream\n{$teks}\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $posisi = [];
        foreach ($objek as $i => $isi) {
            $posisi[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$isi}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objek) + 1)."\n0000000000 65535 f \n";
        foreach ($posisi as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objek) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
