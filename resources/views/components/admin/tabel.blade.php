{{-- Tabel daftar (slot = <thead> + <tbody>); di layar sempit digeser horizontal alih-alih memeras kolomnya. --}}
<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table']) }}>
        {{ $slot }}
    </table>
</div>
