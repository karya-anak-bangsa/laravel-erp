@use('App\Enums\CompanyProfile\JenisTemplate')

@extends('layouts.admin')

@section('content')
    <x-admin.page-header title="Dashboard" />

    {{-- Ringkasan Company Profile dari tabel yang sudah ada (App\Services\CompanyProfile\RingkasanService). --}}
    <div class="row col-3">
        <x-admin.statistik label="Artikel terbit" :nilai="$statistik['artikel']['terbit']" ikon="newspaper" warna="teal"
            :keterangan="$statistik['artikel']['draf'].' draf · '.$statistik['artikel']['kategori'].' kategori'"
            :href="route('admin.artikel.index')" />
        <x-admin.statistik label="Portofolio" :nilai="$statistik['portofolio']['jumlah']" ikon="briefcase" warna="blue"
            :keterangan="$statistik['portofolio']['kategori'].' kategori'"
            :href="route('admin.portofolio.index')" />
        <x-admin.statistik label="Tutorial tayang" :nilai="$statistik['tutorial']['tayang']" ikon="graduation-cap" warna="purple"
            :keterangan="$statistik['tutorial']['materi'].' materi · '.$statistik['tutorial']['belumTayang'].' belum tayang'"
            :href="route('admin.tutorial.index')" />
        <x-admin.statistik label="Layanan" :nilai="$statistik['layanan']" ikon="handshake" warna="green"
            keterangan="Tampil di beranda" :href="route('admin.layanan.index')" />
        <x-admin.statistik label="FAQ" :nilai="$statistik['faq']" ikon="circle-question" warna="yellow"
            keterangan="Tampil di beranda" :href="route('admin.faq.index')" />
        <x-admin.statistik label="Pesan belum dibaca" :nilai="$statistik['pesan']['belumDibaca']" ikon="envelope" warna="red"
            :keterangan="$statistik['pesan']['jumlah'].' pesan masuk'"
            :href="route('admin.kontak-kami.index')" />
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
            <x-admin.card title="Pesan masuk terbaru" :flush="true">
                <x-slot:aksi>
                    <a href="{{ route('admin.kontak-kami.index') }}" class="btn btn-info btn-sm">
                        <x-admin.icon name="eye" />
                        Lihat semua
                    </a>
                </x-slot>

                @if ($pesanTerbaru->isEmpty())
                    <x-admin.empty-state title="Belum ada pesan" description="Pesan dari form kontak website akan tampil di sini." />
                @else
                    <ul class="daftar-ringkas">
                        @foreach ($pesanTerbaru as $item)
                            <li>
                                <div class="isi-ringkas">
                                    <div class="judul-ringkas">{{ Str::limit($item->subjek, 80) }}</div>
                                    <div class="keterangan-ringkas">{{ $item->nama }} · {{ $item->tanggal->translatedFormat('d F Y, H:i') }}</div>
                                </div>
                                @include('admin.company-profile.kontak-kami._status')
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

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
                                    <span class="status status-green">Diindeks</span>
                                @else
                                    <span class="status status-yellow">Tidak diindeks</span>
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
        </div>
    </div>
@endsection
