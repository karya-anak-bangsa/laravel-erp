@props(['batal'])

{{-- Bilah tombol di bawah form tambah/ubah: Simpan di kiri, Batal (kembali ke alamat :batal) di kanan. --}}
<div class="form-actions">
    <button type="submit" class="btn btn-success">
        <x-admin.icon name="floppy-disk" />
        Simpan
    </button>
    <a href="{{ $batal }}" class="btn btn-secondary">
        <x-admin.icon name="rotate-left" />
        Batal
    </a>
</div>
