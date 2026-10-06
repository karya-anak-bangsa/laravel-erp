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
        <x-admin.icon name="trash-can" />
        {{ $label }}
    </button>
</form>
