@props(['title' => 'Belum ada data', 'description' => null])

{{-- Slot default = tombol aksi, mis. "Tambah data pertama". --}}
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 7a2 2 0 012-2h4l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
    </div>
    <div class="empty-state-title">{{ $title }}</div>
    @if ($description)
        <div class="empty-state-text">{{ $description }}</div>
    @endif
    @if ($slot->isNotEmpty())
        <div class="empty-state-actions">{{ $slot }}</div>
    @endif
</div>
