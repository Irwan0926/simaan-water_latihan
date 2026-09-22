@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert-info mb-4']) }}>
        <span aria-hidden="true">&#9432;</span>
        <span>{{ $status }}</span>
    </div>
@endif
