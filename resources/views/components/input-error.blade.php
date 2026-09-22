@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'input-error-list']) }}>
        @foreach ((array) $messages as $message)
            <li>
                <span aria-hidden="true">&bull;</span>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
