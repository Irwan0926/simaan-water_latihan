<x-guest-layout>
    <div class="mb-4">
        <span class="mono-eyebrow">Konfirmasi Password</span>
        <h2 class="page-title mt-2">Area Aman</h2>
    </div>

    <p class="mb-4 text-14 text-mute lh-base">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="w-100"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <x-primary-button class="w-100 justify-content-center" style="height:3rem;">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
