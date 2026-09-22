@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'dropdown-panel'])

@php
$alignmentClasses = match ($align) {
    'left' => 'start-0',
    'top' => '',
    default => 'end-0',
};

$widthStyle = match ($width) {
    '48' => '12rem',
    '56' => '14rem',
    default => is_numeric($width) ? ($width * 0.25) . 'rem' : $width,
};
@endphp

<div class="position-relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition
            class="position-absolute z-3 mt-2 {{ $alignmentClasses }}"
            style="display: none; width: {{ $widthStyle }};"
            @click="open = false">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
