<?php

use App\Enums\CompanyProfile\BayanganTemplate;
use App\Enums\CompanyProfile\JenisTemplate;

// Token yang hilang di salah satu tingkat membuat var() tidak sah sehingga bayangan atau garis kartu lenyap diam-diam.
it('memberi nama token yang sama di setiap tingkat bayangan', function (JenisTemplate $jenis) {
    $nama = array_keys(BayanganTemplate::Halus->token($jenis));

    foreach (BayanganTemplate::cases() as $tingkat) {
        expect(array_keys($tingkat->token($jenis)))->toBe($nama);
    }
})->with(JenisTemplate::cases());

it('menghapus semua bayangan pada pilihan tanpa', function (JenisTemplate $jenis) {
    $bayangan = array_filter(
        BayanganTemplate::Tanpa->token($jenis),
        fn (string $nama): bool => str_starts_with($nama, '--bayangan-'),
        ARRAY_FILTER_USE_KEY,
    );

    // `0 0 #0000`, bukan `none`: sebagian token dipakai di dalam daftar --tw-shadow.
    expect($bayangan)->not->toBeEmpty()->each->toBe('0 0 #0000');
})->with(JenisTemplate::cases());

// Nilai keluaran build CSS sebelum bayangan pindah ke TemaService (Fase 4 langkah 3b): Halus wajib tetap identik
// dengan tampilan yang disetujui pemilik 2026-10-11.
it('mempertahankan tampilan yang disetujui pada pilihan halus', function () {
    expect(BayanganTemplate::Halus->token(JenisTemplate::FullColor))->toBe([
        '--bayangan-tombol' => '0 1px 2px #0f172a0f, 0 2px 6px -3px #0f172a1a',
        '--bayangan-tombol-garis' => '0 1px 2px 0 #0000000d',
        '--bayangan-isian' => '0 1px 2px 0 #0000000d',
        '--bayangan-lencana' => '0 1px #0000000d',
        '--bayangan-kartu' => '0 1px 2px #0f172a08, 0 6px 16px -10px #0f172a12',
        '--bayangan-kartu-sorot' => '0 1px 2px #0f172a08, 0 12px 24px -14px #0f172a1c',
        '--bayangan-formulir' => '0 1px 2px #0f172a08, 0 6px 16px -10px #0f172a12',
        '--bayangan-gambar' => '0 1px 2px #0f172a08, 0 10px 28px -16px #0f172a24',
        '--bayangan-navbar' => '0 1px 0 #0f172a08, 0 6px 18px -12px #0f172a14',
        '--bayangan-ajakan' => '0 1px 2px #0f172a0a, 0 18px 36px -24px color-mix(in oklab, var(--utama-teks) 16%, transparent)',
        '--bayangan-ikon' => '0 1px 2px #0f172a1f',
    ])
        ->and(BayanganTemplate::Halus->token(JenisTemplate::Monochrome))->toBe([
            '--bayangan-tombol' => '0 0 #0000',
            '--bayangan-tombol-garis' => '0 1px 2px 0 #0000000d',
            '--bayangan-isian' => '0 1px 2px 0 #0000000d',
            '--bayangan-lencana' => '0 1px 2px 0 #0000000d',
            '--bayangan-kartu' => '0 1px 2px 0 #0000000d',
            '--bayangan-formulir' => '0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a',
            '--bayangan-gambar' => '0 1px 3px 0 #0000001a, 0 1px 2px -1px #0000001a',
            '--garis-kartu' => 'color-mix(in oklab, var(--nada-200), var(--nada-300))',
        ]);
});

it('mempertegas bayangan tombol, kartu, dan gambar hero pada pilihan tegas', function (JenisTemplate $jenis) {
    $halus = BayanganTemplate::Halus->token($jenis);
    $tegas = BayanganTemplate::Tegas->token($jenis);

    foreach (['--bayangan-tombol', '--bayangan-kartu', '--bayangan-formulir', '--bayangan-gambar'] as $nama) {
        expect($tegas[$nama])->not->toBe($halus[$nama]);
    }

    // Isian tidak dipertegas agar tidak tampak seperti tombol.
    expect($tegas['--bayangan-isian'])->toBe($halus['--bayangan-isian']);
})->with(JenisTemplate::cases());

it('mempertegas garis kartu Monochrome hanya saat tanpa bayangan', function () {
    expect(BayanganTemplate::Tanpa->token(JenisTemplate::Monochrome)['--garis-kartu'])->toBe('var(--nada-300)')
        ->and(BayanganTemplate::Tegas->token(JenisTemplate::Monochrome)['--garis-kartu'])
        ->toBe(BayanganTemplate::Halus->token(JenisTemplate::Monochrome)['--garis-kartu'])
        ->and(BayanganTemplate::Halus->token(JenisTemplate::FullColor))->not->toHaveKey('--garis-kartu');
});
