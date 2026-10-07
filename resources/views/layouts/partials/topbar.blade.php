@php
    // $breadcrumb: ['Label' => url|null, ...]; item terakhir adalah halaman aktif.
    $jejak = ['Dashboard' => route('admin.dashboard')] + $breadcrumb;
@endphp

<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" type="button" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
            <x-admin.icon name="bars" />
        </button>

        <nav class="breadcrumb" aria-label="Breadcrumb">
            @foreach ($jejak as $label => $url)
                @unless ($loop->first)
                    <span class="sep" aria-hidden="true">›</span>
                @endunless

                @if ($loop->last)
                    <span class="current" aria-current="page">{{ $label }}</span>
                @elseif ($url)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    <span>{{ $label }}</span>
                @endif
            @endforeach
        </nav>
    </div>

    {{-- Menu pengguna (logout) ada di sidebar-footer (layouts/partials/sidebar). --}}
</header>
