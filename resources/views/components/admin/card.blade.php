@props(['title' => null, 'subtitle' => null, 'flush' => false])

{{--
    Slot bernama: <x-slot:aksi> (tombol di header kanan), <x-slot:footer>.
    flush = body tanpa padding, untuk tabel atau daftar.
--}}
<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($aksi))
        <div class="card-header">
            <div>
                <div class="card-title">{{ $title }}</div>
                @if ($subtitle)
                    <div class="card-subtitle">{{ $subtitle }}</div>
                @endif
            </div>

            @isset($aksi)
                <div class="card-options">{{ $aksi }}</div>
            @endisset
        </div>
    @endif

    <div @class(['card-body', 'p-0' => $flush])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endisset
</div>
