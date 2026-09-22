<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Manajemen</span>
        <h1 class="page-title mt-1">Edit Pengguna</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('users.index') }}" class="btn-ghost border">← Kembali</a>
    </x-slot>

    <div class="container-app page-stack" style="max-width:36rem;">
        <div class="surface-1 p-3 p-md-4">
            @include('users._partials.form-fields', ['user' => $user])
        </div>
    </div>
</x-app-layout>
