@props(['model' => null])

{{--
    Tabel rincian di modal Lihat (isi <x-admin.detail-button>); slot = baris <tr><th scope="row">Label</th><td>…</td></tr>.
    :model = data yang tanggal Dibuat & Diperbarui-nya menutup tabel; tanpa :model (mis. log aktivitas yang tidak
    pernah diubah) baris itu tidak ada.
--}}
<div class="tabel-detail">
    <table class="table tabel-informasi">
        <tbody>
            {{ $slot }}
            @if ($model)
                <tr>
                    <th scope="row">Dibuat</th>
                    <td>{{ $model->created_at->translatedFormat('d F Y, H:i') }}</td>
                </tr>
                <tr>
                    <th scope="row">Diperbarui</th>
                    <td>{{ $model->updated_at->translatedFormat('d F Y, H:i') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
