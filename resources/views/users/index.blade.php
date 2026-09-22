@php
    $usersPayload = $users->map(fn ($u) => [
        'id' => $u->id,
        'name' => $u->name,
        'email' => $u->email,
        'role' => $u->role,
        'is_active' => (bool) $u->is_active,
        'can_delete' => ! in_array($u->id, $protectedDeleteIds, true),
        'delete_reason' => $deleteReasons[$u->id] ?? null,
    ])->values();

    // Jika validasi gagal, buka kembali modal yang bersangkutan
    // beserta input lama agar admin tidak kehilangan isian.
    $failedUserId = old('_form_user_id');
    $reopenCreate = $errors->any() && ! $failedUserId;
    $reopenEdit = $errors->any() && $failedUserId;
@endphp

<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Manajemen</span>
        <h1 class="page-title mt-1">Kelola Pengguna</h1>
        <p class="page-subtitle">Hanya admin yang dapat menambah, mengedit, dan menghapus pengguna.</p>
    </x-slot>
    <x-slot name="header-actions">
        <button type="button" class="btn-brand" onclick="window.dispatchEvent(new CustomEvent('open-user-create'))">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </button>
    </x-slot>

    <div
        class="container-app page-stack"
        x-data="{
            deleteId: null,
            createOpen: {{ $reopenCreate ? 'true' : 'false' }},
            editOpen: {{ $reopenEdit ? 'true' : 'false' }},
            form: {
                id: {{ $failedUserId ? (int) $failedUserId : 'null' }},
                name: @js(old('name', '')),
                email: @js(old('email', '')),
                role: @js(old('role', 'pegawai')),
                is_active: @js((string) old('is_active', '1')),
                password: '',
                password_confirmation: ''
            },
            openCreate() {
                this.form = { id: null, name: '', email: '', role: 'pegawai', is_active: '1', password: '', password_confirmation: '' };
                this.createOpen = true;
            },
            openEdit(user) {
                this.form = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    role: user.role,
                    is_active: user.is_active ? '1' : '0',
                    password: '',
                    password_confirmation: ''
                };
                this.editOpen = true;
            },
            closeCreate() { this.createOpen = false; },
            closeEdit() { this.editOpen = false; },
            editAction() {
                return '{{ url('/users') }}/' + this.form.id;
            }
        }"
        @open-user-create.window="openCreate()"
    >
        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>✓</span><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="alert-error animate-fade-in"><span>✕</span><span>{{ session('error') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in">
                <span>✗</span>
                <span>
                    @if($errors->count() === 1)
                        {{ $errors->first() }}
                    @else
                        Periksa kembali data berikut:
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </span>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Total</span>
                    <p class="stat-value mt-2">{{ $users->count() }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Admin</span>
                    <p class="stat-value mt-2">{{ $users->where('role', 'admin')->count() }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat stat-accent h-100">
                    <span class="mono-caps text-brand-deep">Aktif</span>
                    <p class="stat-value text-brand-deep mt-2">{{ $users->where('is_active', true)->count() }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Tidak Aktif</span>
                    <p class="stat-value mt-2">{{ $users->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Daftar Pengguna</span>
                    <span class="mono-micro">{{ $users->count() }} akun</span>
                </div>
                <button type="button" class="btn-brand" @click="openCreate()">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Pengguna
                </button>
            </div>

            <div class="table-responsive">
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Pengguna</th>
                            <th class="col-role text-center">Peran</th>
                            <th class="col-status text-center">Status</th>
                            <th class="col-actions-icons-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            @php
                                $isSelf = $u->id === $authId;
                                $canDelete = ! in_array($u->id, $protectedDeleteIds, true);
                                $deleteReason = $deleteReasons[$u->id] ?? null;
                                $isInactive = ! $u->is_active;
                            @endphp
                            <tr class="{{ $isInactive ? 'row-stock-empty' : '' }}">
                                <td class="text-mute font-mono">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm {{ $u->role === 'admin' ? 'avatar-brand' : '' }}">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <span class="fw-medium {{ $isInactive ? 'text-mute' : 'text-ink' }}">
                                                {{ $u->name }}
                                                @if($isSelf)
                                                    <span class="badge badge-brand ms-1" style="font-size:9px; vertical-align:middle;">Anda</span>
                                                @endif
                                            </span>
                                            <span class="d-block font-mono text-mute text-truncate" style="font-size:11px;">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($u->role === 'admin')
                                        <span class="badge badge-filled">Admin</span>
                                    @else
                                        <span class="badge badge-neutral">Pegawai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($u->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-empty">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button
                                            type="button"
                                            class="btn-action btn-action-icon btn-action-edit"
                                            title="Edit"
                                            aria-label="Edit pengguna {{ $u->name }}"
                                            @click="openEdit(@js([
                                                'id' => $u->id,
                                                'name' => $u->name,
                                                'email' => $u->email,
                                                'role' => $u->role,
                                                'is_active' => (bool) $u->is_active,
                                            ]))"
                                        >
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if($canDelete)
                                            <button
                                                type="button"
                                                class="btn-action btn-action-icon btn-action-delete"
                                                title="Hapus"
                                                aria-label="Hapus pengguna {{ $u->name }}"
                                                @click="deleteId = {{ $u->id }}"
                                            >
                                                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn-action btn-action-icon btn-action-delete opacity-50"
                                                style="cursor:not-allowed;"
                                                disabled
                                                title="{{ $deleteReason }}"
                                                aria-label="{{ $deleteReason }}"
                                            >
                                                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-5 text-center">
                                    <div class="d-flex flex-column align-items-center gap-2 py-3">
                                        <div class="empty-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M16 11a4 4 0 11-8 0 4 4 0 018 0z"/><path d="M4 21a8 8 0 0116 0"/></svg>
                                        </div>
                                        <p class="text-ink text-14 fw-medium mb-0">Belum ada pengguna</p>
                                        <button type="button" class="btn-brand mt-1" @click="openCreate()">Tambah pengguna</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal: Tambah --}}
        <div x-show="createOpen" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="closeCreate()"></div>
            <div class="modal-panel animate-scale-in" @click.stop style="max-width:28rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Pengguna</span>
                        <h3 class="heading-md mt-1 mb-0">Tambah Pengguna</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="closeCreate()" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="label-app" for="create_name">Nama</label>
                        <input id="create_name" type="text" name="name" class="field" required placeholder="cth. Budi Santoso" x-model="form.name">
                        @error('name')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="label-app" for="create_email">Email</label>
                        <input id="create_email" type="email" name="email" class="field" required placeholder="nama@contoh.com" autocapitalize="none" autocorrect="off" spellcheck="false" x-model="form.email">
                        @error('email')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="create_role">Peran</label>
                            <select id="create_role" name="role" class="select-dark" required x-model="form.role">
                                <option value="pegawai">Pegawai</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="create_is_active">Status</label>
                            <select id="create_is_active" name="is_active" class="select-dark" required x-model="form.is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                            @error('is_active')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="create_password">Kata Sandi</label>
                            <input id="create_password" type="password" name="password" class="field" required placeholder="min. 8 karakter" autocomplete="new-password" x-model="form.password">
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="create_password_confirmation">Konfirmasi</label>
                            <input id="create_password_confirmation" type="password" name="password_confirmation" class="field" required placeholder="ulangi sandi" autocomplete="new-password" x-model="form.password_confirmation">
                        </div>
                        @error('password')<div class="col-12"><p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p></div>@enderror
                    </div>
                    <p class="font-mono text-mute mb-3" style="font-size:10px;">Kata sandi minimal 8 karakter. Zona waktu tampilan mengikuti perangkat pengguna.</p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-ghost border" @click="closeCreate()">Batal</button>
                        <button type="submit" class="btn-brand">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Edit --}}
        <div x-show="editOpen" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="closeEdit()"></div>
            <div class="modal-panel animate-scale-in" @click.stop style="max-width:28rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Pengguna</span>
                        <h3 class="heading-md mt-1 mb-0">Edit Pengguna</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="closeEdit()" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form :action="editAction()" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form_user_id" :value="form.id">
                    <div class="mb-3">
                        <label class="label-app" for="edit_name">Nama</label>
                        <input id="edit_name" type="text" name="name" class="field" required x-model="form.name">
                        @error('name')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="label-app" for="edit_email">Email</label>
                        <input id="edit_email" type="email" name="email" class="field" required autocapitalize="none" autocorrect="off" spellcheck="false" x-model="form.email">
                        @error('email')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="edit_role">Peran</label>
                            <select id="edit_role" name="role" class="select-dark" required x-model="form.role">
                                <option value="pegawai">Pegawai</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="edit_is_active">Status</label>
                            <select id="edit_is_active" name="is_active" class="select-dark" required x-model="form.is_active">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                            @error('is_active')<p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="label-app" for="edit_password">Kata Sandi Baru</label>
                            <input id="edit_password" type="password" name="password" class="field" placeholder="••••••••" autocomplete="new-password" x-model="form.password">
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="edit_password_confirmation">Konfirmasi</label>
                            <input id="edit_password_confirmation" type="password" name="password_confirmation" class="field" placeholder="••••••••" autocomplete="new-password" x-model="form.password_confirmation">
                        </div>
                        @error('password')<div class="col-12"><p class="text-13 text-danger mt-1 mb-0">{{ $message }}</p></div>@enderror
                    </div>
                    <p class="font-mono text-mute mb-3" style="font-size:10px;">Kosongkan sandi jika tidak diubah. Status tidak aktif = tidak bisa login.</p>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-ghost border" @click="closeEdit()">Batal</button>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Hapus --}}
        <div x-show="deleteId !== null" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="deleteId = null"></div>
            <div class="modal-panel animate-scale-in" @click.stop>
                <h3 class="heading-md">Hapus pengguna?</h3>
                <p class="mt-2 text-13 text-mute mb-0">Tindakan ini tidak dapat dibatalkan. Jika pengguna punya riwayat transaksi, nonaktifkan saja.</p>
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" @click="deleteId = null" class="btn-ghost border">Batal</button>
                    <template x-if="deleteId !== null">
                        <form :action="'{{ route('users.destroy', ':id') }}'.replace(':id', deleteId)" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger">Hapus</button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
