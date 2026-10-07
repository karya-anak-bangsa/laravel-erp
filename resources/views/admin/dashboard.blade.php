@extends('layouts.admin')

@use('App\Enums\Kas\JenisTransaksi')
@use('App\Support\FormatRupiah')

@section('content')
    <x-admin.page-header title="Dashboard" :pretitle="'Selamat datang, '.auth()->user()->nama" />

    <div class="row col-4">
        <x-admin.stat label="Total Saldo" ikon="wallet" warna="teal"
            :nilai="FormatRupiah::format($saldoTotal)" :negatif="$saldoTotal < 0">
            {{ $jumlahAkunAktif }} akun kas aktif
        </x-admin.stat>
        <x-admin.stat label="Pemasukan" ikon="arrow-down" warna="green" :nilai="FormatRupiah::format($bulanIni['pemasukan'])">
            {{ $hariIni->translatedFormat('F Y') }}
        </x-admin.stat>
        <x-admin.stat label="Pengeluaran" ikon="arrow-up" warna="red" :nilai="FormatRupiah::format($bulanIni['pengeluaran'])">
            {{ $hariIni->translatedFormat('F Y') }}
        </x-admin.stat>
        <x-admin.stat label="Transaksi" ikon="receipt" warna="blue" :nilai="$bulanIni['jumlah_transaksi']">
            {{ $hariIni->translatedFormat('F Y') }}
        </x-admin.stat>
    </div>

    <div class="row col-8-4">
        <x-admin.card :title="'Arus Kas Tahun '.$hariIni->year" subtitle="Pemasukan & pengeluaran per bulan, Januari–Desember">
            <x-slot:aksi>
                <a href="{{ route('admin.laporan-arus-kas.index') }}" class="btn btn-sm btn-outline">
                    <x-admin.icon name="chart-column" />
                    Laporan
                </a>
            </x-slot>

            <div class="grafik-kas skeleton chart-skeleton" data-grafik-kas="{{ json_encode($grafik) }}"
                role="img" aria-label="Grafik batang pemasukan dan pengeluaran per bulan tahun {{ $hariIni->year }}"></div>
        </x-admin.card>

        <x-admin.card title="Transaksi Terbaru" :flush="true">
            <x-slot:aksi>
                <a href="{{ route('admin.transaksi-kas.index') }}" class="btn btn-sm btn-outline">
                    <x-admin.icon name="list" />
                    Lihat semua
                </a>
            </x-slot>

            @if ($transaksiTerbaru->isEmpty())
                <x-admin.empty-state title="Belum ada transaksi" description="Pemasukan dan pengeluaran yang dicatat akan tampil di sini.">
                    <a href="{{ route('admin.transaksi-kas.create') }}" class="btn btn-success">
                        <x-admin.icon name="plus" />
                        Catat Transaksi
                    </a>
                </x-admin.empty-state>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            @foreach ($transaksiTerbaru as $transaksi)
                                @php($masuk = $transaksi->jenis_transaksi === JenisTransaksi::Pemasukan)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.transaksi-kas.show', $transaksi) }}" class="cell-strong">{{ $transaksi->kategoriTransaksi->nama_kategori }}</a>
                                        <div class="card-subtitle">{{ $transaksi->tanggal_transaksi->translatedFormat('d M Y') }} · {{ $transaksi->akunKas->nama_akun }}</div>
                                    </td>
                                    <td class="kolom-nominal">
                                        <span @class(['cell-mono', 'nominal-positif' => $masuk, 'nominal-negatif' => ! $masuk])>{{ $masuk ? '+' : '-' }}{{ FormatRupiah::format($transaksi->jumlah) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>
@endsection
