<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Analisis Rule Based</span>
        <h1 class="page-title mt-1">Riwayat Saran</h1>
        <p class="page-subtitle mb-0">Semua saran yang dibuat admin lewat tombol “Buat Saran”.</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('analysis.index') }}" class="btn-brand">Buat saran baru</a>
        <a href="{{ route('dashboard') }}" class="btn-ghost border">&larr; Dashboard</a>
    </x-slot>

    <div class="container-app page-stack">

        {{-- Navigasi tab --}}
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('analysis.index') }}" class="btn-app-tab">Saran hari ini</a>
            <a href="{{ route('analysis.history') }}" class="btn-app-tab is-active">
                Riwayat saran
                @if($totalRiwayat > 0)
                    <span class="badge badge-filled ms-1">{{ $totalRiwayat }}</span>
                @endif
            </a>
        </div>

        <div class="row g-3">
            <div class="col-6 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Total riwayat</span>
                    <p class="stat-value mt-2">{{ $totalRiwayat }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Saran manual tersimpan</p>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Hari ini</span>
                    <p class="stat-value mt-2">{{ $hariIni }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Generate hari ini</p>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="stat h-100">
                    <span class="mono-caps">Produk dianalisis</span>
                    <p class="stat-value mt-2">{{ $produkUnik }}</p>
                    <p class="text-13 text-mute mt-1 mb-0">Jenis produk berbeda</p>
                </div>
            </div>
        </div>

        <section class="surface-1 p-4">
            <p class="text-13 text-ink-soft mb-0 lh-base">
                Setiap kali Anda menekan <strong class="text-ink">Buat Saran</strong> di Analisis Rule Based,
                hasilnya dicatat di tabel ini. Klik <strong class="text-ink">Lihat</strong> untuk membaca langkah
                Forward Chaining.
            </p>
        </section>

        @if($historyResults->isEmpty())
            <div class="surface-1 p-5 text-center">
                <p class="text-ink fw-medium mb-1">Belum ada riwayat</p>
                <p class="text-13 text-mute mb-3">Buat saran di halaman Analisis Rule Based, lalu kembali ke sini.</p>
                <a href="{{ route('analysis.index') }}" class="btn-brand">Ke Analisis Rule Based</a>
            </div>
        @else
            <div class="surface-1 overflow-hidden">
                <div class="table-responsive">
                    <table class="tbl mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Produk</th>
                                <th>Periode</th>
                                <th class="text-center">Kondisi</th>
                                <th>Saran</th>
                                <th class="text-center">Aturan</th>
                                <th>Oleh</th>
                                <th class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historyResults as $item)
                                <tr>
                                    <td class="text-13 text-ink-soft text-nowrap">
                                        {{ \App\Support\Waktu::tanggalPanjang($item->created_at) }}
                                        <span class="d-block text-mute"
                                            style="font-size:11px;">{{ \App\Support\Waktu::jam($item->created_at) }}</span>
                                    </td>
                                    <td class="text-ink fw-medium">{{ $item->product->nama_produk ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-filled">{{ $item->periodeLabel() }}</span>
                                        @if($item->qty_saat_ini !== null)
                                            <span class="d-block text-mute mt-1" style="font-size:11px;">
                                                {{ $item->qty_saat_ini }} unit · stok {{ $item->stok_saat_analisis ?? '-' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                                            <span class="badge badge-success"
                                                style="font-size:10px;">{{ $item->kondisi_penjualan }}</span>
                                            <span class="badge badge-blue"
                                                style="font-size:10px;">{{ $item->kondisi_stok }}</span>
                                            <span class="badge badge-amber" style="font-size:10px;">{{ $item->tren }}</span>
                                        </div>
                                    </td>
                                    <td class="text-ink-soft">{{ $item->rekomendasi }}</td>
                                    <td class="text-center"><span class="badge badge-filled">{{ $item->rule_terpakai }}</span>
                                    </td>
                                    <td class="text-13 text-ink-soft">{{ $item->user->name ?? 'Admin' }}</td>
                                    <td class="text-center">
                                        <button type="button" onclick="showTrace({{ $item->id }})"
                                            class="btn-action btn-action-icon btn-action-view" title="Lihat proses berpikir"
                                            aria-label="Lihat proses berpikir {{ $item->rule_terpakai }}">
                                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-center">
                {{ $historyResults->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Modal proses berpikir --}}
    <div id="traceModal" class="modal-backdrop-app d-none align-items-start" style="overflow-y:auto;">
        <div class="modal-scrim" onclick="closeTrace()"></div>
        <div class="modal-panel my-4" style="max-width:40rem; max-height:88vh; overflow-y:auto;">
            <div class="position-sticky top-0 z-1 d-flex align-items-center justify-content-between px-1 py-2 border-bottom border-hairline mb-3"
                style="background:rgba(255,255,255,0.96); backdrop-filter:blur(8px);">
                <div>
                    <span class="mono-eyebrow">Proses berpikir</span>
                    <h3 class="heading-sm text-ink mt-1 mb-0" id="traceTitle">Detail saran</h3>
                </div>
                <button type="button" onclick="closeTrace()" class="btn-icon" style="width:2.25rem;height:2.25rem;"
                    title="Tutup">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="traceHeader" class="d-flex flex-wrap gap-2 mb-4"></div>

            <div class="mb-4">
                <p class="text-ink fw-medium mb-2">Ringkasan</p>
                <div id="traceSimple"
                    class="p-3 rounded-app-md bg-canvas-paper border border-hairline text-13 lh-base text-ink-soft">
                </div>
            </div>

            <div class="mb-4">
                <p class="text-ink fw-medium mb-2">Aturan yang dicek</p>
                <div id="ruleTable" class="table-responsive surface-2"></div>
            </div>

            <div>
                <p class="text-ink fw-medium mb-2">Catatan langkah demi langkah</p>
                <pre id="traceContent" class="font-mono text-13 lh-base p-4 rounded-app-md mb-0"
                    style="white-space:pre-wrap; background:#0b0b0b; color:#b9b9b9; overflow-x:auto;"></pre>
            </div>
        </div>
    </div>

    <script>
        @php
            $traceData = [];
            foreach ($historyResults as $result) {
                $traceData[$result->id] = [
                    'product' => $result->product->nama_produk ?? '-',
                    'trace' => $result->inference_trace ?? '',
                    'penjualan' => $result->kondisi_penjualan,
                    'stok' => $result->kondisi_stok,
                    'tren' => $result->tren,
                    'rule' => $result->rule_terpakai,
                    'rekomendasi' => $result->rekomendasi,
                    'evaluations' => $ruleEvaluations[$result->id] ?? [],
                ];
            }
        @endphp
        window.SIMAAN_TRACE = @json($traceData);

        function syaratLabel(v) {
            return v === null ? 'apa saja' : v;
        }

        function showTrace(id) {
            const d = window.SIMAAN_TRACE[id];
            if (!d) return;

            document.getElementById('traceTitle').textContent = d.product;
            document.getElementById('traceHeader').innerHTML = `
                <span class="badge badge-success">Jual: ${d.penjualan}</span>
                <span class="badge badge-blue">Stok: ${d.stok}</span>
                <span class="badge badge-amber">Tren: ${d.tren}</span>
                <span class="badge badge-filled">Aturan: ${d.rule ?? '-'}</span>
            `;

            document.getElementById('traceSimple').innerHTML = `
                <p class="mb-2">Produk <strong class="text-ink">${d.product}</strong>:</p>
                <ul class="mb-2 ps-3">
                    <li>Penjualan <strong>${d.penjualan}</strong></li>
                    <li>Stok <strong>${d.stok}</strong></li>
                    <li>Tren <strong>${d.tren}</strong></li>
                </ul>
                <p class="mb-0">Saran: <strong class="text-brand">${d.rekomendasi}</strong>
                (aturan <strong>${d.rule ?? '-'}</strong>).</p>
            `;

            let rowsHTML = '';
             (d.evaluations || []).forEach(function(r) {
                const isWinner = r.kode_rule === d.rule;
                const badge = r.cocok ? 'badge-success' : 'badge-error';
                const status = r.cocok ? 'Cocok' : 'Lewati';
                const rowBg = isWinner ? 'bg-brand-soft' : '';
                const winnerTag = isWinner ? ' <span class="badge badge-filled">Dipakai</span>' : '';
                const alasan = (r.alasan || []).length
                    ? r.alasan.join('; ')
                    : (r.cocok ? 'Semua syarat terpenuhi' : '');

                rowsHTML += `
                    <tr class="${rowBg}">
                        <td class="text-ink fw-medium text-nowrap">${r.kode_rule}${winnerTag}</td>
                        <td class="text-13 text-ink-soft">
                            Jual: ${syaratLabel(r.penjualan)} · Stok: ${syaratLabel(r.stok)} · Tren: ${syaratLabel(r.tren)}
                        </td>
                        <td><span class="badge ${badge}">${status}</span>
                            ${alasan ? `<div class="text-mute mt-1" style="font-size:11px;">${alasan}</div>` : ''}
                        </td>
                        <td class="text-13 text-ink-soft">${r.rekomendasi}</td>
                    </tr>
                `;
            });

            document.getElementById('ruleTable').innerHTML = `
                <table class="tbl mb-0">
                    <thead>
                        <tr>
                            <th>Aturan</th>
                            <th>Syarat</th>
                            <th>Hasil cek</th>
                            <th>Saran</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
            `;

            document.getElementById('traceContent').textContent = d.trace;
            const modal = document.getElementById('traceModal');
            modal.classList.remove('d-none');
            modal.classList.add('d-flex');
            document.body.style.overflow = 'hidden';
        }

        function closeTrace() {
            const modal = document.getElementById('traceModal');
            modal.classList.remove('d-flex');
            modal.classList.add('d-none');
            document.body.style.overflow = '';
        }

         document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeTrace();
        });
    </script>
</x-app-layout>
