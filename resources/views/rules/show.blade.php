@php
    $rulePayload = [
        'id' => $rule->id,
        'kode_rule' => $rule->kode_rule,
        'penjualan' => $rule->penjualan,
        'stok' => $rule->stok,
        'tren' => $rule->tren,
        'rekomendasi' => $rule->rekomendasi,
        'kategori' => $rule->kategori,
        'keterangan' => $rule->keterangan,
        'is_active' => (bool) $rule->is_active,
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Basis Aturan</span>
        <h1 class="page-title mt-1">Aturan {{ $rule->kode_rule }}</h1>
        <p class="page-subtitle mb-0">Detail premis, saran, dan status aturan pada Forward Chaining (First Match).</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('rules.index') }}" class="btn-icon" title="Kembali ke Daftar Aturan"
            aria-label="Kembali ke Daftar Aturan">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <button type="button" class="btn-brand" @click="$dispatch('open-rule-edit')">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Aturan
        </button>
    </x-slot>

    <div class="container-app page-stack" x-data="{
            editOpen: false,
            deleteOpen: false,
            form: @js($rulePayload),
            editAction() { return '{{ url('/rules') }}/' + this.form.id; }
        }" @open-rule-edit.window="editOpen = true">

        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>&#10003;</span><span>{{ session('success') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in"><span>&#10007;</span><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="row g-3">
            <div class="col-6 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Kode</span>
                    <p class="stat-value mt-2">{{ $rule->kode_rule }}</p>
                    <p class="text-mute mb-0" style="font-size:11px;">Menentukan urutan pengecekan</p>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Kategori</span>
                    <p class="mb-0 mt-2">
                        <span class="badge badge-brand">{{ $rule->kategori ?? '-' }}</span>
                    </p>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Status</span>
                    <p class="mb-0 mt-2">
                        @if($rule->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-error">Nonaktif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="surface-1 p-4">
            <div class="section-eyebrow-row mb-3">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Logika Aturan</span>
            </div>
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                        <span class="mono-caps text-ink d-block mb-2">JIKA</span>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-filled">Penjualan = {{ $rule->penjualan ?? '—' }}</span>
                            <span class="badge badge-filled">Stok = {{ $rule->stok ?? '—' }}</span>
                            <span class="badge badge-filled">Tren = {{ $rule->tren ?? '—' }}</span>
                        </div>
                        <span class="mono-caps text-ink d-block mb-2">MAKA</span>
                        <p class="text-ink text-18 fw-medium mb-0">{{ $rule->rekomendasi }}</p>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                        <span class="mono-caps text-ink d-block mb-2">Dibuat</span>
                        <p class="text-13 text-mute mb-3">
                            {{ \App\Support\Waktu::tanggalPanjang($rule->created_at, 'd M Y H:i') }}
                        </p>
                        <span class="mono-caps text-ink d-block mb-2">Diperbarui</span>
                        <p class="text-13 text-mute mb-0">
                            {{ \App\Support\Waktu::tanggalPanjang($rule->updated_at, 'd M Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="surface-1 p-4">
            <div class="section-eyebrow-row mb-3">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Keterangan</span>
            </div>
            <p class="text-13 {{ $rule->keterangan ? 'text-ink-soft' : 'text-mute' }} mb-0">
                {{ $rule->keterangan ?: 'Belum ada keterangan untuk aturan ini.' }}
            </p>
        </div>

        <div class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Aturan dengan Premis Sama</span>
                    <span class="mono-micro">{{ $konflik->count() }} item</span>
                </div>
            </div>

            @if($tertutup)
                <div class="px-4 py-3">
                    <div class="alert-error animate-fade-in mb-0">
                        <span>&#10007;</span>
                        <span>Aturan ini tidak akan pernah terpakai. Ada aturan aktif lain dengan premis identik yang
                            dicek lebih dulu (First Match).</span>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Saran</th>
                            <th class="text-center">Status</th>
                            <th class="col-actions-icons-1">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($konflik as $k)
                            <tr class="{{ $k->is_active ? '' : 'opacity-50' }}">
                                <td class="text-ink fw-medium">{{ $k->kode_rule }}</td>
                                <td class="text-ink-soft">{{ $k->rekomendasi }}</td>
                                <td class="text-center">
                                    @if($k->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-error">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('rules.show', $k) }}"
                                        class="btn-action btn-action-icon btn-action-view" title="Detail aturan"
                                        aria-label="Detail aturan {{ $k->kode_rule }}">
                                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-mute">
                                    Tidak ada aturan lain dengan kombinasi premis yang sama. Aturan ini unik.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <button type="button" class="btn-ghost border text-error" @click="deleteOpen = true">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Aturan
            </button>
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
        <div x-show="deleteOpen" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="deleteOpen = false"></div>
            <div class="modal-panel" style="max-width:24rem;">
                <h3 class="heading-sm mb-2">Hapus Aturan?</h3>
                <p class="text-13 text-mute mb-4">Aturan {{ $rule->kode_rule }} yang dihapus tidak bisa dikembalikan.
                </p>
                <form method="POST" action="{{ route('rules.destroy', $rule) }}"
                    class="d-flex gap-2 justify-content-end">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-ghost border" @click="deleteOpen = false">Batal</button>
                    <button type="submit" class="btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>