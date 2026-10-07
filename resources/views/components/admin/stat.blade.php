@props(['label', 'nilai', 'ikon', 'warna' => 'teal', 'negatif' => false])

{{--
    Kartu angka ringkasan (widget .stat Gentelella). Slot default = keterangan kecil di bawah nilai.
    warna: teal, blue, green, yellow, red, purple. negatif = nilai ditampilkan merah.
--}}
<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="stat">
        <div class="stat-icon {{ $warna }}">
            <x-admin.icon :name="$ikon" />
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value-row">
                <span @class(['stat-value', 'nominal-negatif' => $negatif])>{{ $nilai }}</span>
            </div>
            @if ($slot->isNotEmpty())
                <div class="stat-subtext">{{ $slot }}</div>
            @endif
        </div>
    </div>
</div>
