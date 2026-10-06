@props(['variant' => 'info'])

@php
    $ikon = match ($variant) {
        'success' => 'circle-check',
        'info' => 'circle-info',
        default => 'triangle-exclamation',
    };
@endphp

{{-- variant: info, success, warning, error --}}
<div {{ $attributes->merge(['class' => 'alert alert-'.$variant, 'role' => $variant === 'error' ? 'alert' : 'status']) }}>
    <x-admin.icon class="alert-icon" :name="$ikon" />
    <div class="alert-body">{{ $slot }}</div>
</div>
