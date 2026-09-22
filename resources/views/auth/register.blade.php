<x-guest-layout>
    <div class="mb-4">
        <span class="mono-eyebrow">Daftar</span>
        <h2 class="page-title mt-2">Buat akun baru</h2>
        <p class="page-subtitle">Mulai kelola depot air Anda hari ini.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="w-100" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="w-100" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="w-100"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="col-12 col-sm-6">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="w-100"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <x-primary-button class="w-100 justify-content-center" style="height:3rem;">
            {{ __('Register') }}
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </x-primary-button>
    </form>

    <div class="mt-4 pt-4 border-top border-hairline text-center">
        <p class="text-14 text-mute mb-0">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-ink fw-medium text-decoration-none">Masuk</a>
        </p>
    </div>
</x-guest-layout>
