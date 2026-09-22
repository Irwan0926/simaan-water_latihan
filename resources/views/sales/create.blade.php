<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Kasir</span>
        <h1 class="page-title mt-1">Input Transaksi</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('dashboard') }}" class="btn-ghost border">← Dashboard</a>
    </x-slot>

    <div class="container-app page-stack">
        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>✓</span><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="alert-error animate-fade-in"><span>✗</span><span>{{ session('error') }}</span></div>
        @endif

        <form action="{{ route('sales.store') }}" method="POST" x-data="{
            qty: {},
            prices: @js($products->pluck('harga','id')->all()),
            stocks: @js($products->pluck('stok','id')->all()),
            stockOf(id) {
                return Math.max(0, parseInt(this.stocks[id] || 0));
            },
            getQty(id) {
                return Math.max(0, parseInt(this.qty[id] || 0));
            },
            setQty(id, value) {
                const max = this.stockOf(id);
                let n = parseInt(value);
                if (isNaN(n) || n < 0) n = 0;
                if (n > max) n = max;
                this.qty[id] = n;
            },
            dec(id) {
                this.setQty(id, this.getQty(id) - 1);
            },
            inc(id) {
                this.setQty(id, this.getQty(id) + 1);
            },
            canInc(id) {
                return this.stockOf(id) > 0 && this.getQty(id) < this.stockOf(id);
            },
            canDec(id) {
                return this.getQty(id) > 0;
            },
            total() {
                let sum = 0;
                for (const id in this.qty) {
                    const q = this.getQty(id);
                    if (q > 0) sum += q * Number(this.prices[id] || 0);
                }
                return sum;
            },
            items() {
                return Object.keys(this.qty).filter(id => this.getQty(id) > 0).length;
            }
        }">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="surface-1 overflow-hidden">
                        <div class="panel-header">
                            <div class="section-eyebrow-row mb-0">
                                <span class="eyebrow-rule"></span>
                                <span class="mono-eyebrow">Daftar Produk</span>
                            </div>
                            <span class="mono-micro">{{ $products->count() }} item</span>
                        </div>
                        <div class="table-responsive">
                            <table class="tbl mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-center">Kondisi</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        @php
                                            $outOfStock = (int) $product->stok <= 0;
                                            if ($outOfStock) {
                                                $stokBadge = 'badge-empty';
                                                $stokLabel = 'Habis';
                                            } elseif ($product->stok <= 50) {
                                                $stokBadge = 'badge-error';
                                                $stokLabel = 'Menipis';
                                            } elseif ($product->stok <= 100) {
                                                $stokBadge = 'badge-amber';
                                                $stokLabel = 'Aman';
                                            } else {
                                                $stokBadge = 'badge-success';
                                                $stokLabel = 'Banyak';
                                            }
                                        @endphp
                                        <tr class="{{ $outOfStock ? 'row-stock-empty' : '' }}">
                                            <td>
                                                <span class="fw-medium {{ $outOfStock ? 'text-mute' : 'text-ink' }}">{{ $product->nama_produk }}</span>
                                                @if($outOfStock)
                                                    <span class="d-block text-mute mt-1" style="font-size:11px;">Tidak tersedia</span>
                                                @endif
                                            </td>
                                            <td class="text-end">Rp {{ number_format($product->harga,0,',','.') }}</td>
                                            <td class="text-center tabular-nums {{ $outOfStock ? 'text-mute' : '' }}">{{ $product->stok }}</td>
                                            <td class="text-center"><span class="badge {{ $stokBadge }}" title="Stok: {{ $product->stok }}">{{ $stokLabel }}</span></td>
                                            <td class="text-center">
                                                @if($outOfStock)
                                                    <span class="text-mute text-13">—</span>
                                                    <input type="hidden" name="items[{{ $loop->index }}][qty]" value="0">
                                                @else
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <button
                                                            type="button"
                                                            @click="dec({{ $product->id }})"
                                                            :disabled="!canDec({{ $product->id }})"
                                                            class="qty-btn"
                                                            aria-label="Kurangi">−</button>
                                                        <input
                                                            type="number"
                                                            name="items[{{ $loop->index }}][qty]"
                                                            x-model.number="qty[{{ $product->id }}]"
                                                            @input="setQty({{ $product->id }}, $event.target.value)"
                                                            @blur="setQty({{ $product->id }}, qty[{{ $product->id }}])"
                                                            value="0"
                                                            min="0"
                                                            max="{{ (int) $product->stok }}"
                                                            class="qty-input"
                                                            placeholder="0">
                                                        <button
                                                            type="button"
                                                            @click="inc({{ $product->id }})"
                                                            :disabled="!canInc({{ $product->id }})"
                                                            class="qty-btn"
                                                            aria-label="Tambah">+</button>
                                                    </div>
                                                @endif
                                                <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                                            </td>
                                            <td class="text-end text-ink fw-medium tabular-nums"
                                                @if(!$outOfStock)
                                                    x-text="'Rp ' + (getQty({{ $product->id }}) * {{ $product->harga }}).toLocaleString('id-ID')"
                                                @endif
                                            >Rp 0</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="surface-1 p-3 sticky-summary">
                        <span class="mono-eyebrow">Ringkasan</span>
                        <h3 class="heading-sm mt-2">Total Transaksi</h3>
                        <p class="stat-value mt-2" x-text="'Rp ' + total().toLocaleString('id-ID')">Rp 0</p>

                        <div class="mt-3 pt-3 border-top border-hairline">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-mute text-13">Item dipilih</span>
                                <span class="text-ink text-13 fw-medium tabular-nums" x-text="items() + ' produk'">0 produk</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-mute text-13">Subtotal</span>
                                <span class="text-ink-soft text-13 tabular-nums" x-text="'Rp ' + total().toLocaleString('id-ID')">Rp 0</span>
                            </div>
                        </div>

                        <button type="submit" class="btn-brand w-100 mt-3 justify-content-center">
                            Simpan Transaksi
                        </button>
                        <p class="mt-2 text-13 text-mute text-center mb-0">Produk dengan jumlah 0 tidak ikut disimpan. Jumlah tidak boleh melebihi stok.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
