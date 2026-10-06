@extends('layouts.admin')

@section('content')
    @php
        $bulanIni = now()->translatedFormat('F Y');
        // Placeholder; nilai diisi dari modul Kas Perusahaan (ROADMAP Fase 3c).
        $ringkasan = [
            ['label' => 'Saldo Total', 'ikon' => 'wallet', 'warna' => 'teal', 'keterangan' => 'Semua akun kas'],
            ['label' => 'Pemasukan', 'ikon' => 'arrow-down', 'warna' => 'green', 'keterangan' => $bulanIni],
            ['label' => 'Pengeluaran', 'ikon' => 'arrow-up', 'warna' => 'red', 'keterangan' => $bulanIni],
            ['label' => 'Transaksi', 'ikon' => 'receipt', 'warna' => 'blue', 'keterangan' => $bulanIni],
        ];
    @endphp

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <div class="page-pretitle">Ringkasan</div>
                <h1 class="page-title">Dashboard</h1>
            </div>
        </div>
    </div>

    <div class="row col-4">
        @foreach ($ringkasan as $kartu)
            <div class="card">
                <div class="stat">
                    <div class="stat-icon {{ $kartu['warna'] }}">
                        <x-admin.icon :name="$kartu['ikon']" />
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">{{ $kartu['label'] }}</div>
                        <div class="stat-value-row">
                            <span class="stat-value">—</span>
                        </div>
                        <div class="stat-subtext">{{ $kartu['keterangan'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Selamat datang, {{ auth()->user()->nama }}</div>
        </div>
        <div class="card-body">
            Ringkasan kas akan tampil di halaman ini setelah modul Kas Perusahaan aktif.
        </div>
    </div>
@endsection
