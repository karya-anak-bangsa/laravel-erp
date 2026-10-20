@props(['label', 'nilai', 'ikon', 'href', 'warna' => 'teal', 'keterangan' => null])

{{--
    Kartu angka dashboard (komponen stat Gentelella); seluruh kartu menuju daftar modulnya.
    warna: teal, blue, green, yellow, red, purple (kelas .stat-icon Gentelella).
--}}
<a href="{{ $href }}" {{ $attributes->class(['card', 'kartu-statistik']) }}>
    <div class="stat">
        <div class="stat-icon {{ $warna }}"><x-admin.icon :name="$ikon" /></div>
        <div class="stat-content">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value-row"><span class="stat-value">{{ number_format($nilai, 0, ',', '.') }}</span></div>
            @if ($keterangan)
                <div class="stat-subtext">{{ $keterangan }}</div>
            @endif
        </div>
    </div>
</a>
