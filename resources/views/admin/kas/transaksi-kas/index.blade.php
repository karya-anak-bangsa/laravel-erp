@extends('layouts.admin', ['breadcrumb' => ['Kas Perusahaan' => null, 'Transaksi Kas' => null]])

@use('App\Enums\Kas\JenisTransaksi')
@use('App\Support\FormatRupiah')

@section('content')
    <x-admin.page-header title="Transaksi Kas" pretitle="Kas Perusahaan">
        <a href="{{ route('admin.transaksi-kas.create') }}" class="btn btn-success">
            <x-admin.icon name="plus" />
            Catat Transaksi
        </a>
    </x-admin.page-header>

    <x-admin.card :flush="true">
        <x-admin.filter-bar :action="route('admin.transaksi-kas.index')">
            <select name="jenis" class="form-control" style="width:150px;height:32px" aria-label="Filter jenis transaksi">
                <option value="">Semua jenis</option>
                @foreach (JenisTransaksi::opsi() as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected(request('jenis') === $nilai)>{{ $teks }}</option>
                @endforeach
            </select>
            <select name="akun" class="form-control" style="width:160px;height:32px" aria-label="Filter akun kas">
                <option value="">Semua akun</option>
                @foreach ($opsiAkun as $id => $nama)
                    <option value="{{ $id }}" @selected((string) request('akun') === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>
            <input type="date" name="dari" value="{{ request('dari') }}" class="form-control" style="width:140px;height:32px"
                aria-label="Dari tanggal" title="Dari tanggal">
            <span class="card-subtitle">s.d.</span>
            <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control" style="width:140px;height:32px"
                aria-label="Sampai tanggal" title="Sampai tanggal">
        </x-admin.filter-bar>

        @if ($transaksiKas->isEmpty())
            @if (request()->anyFilled(['q', 'dari', 'sampai', 'jenis', 'akun', 'kategori']))
                <x-admin.empty-state title="Transaksi tidak ditemukan" description="Coba ubah kata kunci, periode, atau filter pencarian." />
            @else
                <x-admin.empty-state title="Belum ada transaksi kas" description="Catat setiap pemasukan dan pengeluaran perusahaan beserta buktinya.">
                    <a href="{{ route('admin.transaksi-kas.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Catat Transaksi Pertama
                    </a>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor</th>
                            <th>Jenis</th>
                            <th>Kategori / Akun</th>
                            <th class="kolom-nominal">Jumlah</th>
                            <th class="kolom-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksiKas as $transaksi)
                            <tr>
                                <td style="white-space:nowrap">{{ $transaksi->tanggal_transaksi->translatedFormat('d F Y') }}</td>
                                <td style="white-space:nowrap">
                                    <a href="{{ route('admin.transaksi-kas.show', $transaksi) }}" class="cell-mono" title="Lihat detail transaksi">
                                        {{ $transaksi->nomor_transaksi }}
                                    </a>
                                    @if ($transaksi->bukti_transaksi)
                                        <x-admin.icon name="paperclip" class="card-subtitle" title="Ada bukti transaksi" />
                                    @endif
                                </td>
                                <td><span class="chip chip-{{ $transaksi->jenis_transaksi->warna() }}">{{ $transaksi->jenis_transaksi->label() }}</span></td>
                                <td>
                                    <span class="cell-strong">{{ $transaksi->kategoriTransaksi->nama_kategori }}</span>
                                    <div class="card-subtitle">{{ $transaksi->akunKas->nama_akun }}</div>
                                </td>
                                <td class="kolom-nominal"><span class="cell-mono">{{ FormatRupiah::format($transaksi->jumlah) }}</span></td>
                                <td class="kolom-aksi">
                                    <div class="aksi-tabel">
                                        <a href="{{ route('admin.transaksi-kas.edit', $transaksi) }}" class="btn btn-sm btn-warning">
                                            <x-admin.icon name="pen-to-square" />
                                            Ubah
                                        </a>
                                        <x-admin.delete-button :action="route('admin.transaksi-kas.destroy', $transaksi)"
                                            title="Hapus transaksi?"
                                            :message="'Transaksi '.$transaksi->nomor_transaksi.' tidak akan tampil lagi di daftar dan tidak dihitung di saldo.'" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($transaksiKas->hasPages())
            <x-slot:footer>
                <x-admin.pagination :paginator="$transaksiKas" />
            </x-slot>
        @endif
    </x-admin.card>
@endsection
