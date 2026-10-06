@php($pengguna = auth()->user())

<aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-landmark" aria-hidden="true"></i></div>
        <div class="brand-name">ERP TKAB</div>
    </div>

    <nav class="sidebar-nav">
        @foreach (config('menu') as $grup)
            <div class="nav-group">
                <div class="nav-label">{{ $grup['judul'] }}</div>

                @foreach ($grup['item'] as $item)
                    @php($aktif = request()->routeIs($item['aktif'] ?? $item['rute']))

                    <a @class(['nav-link', 'active' => $aktif]) href="{{ route($item['rute']) }}" @if ($aktif) aria-current="page" @endif>
                        <x-admin.icon :name="$item['ikon']" />
                        <span class="nav-text">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</div>
            <div class="sidebar-user-info">
                <div class="name">{{ $pengguna->nama }}</div>
                <div class="role">Administrator</div>
            </div>
        </div>
    </div>
</aside>
