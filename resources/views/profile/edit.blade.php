<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Akun</span>
        <h1 class="page-title mt-1">{{ __('Profile') }}</h1>
    </x-slot>

    <div class="container-app page-stack" style="max-width:36rem;">
        <div class="surface-1 p-3 p-md-4">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="surface-1 p-3 p-md-4">
            @include('profile.partials.update-password-form')
        </div>
        <div class="surface-1 p-3 p-md-4 border-error-soft">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
