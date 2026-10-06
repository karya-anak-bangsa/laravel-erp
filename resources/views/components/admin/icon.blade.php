@props(['name'])

@php
    // Path SVG disalin dari ikon Gentelella v4 (src/v4/shell-render.js) agar gaya sidebar konsisten.
    $ikon = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/>',
        'logout' => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'wallet' => '<rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M16 15h2"/><path d="M6 6V4h12v2"/>',
        'arrow-down' => '<path d="M12 5v14"/><path d="M19 12l-7 7-7-7"/>',
        'arrow-up' => '<path d="M12 19V5"/><path d="M5 12l7-7 7 7"/>',
        'receipt' => '<path d="M5 21V3h14v18l-3-2-3 2-3-2-3 2-2-2z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'icon', 'width' => 18, 'height' => 18]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">{!! $ikon[$name] ?? '' !!}</svg>
