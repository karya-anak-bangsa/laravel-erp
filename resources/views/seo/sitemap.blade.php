@use('App\Support\Seo')
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
{{-- Deklarasi XML wajib di awal dokumen: jangan menaruh apa pun di atasnya. Peta situs dari SeoController; lastmod
     hanya untuk halaman yang tanggal perubahannya pasti, yaitu artikel, daftarnya, dan kategorinya (berubah bila nama
     kategori atau salah satu artikel terbitnya diubah), portofolio dan daftarnya, serta tutorial, daftarnya, dan
     materinya. --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ Seo::alamat() }}</loc>
    </url>
    <url>
        <loc>{{ Seo::alamat(route('artikel.index', [], false)) }}</loc>
@if ($artikel->isNotEmpty())
        <lastmod>{{ $artikel->max('updated_at')->toAtomString() }}</lastmod>
@endif
    </url>
@foreach ($kategori as $item)
    <url>
        <loc>{{ Seo::alamat(route('kategori-artikel.show', $item, false)) }}</loc>
        <lastmod>{{ $artikel->where('id_kategori_artikel', $item->id_kategori_artikel)->max('updated_at')->max($item->updated_at)->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach ($artikel as $item)
    <url>
        <loc>{{ Seo::alamat(route('artikel.show', $item, false)) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
    <url>
        <loc>{{ Seo::alamat(route('portofolio.index', [], false)) }}</loc>
@if ($portofolio->isNotEmpty())
        <lastmod>{{ $portofolio->max('updated_at')->toAtomString() }}</lastmod>
@endif
    </url>
@foreach ($portofolio as $item)
    <url>
        <loc>{{ Seo::alamat(route('portofolio.show', $item, false)) }}</loc>
        <lastmod>{{ $item->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@php
    // Halaman tutorial memuat pengantar dan judul semua materinya, jadi ikut berubah bila salah satu materinya diubah.
    $perubahanTutorial = $tutorial->mapWithKeys(fn ($item) => [$item->id_tutorial => $item->materiTutorial->max('updated_at')->max($item->updated_at)]);
@endphp
    <url>
        <loc>{{ Seo::alamat(route('tutorial.index', [], false)) }}</loc>
@if ($tutorial->isNotEmpty())
        <lastmod>{{ $perubahanTutorial->max()->toAtomString() }}</lastmod>
@endif
    </url>
@foreach ($tutorial as $item)
    <url>
        <loc>{{ Seo::alamat(route('tutorial.show', $item, false)) }}</loc>
        <lastmod>{{ $perubahanTutorial[$item->id_tutorial]->toAtomString() }}</lastmod>
    </url>
@foreach ($item->materiTutorial as $materi)
    <url>
        <loc>{{ Seo::alamat(route('materi-tutorial.show', [$item, $materi], false)) }}</loc>
        <lastmod>{{ $materi->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@endforeach
</urlset>
