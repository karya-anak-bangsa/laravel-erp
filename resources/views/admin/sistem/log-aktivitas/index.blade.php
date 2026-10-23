@use('App\Enums\Sistem\AksiAktivitas')
@use('App\Models\Sistem\LogAktivitas')

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

    {{-- Log ditulis otomatis, jadi tanpa tombol tambah, ubah, atau hapus per baris; dari panel hanya bisa dikosongkan
         seluruhnya, dan pengosongannya tercatat sebagai satu baris baru (pilihan pemilik). --}}
    <x-admin.card title="Log Aktivitas" :subtitle="'Aktivitas '.LogAktivitas::MASA_SIMPAN_HARI.' hari terakhir; yang lebih lama dihapus otomatis.'"
        :flush="true">
        @if ($jumlahLog > 0)
            <x-slot:aksi>
                <form method="POST" action="{{ route('admin.log-aktivitas.kosongkan') }}" style="display:inline"
                    data-confirm="Sebanyak {{ $jumlahLog }} log aktivitas akan dihapus permanen dan tidak bisa dikembalikan. Pengosongan ini tetap tercatat sebagai satu log baru."
                    data-confirm-title="Kosongkan log aktivitas?" data-confirm-label="Ya, kosongkan">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger tombol-ikon-ponsel" title="Kosongkan Log" aria-label="Kosongkan Log">
                        <x-admin.icon name="trash-can" />
                        <span class="label-tombol">Kosongkan Log</span>
                    </button>
                </form>
            </x-slot>
        @endif

        @if ($logAktivitas->isEmpty())
            @if (request()->anyFilled(['q', 'pengguna', 'aktivitas']))
                <x-admin.empty-state title="Aktivitas tidak ditemukan" description="Coba ubah kata kunci atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada aktivitas"
                    description="Perubahan data serta masuk dan keluar panel admin akan tercatat di sini." />
            @endif
        @else
            <x-admin.tabel>
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
                            <x-admin.aksi-tabel>
                                <x-admin.detail-button title="Detail Log Aktivitas" :ikon-saja="true">
                                    <x-admin.tabel-detail>
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
                                    </x-admin.tabel-detail>
                                </x-admin.detail-button>
                            </x-admin.aksi-tabel>
                        </tr>
                    @endforeach
                </tbody>
            </x-admin.tabel>
        @endif

        @if ($logAktivitas->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$logAktivitas" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
