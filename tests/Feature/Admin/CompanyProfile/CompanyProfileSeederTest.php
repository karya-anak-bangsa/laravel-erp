<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KategoriPortofolio;
use App\Models\CompanyProfile\KategoriTutorial;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\MateriTutorial;
use App\Models\CompanyProfile\Portofolio;
use App\Models\CompanyProfile\Template;
use App\Models\CompanyProfile\Tutorial;
use App\Services\CompanyProfile\TemaService;
use Database\Seeders\CompanyProfileSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('mengisi data company profile yang sama di produksi seperti di lokal', function () {
    // Server memakai composer --no-dev, jadi seeder tidak boleh bergantung pada Faker.
    $this->app['env'] = 'production';

    $this->artisan('db:seed', ['--class' => CompanyProfileSeeder::class, '--force' => true])->assertSuccessful();

    expect(Identitas::count())->toBe(1)
        ->and(Hero::count())->toBe(1)
        ->and(Layanan::count())->toBe(5)
        ->and(KategoriPortofolio::count())->toBe(5)
        ->and(Portofolio::count())->toBe(7)
        ->and(Faq::count())->toBe(5)
        ->and(KategoriArtikel::count())->toBe(10)
        ->and(Artikel::count())->toBe(32)
        ->and(KategoriTutorial::count())->toBe(5)
        ->and(Tutorial::count())->toBe(10)
        ->and(MateriTutorial::count())->toBe(40)
        ->and(Template::aktif()->pluck('nama')->all())->toBe(['TKAB Full Color', 'TKAB Monochrome']);
});

it('membuat template aktif yang tampilannya sama dengan cadangan config', function () {
    // Token dari config (database kosong) harus identik dengan token dari template hasil seeder.
    $cadangan = (new TemaService)->tokenCss();

    $this->seed(CompanyProfileSeeder::class);

    expect((new TemaService)->tokenCss())->toBe($cadangan)
        ->and(Template::where('nama', 'TKAB Full Color')->sole()->warna_aksen)->toBe('#cb1839');
});

it('tidak memberi CTA hero tautan mati: hanya anchor seksi beranda yang ada atau situs lain', function () {
    $this->seed(CompanyProfileSeeder::class);

    // Path internal (mis. /layanan) bisa menghasilkan 404, dan anchor seksi yang sudah dihapus (mis. #kontak) tidak
    // menuju ke mana pun.
    $url = Hero::all()->flatMap(fn (Hero $hero) => array_column($hero->cta, 'url'));
    $beranda = $this->get(route('beranda'))->assertOk()->getContent();

    expect($url)->not->toBeEmpty()
        ->each(fn ($item) => $item->toMatch('#^(\#[a-z-]+|https://)#'));

    $url->filter(fn (string $item) => str_starts_with($item, '#'))
        ->each(fn (string $anchor) => expect($beranda)->toContain('id="'.substr($anchor, 1).'"'));
});

it('mengarahkan tombol WhatsApp hero ke nomor yang sama dengan identitas', function () {
    $this->seed(CompanyProfileSeeder::class);

    $whatsapp = collect(Hero::sole()->cta)->pluck('url')->filter(fn (string $url) => str_starts_with($url, 'https://wa.me/'));

    expect($whatsapp)->not->toBeEmpty()
        ->each->toBe(Identitas::sole()->link_whatsapp);
});

it('memakai logo lebar berlatar transparan untuk identitas awal', function () {
    $this->seed(CompanyProfileSeeder::class);

    $logo = Identitas::sole()->logo_website;

    expect($logo)->toEndWith('.webp');
    Storage::disk('public')->assertExists($logo);
});

it('menjalankan seeder company profile ulang tanpa duplikasi data maupun gambar', function () {
    $this->seed(CompanyProfileSeeder::class);
    $jumlahBerkas = count(Storage::disk('public')->allFiles());

    $this->seed(CompanyProfileSeeder::class);

    expect(Hero::count())->toBe(1)
        ->and(Portofolio::count())->toBe(7)
        ->and(Artikel::count())->toBe(32)
        ->and(MateriTutorial::count())->toBe(40)
        ->and(Template::count())->toBe(2)
        ->and(Storage::disk('public')->allFiles())->toHaveCount($jumlahBerkas);
});
