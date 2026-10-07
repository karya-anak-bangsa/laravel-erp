@use('App\Support\FormatRupiah')

{{-- Rincian satu jenis transaksi, satu baris per transaksi. Variabel: $jenis, $baris, $total. --}}
<x-admin.card :title="'Rincian '.$jenis->label()" subtitle="Per transaksi, urut tanggal" :flush="true">
    @if ($baris->isEmpty())
        <x-admin.empty-state class="empty-state-ringkas" :title="'Tidak ada '.mb_strtolower($jenis->label())"
            description="Belum ada transaksi jenis ini pada periode dan akun yang dipilih." />
    @else
        <div class="table-responsive">
            <table class="table">
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
                            <td>
                                {{-- Menuju detail transaksi agar angka bisa ditelusuri sampai buktinya. --}}
                                <a href="{{ route('admin.transaksi-kas.show', $transaksi) }}" class="cell-strong"
                                    title="Lihat transaksi {{ $transaksi->nomor_transaksi }}">{{ $transaksi->kategoriTransaksi->nama_kategori }}</a>
                            </td>
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
