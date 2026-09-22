@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->except('variant')->merge(['class' => 'field']) }}>
