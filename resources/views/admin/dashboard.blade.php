@use('App\Enums\CompanyProfile\JenisTemplate')

@extends('layouts.admin')

@section('content')
    {{-- Batas uji skala teks & jarak baru (admin.scss); dilepas setelah skala itu berlaku di semua halaman admin. --}}
    <div class="halaman-dashboard">
    <x-admin.page-header title="Dashboard" />

    {{-- Ringkasan Company Profile dari tabel yang sudah ada (App\Services\CompanyProfile\RingkasanService). --}}
    <div class="row col-3">
        <x-admin.statistik label="Artikel terbit" :nilai="$statistik['artikel']['terbit']" ikon="newspaper" warna="teal"
            :keterangan="$statistik['artikel']['draf'].' draf · '.$statistik['artikel']['kategori'].' kategori'"
            :href="route('admin.artikel.index')" />
        <x-admin.statistik label="Portofolio" :nilai="$statistik['portofolio']['jumlah']" ikon="briefcase" warna="blue"
            :keterangan="$statistik['portofolio']['kategori'].' kategori'"
            :href="route('admin.portofolio.index')" />
        <x-admin.statistik label="Tutorial terbit" :nilai="$statistik['tutorial']['terbit']" ikon="graduation-cap" warna="purple"
            :keterangan="$statistik['tutorial']['draf'].' draf · '.$statistik['tutorial']['materi'].' materi'"
            :href="route('admin.tutorial.index')" />
        <x-admin.statistik label="Layanan" :nilai="$statistik['layanan']" ikon="handshake" warna="green"
            keterangan="Tampil di beranda" :href="route('admin.layanan.index')" />
        <x-admin.statistik label="FAQ" :nilai="$statistik['faq']" ikon="circle-question" warna="yellow"
            keterangan="Tampil di beranda" :href="route('admin.faq.index')" />
        <x-admin.statistik label="Hero" :nilai="$statistik['hero']['jumlah']" ikon="panorama" warna="red"
            :keterangan="$statistik['hero']['aktif'].' aktif di beranda'" :href="route('admin.hero.index')" />
    </div>

    <div class="row col-8-4 sejajar-col-3">
        <x-admin.card title="Terakhir diperbarui" subtitle="Konten yang terakhir disimpan, terbaru lebih dulu." :flush="true">
            @if ($terakhirDiperbarui->isEmpty())
                <x-admin.empty-state title="Belum ada konten" description="Konten yang ditambahkan atau diubah akan tampil di sini." />
            @else
                <ul class="daftar-ringkas">
                    @foreach ($terakhirDiperbarui as $item)
                        <li>
                            <div class="isi-ringkas">
                                <a href="{{ $item['url'] }}" class="judul-ringkas">{{ Str::limit($item['judul'], 100) }}</a>
                                <div class="keterangan-ringkas">{{ $item['modul'] }} · {{ $item['diperbarui']->translatedFormat('d F Y, H:i') }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <div class="tumpukan-kartu">
            <x-admin.card title="Status website" :flush="true">
                <table class="table tabel-informasi tabel-status-situs">
                    <tbody>
                        <tr>
                            <th scope="row">Hero aktif</th>
                            <td>{{ $statusSitus['hero']?->judul ?? '—' }}</td>
                        </tr>
                        @foreach (JenisTemplate::cases() as $jenis)
                            <tr>
                                <th scope="row">Template {{ $jenis->label() }}</th>
                                <td>{{ $statusSitus['template']->get($jenis->value)?->nama ?? '—' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <th scope="row">Mesin pencari</th>
                            <td>
                                {{-- SEO_INDEKS di .env; selama tahap uji produksi tidak diindeks. --}}
                                @if ($statusSitus['indeks'])
                                    <span class="chip chip-green">Diindeks</span>
                                @else
                                    <span class="chip chip-yellow">Tidak diindeks</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Modul website</th>
                            <td>
                                @if ($statusSitus['modulNonaktif'] === 0)
                                    <span class="chip chip-green">Semua tampil</span>
                                @elseif (Gate::allows('kelola-sistem'))
                                    <a href="{{ route('admin.modul-website.index') }}" class="chip chip-yellow">{{ $statusSitus['modulNonaktif'] }} nonaktif</a>
                                @else
                                    {{-- Administrator tidak bisa membuka Manajemen Modul. --}}
                                    <span class="chip chip-yellow">{{ $statusSitus['modulNonaktif'] }} nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <x-slot:footer>
                    <a href="{{ route('beranda') }}" target="_blank" rel="noopener" class="btn btn-info btn-blok">
                        <x-admin.icon name="eye" />
                        Buka website
                    </a>
                </x-slot>
            </x-admin.card>

            <x-admin.card title="Belum tampil" subtitle="Artikel draf dan tutorial yang belum tampil di website." :flush="true">
                @if ($belumTampil->isEmpty())
                    <x-admin.empty-state title="Semua konten sudah tampil" description="Artikel draf dan tutorial yang belum tampil di website akan muncul di sini." />
                @else
                    <ul class="daftar-ringkas">
                        @foreach ($belumTampil as $item)
                            <li>
                                <div class="isi-ringkas">
                                    <a href="{{ $item['url'] }}" class="judul-ringkas">{{ Str::limit($item['judul'], 100) }}</a>
                                    <div class="keterangan-ringkas">{{ $item['modul'] }} · {{ $item['diperbarui']->translatedFormat('d F Y, H:i') }}</div>
                                </div>
                                <span class="chip chip-yellow">{{ $item['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>
    </div>
    </div>
@endsection
