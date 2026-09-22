<x-guest-layout>
    <div class="mb-4">
        <span class="mono-eyebrow">Reset Password</span>
        <h2 class="page-title mt-2">Lupa password?</h2>
    </div>

    <p class="mb-4 text-14 text-mute lh-base">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="w-100" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <x-primary-button class="w-100 justify-content-center" style="height:3rem;">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>

    <div class="mt-4 pt-4 border-top border-hairline text-center">
        <a href="{{ route('login') }}" class="text-14 text-ink fw-medium text-decoration-none">&larr; Kembali ke login</a>
    </div>
</x-guest-layout>
