<section>
    <header>
        <div class="section-eyebrow-row mb-2">
            <span class="eyebrow-rule"></span>
            <span class="mono-eyebrow">Informasi</span>
        </div>
        <h2 class="heading-sm mb-0">{{ __('Profile Information') }}</h2>
        <p class="page-subtitle">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="w-100" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="w-100" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="text-14 text-ink-soft mb-0">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="btn btn-link p-0 text-link-blue text-decoration-underline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="alert-success mt-3 mb-0">
                            <span aria-hidden="true">&#10003;</span>
                            <span>{{ __('A new verification link has been sent to your email address.') }}</span>
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="mb-3">
            <x-input-label for="timezone" value="Zona Waktu" />
            <select id="timezone" name="timezone" class="select-dark w-100">
                <option value="">Ikuti perangkat ({{ \App\Support\Waktu::zona() }})</option>
                @foreach (\App\Support\Waktu::pilihanZona() as $zona => $labelZona)
                    <option value="{{ $zona }}" @selected(old('timezone', $user->timezone) === $zona)>
                        {{ $labelZona }}
                    </option>
                @endforeach
            </select>
            <p class="font-mono text-mute mt-1 mb-0" style="font-size:10px;">
                Tanggal &amp; jam ditampilkan memakai zona waktu ini. Data tetap disimpan dalam UTC.
                Sekarang: {{ \App\Support\Waktu::sekarang() }} {{ \App\Support\Waktu::labelZona() }}.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
        </div>

        <div class="d-flex align-items-center gap-3 pt-2">
            <x-primary-button>
                {{ __('Save') }}
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-14 text-success fw-medium mb-0"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
