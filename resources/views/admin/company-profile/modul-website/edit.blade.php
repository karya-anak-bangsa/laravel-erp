@extends('layouts.admin', ['breadcrumb' => [
    'Pengaturan Sistem' => null,
    'Manajemen Modul' => route('admin.modul-website.index'),
    'Ubah' => null,
]])

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" />

    <form method="POST" action="{{ route('admin.modul-website.update', $modulWebsite) }}" novalidate
        data-confirm="Status modul “{{ $modulWebsite->jenis->label() }}” akan disimpan dan langsung berlaku di website."
        data-confirm-title="Simpan perubahan?" data-confirm-label="Ya, simpan" data-confirm-variant="success">
        @csrf
        @method('PUT')

        <div class="row col-8-4">
            <x-admin.card :title="'Modul '.$modulWebsite->jenis->label()" subtitle="Mengatur tampil atau tidaknya modul ini di website.">
                <div class="form-group">
                    <div class="form-label">Tampil di</div>
                    <div class="konten-html">
                        <ul>
                            @foreach ($modulWebsite->jenis->tempatTampil() as $tempat)
                                <li>{{ $tempat }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <x-admin.form-switch name="status_aktif" label="Status" text="Tampilkan di website" :checked="$modulWebsite->status_aktif"
                    :hint="$modulWebsite->jenis->akibatNonaktif()" />
            </x-admin.card>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <x-admin.icon name="floppy-disk" />
                Simpan
            </button>
            <a href="{{ route('admin.modul-website.index') }}" class="btn btn-secondary">
                <x-admin.icon name="rotate-left" />
                Batal
            </a>
        </div>
    </form>
@endsection
