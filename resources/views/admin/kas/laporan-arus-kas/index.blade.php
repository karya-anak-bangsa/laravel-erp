@extends('layouts.admin', ['breadcrumb' => ['Kas Perusahaan' => null, 'Laporan Arus Kas' => null]])

@use('App\Enums\Kas\JenisTransaksi')
@use('App\Support\FormatRupiah')

@section('content')
    <x-admin.page-header title="Laporan Arus Kas" pretitle="Kas Perusahaan" />

    <x-admin.card :flush="true">
        <x-admin.filter-bar :action="route('admin.laporan-arus-kas.index')" :cari="false">
            <select name="akun" class="form-control" style="width:180px;height:32px" aria-label="Filter akun kas">
                <option value="">Semua akun</option>
                @foreach ($opsiAkun as $id => $nama)
                    <option value="{{ $id }}" @selected($akun === $id)>{{ $nama }}</option>
                @endforeach
            </select>
            <input type="date" name="dari" value="{{ $dari->format('Y-m-d') }}" class="form-control" style="width:140px;height:32px"
                aria-label="Dari tanggal" title="Dari tanggal">
            <span class="card-subtitle">s.d.</span>
            <input type="date" name="sampai" value="{{ $sampai->format('Y-m-d') }}" class="form-control" style="width:140px;height:32px"
                aria-label="Sampai tanggal" title="Sampai tanggal">
        </x-admin.filter-bar>
        <div class="card-subtitle" style="padding:10px 16px">
            Periode {{ $dari->translatedFormat('d F Y') }} – {{ $sampai->translatedFormat('d F Y') }}
            · {{ $akun ? ($opsiAkun[$akun] ?? 'Akun tidak ditemukan') : 'Semua akun' }}
            · {{ $ringkasan['jumlah_transaksi'] }} transaksi
        </div>
    </x-admin.card>

    <div class="row col-4">
        <x-admin.stat label="Saldo Awal" ikon="wallet" warna="teal"
            :nilai="FormatRupiah::format($ringkasan['saldo_awal'])" :negatif="$ringkasan['saldo_awal'] < 0">
            per {{ $dari->subDay()->translatedFormat('d F Y') }}
        </x-admin.stat>
        <x-admin.stat label="Pemasukan" ikon="arrow-down" warna="green" :nilai="FormatRupiah::format($ringkasan['pemasukan'])">
            {{ $rincian[JenisTransaksi::Pemasukan->value]->sum('jumlah_transaksi') }} transaksi
        </x-admin.stat>
        <x-admin.stat label="Pengeluaran" ikon="arrow-up" warna="red" :nilai="FormatRupiah::format($ringkasan['pengeluaran'])">
            {{ $rincian[JenisTransaksi::Pengeluaran->value]->sum('jumlah_transaksi') }} transaksi
        </x-admin.stat>
        <x-admin.stat label="Saldo Akhir" ikon="scale-balanced" warna="blue"
            :nilai="FormatRupiah::format($ringkasan['saldo_akhir'])" :negatif="$ringkasan['saldo_akhir'] < 0">
            per {{ $sampai->translatedFormat('d F Y') }} · selisih
            @if ($ringkasan['selisih'] < 0)
                <span class="nominal-negatif">{{ FormatRupiah::format($ringkasan['selisih']) }}</span>
            @else
                {{ ($ringkasan['selisih'] > 0 ? '+' : '').FormatRupiah::format($ringkasan['selisih']) }}
            @endif
        </x-admin.stat>
    </div>

    @if ($ringkasan['saldo_akun_baru'] != 0)
        <x-admin.alert variant="info" style="margin-bottom:16px">
            Saldo akhir sudah termasuk saldo awal {{ FormatRupiah::format($ringkasan['saldo_akun_baru']) }}
            dari akun yang mulai dicatat di dalam periode ini.
        </x-admin.alert>
    @endif

    <div class="row col-2">
        @foreach (JenisTransaksi::cases() as $jenis)
            @include('admin.kas.laporan-arus-kas._rincian', [
                'jenis' => $jenis,
                'baris' => $rincian[$jenis->value],
                'total' => $ringkasan[$jenis->value],
            ])
        @endforeach
    </div>
@endsection
