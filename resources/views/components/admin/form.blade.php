@props([
    'action',
    'method' => 'POST',
    'berkas' => false,
    'title' => 'Simpan perubahan?',
    'message' => 'Pastikan data sudah benar sebelum disimpan.',
])

{{--
    Form tambah/ubah. Konfirmasi SweetAlert2 dipasang resources/js/admin.js lewat atribut data-confirm, yang juga
    menonaktifkan tombol submit setelah dikonfirmasi agar data tidak terkirim dua kali. Form tambah memberi
    title="Simpan <entitas> baru?"; form ubah memberi method="PUT" dan :message berisi nama datanya.
    :berkas="true" untuk form yang mengunggah berkas.
--}}
<form method="POST" action="{{ $action }}" @if ($berkas) enctype="multipart/form-data" @endif novalidate {{ $attributes }}
    data-confirm="{{ $message }}" data-confirm-title="{{ $title }}"
    data-confirm-label="Ya, simpan" data-confirm-variant="success">
    @csrf
    @unless (strtoupper($method) === 'POST')
        @method($method)
    @endunless
    {{ $slot }}
</form>
