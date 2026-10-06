@php
    $pengguna = auth()->user();
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

    <div class="topbar-right">
        <button class="tb-user" type="button" data-menu="menu-pengguna" aria-haspopup="true" aria-expanded="false"
            title="Menu pengguna">
            <span class="tb-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</span>
            <span class="tb-user-name">{{ $pengguna->nama }}</span>
            <x-admin.icon name="chevron-down" class="tb-user-caret" />
        </button>

        {{-- Isi menu dirender Blade agar logout tetap POST + CSRF; dibuka admin.js lewat openPanel(). --}}
        <template id="menu-pengguna">
            <div class="menu-pengguna-header">
                <div class="menu-pengguna-nama">{{ $pengguna->nama }}</div>
                <div class="menu-pengguna-email">{{ $pengguna->email }}</div>
            </div>
            <div class="menu-separator"></div>
            <button type="button" class="menu-item" data-aksi="ganti-tema">
                <span class="tema-gelap"><x-admin.icon name="moon" /> Mode gelap</span>
                <span class="tema-terang"><x-admin.icon name="sun" /> Mode terang</span>
            </button>
            <div class="menu-separator"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="menu-item menu-item-keluar">
                    <x-admin.icon name="right-from-bracket" />
                    Keluar
                </button>
            </form>
        </template>
    </div>
</header>
