@php
    $pengguna = auth()->user();
    // $breadcrumb: ['Label' => url|null, ...]; item terakhir adalah halaman aktif.
    $jejak = ['Dashboard' => route('admin.dashboard')] + $breadcrumb;
@endphp

<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" type="button" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
            <x-admin.icon name="menu" width="20" height="20" />
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
            <svg class="theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            <svg class="theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>

        <div class="tb-user">
            <span class="tb-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</span>
            <span class="tb-user-name">{{ $pengguna->nama }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="tb-logout">
            @csrf
            <button class="tb-btn" type="submit" title="Keluar" aria-label="Keluar">
                <x-admin.icon name="logout" />
            </button>
        </form>
    </div>
</header>
