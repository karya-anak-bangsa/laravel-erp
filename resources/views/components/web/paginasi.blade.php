@props(['paginator'])

{{-- Paginasi halaman publik (Basecoat tidak punya komponen paginasi): <x-web.paginasi :paginator="$artikel" />. Butuh ->paginate(). --}}
{{ $paginator->onEachSide(1)->links('vendor.pagination.web') }}
