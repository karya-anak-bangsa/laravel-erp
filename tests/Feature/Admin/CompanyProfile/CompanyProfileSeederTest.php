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

it('menjalankan seeder company profile ulang tanpa duplikasi data maupun gambar', function () {
    $this->seed(CompanyProfileSeeder::class);
    $jumlahBerkas = count(Storage::disk('public')->allFiles());

    $this->seed(CompanyProfileSeeder::class);

    expect(Hero::count())->toBe(5)
        ->and(Portofolio::count())->toBe(6)
        ->and(Artikel::count())->toBe(6)
        ->and(Storage::disk('public')->allFiles())->toHaveCount($jumlahBerkas);
});
