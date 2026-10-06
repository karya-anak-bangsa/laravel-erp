@props(['title' => 'Belum ada data', 'description' => null])

{{-- Slot default = tombol aksi, mis. "Tambah data pertama". --}}
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-state-icon">
        <x-admin.icon name="folder-open" />
    </div>
    <div class="empty-state-title">{{ $title }}</div>
    @if ($description)
        <div class="empty-state-text">{{ $description }}</div>
    @endif
    @if ($slot->isNotEmpty())
        <div class="empty-state-actions">{{ $slot }}</div>
    @endif
</div>
