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

    {{-- Terapkan bergaya tombol Batal, Reset bergaya tombol Hapus; keduanya sama ukuran (pola paradiplomasi-v2, pilihan pemilik). --}}
    <div class="aksi-filter">
        <button type="submit" class="btn btn-secondary">
            <x-admin.icon name="magnifying-glass" />
            Terapkan
        </button>

        @if ($adaFilter)
            <a href="{{ $action }}" class="btn btn-danger">
                <x-admin.icon name="clock-rotate-left" />
                Reset
            </a>
        @endif
    </div>
</form>
</div>
