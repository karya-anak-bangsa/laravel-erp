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
        <button class="tb-btn theme-toggle" type="button" title="Ganti tema" aria-label="Ganti tema terang/gelap" aria-pressed="false">
            <x-admin.icon name="sun" class="theme-icon-light" />
            <x-admin.icon name="moon" class="theme-icon-dark" />
        </button>

        <div class="tb-user">
            <span class="tb-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</span>
            <span class="tb-user-name">{{ $pengguna->nama }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="tb-logout">
            @csrf
            <button class="tb-btn" type="submit" title="Keluar" aria-label="Keluar">
                <x-admin.icon name="right-from-bracket" />
            </button>
        </form>
    </div>
</header>
