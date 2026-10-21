@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Manajemen Pengguna' => null]])

{{-- Bentuk blok karena berkas ini juga memuat blok php di dalam loop; Blade salah mengurai bila bentuk sebaris
     dan bentuk blok bercampur. --}}
@php
    $akunSaya = auth()->user();
@endphp

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.filter-bar :action="route('admin.pengguna.index')" />

    <x-admin.card title="Manajemen Pengguna" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.pengguna.create')" />
        </x-slot>

        {{-- Daftar tanpa pencarian tidak pernah kosong karena memuat akun yang sedang login. Akun terhapus ikut tercantum
             sebagai Nonaktif dan bisa diaktifkan kembali (pilihan pemilik). --}}
        @if ($pengguna->isEmpty())
            <x-admin.empty-state title="Pengguna tidak ditemukan" description="Coba ubah kata kunci pencarian." />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Nama</th>
                            <th class="kolom-pas">Email</th>
                            <th>Peran</th>
                            <th class="kolom-status">Status</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengguna as $item)
                            @php
                                // Alasan tombol Ubah dinonaktifkan; server juga menolaknya.
                                $alasanUbahTerkunci = match (true) {
                                    $item->is($akunSaya) => 'akun Anda diubah lewat menu pengguna › Profil Saya',
                                    $item->trashed() => 'akun nonaktif tidak bisa diubah sebelum diaktifkan kembali',
                                    default => null,
                                };
                            @endphp
                            <tr>
                                <td class="kolom-pas">
                                    <span class="cell-strong">{{ $item->nama }}</span>
                                    @if ($item->is($akunSaya))
                                        (Anda)
                                    @endif
                                </td>
                                <td class="kolom-pas">{{ $item->email }}</td>
                                <td><span class="chip {{ $item->peran->kelasChip() }}">{{ $item->peran->label() }}</span></td>
                                <td class="kolom-status">
                                    @if ($item->trashed())
                                        <span class="chip chip-red">Nonaktif</span>
                                    @else
                                        <span class="chip chip-green">Aktif</span>
                                    @endif
                                </td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Pengguna" :ikon-saja="true">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Nama</th>
                                                            <td class="cell-strong">{{ $item->nama }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Email</th>
                                                            <td>{{ $item->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Peran</th>
                                                            <td><span class="chip {{ $item->peran->kelasChip() }}">{{ $item->peran->label() }}</span></td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Status</th>
                                                            <td>
                                                                @if ($item->trashed())
                                                                    <span class="chip chip-red">Nonaktif</span>
                                                                @else
                                                                    <span class="chip chip-green">Aktif</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Dibuat</th>
                                                            <td>{{ $item->created_at->translatedFormat('d F Y, H:i') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Diperbarui</th>
                                                            <td>{{ $item->updated_at->translatedFormat('d F Y, H:i') }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </x-admin.detail-button>
                                        @if ($alasanUbahTerkunci)
                                            <button type="button" class="btn btn-warning btn-ikon" disabled
                                                title="{{ ucfirst($alasanUbahTerkunci) }}" aria-label="Ubah ({{ $alasanUbahTerkunci }})">
                                                <x-admin.icon name="pen-to-square" />
                                            </button>
                                        @else
                                            <a href="{{ route('admin.pengguna.edit', $item) }}" class="btn btn-warning btn-ikon" title="Ubah" aria-label="Ubah">
                                                <x-admin.icon name="pen-to-square" />
                                            </a>
                                        @endif
                                        @if ($item->is($akunSaya))
                                            {{-- Selalu ada satu akun yang bisa login; server juga menolak. --}}
                                            <button type="button" class="btn btn-danger btn-ikon" disabled
                                                title="Akun yang sedang dipakai tidak bisa dihapus" aria-label="Hapus (akun yang sedang dipakai tidak bisa dihapus)">
                                                <x-admin.icon name="trash-can" />
                                            </button>
                                        @elseif ($item->trashed())
                                            {{-- Menggantikan tombol Hapus: akun terhapus = Nonaktif, jadi kebalikannya = mengaktifkan kembali. --}}
                                            <form method="POST" action="{{ route('admin.pengguna.aktifkan', $item) }}" style="display:inline"
                                                data-confirm="Akun “{{ $item->nama }}” bisa dipakai login lagi dengan email dan kata sandi lamanya."
                                                data-confirm-title="Aktifkan kembali pengguna?" data-confirm-label="Ya, aktifkan"
                                                data-confirm-variant="success">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-ikon" title="Aktifkan kembali" aria-label="Aktifkan kembali">
                                                    <x-admin.icon name="user-check" />
                                                </button>
                                            </form>
                                        @else
                                            <x-admin.delete-button :action="route('admin.pengguna.destroy', $item)" :ikon-saja="true"
                                                title="Hapus pengguna?"
                                                :message="'Akun “'.$item->nama.'” tidak bisa dipakai login lagi sampai diaktifkan kembali. Emailnya tidak bisa dipakai akun baru.'" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($pengguna->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$pengguna" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
