@use('App\Support\FormatRupiah')

{{-- Rincian satu jenis transaksi per kategori. Variabel: $jenis, $baris, $total, $dari, $sampai, $akun. --}}
<x-admin.card :title="'Rincian '.$jenis->label()" subtitle="Per kategori, dari nominal terbesar" :flush="true">
    @if ($baris->isEmpty())
        <x-admin.empty-state :title="'Tidak ada '.mb_strtolower($jenis->label())"
            description="Belum ada transaksi jenis ini pada periode dan akun yang dipilih." />
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th class="kolom-nominal">Transaksi</th>
                        <th class="kolom-nominal">Jumlah</th>
                        <th class="kolom-nominal">Porsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baris as $kategori)
                        <tr>
                            <td>
                                {{-- Menuju daftar transaksi dengan filter yang sama agar angka bisa ditelusuri. --}}
                                <a href="{{ route('admin.transaksi-kas.index', array_filter([
                                    'kategori' => $kategori->id_kategori_transaksi,
                                    'akun' => $akun,
                                    'dari' => $dari->format('Y-m-d'),
                                    'sampai' => $sampai->format('Y-m-d'),
                                ])) }}" class="cell-strong" title="Lihat transaksi kategori ini">{{ $kategori->nama_kategori }}</a>
                            </td>
                            <td class="kolom-nominal">{{ $kategori->jumlah_transaksi }}</td>
                            <td class="kolom-nominal"><span class="cell-mono">{{ FormatRupiah::format($kategori->total) }}</span></td>
                            <td class="kolom-nominal">{{ number_format($total > 0 ? $kategori->total / $total * 100 : 0, 1, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="cell-strong">Total</td>
                        <td class="kolom-nominal cell-strong">{{ $baris->sum('jumlah_transaksi') }}</td>
                        <td class="kolom-nominal"><span class="cell-mono cell-strong">{{ FormatRupiah::format($total) }}</span></td>
                        <td class="kolom-nominal cell-strong">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-admin.card>
