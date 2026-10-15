@use('App\Support\Seo')
{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
{{-- Deklarasi XML wajib di awal dokumen: jangan menaruh apa pun di atasnya. Peta situs dari SeoController; lastmod
     hanya untuk halaman yang tanggal perubahannya pasti, yaitu artikel, daftarnya, dan kategorinya (berubah bila nama
     kategori atau salah satu artikel terbitnya diubah). --}}
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
</urlset>
