@use('App\Support\FormatRupiah')

{{-- Rincian satu jenis transaksi, satu baris per transaksi. Variabel: $jenis, $baris, $total. --}}
<x-admin.card :title="'Rincian '.$jenis->label()" subtitle="Per transaksi, urut tanggal" :flush="true">
    @if ($baris->isEmpty())
        <x-admin.empty-state class="empty-state-ringkas" :title="'Tidak ada '.mb_strtolower($jenis->label())"
            description="Belum ada transaksi jenis ini pada periode dan akun yang dipilih." />
    @else
        <div class="table-responsive">
            <table class="table tabel-informasi">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th class="kolom-nominal">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baris as $transaksi)
                        <tr>
                            <td style="white-space:nowrap">{{ $transaksi->tanggal_transaksi->translatedFormat('d F Y') }}</td>
                            {{-- Hanya informasi, sengaja tanpa tautan (keputusan pemilik). --}}
                            <td class="cell-strong">{{ $transaksi->kategoriTransaksi->nama_kategori }}</td>
                            <td class="kolom-nominal"><span class="cell-mono">{{ FormatRupiah::format($transaksi->jumlah) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total</td>
                        <td class="kolom-nominal"><span class="cell-mono">{{ FormatRupiah::format($total) }}</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-admin.card>
