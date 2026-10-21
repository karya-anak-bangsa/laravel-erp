@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Manajemen Pengguna' => null]])

@php($akunSaya = auth()->user())

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.filter-bar :action="route('admin.pengguna.index')" />

    <x-admin.card title="Manajemen Pengguna" :flush="true">
        <x-slot:aksi>
            <x-admin.tombol-tambah :href="route('admin.pengguna.create')" />
        </x-slot>

        {{-- Daftar tanpa pencarian tidak pernah kosong karena memuat akun yang sedang login. --}}
        @if ($pengguna->isEmpty())
            <x-admin.empty-state title="Pengguna tidak ditemukan" description="Coba ubah kata kunci pencarian." />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Nama</th>
                            <th>Email</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengguna as $item)
                            <tr>
                                <td class="kolom-pas">
                                    <span class="cell-strong">{{ $item->nama }}</span>
                                    @if ($item->is($akunSaya))
                                        (Anda)
                                    @endif
                                </td>
                                <td>{{ $item->email }}</td>
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
                                        <a href="{{ $item->is($akunSaya) ? route('admin.profil.edit') : route('admin.pengguna.edit', $item) }}" class="btn btn-warning btn-ikon"
                                            title="Ubah" aria-label="Ubah">
                                            <x-admin.icon name="pen-to-square" />
                                        </a>
                                        @if ($item->is($akunSaya))
                                            {{-- Selalu ada satu akun yang bisa login; server juga menolak. --}}
                                            <button type="button" class="btn btn-danger btn-ikon" disabled
                                                title="Akun yang sedang dipakai tidak bisa dihapus" aria-label="Hapus (akun yang sedang dipakai tidak bisa dihapus)">
                                                <x-admin.icon name="trash-can" />
                                            </button>
                                        @else
                                            <x-admin.delete-button :action="route('admin.pengguna.destroy', $item)" :ikon-saja="true"
                                                title="Hapus pengguna?"
                                                :message="'Akun “'.$item->nama.'” tidak bisa dipakai login lagi dan emailnya tidak bisa dipakai akun baru.'" />
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
