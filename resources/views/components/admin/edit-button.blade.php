@props(['href', 'terkunci' => null])

{{--
    Tombol Ubah di kolom aksi tabel: ikon persegi seperti <x-admin.detail-button> dan <x-admin.delete-button>
    ber-:ikon-saja (nama aksi di title & aria-label). :terkunci = alasan tombol dinonaktifkan, tampil di tooltip;
    server tetap harus menolak aksinya.
--}}
@if ($terkunci)
    <button type="button" class="btn btn-warning btn-ikon" disabled
        title="{{ ucfirst($terkunci) }}" aria-label="Ubah ({{ $terkunci }})" {{ $attributes }}>
        <x-admin.icon name="pen-to-square" />
    </button>
@else
    <a href="{{ $href }}" class="btn btn-warning btn-ikon" title="Ubah" aria-label="Ubah" {{ $attributes }}>
        <x-admin.icon name="pen-to-square" />
    </a>
@endif
