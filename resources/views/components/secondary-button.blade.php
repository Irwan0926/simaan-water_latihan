<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-secondary-dark']) }}>
    {{ $slot }}
</button>
