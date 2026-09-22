<x-guest-layout>
    <x-auth-session-status class="mb-3" :status="session('status')" />

    <div class="mb-4">
        <span class="mono-eyebrow">Masuk</span>
        <h2 class="page-title mt-2">Selamat datang kembali</h2>
        <p class="page-subtitle">Masuk untuk mengakses dashboard Simaan Water.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="w-100" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between">
                <x-input-label for="password" :value="__('Password')" />
                @if (Route::has('password.request'))
                    <a class="text-13 text-link-blue text-decoration-none" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="w-100"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="d-inline-flex align-items-center gap-2_5 mb-4" style="cursor:pointer;user-select:none;">
            <input id="remember_me" type="checkbox" name="remember" class="form-check-input-app">
            <span class="text-14 text-ink-soft">{{ __('Remember me') }}</span>
        </label>

        <x-primary-button class="w-100 justify-content-center" style="height:3rem;">
            {{ __('Log in') }}
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </x-primary-button>
    </form>

    <div class="mt-4 pt-4 border-top border-hairline text-center">
        <p class="font-mono text-uppercase tracking-wide text-mute mb-0" style="font-size:11px;">
            Pendaftaran akun hanya dapat dilakukan oleh administrator.
        </p>
    </div>
</x-guest-layout>
