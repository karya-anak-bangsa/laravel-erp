@props(['name', 'label' => null, 'value' => null, 'required' => false, 'hint' => null, 'maks' => null, 'judulBagian' => false, 'baris' => 5])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->has($name);

    // [perintah, ikon, label, teks?]; null = pemisah grup. Perintah dijalankan resources/js/admin/editor.js.
    // Sub-judul (H2/H3) hanya untuk isi artikel; sanitasinya bersihkanHtml(..., judulBagian: true).
    $tombol = [
        ...($judulBagian ? [['judul2', null, 'Sub-judul', 'H2'], ['judul3', null, 'Sub-judul kecil', 'H3'], null] : []),
        ['bold', 'bold', 'Tebal'],
        ['italic', 'italic', 'Miring'],
        ['underline', 'underline', 'Garis bawah'],
        null,
        ['bulletList', 'list-ul', 'Daftar poin'],
        ['orderedList', 'list-ol', 'Daftar bernomor'],
        null,
        ['rataKiri', 'align-left', 'Rata kiri'],
        ['rataTengah', 'align-center', 'Rata tengah'],
        ['rataKanan', 'align-right', 'Rata kanan'],
        ['rataKananKiri', 'align-justify', 'Rata kanan-kiri'],
        null,
        ['link', 'link', 'Tautan'],
        ['hapusFormat', 'text-slash', 'Hapus format'],
        null,
        ['undo', 'rotate-left', 'Urungkan'],
        ['redo', 'rotate-right', 'Ulangi'],
    ];
@endphp

{{--
    Editor WYSIWYG (TipTap) untuk teks yang tampil di frontend; teks internal cukup
    <x-admin.form-textarea>. Nilai HTML disimpan di textarea asli, yang tetap tampil sebagai
    cadangan bila JavaScript tidak berjalan. HTML wajib disanitasi di Form Request
    (trait MembersihkanHtml) dan batas panjang memakai App\Rules\PanjangTeksHtml.
--}}
<div class="form-group">
    @if ($label)
        <label class="form-label" id="{{ $id }}-label" for="{{ $id }}">@if ($required)<span class="required">*</span>@endif{{ $label }}</label>
    @endif

    {{-- Tinggi bawaan 5 baris untuk semua editor; isi artikel 15 baris (pilihan pemilik). --}}
    <div @class(['editor', 'is-invalid' => $error]) data-editor{{ $judulBagian ? ' data-judul-bagian' : '' }}@if ((int) $baris !== 5) style="--editor-baris: {{ (int) $baris }}"@endif>
        <div class="editor-toolbar" role="toolbar" aria-label="Format {{ Str::lower($label ?? 'teks') }}" hidden>
            @foreach ($tombol as $item)
                @if ($item === null)
                    <span class="editor-pemisah" aria-hidden="true"></span>
                @else
                    <button type="button" class="editor-tombol" data-perintah="{{ $item[0] }}"
                        title="{{ $item[2] }}" aria-label="{{ $item[2] }}">
                        @if ($item[1])
                            <x-admin.icon :name="$item[1]" />
                        @else
                            <span class="editor-tombol-teks">{{ $item[3] }}</span>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>
        <div class="editor-wadah" data-editor-isi></div>
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ (int) $baris }}" data-editor-sumber
            @if ($label) data-label="{{ $id }}-label" @endif
            @required($required)
            {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $error]) }}>{{ old($name, $value) }}</textarea>
    </div>

    @if ($hint || $maks)
        <div class="form-help editor-bantuan">
            <span>{{ $hint }}</span>
            @if ($maks)
                <span class="editor-hitung" hidden><span data-editor-hitung>0</span>/{{ number_format($maks, 0, ',', '.') }} karakter</span>
            @endif
        </div>
    @endif

    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>
