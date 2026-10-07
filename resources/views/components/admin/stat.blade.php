@props(['label', 'nilai', 'ikon', 'warna' => 'teal', 'negatif' => false])

{{--
    Kartu angka ringkasan (widget .stat Gentelella). Slot default = keterangan kecil di bawah nilai.
    warna: teal, blue, green, yellow, red, purple. negatif = nilai ditampilkan merah.
    Nilai panjang (mis. Rp 534.196.835,22) diperkecil agar tetap satu baris di kartu 4 kolom.
--}}
@php($panjang = mb_strlen((string) $nilai) > 18)
<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="stat">
        <div class="stat-icon {{ $warna }}">
            <x-admin.icon :name="$ikon" />
        </div>
        <div class="stat-content">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value-row">
                <span @class(['stat-value', 'stat-value-panjang' => $panjang, 'nominal-negatif' => $negatif])>{{ $nilai }}</span>
            </div>
            @if ($slot->isNotEmpty())
                <div class="stat-subtext">{{ $slot }}</div>
            @endif
        </div>
    </div>
</div>
