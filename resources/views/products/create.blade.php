<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Manajemen</span>
        <h1 class="page-title mt-1">Tambah Produk</h1>
        <p class="page-subtitle mb-0">Produk baru akan dibuatkan ambang batas default (bisa diatur di halaman detail).</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('products.index') }}" class="btn-ghost border">&larr; Kembali</a>
    </x-slot>

    <div class="container-app page-stack" style="max-width:36rem;">
        @if($errors->any())
            <div class="alert-error animate-fade-in"><span>&#10007;</span><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="surface-1 p-3 p-md-4">
            <div class="section-eyebrow-row mb-3">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Form Tambah</span>
            </div>
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="label-app" for="nama_produk">Nama Produk</label>
                    <input id="nama_produk" type="text" name="nama_produk" value="{{ old('nama_produk') }}"
                           class="field" required placeholder="cth. Isi Ulang Air Galon Ukuran 19 Liter">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="label-app" for="harga">Harga (Rp)</label>
                        <input id="harga" type="number" name="harga" value="{{ old('harga') }}"
                               class="field" min="0" step="any" required placeholder="0">
                    </div>
                    <div class="col-6">
                        <label class="label-app" for="stok">Stok</label>
                        <input id="stok" type="number" name="stok" value="{{ old('stok') }}"
                               class="field" min="0" required placeholder="0">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('products.index') }}" class="btn-ghost border">Batal</a>
                    <button type="submit" class="btn-brand">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
