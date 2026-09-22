<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Kasir</span>
        <h1 class="page-title mt-1">Edit Transaksi</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('sales.history') }}" class="btn-ghost border">&larr; Riwayat</a>
    </x-slot>

    <div class="container-app page-stack">
        @if(session('error'))
            <div class="alert-error animate-fade-in"><span>✗</span><span>{{ session('error') }}</span></div>
        @endif
        @if($errors->any())
            <div class="alert-error animate-fade-in">
                <span>✗</span>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('sales.update', $sale) }}" method="POST" x-data="{
            qty: @js($quantities->all()),
            initialQty: @js($quantities->all()),
            prices: @js($prices->all()),
            stocks: @js($products->pluck('stok','id')->all()),
            stockOf(id) {
                return Math.max(0, parseInt(this.stocks[id] || 0) + parseInt(this.initialQty[id] || 0));
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
                return this.getQty(id) < this.stockOf(id);
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
            @method('PUT')
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="surface-1 overflow-hidden">
                        <div class="panel-header">
                            <div>
                                <span class="mono-eyebrow">{{ $sale->invoice }}</span>
                                <p class="section-title mt-1 mb-0">Daftar Produk</p>
                            </div>
                            <span class="mono-micro">Ubah jumlah item</span>
                        </div>
                        <div class="table-responsive">
                            <table class="tbl mb-0">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Stok tersedia</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td class="text-ink fw-medium">{{ $product->nama_produk }}</td>
                                            <td class="text-end">Rp {{ number_format($prices->get($product->id, $product->harga), 0, ',', '.') }}</td>
                                            <td class="text-center tabular-nums" x-text="stockOf({{ $product->id }})"></td>
                                            <td class="text-center">
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button type="button" @click="dec({{ $product->id }})" :disabled="!canDec({{ $product->id }})" class="qty-btn" aria-label="Kurangi">-</button>
                                                    <input type="number"
                                                           name="items[{{ $loop->index }}][qty]"
                                                           x-model.number="qty[{{ $product->id }}]"
                                                           @input="setQty({{ $product->id }}, $event.target.value)"
                                                           @blur="setQty({{ $product->id }}, qty[{{ $product->id }}])"
                                                           value="0"
                                                           min="0"
                                                           :max="stockOf({{ $product->id }})"
                                                           class="qty-input"
                                                           placeholder="0">
                                                    <button type="button" @click="inc({{ $product->id }})" :disabled="!canInc({{ $product->id }})" class="qty-btn" aria-label="Tambah">+</button>
                                                </div>
                                                <input type="hidden" name="items[{{ $loop->index }}][product_id]" value="{{ $product->id }}">
                                            </td>
                                            <td class="text-end text-ink fw-medium tabular-nums" x-text="'Rp ' + (getQty({{ $product->id }}) * Number(prices[{{ $product->id }}] || 0)).toLocaleString('id-ID')">Rp 0</td>
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
                        </div>
                        <button type="submit" class="btn-brand w-100 mt-3 justify-content-center">Simpan Perubahan</button>
                        <p class="mt-2 text-13 text-mute text-center mb-0">Stok akan disesuaikan berdasarkan perubahan jumlah.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
