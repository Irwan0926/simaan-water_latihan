<x-guest-layout>
    <div class="mb-4">
        <span class="mono-eyebrow">Verifikasi Email</span>
        <h2 class="page-title mt-2">Konfirmasi email Anda</h2>
    </div>

    <p class="mb-4 text-14 text-mute lh-base">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert-success mb-4">
            <span aria-hidden="true">&#10003;</span>
            <span>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</span>
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-100 justify-content-center" style="height:3rem;">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost w-100 justify-content-center border" style="height:3rem;">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
