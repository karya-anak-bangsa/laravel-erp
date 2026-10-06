@props(['title', 'pretitle' => null])

{{-- Slot default = tombol aksi di kanan judul (mis. "Tambah"). --}}
<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header-row">
        <div>
            @if ($pretitle)
                <div class="page-pretitle">{{ $pretitle }}</div>
            @endif
            <h1 class="page-title">{{ $title }}</h1>
        </div>

        @if ($slot->isNotEmpty())
            <div class="page-actions">{{ $slot }}</div>
        @endif
    </div>
</div>
