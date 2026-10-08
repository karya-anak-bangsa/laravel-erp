@php($pengguna = auth()->user())

<aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-landmark" aria-hidden="true"></i></div>
        <div class="brand-name">ERP System</div>
    </div>

    <nav class="sidebar-nav">
        @foreach (config('menu') as $grup)
            <div class="nav-group">
                <div class="nav-label">{{ $grup['judul'] }}</div>

                @foreach ($grup['item'] as $item)
                    @isset($item['sub'])
                        {{-- Submenu ala Gentelella (.nav-tree): buka-tutupnya dipasang mountShell(); grup yang
                             memuat halaman aktif dirender terbuka. --}}
                        @php($indukAktif = collect($item['sub'])->contains(fn ($anak) => request()->routeIs($anak['aktif'] ?? $anak['rute'])))

                        <div @class(['nav-tree', 'open' => $indukAktif, 'has-active' => $indukAktif])>
                            <button type="button" class="nav-link nav-toggle" aria-expanded="{{ $indukAktif ? 'true' : 'false' }}">
                                <x-admin.icon :name="$item['ikon']" />
                                <span class="nav-text">{{ $item['label'] }}</span>
                                <x-admin.icon name="chevron-right" class="nav-chev" />
                            </button>
                            <div class="nav-sub">
                                <div class="nav-sub-inner">
                                    @foreach ($item['sub'] as $anak)
                                        @php($aktif = request()->routeIs($anak['aktif'] ?? $anak['rute']))

                                        <a @class(['nav-sublink', 'active' => $aktif]) href="{{ route($anak['rute']) }}" @if ($aktif) aria-current="page" @endif>{{ $anak['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @continue
                    @endisset

                    @php($aktif = request()->routeIs($item['aktif'] ?? $item['rute']))

                    <a @class(['nav-link', 'active' => $aktif]) href="{{ route($item['rute']) }}" @if ($aktif) aria-current="page" @endif>
                        <x-admin.icon :name="$item['ikon']" />
                        <span class="nav-text">{{ $item['label'] }}</span>
                        @isset($item['badge'])
                            @php($jumlah = $badgeMenu[$item['badge']] ?? 0)
                            {{-- Tetap dirender saat 0 (hidden) agar admin.js bisa memperbarui angkanya. --}}
                            <span class="badge badge-red" data-badge="{{ $item['badge'] }}" @if ($jumlah === 0) hidden @endif>{{ $jumlah }}</span>
                        @endisset
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
