<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Laporan</span>
        <h1 class="page-title mt-1">Penjualan</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('dashboard') }}" class="btn-ghost border">← Dashboard</a>
    </x-slot>

    <div class="container-app page-stack">
        <div class="row g-3">
            <div class="col-4">
                <div class="stat h-100">
                    <span class="mono-caps">Transaksi</span>
                    <p class="stat-value mt-2">{{ $totalTransaksi }}</p>
                </div>
            </div>
            <div class="col-4">
                <div class="stat h-100">
                    <span class="mono-caps">Pendapatan</span>
                    <p class="stat-value mt-2">Rp {{ number_format($totalPendapatan,0,',','.') }}</p>
                </div>
            </div>
            <div class="col-4">
                <div class="stat h-100">
                    <span class="mono-caps">Unit Terjual</span>
                    <p class="stat-value mt-2">{{ $totalProdukTerjual }}</p>
                </div>
            </div>
        </div>

        <div class="surface-1 p-3">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <span class="mono-eyebrow">Filter</span>
                    <p class="section-title mt-1 mb-0">Periode penjualan</p>
                </div>
                <button type="submit"
                        form="report-filter-form"
                        formaction="{{ route('reports.exportPdf') }}"
                        class="btn-brand">Export PDF</button>
            </div>

            <form id="report-filter-form" method="GET" action="{{ route('reports.index') }}">
                <div class="mb-3">
                    <label class="label-app" for="report-product">Produk</label>
                    <select id="report-product"
                            name="product_id"
                            class="select-dark"
                            style="max-width:22rem;">
                        <option value="">Semua</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(($filter['product_id'] ?? null) === $product->id)>
                                {{ $product->nama_produk }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-13 text-mute mt-1 mb-0">Pilih produk yang akan ditampilkan dan diekspor ke PDF.</p>
                </div>

                <div class="mb-3">
                    <span class="label-app">Jenis periode</span>
                    <div class="d-flex flex-wrap gap-2 mt-1" id="periode-tabs">
                        @foreach($periodeOptions as $value => $opt)
                            <label class="btn-app-tab {{ ($filter['periode'] ?? '') === $value ? 'is-active' : '' }}" style="cursor:pointer;">
                                <input type="radio"
                                       name="periode"
                                       value="{{ $value }}"
                                       class="sr-only periode-radio"
                                       {{ ($filter['periode'] ?? '') === $value ? 'checked' : '' }}>
                                {{ $opt['label'] }}
                            </label>
                        @endforeach
                    </div>
                    <p id="periode-deskripsi" class="text-13 text-mute mt-2 mb-0">
                        {{ $periodeOptions[$filter['periode'] ?? '']['deskripsi'] ?? '' }}
                    </p>
                </div>

                <div id="field-bulan" class="mb-3 {{ ($filter['periode'] ?? '') === 'bulan' ? '' : 'd-none' }}">
                    <span class="label-app">Pilih bulan</span>
                    <input type="month"
                           id="report-bulan"
                           name="bulan"
                           value="{{ $filter['bulan'] ?? '' }}"
                           class="select-dark"
                           style="max-width:14rem;"
                           @disabled(($filter['periode'] ?? '') !== 'bulan')
                           @required(($filter['periode'] ?? '') === 'bulan')>
                    <p class="text-13 text-mute mt-1 mb-0">Satu bulan kalender penuh (tanggal 1 s/d akhir bulan).</p>
                </div>

                <div id="field-kustom" class="mb-3 {{ ($filter['periode'] ?? '') === 'kustom' ? '' : 'd-none' }}">
                    <div class="d-flex flex-wrap gap-3 align-items-end">
                        <div>
                            <span class="label-app">Dari tanggal</span>
                            <input type="date"
                                   id="report-dari"
                                   name="dari"
                                   value="{{ $filter['dari'] ?? '' }}"
                                   class="select-dark"
                                   @disabled(($filter['periode'] ?? '') !== 'kustom')
                                   @required(($filter['periode'] ?? '') === 'kustom')>
                        </div>
                        <div>
                            <span class="label-app">Sampai tanggal</span>
                            <input type="date"
                                   id="report-sampai"
                                   name="sampai"
                                   value="{{ $filter['sampai'] ?? '' }}"
                                   class="select-dark"
                                   @disabled(($filter['periode'] ?? '') !== 'kustom')
                                   @required(($filter['periode'] ?? '') === 'kustom')>
                        </div>
                    </div>
                    <p class="text-13 text-mute mt-1 mb-0">Kedua tanggal inklusif (transaksi pada tanggal tersebut ikut dihitung).</p>
                </div>
            </form>
        </div>

        <script>
            (function () {
                var form = document.getElementById('report-filter-form');
                var inputProduct = document.getElementById('report-product');
                var radios = form.querySelectorAll('.periode-radio');
                var fieldBulan = document.getElementById('field-bulan');
                var fieldKustom = document.getElementById('field-kustom');
                var inputBulan = document.getElementById('report-bulan');
                var inputDari = document.getElementById('report-dari');
                var inputSampai = document.getElementById('report-sampai');
                var deskripsi = document.getElementById('periode-deskripsi');
                var tabs = document.getElementById('periode-tabs');

                var deskripsiMap = @json(collect($periodeOptions)->mapWithKeys(fn ($o, $k) => [$k => $o['deskripsi']]));

                function selectedPeriode() {
                    var checked = form.querySelector('.periode-radio:checked');
                    return checked ? checked.value : '';
                }

                function syncFields() {
                    var p = selectedPeriode();
                    fieldBulan.classList.toggle('d-none', p !== 'bulan');
                    fieldKustom.classList.toggle('d-none', p !== 'kustom');
                    inputBulan.disabled = p !== 'bulan';
                    inputBulan.required = p === 'bulan';
                    inputDari.disabled = p !== 'kustom';
                    inputDari.required = p === 'kustom';
                    inputSampai.disabled = p !== 'kustom';
                    inputSampai.required = p === 'kustom';
                    if (deskripsi) {
                        deskripsi.textContent = deskripsiMap[p] || '';
                    }
                    tabs.querySelectorAll('label.btn-app-tab').forEach(function (label) {
                        var input = label.querySelector('input');
                        label.classList.toggle('is-active', input && input.checked);
                    });
                }

                function submitFilter() {
                    var p = selectedPeriode();
                    if (p === 'bulan' && !inputBulan.value) {
                        return;
                    }
                    if (p === 'kustom' && (!inputDari.value || !inputSampai.value)) {
                        return;
                    }
                    form.requestSubmit();
                }

                radios.forEach(function (r) {
                    r.addEventListener('change', function () {
                        syncFields();
                        submitFilter();
                    });
                });

                inputBulan.addEventListener('change', submitFilter);
                inputDari.addEventListener('change', submitFilter);
                inputSampai.addEventListener('change', submitFilter);
                inputProduct.addEventListener('change', submitFilter);

                syncFields();
            })();
        </script>
    </div>
</x-app-layout>
