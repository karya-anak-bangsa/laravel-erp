<section id="faq" class="seksi seksi-redup" aria-labelledby="judul-faq">
    <div class="wadah tata-faq">
        <div class="faq-kepala">
            <x-web.kepala-seksi id="judul-faq" :nomor="$nomorSeksi('faq')" label="FAQ" judul="Pertanyaan yang sering diajukan">
                Belum menemukan jawaban? <a href="#kontak" class="tautan-teks">Hubungi kami</a> dan tim kami akan membantu.
            </x-web.kepala-seksi>
        </div>

        {{-- <details name="faq">: hanya satu item terbuka sekaligus (bawaan browser, tanpa JS). --}}
        <div class="daftar-faq">
            @foreach ($faq as $item)
                <details class="item-faq" name="faq" id="faq-{{ $item->id_faq }}" @if ($loop->first) open @endif>
                    <summary class="tanya-faq">
                        <span>{{ $item->pertanyaan }}</span>
                        <span class="ikon-faq" aria-hidden="true">
                            <x-web.ikon nama="tambah" :tebal="2.25" class="ikon-plus size-4" />
                            <x-web.ikon nama="kurang" :tebal="2.25" class="ikon-minus size-4" />
                            <x-web.ikon nama="chevron-bawah" class="ikon-chevron size-4" />
                        </span>
                    </summary>
                    <div class="jawab-faq konten-html">{!! $item->jawaban !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
