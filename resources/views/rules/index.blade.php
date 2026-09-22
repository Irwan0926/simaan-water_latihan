@php
    $rulesPayload = $rules->map(fn($r) => [
        'id' => $r->id,
        'kode_rule' => $r->kode_rule,
        'penjualan' => $r->penjualan,
        'stok' => $r->stok,
        'tren' => $r->tren,
        'rekomendasi' => $r->rekomendasi,
        'kategori' => $r->kategori,
        'keterangan' => $r->keterangan,
        'is_active' => (bool) $r->is_active,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Basis Aturan</span>
        <h1 class="page-title mt-1">Kelola Aturan</h1>
        <p class="page-subtitle">Jumlah aturan tidak dibatasi. Dipakai Analisis Rule Based dengan metode Forward
            Chaining (First Match).</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('analysis.index') }}" class="btn-ghost border">Analisis Rule Based</a>
        <button type="button" class="btn-brand" @click="$dispatch('open-rule-create')">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Aturan
        </button>
    </x-slot>

    <div class="container-app page-stack" x-data="{
            rules: @js($rulesPayload),
            createOpen: false,
            editOpen: false,
            deleteId: null,
                form: {
                id: null, kode_rule: '', penjualan: '', stok: '', tren: '',
                rekomendasi: '', kategori: '',
                keterangan: '', is_active: true
            },
            openCreate() {
                this.form = {
                    id: null, kode_rule: '', penjualan: '', stok: '', tren: '',
                    rekomendasi: '', kategori: '',
                    keterangan: '', is_active: true
                };
                this.createOpen = true;
            },
            openEdit(rule) {
                this.form = {
                    id: rule.id,
                    kode_rule: rule.kode_rule,
                    penjualan: rule.penjualan ?? '',
                    stok: rule.stok ?? '',
                    tren: rule.tren ?? '',
                    rekomendasi: rule.rekomendasi,
                    kategori: rule.kategori ?? '',
                    keterangan: rule.keterangan ?? '',
                    is_active: !!rule.is_active
                };
                this.editOpen = true;
            },
            editAction() { return '{{ url('/rules') }}/' + this.form.id; }
        }" @open-rule-create.window="openCreate()">

        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>✓</span><span>{{ session('success') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in"><span>✗</span><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Total Aturan</span>
                    <p class="stat-value mt-2">{{ $count }}</p>
                    <span class="mono-micro text-mute">{{ $totalKombinasi }} kombinasi premis</span>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Aktif</span>
                    <p class="stat-value mt-2">{{ $rules->where('is_active', true)->count() }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Metode</span>
                    <p class="text-ink text-18 fw-medium mt-2 mb-0">Forward Chaining</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Cara pilih</span>
                    <p class="text-ink text-18 fw-medium mt-2 mb-0">First Match</p>
                </div>
            </div>
        </div>

        <div class="surface-1 p-4">
            <div class="section-eyebrow-row mb-2">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Cara kerja singkat</span>
            </div>
            <div class="row g-3 text-13 text-ink-soft">
                <div class="col-md-4">
                    <p class="mb-1 fw-medium text-ink">1. Baca data</p>
                    <p class="mb-0">Penjualan, stok, dan tren diubah jadi kondisi (Rendah/Sedang/Tinggi, dll).</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 fw-medium text-ink">2. Cek aturan berurutan</p>
                    <p class="mb-0">Urut kode aturan. Aturan pertama yang cocok dipakai.</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 fw-medium text-ink">3. Tampilkan saran</p>
                    <p class="mb-0">Contoh: Restock Prioritas, Promosi Agresif, Pertahankan.</p>
                </div>
            </div>
        </div>

        <div class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Daftar Aturan</span>
                    <span class="mono-micro">{{ $count }} item</span>
                </div>
                <button type="button" class="btn-brand" @click="openCreate()">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Aturan
                </button>
            </div>
            <div class="table-responsive">
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Penjualan</th>
                            <th>Stok</th>
                            <th>Tren</th>
                            <th>Saran</th>
                            <th>Kategori</th>
                            <th class="text-center">Status</th>
                            <th class="col-actions-icons">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr class="{{ $rule->is_active ? '' : 'opacity-50' }}">
                                <td class="text-ink fw-medium">{{ $rule->kode_rule }}</td>
                                <td>{{ $rule->penjualan ?? '—' }}</td>
                                <td>{{ $rule->stok ?? '—' }}</td>
                                <td>{{ $rule->tren ?? '—' }}</td>
                                <td class="text-ink-soft">{{ $rule->rekomendasi }}</td>
                                <td>
                                    @php
                                        $kat = $rule->kategori;
                                        $katBadge = match ($kat) {
                                            'restock' => 'badge-restock',
                                            'promosi' => 'badge-promosi',
                                            'pertahankan' => 'badge-pertahankan',
                                            'evaluasi' => 'badge-evaluasi',
                                            'pantau' => 'badge-pantau',
                                            default => 'badge-filled',
                                        };
                                    @endphp
                                    <span class="badge {{ $katBadge }}">{{ $kat ?? '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($rule->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-error">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ route('rules.show', $rule) }}"
                                            class="btn-action btn-action-icon btn-action-view" title="Detail aturan"
                                            aria-label="Detail aturan {{ $rule->kode_rule }}">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button type="button" class="btn-action btn-action-icon btn-action-edit"
                                            title="Edit aturan" aria-label="Edit aturan {{ $rule->kode_rule }}"
                                            @click="openEdit(rules.find(r => r.id === {{ $rule->id }}))">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-action btn-action-icon btn-action-delete"
                                            title="Hapus aturan" aria-label="Hapus aturan {{ $rule->kode_rule }}"
                                            @click="deleteId = {{ $rule->id }}">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <p class="text-mute mb-2">Belum ada aturan. Jalankan seeder atau tambah manual.</p>
                                    <button type="button" class="btn-brand" @click="openCreate()">Tambah Aturan</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create modal --}}
        <div x-show="createOpen" x-cloak x-transition.opacity class="modal-backdrop-app"
            style="display:none; overflow-y:auto;">
            <div class="modal-scrim" @click="createOpen = false"></div>
            <div class="modal-panel my-4" @click.stop style="max-width:36rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Basis Aturan</span>
                        <h3 class="heading-md mt-1 mb-0">Tambah Aturan</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="createOpen = false" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('rules.store') }}" class="d-flex flex-column gap-3">
                    @csrf
                    @include('rules._form')
                    <div class="d-flex gap-2 justify-content-end pt-1">
                        <button type="button" class="btn-ghost border" @click="createOpen = false">Batal</button>
                        <button type="submit" class="btn-brand">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit modal --}}
        <div x-show="editOpen" x-cloak x-transition.opacity class="modal-backdrop-app"
            style="display:none; overflow-y:auto;">
            <div class="modal-scrim" @click="editOpen = false"></div>
            <div class="modal-panel my-4" @click.stop style="max-width:36rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Basis Aturan</span>
                        <h3 class="heading-md mt-1 mb-0">Edit Aturan</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="editOpen = false" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" :action="editAction()" class="d-flex flex-column gap-3">
                    @csrf
                    @method('PUT')
                    @include('rules._form')
                    <div class="d-flex gap-2 justify-content-end pt-1">
                        <button type="button" class="btn-ghost border" @click="editOpen = false">Batal</button>
                        <button type="submit" class="btn-brand">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete confirm --}}
        <div x-show="deleteId !== null" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="deleteId = null"></div>
            <div class="modal-panel" style="max-width:24rem;">
                <h3 class="heading-sm mb-2">Hapus Aturan?</h3>
                <p class="text-13 text-mute mb-4">Aturan yang dihapus tidak bisa dikembalikan.</p>
                <form method="POST" :action="'{{ url('/rules') }}/' + deleteId"
                    class="d-flex gap-2 justify-content-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-ghost border" @click="deleteId = null">Batal</button>
                    <button type="submit" class="btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>