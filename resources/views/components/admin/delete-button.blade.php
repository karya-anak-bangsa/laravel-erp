@props([
    'action',
    'label' => 'Hapus',
    'title' => 'Hapus data ini?',
    'message' => 'Data yang dihapus tidak akan tampil lagi di daftar.',
    'ikonSaja' => false,
])

{{--
    Konfirmasi modal dipasang oleh resources/js/admin.js lewat atribut data-confirm.
    :ikon-saja="true" = tombol ikon persegi 32px tanpa teks, setinggi tombol Tambah
    (label tetap di tooltip & aria-label).
--}}
<form method="POST" action="{{ $action }}" style="display:inline"
    data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-label="Ya, hapus"
    {{ $attributes }}>
    @csrf
    @method('DELETE')
    <button type="submit" @class(['btn', 'btn-sm' => ! $ikonSaja, 'btn-danger', 'btn-ikon' => $ikonSaja]) title="{{ $label }}"
        @if ($ikonSaja) aria-label="{{ $label }}" @endif>
        <x-admin.icon name="trash-can" />
        @unless ($ikonSaja)
            {{ $label }}
        @endunless
    </button>
</form>
