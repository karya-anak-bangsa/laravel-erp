{{-- Label status baca; data-status-baca dipakai admin.js untuk mengubahnya tanpa memuat ulang halaman. --}}
@if ($item->status_baca)
    <span class="status status-abu" data-status-baca>Dibaca</span>
@else
    <span class="status status-green" data-status-baca>Belum dibaca</span>
@endif
