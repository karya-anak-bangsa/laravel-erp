@props(['name'])

{{-- Ikon Font Awesome gaya solid; name tanpa awalan "fa-", mis. name="gauge". --}}
<i {{ $attributes->class(['icon', 'fa-solid', 'fa-'.$name]) }} aria-hidden="true"></i>
