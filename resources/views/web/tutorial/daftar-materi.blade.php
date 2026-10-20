{{--
    Daftar materi bernomor sesuai urutan tampil: di halaman tutorial (kolom utama) dan kolom samping halaman materi.
    Nomornya dari posisi, bukan urutan_ke, karena angka urutan boleh berselang atau kembar.
    Pakai: @include('web.tutorial.daftar-materi', ['kelas' => 'daftar-materi', 'materiAktif' => $materiTutorial])
    — $tutorial dan $daftarMateri dari controller.
--}}
<ol class="{{ $kelas }}">
    @foreach ($daftarMateri as $materi)
        <li>
            <a href="{{ route('materi-tutorial.show', [$tutorial, $materi]) }}" class="tautan-materi" @if (isset($materiAktif) && $materiAktif->is($materi)) aria-current="page" @endif>
                <span class="nomor-materi">{{ sprintf('%02d', $loop->iteration) }}</span>
                <span class="judul-materi">{{ $materi->judul }}</span>
            </a>
        </li>
    @endforeach
</ol>
