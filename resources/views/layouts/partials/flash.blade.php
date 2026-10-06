@php
    // Flash dari controller: ->with('success'|'error'|'warning'|'info', 'pesan'). Ditampilkan sebagai toast oleh admin.js.
    $toast = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn (string $jenis) => session()->has($jenis))
        ->map(fn (string $jenis) => ['jenis' => $jenis, 'pesan' => session($jenis)])
        ->values();

    if ($errors->any()) {
        $toast->push(['jenis' => 'error', 'pesan' => 'Periksa kembali isian formulir.']);
    }
@endphp

@if ($toast->isNotEmpty())
    <script type="application/json" id="flash-toast">@json($toast)</script>
@endif
