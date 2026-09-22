@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'nav-item-app active'
        : 'nav-item-app';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
