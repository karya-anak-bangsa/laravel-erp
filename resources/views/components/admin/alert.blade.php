@props(['variant' => 'info'])

{{-- variant: info, success, warning, error --}}
<div {{ $attributes->merge(['class' => 'alert alert-'.$variant, 'role' => $variant === 'error' ? 'alert' : 'status']) }}>
    <svg class="alert-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        @if ($variant === 'success')
            <circle cx="8" cy="8" r="6"/><path d="M5.5 8l2 2 3-4"/>
        @elseif ($variant === 'info')
            <circle cx="8" cy="8" r="6"/><path d="M8 5v.01M8 7v4"/>
        @else
            <path d="M8 1.5l6.5 12h-13L8 1.5z"/><path d="M8 6v3.5M8 11.5v.01"/>
        @endif
    </svg>
    <div class="alert-body">{{ $slot }}</div>
</div>
