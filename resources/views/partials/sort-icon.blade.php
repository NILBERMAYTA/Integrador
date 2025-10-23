{{-- resources/views/partials/sort-icon.blade.php --}}
@php
    $active = $field === $sortField;
@endphp
@if ($active)
    <span class="inline-block ml-1">
        {!! $sortDirection === 'asc' ? '&#9650;' : '&#9660;' !!}
    </span>
@else
    <span class="inline-block ml-1 opacity-40">&#9650;</span>
@endif
