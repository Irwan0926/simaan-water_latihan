@props(['value'])

<label {{ $attributes->merge(['class' => 'label-app']) }}>
    {{ $value ?? $slot }}
</label>
