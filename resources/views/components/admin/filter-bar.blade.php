@props(['action', 'placeholder' => 'Cari Data'])

@php
    // Tombol reset hanya muncul bila ada pencarian/filter aktif (parameter page diabaikan).
    $adaFilter = collect(request()->except('page'))->filter(fn ($nilai) => filled($nilai))->isNotEmpty();
@endphp

{{--
    Pencarian (?q=) + filter server-side di bagian atas <x-admin.card :flush="true">.
    Slot default = filter tambahan, mis. <select class="form-control" name="jenis">.
    Inline style mengikuti markup demo Gentelella (users-filters) agar tidak perlu build aset.
--}}
<form method="GET" action="{{ $action }}" role="search" class="users-filters"
    style="padding:12px 16px;border-bottom:1px solid var(--border-color-light)">
    <div class="search-box">
        <x-admin.icon name="magnifying-glass" class="s-icon" />
        <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
    </div>

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
