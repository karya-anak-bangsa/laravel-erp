@props(['href'])

{{--
    Tombol Tambah Data di slot aksi kartu daftar, sama untuk semua halaman daftar (pola paradiplomasi-v2, pilihan
    pemilik). Di ponsel hanya ikon, jadi namanya juga dipasang di title & aria-label.
--}}
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-success tombol-tambah']) }} title="Tambah Data"
    aria-label="Tambah Data">
    <x-admin.icon name="plus" />
    <span class="label-tombol">Tambah Data</span>
</a>
