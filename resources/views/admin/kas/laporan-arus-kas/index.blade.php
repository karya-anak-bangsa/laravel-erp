@extends('layouts.admin', ['breadcrumb' => ['Kas Perusahaan' => null, 'Laporan Arus Kas' => null]])

@use('App\Enums\Kas\JenisTransaksi')
@use('App\Support\FormatRupiah')

@section('content')
    <x-admin.page-header title="Laporan Arus Kas" pretitle="Kas Perusahaan" />

    @php($selisih = $ringkasan['selisih'])

    {{-- .row memberi jarak bawah yang sama dengan baris kartu lain (.card tidak punya margin). --}}
    <div class="row">
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

            <div class="ringkasan-periode">
                <span>
                    Periode {{ $dari->translatedFormat('d F Y') }} – {{ $sampai->translatedFormat('d F Y') }}
                    · {{ $akun ? ($opsiAkun[$akun] ?? 'Akun tidak ditemukan') : 'Semua akun' }}
                    · {{ $ringkasan['jumlah_transaksi'] }} transaksi
                </span>
                <span>
                    Selisih periode
                    <strong @class(['nilai-selisih', 'nominal-positif' => $selisih > 0, 'nominal-negatif' => $selisih < 0])>{{ ($selisih > 0 ? '+' : '').FormatRupiah::format($selisih) }}</strong>
                </span>
            </div>
        </x-admin.card>
    </div>

    <div class="row col-3">
        <x-admin.stat label="Pemasukan" ikon="arrow-down" warna="green" :nilai="FormatRupiah::format($ringkasan['pemasukan'])">
            {{ $rincian[JenisTransaksi::Pemasukan->value]->count() }} transaksi
        </x-admin.stat>
        <x-admin.stat label="Pengeluaran" ikon="arrow-up" warna="red" :nilai="FormatRupiah::format($ringkasan['pengeluaran'])">
            {{ $rincian[JenisTransaksi::Pengeluaran->value]->count() }} transaksi
        </x-admin.stat>
        <x-admin.stat label="Total Saldo" ikon="wallet" warna="teal"
            :nilai="FormatRupiah::format($ringkasan['total_saldo'])" :negatif="$ringkasan['total_saldo'] < 0">
            per {{ $sampai->translatedFormat('d F Y') }}
        </x-admin.stat>
    </div>

    {{-- align-items:start: kartu setinggi isinya, agar baris Total tetap menjadi bagian paling bawah kartu. --}}
    <div class="row col-2" style="align-items:start">
        @foreach (JenisTransaksi::cases() as $jenis)
            @include('admin.kas.laporan-arus-kas._rincian', [
                'jenis' => $jenis,
                'baris' => $rincian[$jenis->value],
                'total' => $ringkasan[$jenis->value],
            ])
        @endforeach
    </div>
@endsection
