<section id="faq" class="seksi seksi-redup" aria-labelledby="judul-faq">
    <div class="wadah tata-faq">
        <div class="faq-kepala">
            <x-web.kepala-seksi id="judul-faq" label="FAQ" judul="Pertanyaan yang sering diajukan">
                {{-- Tautan dan frasa penutup tidak dipatah agar tautan tidak terbelah dan "tim kami" tetap sebaris. --}}
                @if (filled($identitas->link_whatsapp))
                    Belum menemukan jawaban? <x-web.tautan-luar :href="$identitas->link_whatsapp" class="tautan-teks whitespace-nowrap">Hubungi kami melalui WhatsApp</x-web.tautan-luar> <span class="whitespace-nowrap">dan tim kami akan membantu.</span>
                @endif
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
                        </span>
                    </summary>
                    <div class="jawab-faq konten-html">{!! $item->jawaban !!}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>
