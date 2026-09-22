@props(['user' => null])

@php
    $isEdit = ($user !== null);
    $action = $isEdit ? route('users.update', $user->id) : route('users.store');
    $name   = old('name', $isEdit ? $user->name : '');
    $email  = old('email', $isEdit ? $user->email : '');
    $role   = old('role', $isEdit ? $user->role : 'pegawai');
    $active = old('is_active', $isEdit ? (int) $user->is_active : 1);
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if($isEdit) @method('PATCH') @endif

    <div class="mb-3">
        <x-input-label for="name" value="Nama" />
        <input id="name" type="text" name="name" value="{{ $name }}" class="field" required placeholder="cth. Budi Santoso">
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <x-input-label for="email" value="Email" />
        <input id="email" type="email" name="email" value="{{ $email }}" class="field" required placeholder="nama@contoh.com">
        <x-input-error :messages="$errors->get('email')" />
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6">
            <x-input-label for="role" value="Peran" />
            <select id="role" name="role" class="select-dark" required>
                <option value="pegawai" {{ $role === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <x-input-error :messages="$errors->get('role')" />
        </div>
        <div class="col-6">
            <x-input-label for="is_active" value="Status" />
            <select id="is_active" name="is_active" class="select-dark" required>
                <option value="1" {{ (int) $active === 1 ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ (int) $active === 0 ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            <x-input-error :messages="$errors->get('is_active')" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6">
            <x-input-label for="password" :value="$isEdit ? 'Kata Sandi Baru' : 'Kata Sandi'" />
            <input id="password" type="password" name="password" class="field" {{ $isEdit ? '' : 'required' }} placeholder="••••••••" autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="col-6">
            <x-input-label for="password_confirmation" :value="$isEdit ? 'Konfirmasi Sandi Baru' : 'Konfirmasi Sandi'" />
            <input id="password_confirmation" type="password" name="password_confirmation" class="field" {{ $isEdit ? '' : 'required' }} placeholder="••••••••" autocomplete="new-password">
        </div>
    </div>

    @if($isEdit)
        <p class="font-mono text-mute mb-3" style="font-size:10px;">Kosongkan sandi jika tidak diubah.</p>
    @endif

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('users.index') }}" class="btn-ghost border">Batal</a>
        <x-primary-button>{{ $isEdit ? 'Simpan' : 'Tambah' }}</x-primary-button>
    </div>
</form>
