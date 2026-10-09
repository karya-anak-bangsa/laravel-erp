{{-- Modal detail layanan (Full Color): satu <dialog> yang diisi resources/js/web/dialog-layanan.js dari kartu yang diklik.
     data-wa kosong bila identitas tidak punya link WhatsApp, maka tombol WA di modal disembunyikan. --}}
<dialog id="dialog-layanan" class="dialog-layanan" aria-labelledby="dialog-layanan-judul" data-wa="{{ $identitas->link_whatsapp }}">
    <div class="dialog-layanan-isi">
        <div class="dialog-layanan-kepala">
            <div class="dialog-layanan-gambar"><img src="data:," width="800" height="600" alt=""></div>
            <div class="min-w-0">
                <span class="dialog-layanan-nomor"></span>
                <h2 id="dialog-layanan-judul" class="dialog-layanan-judul"></h2>
            </div>
        </div>
        <div class="dialog-layanan-badan">
            <div class="dialog-layanan-deskripsi konten-html"></div>
            <div class="dialog-layanan-rinci konten-html"></div>
            <div class="dialog-layanan-aksi">
                <x-web.tautan-luar href="#" class="btn btn-sedang" data-wa-layanan>Konsultasi via WhatsApp</x-web.tautan-luar>
                <button type="button" class="btn btn-sedang" data-variant="outline" data-tutup-dialog>Tutup</button>
            </div>
        </div>
        <button type="button" class="dialog-layanan-tutup" aria-label="Tutup" data-tutup-dialog autofocus>
            <x-web.ikon nama="tutup" class="size-5" />
        </button>
    </div>
</dialog>
