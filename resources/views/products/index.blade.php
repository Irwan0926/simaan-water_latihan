@php
    $productsPayload = $products->map(fn($p) => [
        'id' => $p->id,
        'nama_produk' => $p->nama_produk,
        'harga' => $p->harga,
        'stok' => $p->stok,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Manajemen</span>
        <h1 class="page-title mt-1">Kelola Produk</h1>
        <p class="page-subtitle">Hanya admin yang dapat menambah, mengedit, dan menghapus produk.</p>
    </x-slot>
    <x-slot name="header-actions">
        <button type="button" class="btn-brand" onclick="window.dispatchEvent(new CustomEvent('open-product-create'))">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Produk
        </button>
    </x-slot>

    <div class="container-app page-stack" x-data="{
            products: @js($productsPayload),
            deleteId: null,
            createOpen: false,
            editOpen: false,
            form: { id: null, nama_produk: '', harga: '', stok: '' },
            openCreate() {
                this.form = { id: null, nama_produk: '', harga: '', stok: '' };
                this.createOpen = true;
            },
            openEdit(product) {
                this.form = {
                    id: product.id,
                    nama_produk: product.nama_produk,
                    harga: product.harga,
                    stok: product.stok
                };
                this.editOpen = true;
            },
            closeCreate() { this.createOpen = false; },
            closeEdit() { this.editOpen = false; },
            editAction() {
                return '{{ url('/products') }}/' + this.form.id;
            }
        }" @open-product-create.window="openCreate()">
        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>✓</span><span>{{ session('success') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in">
                <span>✗</span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @php
            $totalProduk = $products->count();
            $stokSedikit = $stokCounts['sedikit'];
            $stokCukup = $stokCounts['cukup'];
            $stokBanyak = $stokCounts['banyak'];
            $nilaiInventori = $products->sum(fn($p) => (float) $p->harga * (int) $p->stok);
        @endphp

        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="stat h-100">
                    <span class="mono-caps">Jumlah Produk</span>
                    <p class="stat-value mt-2">{{ $totalProduk }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Nilai stok Rp
                        {{ number_format($nilaiInventori, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat stat-accent stat-accent-error h-100">
                    <span class="mono-caps text-error">Stok Sedikit</span>
                    <p class="stat-value text-error mt-2">{{ $stokSedikit }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Dibawah batas level 1</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat stat-accent stat-accent-amber h-100">
                    <span class="mono-caps" style="color: #93670b;">Stok Cukup</span>
                    <p class="stat-value mt-2" style="color: #93670b;">{{ $stokCukup }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Antara level 1 &amp; level 2</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat stat-accent h-100">
                    <span class="mono-caps text-brand-deep">Stok Banyak</span>
                    <p class="stat-value text-brand-deep mt-2">{{ $stokBanyak }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Diatas batas level 2</p>
                </div>
            </div>
        </div>

        <div class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Daftar Produk</span>
                    <span class="mono-micro">{{ $totalProduk }} item</span>
                </div>
                <button type="button" class="btn-brand" @click="openCreate()">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Produk
                </button>
            </div>

            <div class="table-responsive">
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Nama Produk</th>
                            <th class="col-price text-end">Harga</th>
                            <th class="col-stock text-center">Stok</th>
                            <th class="col-actions-icons">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $kondisi = $stokKondisiMap[$product->id] ?? 'sedikit';
                                $isEmpty = (int) $product->stok <= 0;
                                $stokLabel = (string) $product->stok;
                                if ($isEmpty) {
                                    $stokBadge = 'badge-empty';
                                    $stokLabel = 'Habis';
                                } elseif ($kondisi === 'sedikit') {
                                    $stokBadge = 'badge-error';
                                } elseif ($kondisi === 'cukup') {
                                    $stokBadge = 'badge-amber';
                                } else {
                                    $stokBadge = 'badge-success';
                                }
                            @endphp
                            <tr class="{{ $isEmpty ? 'row-stock-empty' : '' }}">
                                <td class="text-mute font-mono">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <a href="{{ route('products.show', $product) }}"
                                        class="fw-medium text-decoration-none {{ $isEmpty ? 'text-mute' : 'text-ink' }}">
                                        {{ $product->nama_produk }}
                                    </a>
                                    @if($isEmpty)
                                        <span class="d-block text-mute mt-1" style="font-size:11px;">Stok kosong · restock
                                            diperlukan</span>
                                    @endif
                                </td>
                                <td class="text-end tabular-nums">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $stokBadge }}"
                                        title="Stok: {{ $product->stok }}">{{ $stokLabel }}</span>
                                </td>
                                <td>
                                    <div class="product-actions d-flex align-items-center gap-1">
                                        <a href="{{ route('products.show', $product) }}"
                                            class="btn-action btn-action-icon btn-action-view" title="Detail & ambang batas"
                                            aria-label="Detail {{ $product->nama_produk }}">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <button type="button" class="btn-action btn-action-icon btn-action-edit"
                                            title="{{ $isEmpty ? 'Edit & restock' : 'Edit' }}"
                                            aria-label="{{ $isEmpty ? 'Restock' : 'Edit' }} {{ $product->nama_produk }}"
                                            @click="openEdit(@js(['id' => $product->id, 'nama_produk' => $product->nama_produk, 'harga' => $product->harga, 'stok' => $product->stok]))">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-action btn-action-icon btn-action-delete"
                                            title="Hapus" aria-label="Hapus {{ $product->nama_produk }}"
                                            @click="deleteId = {{ $product->id }}">
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
                                <td colspan="5" class="py-5 text-center">
                                    <div class="d-flex flex-column align-items-center gap-2 py-3">
                                        <div class="empty-icon">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="1.6">
                                                <path d="M20 7l-8 8-4-4" />
                                            </svg>
                                        </div>
                                        <p class="text-ink text-14 fw-medium mb-0">Belum ada produk.</p>
                                        <button type="button" class="btn-brand mt-1" @click="openCreate()">Tambah
                                            produk</button>
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
            <div class="modal-panel animate-scale-in" @click.stop style="max-width:26rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Produk</span>
                        <h3 class="heading-md mt-1 mb-0">Tambah Produk</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="closeCreate()" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="label-app" for="create_nama_produk">Nama Produk</label>
                        <input id="create_nama_produk" type="text" name="nama_produk" class="field" required
                            placeholder="cth. Isi Ulang Air Galon Ukuran 19 Liter" x-model="form.nama_produk">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="create_harga">Harga (Rp)</label>
                            <input id="create_harga" type="number" name="harga" class="field" min="0" step="any"
                                required placeholder="0" x-model="form.harga">
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="create_stok">Stok</label>
                            <input id="create_stok" type="number" name="stok" class="field" min="0" required
                                placeholder="0" x-model="form.stok">
                        </div>
                    </div>
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
            <div class="modal-panel animate-scale-in" @click.stop style="max-width:26rem;">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <span class="mono-eyebrow">Produk</span>
                        <h3 class="heading-md mt-1 mb-0">Edit Produk</h3>
                    </div>
                    <button type="button" class="btn-icon" @click="closeEdit()" aria-label="Tutup">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form :action="editAction()" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="label-app" for="edit_nama_produk">Nama Produk</label>
                        <input id="edit_nama_produk" type="text" name="nama_produk" class="field" required
                            x-model="form.nama_produk">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="edit_harga">Harga (Rp)</label>
                            <input id="edit_harga" type="number" name="harga" class="field" min="0" step="any" required
                                x-model="form.harga">
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="edit_stok">Stok</label>
                            <input id="edit_stok" type="number" name="stok" class="field" min="0" required
                                x-model="form.stok">
                        </div>
                    </div>
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
                <h3 class="heading-md">Hapus produk?</h3>
                <p class="mt-2 text-13 text-mute mb-0">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" @click="deleteId = null" class="btn-ghost border">Batal</button>
                    <template x-if="deleteId !== null">
                        <form :action="'{{ route('products.destroy', ':id') }}'.replace(':id', deleteId)" method="POST">
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