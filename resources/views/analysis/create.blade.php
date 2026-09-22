<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Analisis Rule Based</span>
        <h1 class="page-title mt-1">Generate Analisis</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('analysis.index') }}" class="btn-ghost border">&larr; Kembali</a>
    </x-slot>

    <div class="py-4">
        <div class="container-app" style="max-width:42rem;">
            <div class="surface-1 overflow-hidden">
                <div class="p-4 p-lg-5 bg-ink text-on-primary position-relative overflow-hidden">
                    <div class="dot-grid position-absolute top-0 start-0 w-100 h-100" style="opacity:0.05; background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 16px 16px;"></div>
                    <span class="font-mono text-uppercase tracking-wide text-ash position-relative" style="font-size:10px;">Analisis Rule Based</span>
                    <h1 class="display-sm text-on-primary mt-3 position-relative mb-0" style="color:#ffffff;">Buat Saran Otomatis</h1>
                    <p class="mt-3 text-ash text-14 lh-base mb-0 position-relative" style="color:#b9b9b9; max-width:36rem;">
                        Pilih produk dan periode. Sistem akan membaca data penjualan &amp; stok,
                        lalu memberi saran dengan metode Forward Chaining.
                    </p>
                </div>

                <form action="{{ route('analysis.generate') }}" method="POST">
                    @csrf
                    <div class="p-4 p-lg-5">
                        <div class="mb-4">
                            <x-input-label for="product_id" value="Produk" />
                            <select id="product_id" name="product_id" required class="select-dark">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->nama_produk }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <span class="label-app">Periode Analisis</span>
                            <div class="row g-2">
                                <div class="col-3">
                                    <label class="btn-app-tab w-100 justify-content-center py-4 h-auto cursor-pointer">
                                        <input type="radio" name="periode" value="harian" checked class="sr-only">
                                        <span style="font-size:11px; letter-spacing:0.14em; text-transform:uppercase;">1 Hari</span>
                                    </label>
                                </div>
                                <div class="col-3">
                                    <label class="btn-app-tab w-100 justify-content-center py-4 h-auto cursor-pointer">
                                        <input type="radio" name="periode" value="3hari" class="sr-only">
                                        <span style="font-size:11px; letter-spacing:0.14em; text-transform:uppercase;">3 Hari</span>
                                    </label>
                                </div>
                                <div class="col-3">
                                    <label class="btn-app-tab w-100 justify-content-center py-4 h-auto cursor-pointer">
                                        <input type="radio" name="periode" value="mingguan" class="sr-only">
                                        <span style="font-size:11px; letter-spacing:0.14em; text-transform:uppercase;">Mingguan</span>
                                    </label>
                                </div>
                                <div class="col-3">
                                    <label class="btn-app-tab w-100 justify-content-center py-4 h-auto cursor-pointer">
                                        <input type="radio" name="periode" value="bulanan" class="sr-only">
                                        <span style="font-size:11px; letter-spacing:0.14em; text-transform:uppercase;">Bulanan</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-brand w-100 justify-content-center">
                            Generate Analisis
                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
