<x-guest-layout>
    <div class="mb-4">
        <span class="mono-eyebrow">Reset Password</span>
        <h2 class="page-title mt-2">Setel password baru</h2>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="w-100" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="w-100" type="password" name="password" required autocomplete="new-password" />
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
            {{ __('Reset Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
