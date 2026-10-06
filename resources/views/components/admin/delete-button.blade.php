@props([
    'action',
    'label' => 'Hapus',
    'title' => 'Hapus data ini?',
    'message' => 'Data yang dihapus tidak akan tampil lagi di daftar.',
])

{{-- Konfirmasi modal dipasang oleh resources/js/admin.js lewat atribut data-confirm. --}}
<form method="POST" action="{{ $action }}" style="display:inline"
    data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-label="Ya, hapus"
    {{ $attributes }}>
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline" title="{{ $label }}">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2.5 4h11M6 4V2.5h4V4M4 4l.7 9.5h6.6L12 4"/></svg>
        {{ $label }}
    </button>
</form>
