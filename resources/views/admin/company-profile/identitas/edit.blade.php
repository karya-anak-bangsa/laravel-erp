@extends('layouts.admin', ['breadcrumb' => [
    'Company Profile' => null,
    'Identitas' => null,
]])

@section('content')
    <x-admin.page-header title="Company Profile" />

    <form method="POST" action="{{ route('admin.identitas.update') }}" enctype="multipart/form-data" novalidate
        data-confirm="Perubahan identitas perusahaan akan disimpan dan tampil di website." data-confirm-title="Simpan perubahan?"
        data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')

        <div class="row col-2">
            <x-admin.card title="Informasi Website" subtitle="Nama dan data SEO yang tampil di mesin pencari.">
                <x-admin.form-input name="nama_perusahaan" label="Nama Perusahaan" :value="$identitas->nama_perusahaan"
                    :required="true" maxlength="150" />
                <x-admin.form-input name="judul_website" label="Judul Website" :value="$identitas->judul_website"
                    :required="true" maxlength="150" hint="Tampil di tab browser dan judul hasil pencarian." />
                <x-admin.form-input name="alamat_website" type="url" label="Alamat Website" :value="$identitas->alamat_website"
                    :required="true" maxlength="255" placeholder="https://karyaanakbangsa.co.id" />
                <x-admin.form-textarea name="meta_deskripsi" label="Meta Deskripsi" :value="$identitas->meta_deskripsi"
                    rows="3" maxlength="255" hint="Ringkasan website di hasil pencarian Google, idealnya 150–160 karakter." />
                <x-admin.form-input name="meta_keyword" label="Meta Keyword" :value="$identitas->meta_keyword"
                    maxlength="255" placeholder="mis. jasa pembuatan website, pelatihan IT, bootcamp" hint="Pisahkan dengan koma." />
            </x-admin.card>

            <x-admin.card title="Logo & Favicon" subtitle="Kosongkan bila tidak ingin mengganti.">
                <div class="pratinjau-gambar">
                    <img src="{{ $identitas->logo_url }}" alt="Logo saat ini">
                </div>
                <x-admin.form-file name="logo_website" label="Logo" accept=".jpg,.jpeg,.png,.webp"
                    hint="JPG, PNG, atau WEBP, maks. 2 MB. Disarankan berlatar transparan (PNG/WEBP)." />

                <div class="pratinjau-gambar">
                    <img src="{{ $identitas->favicon_url }}" alt="Favicon saat ini">
                </div>
                <x-admin.form-file name="favicon_website" label="Favicon" accept=".png,.ico,.webp"
                    hint="PNG, ICO, atau WEBP berbentuk persegi (mis. 512×512 px), maks. 512 KB." />
            </x-admin.card>

            <x-admin.card title="Kontak" subtitle="Tampil di halaman kontak dan footer website.">
                <div class="form-row">
                    <x-admin.form-input name="email" type="email" label="Email" :value="$identitas->email"
                        :required="true" maxlength="150" placeholder="mis. info@karyaanakbangsa.co.id" />
                    <x-admin.form-input name="telepon" type="tel" label="Telepon" :value="$identitas->telepon"
                        :required="true" maxlength="30" placeholder="mis. 0812 3456 7890" />
                </div>
                <x-admin.form-textarea name="alamat" label="Alamat" :value="$identitas->alamat" :required="true"
                    rows="3" maxlength="1000" />
            </x-admin.card>

            <x-admin.card title="Media Sosial" subtitle="Kosongkan akun yang tidak dimiliki.">
                <x-admin.form-input name="link_youtube" type="url" label="YouTube" :value="$identitas->link_youtube"
                    maxlength="255" placeholder="https://www.youtube.com/@namakanal" />
                <x-admin.form-input name="link_instagram" type="url" label="Instagram" :value="$identitas->link_instagram"
                    maxlength="255" placeholder="https://www.instagram.com/namaakun" />
                <x-admin.form-input name="link_whatsapp" type="url" label="WhatsApp" :value="$identitas->link_whatsapp"
                    maxlength="255" placeholder="https://wa.me/6281234567890" hint="Format wa.me dengan kode negara 62, tanpa angka 0 di depan." />
            </x-admin.card>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <x-admin.icon name="floppy-disk" />
                Simpan
            </button>
            <a href="{{ route('admin.identitas.edit') }}" class="btn btn-secondary">
                <x-admin.icon name="rotate-left" />
                Batal
            </a>
        </div>
    </form>
@endsection
