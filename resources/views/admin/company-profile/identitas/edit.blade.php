@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Identitas Website' => null,
]])

@use('App\Http\Requests\Admin\CompanyProfile\UpdateIdentitasRequest')

@php
    // Setelah validasi gagal, baris repeater dibangun ulang dari isian sebelumnya. old() berisi kiriman mentah, jadi baris
    // kosong dibuang seperti di UpdateIdentitasRequest agar indeks baris sama dengan indeks pesan galat (email.1).
    // Minimal satu baris karena wajib.
    $daftarKontak = function (string $nama) use ($identitas): array {
        $daftar = array_values(array_filter((array) old($nama, $identitas->{$nama}), fn ($nilai) => filled($nilai)));

        return $daftar !== [] ? $daftar : [''];
    };
@endphp

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <x-admin.form :action="route('admin.identitas.update')" method="PUT" :berkas="true"
        message="Perubahan identitas perusahaan akan disimpan dan tampil di website.">

        <div class="row col-2">
            <x-admin.card title="Informasi Website" subtitle="Nama dan data SEO yang tampil di mesin pencari.">
                <x-admin.form-input name="nama_perusahaan" label="Nama Perusahaan" :value="$identitas->nama_perusahaan"
                    :required="true" maxlength="150" />
                <x-admin.form-input name="judul_website" label="Judul Website" :value="$identitas->judul_website"
                    :required="true" maxlength="150" hint="Judul beranda di tab browser dan hasil pencarian. Halaman lain diakhiri nama perusahaan." />
                <x-admin.form-input name="alamat_website" type="url" label="Alamat Website" :value="$identitas->alamat_website"
                    :required="true" maxlength="255" placeholder="https://karyaanakbangsa.co.id" />
                <x-admin.form-textarea name="meta_deskripsi" label="Meta Deskripsi" :value="$identitas->meta_deskripsi"
                    rows="3" maxlength="255" hint="Ringkasan website di hasil pencarian Google dan footer, idealnya 150–160 karakter." />
                <x-admin.form-input name="meta_keyword" label="Meta Keyword" :value="$identitas->meta_keyword"
                    maxlength="255" placeholder="mis. jasa pembuatan website, pelatihan IT, bootcamp" hint="Pisahkan dengan koma." />
            </x-admin.card>

            <x-admin.card title="Logo & Favicon" subtitle="Kosongkan bila tidak ingin mengganti.">
                <div class="pratinjau-gambar">
                    <img src="{{ $identitas->logo_url }}" alt="Logo saat ini">
                </div>
                <x-admin.form-file name="logo_website" label="Logo" accept=".jpg,.jpeg,.png,.webp"
                    hint="Gunakan logo lebar (±8:3) berlatar transparan (PNG/WEBP), maks. 2 MB. Logo persegi akan tampil kecil di navbar." />

                <div class="pratinjau-gambar">
                    <img src="{{ $identitas->favicon_url }}" alt="Favicon saat ini">
                </div>
                <x-admin.form-file name="favicon_website" label="Favicon" accept=".png,.ico"
                    hint="PNG atau ICO persegi (mis. 512×512 px), maks. 512 KB; WEBP tidak ditampilkan Google." />
            </x-admin.card>

            <x-admin.card title="Kontak" subtitle="Tampil di footer website.">
                {{-- Bertumpuk, bukan berdampingan, agar alamat email panjang tidak terpotong (pilihan pemilik). --}}
                @foreach (['email' => 'Email', 'telepon' => 'Telepon'] as $nama => $label)
                    <div class="form-group" data-repeater data-repeater-maks="{{ UpdateIdentitasRequest::MAKS_KONTAK }}">
                        <div class="form-label"><span class="required">*</span>{{ $label }}</div>
                        <div class="repeater-daftar" data-repeater-daftar>
                            @foreach ($daftarKontak($nama) as $i => $nilai)
                                @include('admin.company-profile.identitas._baris-kontak', ['nama' => $nama, 'i' => $i, 'nilai' => $nilai])
                            @endforeach
                        </div>
                        <template data-repeater-templat>
                            @include('admin.company-profile.identitas._baris-kontak', ['nama' => $nama, 'i' => '__i__', 'nilai' => null])
                        </template>
                        <button type="button" class="btn btn-outline btn-sm repeater-tambah" data-repeater-tambah>
                            <x-admin.icon name="plus" />
                            Tambah {{ $label }}
                        </button>
                        <div class="form-help">
                            @if ($nama === 'email')
                                Maksimal {{ UpdateIdentitasRequest::MAKS_KONTAK }}; email pertama menjadi email utama.
                            @else
                                Maksimal {{ UpdateIdentitasRequest::MAKS_KONTAK }}; tampil di footer hanya bila link WhatsApp kosong.
                            @endif
                        </div>
                        @error($nama)
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
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

        <x-admin.form-aksi :batal="route('admin.identitas.edit')" />
    </x-admin.form>
@endsection
