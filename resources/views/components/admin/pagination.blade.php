@props(['paginator'])

{{-- Dipakai di bawah tabel: <x-admin.pagination :paginator="$kategoriArtikel" />. Butuh LengthAwarePaginator (->paginate()). --}}
{{ $paginator->onEachSide(1)->links('vendor.pagination.admin') }}
