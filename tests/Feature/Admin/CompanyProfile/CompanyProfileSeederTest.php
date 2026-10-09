<?php

use App\Models\CompanyProfile\Artikel;
use App\Models\CompanyProfile\Faq;
use App\Models\CompanyProfile\Hero;
use App\Models\CompanyProfile\Identitas;
use App\Models\CompanyProfile\KategoriArtikel;
use App\Models\CompanyProfile\KontakKami;
use App\Models\CompanyProfile\Layanan;
use App\Models\CompanyProfile\Portofolio;
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
        ->and(Hero::count())->toBe(5)
        ->and(Layanan::count())->toBe(5)
        ->and(Portofolio::count())->toBe(6)
        ->and(Faq::count())->toBe(5)
        ->and(KategoriArtikel::count())->toBe(4)
        ->and(Artikel::count())->toBe(6)
        ->and(KontakKami::count())->toBe(0);
});

it('tidak memberi CTA hero tautan ke halaman publik yang belum ada', function () {
    $this->seed(CompanyProfileSeeder::class);

    // Path internal (/portofolio dll.) menghasilkan 404; yang boleh hanya anchor beranda (#…) atau situs lain.
    $url = Hero::all()->flatMap(fn (Hero $hero) => array_column($hero->cta, 'url'));

    expect($url)->not->toBeEmpty()
        ->each(fn ($item) => $item->toMatch('#^(\#[a-z-]+|https://)#'));
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

    expect(Hero::count())->toBe(5)
        ->and(Portofolio::count())->toBe(6)
        ->and(Artikel::count())->toBe(6)
        ->and(Storage::disk('public')->allFiles())->toHaveCount($jumlahBerkas);
});
