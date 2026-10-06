@extends('layouts.admin', ['breadcrumb' => ['Kas Perusahaan' => null, 'Akun Kas' => null]])

@use('App\Enums\Kas\JenisAkunKas')
@use('App\Support\FormatRupiah')

@section('content')
    <x-admin.page-header title="Akun Kas" pretitle="Kas Perusahaan">
        <a href="{{ route('admin.akun-kas.create') }}" class="btn btn-primary">
            <x-admin.icon name="plus" />
            Tambah Akun
        </a>
    </x-admin.page-header>

    <x-admin.card :flush="true">
        <x-admin.filter-bar :action="route('admin.akun-kas.index')" placeholder="Cari akun, bank, no. rekening…">
            <select name="jenis" class="form-control" style="width:150px;height:32px" aria-label="Filter jenis akun">
                <option value="">Semua jenis</option>
                @foreach (JenisAkunKas::opsi() as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $teks }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="width:150px;height:32px" aria-label="Filter status akun">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>
        </x-admin.filter-bar>

        @if ($akunKas->isEmpty())
            @if (request()->anyFilled(['q', 'jenis', 'status']))
                <x-admin.empty-state title="Akun kas tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada akun kas" description="Tambahkan tempat uang perusahaan disimpan: kas tunai, rekening bank, atau e-wallet.">
                    <a href="{{ route('admin.akun-kas.create') }}" class="btn btn-primary">
                        <x-admin.icon name="plus" />
                        Tambah Akun Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Akun</th>
                            <th>Jenis</th>
                            <th>Bank / No. Rekening</th>
                            <th style="text-align:end">Saldo Awal</th>
                            <th>Status</th>
                            <th style="text-align:end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($akunKas as $akun)
                            <tr>
                                <td class="cell-strong">{{ $akun->nama_akun }}</td>
                                <td><span class="chip chip-{{ $akun->jenis_akun->warna() }}">{{ $akun->jenis_akun->label() }}</span></td>
                                <td>
                                    @if ($akun->nama_bank)
                                        {{ $akun->nama_bank }}
                                        <div class="cell-mono">{{ $akun->nomor_rekening }}</div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="text-align:end;white-space:nowrap">
                                    <span class="cell-mono">{{ FormatRupiah::format($akun->saldo_awal) }}</span>
                                    <div class="card-subtitle">per {{ $akun->tanggal_saldo_awal->translatedFormat('d F Y') }}</div>
                                </td>
                                <td>
                                    @if ($akun->status_aktif)
                                        <span class="status status-green">Aktif</span>
                                    @else
                                        <span class="status status-red">Nonaktif</span>
                                    @endif
                                </td>
                                <td style="text-align:end;white-space:nowrap">
                                    <a href="{{ route('admin.akun-kas.edit', $akun) }}" class="btn btn-sm btn-outline">
                                        <x-admin.icon name="pen-to-square" />
                                        Ubah
                                    </a>
                                    <x-admin.delete-button :action="route('admin.akun-kas.destroy', $akun)"
                                        title="Hapus akun kas?"
                                        :message="'Akun “'.$akun->nama_akun.'” tidak akan tampil lagi di daftar.'" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($akunKas->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$akunKas" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
