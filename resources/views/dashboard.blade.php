<x-app-layout>
    <div class="container-app page-stack">

        <div>
            <div class="section-eyebrow-row mb-2">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Ringkasan</span>
            </div>
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="stat h-100">
                        <span class="mono-caps">Total Produk</span>
                        <p class="stat-value mt-2">{{ $totalProduk }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Jenis produk</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat h-100">
                        <span class="mono-caps">Total Penjualan</span>
                        <p class="stat-value mt-2">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Akumulasi omzet</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat stat-accent stat-accent-error stat-tint-error h-100">
                        <span class="mono-caps text-error">Stok Menipis</span>
                        <p class="stat-value text-error mt-2">{{ $stokMenipis }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Kondisi stok = Sedikit (per produk)</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat stat-accent stat-tint-brand h-100">
                        <span class="mono-caps text-brand-deep">Perlu Promosi</span>
                        <p class="stat-value text-brand-deep mt-2">{{ $perluPromosi }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Saran Analisis Rule Based (hari ini)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Penjelasan: siapa stok menipis & perlu promosi --}}
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="surface-1 overflow-hidden h-100">
                    <div class="panel-header">
                        <div class="section-eyebrow-row mb-0">
                            <span class="eyebrow-rule"></span>
                            <span class="mono-eyebrow">Produk stok menipis</span>
                        </div>
                        <span class="badge badge-error">{{ $stokMenipis }}</span>
                    </div>
                    <div class="p-3 border-bottom border-hairline">
                        <p class="text-13 text-mute mb-0">
                            Dihitung dari <strong class="text-ink">ambang batas stok per produk</strong>
                            (level 1 = Sedikit)
                        </p>
                    </div>
                    @if($produkStokMenipis->isEmpty())
                        <div class="p-4 text-center">
                            <p class="text-ink fw-medium mb-1">Tidak ada stok menipis</p>
                            <p class="text-13 text-mute mb-0">Semua produk di atas ambang level 1.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="tbl mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:2.5rem; text-wrap-mode:nowrap;">No</th>
                                        <th>Produk</th>
                                        <th class="text-center">Stok</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produkStokMenipis as $p)
                                        <tr>
                                            <td class="text-mute font-mono" style="text-wrap-mode:nowrap;">
                                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td class="text-ink fw-medium">{{ $p->nama_produk }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-error tabular-nums">{{ $p->stok }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if(auth()->user()->role === 'admin')
                                                    <a href="{{ route('products.show', $p) }}" class="btn-action btn-action-icon btn-action-view" title="Detail produk" aria-label="Detail produk {{ $p->nama_produk }}">
                                                        <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="surface-1 overflow-hidden h-100">
                    <div class="panel-header">
                        <div class="section-eyebrow-row mb-0">
                            <span class="eyebrow-rule"></span>
                            <span class="mono-eyebrow">Produk perlu promosi</span>
                        </div>
                        <span class="badge badge-brand">{{ $perluPromosi }}</span>
                    </div>
                    <div class="p-3 border-bottom border-hairline">
                        <p class="text-13 text-mute mb-0">
                            Dari snapshot <strong class="text-ink">Analisis Rule Based otomatis (hari ini)</strong>
                            yang rekomendasinya mengandung “promosi”.
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('analysis.index') }}" class="text-brand">Lihat Analisis Rule Based →</a>
                            @endif
                        </p>
                    </div>
                    @if($produkPerluPromosi->isEmpty())
                        <div class="p-4 text-center">
                            <p class="text-ink fw-medium mb-1">Belum ada yang perlu promosi</p>
                            <p class="text-13 text-mute mb-0">
                                Buka Analisis Rule Based agar snapshot harian ter-generate, atau belum ada saran promosi.
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="tbl mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:2.5rem;">No</th>
                                        <th>Produk</th>
                                        <th>Saran</th>
                                        <th class="text-center">Tren</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produkPerluPromosi as $r)
                                        <tr>
                                            <td class="text-mute font-mono" style="text-wrap-mode:nowrap;">
                                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td class="text-ink fw-medium">{{ $r->product->nama_produk ?? '-' }}</td>
                                            <td class="text-13 text-ink-soft">{{ $r->rekomendasi }}</td>
                                            <td class="text-center">
                                                @php
                                                    $trBadge = match ($r->tren) {
                                                        'Naik' => 'badge-success',
                                                        'Stabil' => 'badge-amber',
                                                        default => 'badge-error',
                                                    };
                                                @endphp
                                                <span class="badge {{ $trBadge }}">{{ $r->tren }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin' && $salesFilter !== null)
                <div>
                    <div class="section-eyebrow-row mb-2">
                        <span class="eyebrow-rule"></span>
                        <span class="mono-eyebrow">Hasil Penjualan</span>
                    </div>

                    <div class="surface-1 p-3 mb-3">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
                            <div>
                                <p class="section-title mt-0 mb-0">Periode</p>
                                @if($salesLabelPeriode)
                                    <p class="text-13 text-mute mt-1 mb-0">
                                        <span class="text-ink fw-medium">{{ $salesLabelPeriode }}</span>
                                        @if($salesRentangLabel)
                                            <span class="text-mute"> · {{ $salesRentangLabel }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('reports.index', array_filter([
                'periode' => $salesFilter['periode'] ?: null,
                'bulan' => $salesFilter['bulan'] ?? null,
                'dari' => $salesFilter['dari'] ?? null,
                'sampai' => $salesFilter['sampai'] ?? null,
            ])) }}" class="btn-ghost border">Lihat laporan →</a>
                        </div>

                        <form id="dashboard-sales-filter" method="GET" action="{{ route('dashboard') }}">
                            <div class="mb-3">
                                <span class="label-app">Jenis periode</span>
                                <div class="d-flex flex-wrap gap-2 mt-1" id="dash-periode-tabs">
                                    @foreach($salesPeriodeOptions as $value => $opt)
                                        <label
                                            class="btn-app-tab {{ ($salesFilter['periode'] ?? '') === $value ? 'is-active' : '' }}"
                                            style="cursor:pointer;">
                                            <input type="radio" name="periode" value="{{ $value }}"
                                                class="sr-only dash-periode-radio" {{ ($salesFilter['periode'] ?? '') === $value ? 'checked' : '' }}>
                                            {{ $opt['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                                <p id="dash-periode-deskripsi" class="text-13 text-mute mt-2 mb-0">
                                    {{ $salesPeriodeOptions[$salesFilter['periode'] ?? '']['deskripsi'] ?? '' }}
                                </p>
                            </div>

                            <div id="dash-field-bulan"
                                class="mb-3 {{ ($salesFilter['periode'] ?? '') === 'bulan' ? '' : 'd-none' }}">
                                <span class="label-app">Pilih bulan</span>
                                <input type="month" id="dash-bulan" name="bulan" value="{{ $salesFilter['bulan'] ?? '' }}"
                                    class="select-dark" style="max-width:14rem;">
                                <p class="text-13 text-mute mt-1 mb-0">Satu bulan kalender penuh (tanggal 1 s/d akhir bulan).
                                </p>
                            </div>

                            <div id="dash-field-kustom"
                                class="mb-3 {{ ($salesFilter['periode'] ?? '') === 'kustom' ? '' : 'd-none' }}">
                                <div class="d-flex flex-wrap gap-3 align-items-end">
                                    <div>
                                        <span class="label-app">Dari tanggal</span>
                                        <input type="date" id="dash-dari" name="dari" value="{{ $salesFilter['dari'] ?? '' }}"
                                            class="select-dark">
                                    </div>
                                    <div>
                                        <span class="label-app">Sampai tanggal</span>
                                        <input type="date" id="dash-sampai" name="sampai"
                                            value="{{ $salesFilter['sampai'] ?? '' }}" class="select-dark">
                                    </div>
                                </div>
                                <p class="text-13 text-mute mt-1 mb-0">Kedua tanggal inklusif.</p>
                            </div>

                            @if(($salesFilter['periode'] ?? '') !== '')
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <a href="{{ route('dashboard') }}" class="btn-ghost border">Reset ke seluruh periode</a>
                                </div>
                            @endif
                        </form>
                    </div>

                    <div class="row g-3">
                        <div class="col-4">
                            <div class="stat h-100">
                                <span class="mono-caps">Transaksi</span>
                                <p class="stat-value mt-2">{{ $salesTotalTransaksi }}</p>
                                <p class="text-13 text-mute mt-1 mb-0">Periode terpilih</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat h-100">
                                <span class="mono-caps">Pendapatan</span>
                                <p class="stat-value mt-2">Rp {{ number_format($salesTotalPendapatan, 0, ',', '.') }}</p>
                                <p class="text-13 text-mute mt-1 mb-0">Periode terpilih</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat h-100">
                                <span class="mono-caps">Unit Terjual</span>
                                <p class="stat-value mt-2">{{ $salesTotalUnit }}</p>
                                <p class="text-13 text-mute mt-1 mb-0">Periode terpilih</p>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var form = document.getElementById('dashboard-sales-filter');
                        if (!form) return;
                        var radios = form.querySelectorAll('.dash-periode-radio');
                        var fieldBulan = document.getElementById('dash-field-bulan');
                        var fieldKustom = document.getElementById('dash-field-kustom');
                        var deskripsi = document.getElementById('dash-periode-deskripsi');
                        var tabs = document.getElementById('dash-periode-tabs');
                        var deskripsiMap = @json(collect($salesPeriodeOptions)->mapWithKeys(fn($o, $k) => [$k => $o['deskripsi']]));
                        var skipAuto = false;

                        function selectedPeriode() {
                            var checked = form.querySelector('.dash-periode-radio:checked');
                            return checked ? checked.value : '';
                        }

                        function syncFields() {
                            var p = selectedPeriode();
                            fieldBulan.classList.toggle('d-none', p !== 'bulan');
                            fieldKustom.classList.toggle('d-none', p !== 'kustom');
                            if (deskripsi) deskripsi.textContent = deskripsiMap[p] || '';
                            tabs.querySelectorAll('label.btn-app-tab').forEach(function (label) {
                                var input = label.querySelector('input');
                                label.classList.toggle('is-active', input && input.checked);
                            });
                            // Kosongkan field yang tidak dipakai agar tidak mengotori query
                            if (p !== 'bulan') {
                                var b = document.getElementById('dash-bulan');
                                if (b) b.value = '';
                            }
                            if (p !== 'kustom') {
                                var d = document.getElementById('dash-dari');
                                var s = document.getElementById('dash-sampai');
                                if (d) d.value = '';
                                if (s) s.value = '';
                            }
                        }

                        function autoSubmit() {
                            if (skipAuto) return;
                            form.submit();
                        }

                        radios.forEach(function (r) {
                            r.addEventListener('change', function () {
                                syncFields();
                                var p = selectedPeriode();
                                // Bulan & kustom butuh input tambahan — jangan auto-submit dulu
                                if (p === 'bulan' || p === 'kustom') return;
                                autoSubmit();
                            });
                        });

                        ['dash-bulan', 'dash-dari', 'dash-sampai'].forEach(function (id) {
                            var el = document.getElementById(id);
                            if (el) el.addEventListener('change', autoSubmit);
                        });

                        skipAuto = true;
                        syncFields();
                        skipAuto = false;
                    })();
                </script>
        @endif

        @if(auth()->user()->role === 'pegawai')
            <div>
                <div class="section-eyebrow-row mb-2">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Menu Cepat</span>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <a href="{{ route('sales.create') }}" class="feature-brand p-3 h-100 group">
                            <div class="d-flex align-items-start justify-content-between position-relative">
                                <div class="icon-box"
                                    style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.25);color:#fff;">
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M3 3h18v18H3zM3 9h18M9 21V9" />
                                    </svg>
                                </div>
                                <span class="group-hover-slide" style="color:rgba(255,255,255,0.8);">→</span>
                            </div>
                            <span class="mono-caps mt-3 d-block position-relative"
                                style="color:rgba(255,255,255,0.75);">Kasir</span>
                            <h3 class="heading-sm mt-1 position-relative" style="color:#fff;">Input Transaksi</h3>
                            <p class="text-13 mt-1 mb-0 position-relative" style="color:rgba(255,255,255,0.85);">Catat
                                penjualan depot air.</p>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if(auth()->user()->role === 'admin')
            <div>
                <div class="section-eyebrow-row mb-2">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Menu Cepat</span>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <a href="{{ route('analysis.index') }}"
                            class="surface-1 surface-step-hover p-3 h-100 text-decoration-none d-block group">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="icon-box">
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M9 19V6l12-3v13M9 19l-6-2V4l6 2" />
                                    </svg>
                                </div>
                                <span class="group-hover-slide">→</span>
                            </div>
                            <span class="mono-caps mt-3 d-block">Analisis Rule Based</span>
                            <h3 class="heading-sm mt-1">Hasil Analisis</h3>
                            <p class="text-13 text-mute mt-1 mb-0">Rekomendasi rule-based.</p>
                        </a>
                    </div>
                    <div class="col-12 col-md-6">
                        <a href="{{ route('analysis.create') }}" class="feature-brand p-3 h-100 group">
                            <div class="d-flex align-items-start justify-content-between position-relative">
                                <div class="icon-box"
                                    style="background:rgba(255,255,255,0.2);border-color:rgba(255,255,255,0.25);color:#fff;">
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <span class="group-hover-slide" style="color:rgba(255,255,255,0.8);">→</span>
                            </div>
                            <span class="mono-caps mt-3 d-block position-relative"
                                style="color:rgba(255,255,255,0.75);">Generate</span>
                            <h3 class="heading-sm mt-1 position-relative" style="color:#fff;">Jalankan Analisis</h3>
                            <p class="text-13 mt-1 mb-0 position-relative" style="color:rgba(255,255,255,0.85);">Forward
                                chaining terbaru.</p>
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>