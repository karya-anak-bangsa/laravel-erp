@use('App\Enums\Sistem\AksiAktivitas')

@extends('layouts.admin', ['breadcrumb' => ['Pengaturan Sistem' => null, 'Log Aktivitas' => null]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.filter-bar :action="route('admin.log-aktivitas.index')">
        <select name="pengguna" class="form-control kolom-filter" aria-label="Filter pengguna">
            <option value="">Semua pengguna</option>
            @foreach ($opsiPengguna as $id => $nama)
                <option value="{{ $id }}" @selected(request('pengguna') === (string) $id)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="aktivitas" class="form-control kolom-filter" aria-label="Filter aktivitas">
            <option value="">Semua aktivitas</option>
            @foreach (AksiAktivitas::opsi() as $nilai => $label)
                <option value="{{ $nilai }}" @selected(request('aktivitas') === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </x-admin.filter-bar>

    {{-- Hanya daftar baca tanpa tombol tambah, ubah, atau hapus: log ditulis otomatis dan jejaknya tidak bisa dihapus. --}}
    <x-admin.card title="Log Aktivitas" :flush="true">
        @if ($logAktivitas->isEmpty())
            @if (request()->anyFilled(['q', 'pengguna', 'aktivitas']))
                <x-admin.empty-state title="Aktivitas tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada aktivitas"
                    description="Perubahan data serta masuk dan keluar panel admin akan tercatat di sini." />
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="kolom-pas">Waktu</th>
                            <th class="kolom-pas">Pengguna</th>
                            <th class="kolom-pas">Aktivitas</th>
                            <th class="kolom-pas">Data</th>
                            <th class="kolom-lebar">Nama Data</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logAktivitas as $item)
                            <tr>
                                <td class="kolom-pas">{{ $item->created_at->translatedFormat('d F Y, H:i') }}</td>
                                <td class="kolom-pas">{{ $item->pengguna->nama }}</td>
                                <td class="kolom-pas">{{ $item->aksi->label() }}</td>
                                <td class="kolom-pas">{{ $item->jenis_data ?? '—' }}</td>
                                <td>
                                    {{-- Nama saat aksi terjadi; masuk & keluar tidak menyangkut data. --}}
                                    @if ($item->label_subjek === null)
                                        —
                                    @else
                                        <div class="cell-strong">{{ $item->label_subjek }}</div>
                                        @if ($item->perubahan)
                                            <div class="card-subtitle ringkasan-tabel">{{ implode(', ', $item->perubahan) }}</div>
                                        @endif
                                    @endif
                                </td>
                                {{-- Alamat IP hanya di modal: alamat IPv6 terlalu lebar untuk kolom tabel (pilihan pemilik). --}}
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <x-admin.detail-button title="Detail Log Aktivitas" :ikon-saja="true">
                                            <div class="tabel-detail">
                                                <table class="table tabel-informasi">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Waktu</th>
                                                            <td>{{ $item->created_at->translatedFormat('d F Y, H:i:s') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Pengguna</th>
                                                            <td class="cell-strong">{{ $item->pengguna->nama }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Email</th>
                                                            <td>{{ $item->pengguna->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Aktivitas</th>
                                                            <td>{{ $item->aksi->label() }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Data</th>
                                                            <td>{{ $item->jenis_data ?? '—' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Nama data</th>
                                                            <td>{{ $item->label_subjek ?? '—' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Isian diubah</th>
                                                            <td>{{ $item->perubahan ? implode(', ', $item->perubahan) : '—' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Alamat IP</th>
                                                            <td>{{ $item->alamat_ip ?? '—' }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </x-admin.detail-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($logAktivitas->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$logAktivitas" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
