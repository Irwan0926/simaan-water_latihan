@php
    $penjualanCanonical = [1 => 'Rendah', 2 => 'Sedang', 3 => 'Tinggi'];
    $stokCanonical = [1 => 'Sedikit', 2 => 'Aman', 3 => 'Banyak'];

    $penjualan = $product->thresholds->where('tipe', 'penjualan')->keyBy('level');
    $stok = $product->thresholds->where('tipe', 'stok')->keyBy('level');
@endphp

<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Manajemen</span>
        <h1 class="page-title mt-1">{{ $product->nama_produk }}</h1>
        <p class="page-subtitle mb-0">Detail produk &amp; ambang batas kondisi yang dipakai Analisis Rule Based.</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ $backUrl }}" class="btn-icon" title="Kembali ke {{ $backLabel }}"
            aria-label="Kembali ke {{ $backLabel }}">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <button type="button" class="btn-ghost border"
            onclick="window.dispatchEvent(new CustomEvent('open-product-edit'))">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Produk
        </button>
    </x-slot>

    <div class="container-app page-stack" x-data="{
            editOpen: false,
            deleteOpen: false,
            form: {
                id: {{ $product->id }},
                nama_produk: @js($product->nama_produk),
                harga: @js($product->harga),
                stok: {{ $product->stok }}
            },
            openEdit() { this.editOpen = true; },
            closeEdit() { this.editOpen = false; },
            editAction() { return '{{ url('/products') }}/' + this.form.id; }
        }" @open-product-edit.window="openEdit()">
        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>&#10003;</span><span>{{ session('success') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in"><span>&#10007;</span><span>{{ $errors->first() }}</span></div>
        @endif

        {{-- Info produk --}}
        <div class="surface-1 p-4">
            <div class="section-eyebrow-row mb-3">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Info Produk</span>
            </div>
            <div class="row g-3">
                <div class="col-4">
                    <div class="stat h-100">
                        <span class="mono-caps">Harga</span>
                        <p class="stat-value mt-2">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat h-100">
                        <span class="mono-caps">Stok Saat Ini</span>
                        <p class="stat-value mt-2 tabular-nums">{{ $product->stok }}</p>
                        @php
                            $stokKondisiLabel = ['sedikit' => 'Sedikit', 'cukup' => 'Cukup', 'banyak' => 'Banyak'];
                            $stokKondisiBadge = ['sedikit' => 'badge-error', 'cukup' => 'badge-amber', 'banyak' => 'badge-success'];
                        @endphp
                        <p class="mb-0 mt-1">
                            <span class="badge {{ $stokKondisiBadge[$stokKondisi] ?? 'badge-neutral' }}">
                                Kondisi: {{ $stokKondisiLabel[$stokKondisi] ?? '-' }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stat h-100">
                        <span class="mono-caps">Dibuat</span>
                        <p class="text-ink text-18 fw-medium mt-2 mb-0">
                            {{ \App\Support\Waktu::tanggalPanjang($product->created_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ambang batas kondisi --}}
        <div class="surface-1 p-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Ambang Batas Kondisi</span>
                </div>
                <span class="mono-micro">3 level · level 3 tanpa batas atas</span>
            </div>
            <p class="text-13 text-mute mb-4">
                Angka penjualan &amp; stok diubah jadi kondisi memakai batas di bawah.
                Kondisi ini dipakai rule base (Forward Chaining) untuk menentukan saran.
            </p>

            <form action="{{ route('products.thresholds', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Penjualan --}}
                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="mono-caps text-ink">Kondisi Penjualan</span>
                                <!-- <span class="text-mute" style="font-size:11px;">per minggu</span> -->
                            </div>
                            <div class="table-responsive">
                                <table class="tbl mb-0" style="font-size:12.5px;">
                                    <thead>
                                        <tr>
                                            <th style="width:2.5rem;">Lv</th>
                                            <th>Label</th>
                                            <th class="text-end" style="width:7rem;">Batas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= 3; $i++)
                                            @php
                                                $th = $penjualan->get($i);
                                                $label = $th?->label ?? $penjualanCanonical[$i];
                                                $batas = $th?->batas ?? 0;
                                                $labelOld = old("thresholds.penjualan.{$i}.label", $label);
                                                $batasOld = old("thresholds.penjualan.{$i}.batas", $batas);
                                            @endphp
                                            <tr>
                                                <td class="text-mute font-mono" style="text-wrap-mode:nowrap;">{{ $i }}</td>
                                                <td>
                                                    <select name="thresholds[penjualan][{{ $i }}][label]"
                                                        class="select-dark" style="height:34px;" required>
                                                        @foreach($penjualanCanonical as $opt)
                                                            <option value="{{ $opt }}" {{ $labelOld === $opt ? 'selected' : '' }}>
                                                                {{ $opt }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-mute d-block" style="font-size:10px;">Rule:
                                                        {{ $penjualanCanonical[$i] }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($i < 3)
                                                        <input type="number" name="thresholds[penjualan][{{ $i }}][batas]"
                                                            value="{{ $batasOld }}" class="field text-end" style="height:34px;"
                                                            min="0" required>
                                                    @else
                                                        <input type="hidden" name="thresholds[penjualan][{{ $i }}][batas]"
                                                            value="">
                                                        <span class="text-mute tabular-nums" id="pj_hint">&gt; <span
                                                                data-pj-hint>{{ $penjualan->get(2)?->batas ?? 0 }}</span></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Stok --}}
                    <div class="col-12 col-lg-6">
                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="mono-caps text-ink">Kondisi Stok</span>
                                <!-- <span class="text-mute" style="font-size:11px;">per minggu</span> -->
                            </div>
                            <div class="table-responsive">
                                <table class="tbl mb-0" style="font-size:12.5px;">
                                    <thead>
                                        <tr>
                                            <th style="width:2.5rem;">Lv</th>
                                            <th>Label</th>
                                            <th class="text-end" style="width:7rem;">Batas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 1; $i <= 3; $i++)
                                            @php
                                                $th = $stok->get($i);
                                                $label = $th?->label ?? $stokCanonical[$i];
                                                $batas = $th?->batas ?? 0;
                                                $labelOld = old("thresholds.stok.{$i}.label", $label);
                                                $batasOld = old("thresholds.stok.{$i}.batas", $batas);
                                            @endphp
                                            <tr>
                                                <td class="text-mute font-mono" style="text-wrap-mode:nowrap;">{{ $i }}</td>
                                                <td>
                                                    <input type="text" name="thresholds[stok][{{ $i }}][label]"
                                                        value="{{ $labelOld }}" class="field" style="height:34px;"
                                                        maxlength="30" required>
                                                    <span class="text-mute d-block" style="font-size:10px;">Rule:
                                                        {{ $stokCanonical[$i] }}</span>
                                                </td>
                                                <td class="text-end">
                                                    @if($i < 3)
                                                        <input type="number" name="thresholds[stok][{{ $i }}][batas]"
                                                            value="{{ $batasOld }}" class="field text-end" style="height:34px;"
                                                            min="0" required>
                                                    @else
                                                        <input type="hidden" name="thresholds[stok][{{ $i }}][batas]" value="">
                                                        <span class="text-mute tabular-nums" id="sk_hint">&gt; <span
                                                                data-sk-hint>{{ $stok->get(2)?->batas ?? 0 }}</span></span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                    <button type="button" class="btn-ghost border text-error" @click="deleteOpen = true">
                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Hapus Produk
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn-brand">
                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Ambang Batas
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Modal: Edit Produk --}}
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
                        <label class="label-app" for="show_edit_nama_produk">Nama Produk</label>
                        <input id="show_edit_nama_produk" type="text" name="nama_produk" class="field" required
                            x-model="form.nama_produk">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="label-app" for="show_edit_harga">Harga (Rp)</label>
                            <input id="show_edit_harga" type="number" name="harga" class="field" min="0" step="any"
                                required x-model="form.harga">
                        </div>
                        <div class="col-6">
                            <label class="label-app" for="show_edit_stok">Stok</label>
                            <input id="show_edit_stok" type="number" name="stok" class="field" min="0" required
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

        {{-- Modal: Hapus Produk --}}
        <div x-show="deleteOpen" x-cloak x-transition.opacity class="modal-backdrop-app" style="display:none;">
            <div class="modal-scrim" @click="deleteOpen = false"></div>
            <div class="modal-panel animate-scale-in" @click.stop>
                <h3 class="heading-md">Hapus produk?</h3>
                <p class="mt-2 text-13 text-mute mb-0">Tindakan ini tidak dapat dibatalkan. Semua ambang batas kondisi
                    untuk produk ini juga akan terhapus.</p>
                <div class="mt-3 d-flex justify-content-end gap-2">
                    <button type="button" @click="deleteOpen = false" class="btn-ghost border">Batal</button>
                    <form action="{{ route('products.destroy', $product) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var pj2 = document.querySelector('input[name="thresholds[penjualan][2][batas]"]');
            var sk2 = document.querySelector('input[name="thresholds[stok][2][batas]"]');
            if (pj2) {
                pj2.addEventListener('input', function () {
                    var hint = document.querySelector('[data-pj-hint]');
                    if (hint) hint.textContent = this.value;
                });
            }
            if (sk2) {
                sk2.addEventListener('input', function () {
                    var hint = document.querySelector('[data-sk-hint]');
                    if (hint) hint.textContent = this.value;
                });
            }
        })();
    </script>
</x-app-layout>