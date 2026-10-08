@props(['name', 'label' => null, 'value' => null, 'required' => false, 'hint' => null, 'maks' => null, 'rows' => 6])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->has($name);

    // [perintah, ikon, label]; null = pemisah grup. Perintah dijalankan resources/js/admin/editor.js.
    $tombol = [
        ['bold', 'bold', 'Tebal'],
        ['italic', 'italic', 'Miring'],
        ['underline', 'underline', 'Garis bawah'],
        null,
        ['bulletList', 'list-ul', 'Daftar poin'],
        ['orderedList', 'list-ol', 'Daftar bernomor'],
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

    <div @class(['editor', 'is-invalid' => $error]) data-editor style="--editor-baris: {{ (int) $rows }}">
        <div class="editor-toolbar" role="toolbar" aria-label="Format {{ Str::lower($label ?? 'teks') }}" hidden>
            @foreach ($tombol as $item)
                @if ($item === null)
                    <span class="editor-pemisah" aria-hidden="true"></span>
                @else
                    <button type="button" class="editor-tombol" data-perintah="{{ $item[0] }}"
                        title="{{ $item[2] }}" aria-label="{{ $item[2] }}">
                        <x-admin.icon :name="$item[1]" />
                    </button>
                @endif
            @endforeach
        </div>
        <div class="editor-wadah" data-editor-isi></div>
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" data-editor-sumber
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
