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
        {{-- Kelas .more-btn bawaan sengaja tidak dipakai karena Gentelella mengikatnya ke menu demo. --}}
        <button class="sidebar-user" type="button" data-menu="menu-pengguna" aria-haspopup="true" aria-expanded="false"
            title="Menu pengguna">
            <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($pengguna->nama, 0, 1)) }}</span>
            <span class="sidebar-user-info">
                <span class="name">{{ $pengguna->nama }}</span>
                <span class="role">Administrator</span>
            </span>
            <x-admin.icon name="chevron-up" class="sidebar-user-caret" />
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
</aside>
