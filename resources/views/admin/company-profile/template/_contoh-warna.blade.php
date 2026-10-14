@use('App\Enums\CompanyProfile\JenisTemplate')

{{-- Contoh warna template ($contoh): warna utama & aksen (Full Color) atau tingkat 200/500/900 nada dasar (Monochrome). --}}
<span class="contoh-template" aria-hidden="true">
    @if ($contoh->jenis === JenisTemplate::FullColor)
        <span style="background: {{ $contoh->warna_utama }}"></span>
        <span style="background: {{ $contoh->warna_aksen }}"></span>
    @else
        @foreach ([2, 5, 9] as $tingkat)
            <span style="background: {{ $contoh->nada_dasar?->skala()[$tingkat] }}"></span>
        @endforeach
    @endif
</span>
