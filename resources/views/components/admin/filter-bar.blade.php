@props(['action', 'placeholder' => 'Cari Data', 'cari' => true])

@php
    // Tombol reset hanya muncul bila ada pencarian/filter aktif (parameter page diabaikan).
    $adaFilter = collect(request()->except('page'))->filter(fn ($nilai) => filled($nilai))->isNotEmpty();
@endphp

{{--
    Pencarian (?q=) + filter server-side dalam kartu tersendiri, diletakkan tepat di bawah
    page-header dan di atas kartu tabel (pilihan pemilik).
    Slot default = filter tambahan, mis. <select class="form-control kolom-filter" name="jenis">
    (kelas kolom-filter = selebar col-3, sama dengan kotak pencarian).
    :cari="false" = hanya filter, tanpa kotak pencarian (mis. laporan).
--}}
<div class="card kartu-filter">
<form method="GET" action="{{ $action }}" role="search" class="users-filters">
    @if ($cari)
        <div class="search-box kolom-filter">
            <x-admin.icon name="magnifying-glass" class="s-icon" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
        </div>
    @endif

    {{ $slot }}

    <button type="submit" class="btn btn-outline">
        <x-admin.icon name="filter" />
        Terapkan
    </button>

    @if ($adaFilter)
        <a href="{{ $action }}" class="btn btn-ghost">
            <x-admin.icon name="xmark" />
            Reset
        </a>
    @endif
</form>
</div>
