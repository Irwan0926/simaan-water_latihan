<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">
            {{ auth()->user()->isAdmin() ? 'Semua Transaksi' : 'Transaksi Saya' }}
        </span>
        <h1 class="page-title mt-1">Riwayat Transaksi</h1>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('dashboard') }}" class="btn-ghost border">&larr; Dashboard</a>
        @if(auth()->user()->isPegawai())
            <a href="{{ route('sales.create') }}" class="btn-brand">+ Transaksi Baru</a>
        @endif
    </x-slot>

    <div class="container-app page-stack">
        @if(session('success'))
            <div class="alert-success animate-fade-in"><span>✓</span><span>{{ session('success') }}</span></div>
        @endif
        @if(session('error'))
            <div class="alert-error animate-fade-in"><span>✗</span><span>{{ session('error') }}</span></div>
        @endif

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
                    <p class="stat-value mt-2">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="col-4">
                <div class="stat h-100">
                    <span class="mono-caps">Unit Terjual</span>
                    <p class="stat-value mt-2">{{ $totalUnitTerjual }}</p>
                </div>
            </div>
        </div>

        <div class="surface-1 p-3">
            <div class="mb-3">
                <span class="mono-eyebrow">Filter</span>
                <p class="section-title mt-1 mb-0">Periode &amp; Kasir</p>
                @if($labelPeriode)
                    <p class="text-13 text-mute mt-1 mb-0">
                        <span class="text-ink fw-medium">{{ $labelPeriode }}</span>
                        @if($rentangLabel)
                            <span class="text-mute"> &middot; {{ $rentangLabel }}</span>
                        @endif
                    </p>
                @endif
            </div>

            <form id="history-filter-form" method="GET" action="{{ route('sales.history') }}">
                @if(auth()->user()->isAdmin())
                    <div class="mb-3">
                        <span class="label-app">Kasir</span>
                        <select name="kasir_id" id="history-kasir" class="select-dark" style="max-width:18rem;">
                            <option value="">Semua kasir</option>
                            @foreach($kasirList as $kasir)
                                <option value="{{ $kasir->id }}" {{ (string) $selectedKasirId === (string) $kasir->id ? 'selected' : '' }}>
                                    {{ $kasir->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

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
                           id="history-bulan"
                           name="bulan"
                           value="{{ $filter['bulan'] ?? '' }}"
                           class="select-dark"
                           style="max-width:14rem;">
                </div>

                <div id="field-kustom" class="mb-3 {{ ($filter['periode'] ?? '') === 'kustom' ? '' : 'd-none' }}">
                    <div class="d-flex flex-wrap gap-3 align-items-end">
                        <div>
                            <span class="label-app">Dari tanggal</span>
                            <input type="date"
                                   id="history-dari"
                                   name="dari"
                                   value="{{ $filter['dari'] ?? '' }}"
                                   class="select-dark">
                        </div>
                        <div>
                            <span class="label-app">Sampai tanggal</span>
                            <input type="date"
                                   id="history-sampai"
                                   name="sampai"
                                   value="{{ $filter['sampai'] ?? '' }}"
                                   class="select-dark">
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="submit" class="btn-app-tab">Terapkan filter</button>
                    @if(($filter['periode'] ?? '') !== '' || (auth()->user()->isAdmin() && $selectedKasirId !== ''))
                        <a href="{{ route('sales.history') }}" class="btn-ghost border">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <script>
            (function () {
                var form = document.getElementById('history-filter-form');
                if (!form) return;
                var radios = form.querySelectorAll('.periode-radio');
                var fieldBulan = document.getElementById('field-bulan');
                var fieldKustom = document.getElementById('field-kustom');
                var deskripsi = document.getElementById('periode-deskripsi');
                var tabs = document.getElementById('periode-tabs');
                var deskripsiMap = @json(collect($periodeOptions)->mapWithKeys(fn ($o, $k) => [$k => $o['deskripsi']]));

                function selectedPeriode() {
                    var checked = form.querySelector('.periode-radio:checked');
                    return checked ? checked.value : '';
                }

                function syncFields() {
                    var p = selectedPeriode();
                    if (fieldBulan) fieldBulan.classList.toggle('d-none', p !== 'bulan');
                    if (fieldKustom) fieldKustom.classList.toggle('d-none', p !== 'kustom');
                    if (deskripsi) deskripsi.textContent = deskripsiMap[p] || '';
                    if (tabs) {
                        tabs.querySelectorAll('label.btn-app-tab').forEach(function (label) {
                            var input = label.querySelector('input');
                            label.classList.toggle('is-active', input && input.checked);
                        });
                    }
                }

                radios.forEach(function (r) { r.addEventListener('change', syncFields); });
                syncFields();
            })();
        </script>

        <div class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Daftar Transaksi</span>
                    <span class="mono-micro">{{ $sales->count() }} transaksi</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="mono-micro text-mute mb-0" for="historyPageSize" style="white-space:nowrap;">Per halaman</label>
                    <select id="historyPageSize" class="select-dark" style="height:32px; width:auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th style="width:3.5rem;">No</th>
                            <th>Tanggal</th>
                            <th>Invoice</th>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th>Kasir</th>
                            <th class="text-end">Total</th>
                            @if(auth()->user()->isPegawai())
                                <th class="text-end">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        @forelse($sales as $sale)
                            <tr data-row="{{ $loop->iteration }}">
                                <td class="text-mute font-mono">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="tabular-nums">{{ \App\Support\Waktu::tampil($sale->tanggal) }}</td>
                                <td class="text-13 font-mono">{{ $sale->invoice }}</td>
                                <td class="text-13">
                                    @foreach($sale->details as $detail)
                                        <div>{{ $detail->product->nama_produk }}</div>
                                    @endforeach
                                </td>
                                <td class="text-center text-13">
                                    @foreach($sale->details as $detail)
                                        <div class="tabular-nums">{{ $detail->qty }}</div>
                                    @endforeach
                                </td>
                                <td class="text-ink fw-medium">{{ $sale->user->name }}</td>
                                <td class="text-end text-ink fw-medium tabular-nums">Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                                @if(auth()->user()->isPegawai())
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="{{ route('sales.edit', $sale) }}" class="btn-action btn-action-edit">Edit</a>
                                            <form action="{{ route('sales.destroy', $sale) }}" method="POST" onsubmit="return confirm('Hapus transaksi {{ $sale->invoice }}? Stok akan dikembalikan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-action-delete">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="historyEmptyRow">
                                <td colspan="{{ auth()->user()->isPegawai() ? 8 : 7 }}" class="py-5 text-center">
                                    <p class="text-ink text-14 fw-medium mb-1">Belum ada transaksi.</p>
                                    <p class="text-13 text-mute mb-0">
                                        @if(auth()->user()->isPegawai())
                                            Transaksi yang Anda lakukan lewat menu Kasir akan muncul di sini.
                                        @elseif(($filter['periode'] ?? '') !== '' || $selectedKasirId !== '')
                                            Tidak ada transaksi yang cocok dengan filter saat ini.
                                        @else
                                            Belum ada transaksi yang tercatat pada sistem.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="historyPagination" class="panel-footer"></div>
        </div>
    </div>

    <script>
        (function () {
            var body = document.getElementById('historyTableBody');
            var pageSizeSel = document.getElementById('historyPageSize');
            var pager = document.getElementById('historyPagination');
            if (!body || !pageSizeSel || !pager) return;

            var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-row]'));
            var total = rows.length;
            var emptyRow = document.getElementById('historyEmptyRow');

            var currentPage = 1;
            var pageSize = 10;

            function isShowAll() { return pageSize === 'all' || pageSize === 'All'; }
            function pageCount() {
                if (isShowAll() || total === 0) return 1;
                return Math.ceil(total / pageSize);
            }
            function clampPage() {
                var pc = pageCount();
                if (currentPage < 1) currentPage = 1;
                if (currentPage > pc) currentPage = pc;
            }
            function renderRows() {
                if (isShowAll()) {
                    rows.forEach(function (r) { r.style.display = ''; });
                    return;
                }
                var start = (currentPage - 1) * pageSize;
                var end = start + pageSize;
                rows.forEach(function (r, i) {
                    r.style.display = (i >= start && i < end) ? '' : 'none';
                });
            }
            function renderPager() {
                if (total === 0) { pager.innerHTML = ''; return; }
                if (isShowAll()) {
                    pager.innerHTML = '<span class="text-13 text-mute">Total ' + total + ' transaksi ditampilkan semua.</span>';
                    return;
                }
                var pc = pageCount();
                var start = (currentPage - 1) * pageSize + 1;
                var end = Math.min(currentPage * pageSize, total);

                var left = '<span class="text-13 text-mute">Menampilkan ' + start + '&ndash;' + end + ' dari ' + total + ' transaksi</span>';

                var btns = '';
                btns += '<button type="button" class="btn-app-tab rp-nav" data-page="' + (currentPage - 1) + '" ' + (currentPage === 1 ? 'disabled' : '') + '>&larr; Prev</button>';

                var maxBtns = 5;
                var from = Math.max(1, currentPage - 2);
                var to = Math.min(pc, from + maxBtns - 1);
                from = Math.max(1, to - maxBtns + 1);

                for (var p = from; p <= to; p++) {
                    btns += '<button type="button" class="btn-app-tab rp-nav ' + (p === currentPage ? 'is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
                }

                btns += '<button type="button" class="btn-app-tab rp-nav" data-page="' + (currentPage + 1) + '" ' + (currentPage === pc ? 'disabled' : '') + '>Next &rarr;</button>';
                btns += '<button type="button" class="btn-app-tab rp-all">Tampilkan Semua</button>';

                pager.innerHTML = left + '<div class="d-flex align-items-center gap-1 flex-wrap">' + btns + '</div>';
            }
            function go(page) {
                currentPage = page;
                clampPage();
                renderRows();
                renderPager();
            }
            function apply() {
                var val = pageSizeSel.value;
                pageSize = (val === 'all') ? 'all' : parseInt(val, 10);
                currentPage = 1;
                renderRows();
                renderPager();
                if (emptyRow) emptyRow.style.display = (total === 0) ? '' : 'none';
            }

            pageSizeSel.addEventListener('change', apply);
            pager.addEventListener('click', function (e) {
                var t = e.target.closest('button');
                if (!t) return;
                if (t.classList.contains('rp-nav')) {
                    go(parseInt(t.getAttribute('data-page'), 10));
                } else if (t.classList.contains('rp-all')) {
                    pageSizeSel.value = 'all';
                    apply();
                }
            });
            apply();
        })();
    </script>
</x-app-layout>
