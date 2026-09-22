<x-app-layout>
    <x-slot name="header">
        <span class="mono-eyebrow">Analisis Rule Based</span>
        <h1 class="page-title mt-1">Saran Penjualan Otomatis</h1>
        <p class="page-subtitle mb-0">Sistem membaca data penjualan &amp; stok, lalu memberi saran apa yang sebaiknya
            dilakukan.</p>
    </x-slot>
    <x-slot name="header-actions">
        <a href="{{ route('rules.index') }}" class="btn-ghost border">Kelola Aturan</a>
        <a href="{{ route('dashboard') }}" class="btn-ghost border">&larr; Dashboard</a>
    </x-slot>

    <div class="container-app page-stack">

        @if(session('success'))
            <div class="alert-success animate-fade-in"><span
                    aria-hidden="true">&#10003;</span><span>{{ session('success') }}</span></div>
        @endif

        {{-- Navigasi tab --}}
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('analysis.index') }}" class="btn-app-tab is-active">Saran hari ini</a>
            <a href="{{ route('analysis.history') }}" class="btn-app-tab">
                Riwayat saran
                @if(($historyCount ?? 0) > 0)
                    <span class="badge badge-filled ms-1">{{ $historyCount }}</span>
                @endif
            </a>
        </div>

        {{-- Cara kerja singkat --}}
        <section class="surface-1 p-4">
            <div class="section-eyebrow-row mb-3">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Cara kerja (3 langkah)</span>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                        <span class="badge badge-filled mb-2">1</span>
                        <p class="text-ink fw-medium mb-1">Pilih produk &amp; periode</p>
                        <p class="text-13 text-mute mb-0">Misalnya Botol Ukuran 330 ml untuk hari ini, 1 minggu, atau 1
                            bulan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                        <span class="badge badge-filled mb-2">2</span>
                        <p class="text-ink fw-medium mb-1">Sistem baca data</p>
                        <p class="text-13 text-mute mb-0">Melihat berapa yang terjual, stok tersisa, dan tren
                            naik/turun.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline h-100">
                        <span class="badge badge-filled mb-2">3</span>
                        <p class="text-ink fw-medium mb-1">Dapatkan saran</p>
                        <p class="text-13 text-mute mb-0">Misalnya “Segera restock” atau “Promosi agresif”.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Form generate --}}
        <section class="surface-1 p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <span class="mono-eyebrow">Mulai di sini</span>
                    <h3 class="heading-sm text-ink mt-2 mb-0">Buat saran untuk satu produk</h3>
                    <p class="text-13 text-mute mt-1 mb-0">Hasilnya ikut tersimpan di tab <a
                            href="{{ route('analysis.history') }}" class="text-brand">Riwayat saran</a>.</p>
                </div>
                <span class="mono-micro d-none d-md-inline">Metode: Forward Chaining</span>
            </div>
            <form action="{{ route('analysis.generate') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="label-app" for="gen_product_id">Produk mana?</label>
                        <select id="gen_product_id" name="product_id" required class="select-dark">
                            <option value="">-- Pilih produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->nama_produk }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <span class="label-app">Periode data yang dipakai</span>
                        <div class="d-flex gap-1_5 flex-wrap">
                            <label class="btn-app-tab flex-fill justify-content-center">
                                <input type="radio" name="periode" value="harian" checked class="sr-only">
                                1 Hari
                            </label>
                            <label class="btn-app-tab flex-fill justify-content-center">
                                <input type="radio" name="periode" value="3hari" class="sr-only">
                                3 Hari
                            </label>
                            <label class="btn-app-tab flex-fill justify-content-center">
                                <input type="radio" name="periode" value="mingguan" class="sr-only">
                                1 Minggu
                            </label>
                            <label class="btn-app-tab flex-fill justify-content-center">
                                <input type="radio" name="periode" value="bulanan" class="sr-only">
                                1 Bulan
                            </label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <button type="submit" class="btn-brand w-100 justify-content-center">
                            Buat Saran
                            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- Hasil generate terbaru --}}
        @if($hasilGenerate)
            <section class="surface-1 p-4 p-lg-5 animate-scale-in position-relative overflow-hidden">
                <div class="position-absolute top-0 start-0 h-100 bg-brand"
                    style="width:4px; border-radius:var(--radius-marketing) 0 0 var(--radius-marketing);"></div>
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="section-eyebrow-row mb-0">
                        <span class="eyebrow-rule"></span>
                        <span class="mono-eyebrow">Hasil terbaru</span>
                    </div>
                    <span class="badge badge-filled">{{ $hasilGenerate['periode'] }}</span>
                </div>

                <h2 class="heading-md mb-1">{{ $hasilGenerate['produk'] }}</h2>
                <p class="text-13 text-mute mb-4">
                    Data: {{ $hasilGenerate['rentang_kini']['label'] }}
                    dibanding {{ $hasilGenerate['rentang_sebelumnya']['label'] }}
                </p>

                <div class="p-4 rounded-app-md border border-hairline bg-canvas-paper mb-4">
                    <span class="mono-caps text-mute">Saran untuk Anda</span>
                    <p class="text-brand text-28 fw-medium mt-2 mb-1 lh-sm">{{ $hasilGenerate['rekomendasi'] }}</p>
                    <p class="text-13 text-mute mb-0">
                        Aturan yang dipakai: <strong class="text-ink">{{ $hasilGenerate['rule'] ?? '-' }}</strong>
                    </p>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-lg-3">
                        <span class="mono-caps">Terjual sekarang</span>
                        <p class="text-ink text-24 fw-medium mt-1 mb-0 tabular-nums">
                            {{ $hasilGenerate['penjualan_saat_ini'] }}
                        </p>
                        <p class="text-13 text-mute mb-0">unit</p>
                    </div>
                    <div class="col-6 col-lg-3">
                        <span class="mono-caps">Terjual sebelumnya</span>
                        <p class="text-ink text-24 fw-medium mt-1 mb-0 tabular-nums">
                            {{ $hasilGenerate['penjualan_sebelumnya'] }}
                        </p>
                        <p class="text-13 text-mute mb-0">unit</p>
                    </div>
                    <div class="col-6 col-lg-3">
                        <span class="mono-caps">Stok saat ini</span>
                        <p class="text-ink text-24 fw-medium mt-1 mb-0 tabular-nums">{{ $hasilGenerate['stok'] }}</p>
                        <p class="text-13 text-mute mb-0">unit</p>
                    </div>
                    <div class="col-6 col-lg-3">
                        <span class="mono-caps">Kondisi ringkas</span>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="badge badge-success">{{ $hasilGenerate['kondisi_penjualan'] }}</span>
                            <span class="badge badge-blue">{{ $hasilGenerate['kondisi_stok'] }}</span>
                            <span class="badge badge-amber">{{ $hasilGenerate['tren'] }}</span>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Ringkasan semua produk --}}
        <section>
            <div class="section-eyebrow-row mb-2">
                <span class="eyebrow-rule"></span>
                <span class="mono-eyebrow">Ringkasan otomatis (hari ini)</span>
            </div>
            <div class="surface-1 p-3 mb-3">
                <p class="text-13 text-ink-soft mb-2 lh-base">
                    Angka di bawah dihitung <strong class="text-ink">otomatis</strong> saat Anda membuka halaman ini.
                    Sistem membandingkan penjualan
                    <strong class="text-ink">{{ $labelRentangKini }}</strong>
                    dengan periode sebelumnya
                    <strong class="text-ink">{{ $labelRentangSebelum }}</strong>,
                    plus stok produk saat ini, lalu memberi saran lewat Forward Chaining.
                </p>
                <p class="text-13 text-mute mb-0">
                    Bukan total sepanjang masa — hanya potret singkat hari ini vs kemarin untuk semua produk aktif.
                </p>
            </div>
            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <div class="stat h-100">
                        <span class="mono-caps">Produk dicek</span>
                        <p class="stat-value mt-2">{{ $totalProduk }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Produk yang punya data &amp; dianalisis otomatis</p>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="stat stat-accent stat-accent-error stat-tint-error h-100">
                        <span class="mono-caps text-error">Perlu restock</span>
                        <p class="stat-value text-error mt-2">{{ $butuhRestock }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Saran berisi restock / tambah stok</p>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="stat stat-accent stat-tint-brand h-100">
                        <span class="mono-caps text-brand">Perlu promosi</span>
                        <p class="stat-value text-brand-deep mt-2">{{ $perluPromosi }}</p>
                        <p class="text-13 text-mute mt-1 mb-0">Saran berisi promosi / dorong penjualan</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Kartu hasil per produk --}}
        <section>
            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Saran per produk</span>
                </div>
                <span class="mono-micro">{{ \App\Support\Waktu::sekarang('d M Y') }} · periode 1 hari</span>
            </div>
            <div class="surface-1 p-3 mb-3">
                <p class="text-13 text-ink-soft mb-2 lh-base">
                    Setiap kartu = <strong class="text-ink">satu produk</strong>.
                    Saran ini <strong class="text-ink">bukan</strong> berdasarkan satu transaksi kasir,
                    melainkan berdasarkan:
                </p>
                <ul class="text-13 text-mute mb-0 ps-3">
                    <li>Total unit terjual <strong class="text-ink">hari ini</strong>
                        ({{ $labelRentangKini }})</li>
                    <li>Dibanding total terjual <strong class="text-ink">kemarin</strong>
                        ({{ $labelRentangSebelum }}) → jadi tren naik/stabil/turun</li>
                    <li>Stok produk <strong class="text-ink">saat ini</strong> di gudang/depot</li>
                    <li>Aturan knowledge base (IF penjualan + stok + tren → MAKA saran), metode <strong
                            class="text-ink">Forward Chaining · First Match</strong></li>
                </ul>
            </div>

            @if($todayResults->isEmpty())
                <div class="surface-1 p-5 text-center">
                    <p class="text-ink fw-medium mb-1">Belum ada data untuk dianalisis</p>
                    <p class="text-13 text-mute mb-0">Pastikan sudah ada transaksi kasir, lalu buka halaman ini lagi.</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($todayResults as $result)
                        @php
                            $isRestock = str_contains(strtolower($result->rekomendasi), 'restock')
                                || str_contains(strtolower($result->rekomendasi), 'tambah stok');
                            $isPromo = str_contains(strtolower($result->rekomendasi), 'promosi');
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="surface-1 p-4 h-100 d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <h3 class="heading-sm text-ink mb-0">{{ $result->product->nama_produk }}</h3>
                                    <span class="badge badge-filled">{{ $result->rule_terpakai }}</span>
                                </div>
                                <p class="text-13 text-mute mb-3">
                                    Berdasarkan penjualan hari ini + stok saat ini
                                    @if($result->qty_saat_ini !== null)
                                        · terjual {{ $result->qty_saat_ini }} unit
                                    @endif
                                </p>

                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    <span
                                        class="badge {{ $result->kondisi_penjualan === 'Tinggi' ? 'badge-success' : ($result->kondisi_penjualan === 'Sedang' ? 'badge-amber' : 'badge-error') }}">
                                        Jual: {{ $result->kondisi_penjualan }}
                                    </span>
                                    <span
                                        class="badge {{ $result->kondisi_stok === 'Sedikit' ? 'badge-error' : ($result->kondisi_stok === 'Aman' ? 'badge-success' : 'badge-blue') }}">
                                        Stok: {{ $result->kondisi_stok }}
                                    </span>
                                    <span
                                        class="badge {{ $result->tren === 'Naik' ? 'badge-success' : ($result->tren === 'Turun' ? 'badge-error' : 'badge-amber') }}">
                                        Tren: {{ $result->tren }}
                                    </span>
                                </div>

                                <div
                                    class="p-3 rounded-app-md border border-hairline flex-grow-1 mb-3
                                                    {{ $isRestock ? 'stat-tint-error' : ($isPromo ? 'stat-tint-brand' : 'bg-canvas-paper') }}">
                                    <span
                                        class="mono-caps {{ $isRestock ? 'text-error' : ($isPromo ? 'text-brand' : 'text-mute') }}">Saran</span>
                                    <p class="text-ink fw-medium mt-1 mb-0 lh-sm">{{ $result->rekomendasi }}</p>
                                </div>

                                <button type="button" onclick="showTrace({{ $result->id }})"
                                    class="btn-ghost border w-100 justify-content-center">
                                    Lihat cara sistem berpikir
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Chart ringkas + pembuktian (skripsi & UMKM) --}}
        <section class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="surface-1 p-4 h-100">
                    <div class="section-eyebrow-row mb-3">
                        <span class="eyebrow-rule"></span>
                        <span class="mono-eyebrow">Distribusi penjualan</span>
                    </div>
                    <p class="text-13 text-mute mb-3">Berapa banyak produk yang jualannya rendah / sedang / tinggi
                        (periode {{ $labelRentangKini }}).</p>
                    <div class="position-relative" style="height:14rem;"><canvas id="penjualanChart"></canvas></div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge badge-error">Rendah: {{ $penjualanRendah }}</span>
                        <span class="badge badge-amber">Sedang: {{ $penjualanSedang }}</span>
                        <span class="badge badge-success">Tinggi: {{ $penjualanTinggi }}</span>
                        <span class="badge badge-filled">Total: {{ $totalProduk }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="surface-1 p-4 h-100">
                    <div class="section-eyebrow-row mb-3">
                        <span class="eyebrow-rule"></span>
                        <span class="mono-eyebrow">Distribusi tren</span>
                    </div>
                    <p class="text-13 text-mute mb-3">
                        Perbandingan qty {{ $labelRentangKini }} vs {{ $labelRentangSebelum }}.
                    </p>
                    <div class="position-relative" style="height:14rem;"><canvas id="trenChart"></canvas></div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge badge-success">Naik: {{ $trenNaik }}</span>
                        <span class="badge badge-amber">Stabil: {{ $trenStabil }}</span>
                        <span class="badge badge-error">Turun: {{ $trenTurun }}</span>
                        <span class="badge badge-filled">Total: {{ $totalProduk }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Dasar hitung chart: kenapa Rendah/Sedang/Tinggi & Naik/Stabil/Turun --}}
        <section class="surface-1 overflow-hidden">
            <div class="panel-header">
                <div class="section-eyebrow-row mb-0">
                    <span class="eyebrow-rule"></span>
                    <span class="mono-eyebrow">Audit Harian</span>
                </div>
                <span class="mono-micro">Sumber data = kartu saran per produk</span>
            </div>
            <div class="p-3 border-bottom border-hairline">
                <p class="text-13 text-ink-soft mb-2 lh-base">
                    Chart di atas dihitung dari <strong class="text-ink">{{ $totalProduk }} produk</strong>
                    yang dianalisis otomatis (periode <strong class="text-ink">{{ $labelRentangKini }}</strong>
                    dibanding <strong class="text-ink">{{ $labelRentangSebelum }}</strong>).
                    Setiap baris di bawah menjelaskan <em>mengapa</em> suatu produk masuk kategori
                    penjualan / tren tertentu.
                </p>
                <ul class="text-13 text-mute mb-0 ps-3">
                    <li>
                        <strong class="text-ink">Rumus selisih tren</strong>:
                        <span class="font-mono text-ink">Qty kini − Qty lalu = Selisih</span>
                        (contoh: <span class="font-mono">1 − 1 = 0</span>).
                        Jika selisih &gt; 0 → Naik, &lt; 0 → Turun, = 0 → Stabil.
                    </li>
                    <li>
                        <strong class="text-ink">Kondisi penjualan</strong>: bandingkan Qty kini dengan ambang batas per
                        produk
                        (Rendah ≤ batas rendah; Sedang di antara; Tinggi &gt; batas sedang).
                    </li>
                    <li>
                        <strong class="text-ink">Jumlah di chart</strong> = jumlah baris dengan badge yang sama
                        (total harus sama dengan total produk).
                    </li>
                </ul>
            </div>

            @if(($buktiChart ?? collect())->isEmpty())
                <div class="p-4 text-center">
                    <p class="text-ink fw-medium mb-1">Belum ada data hitung chart</p>
                    <p class="text-13 text-mute mb-0">Pastikan sudah ada transaksi kasir, lalu buka ulang halaman ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="tbl mb-0">
                        <thead>
                            <tr>
                                <th style="width:2.5rem;">No</th>
                                <th>Produk</th>
                                <th class="text-center">Selisih Qty</th>
                                <th class="text-center">Ambang (R / S / T)</th>
                                <th>Kondisi penjualan</th>
                                <th>Tren</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buktiChart as $i => $b)
                                @php
                                    $pjBadge = match ($b['kondisi_penjualan']) {
                                        'Tinggi' => 'badge-success',
                                        'Sedang' => 'badge-amber',
                                        default => 'badge-error',
                                    };
                                    $trBadge = match ($b['tren']) {
                                        'Naik' => 'badge-success',
                                        'Stabil' => 'badge-amber',
                                        default => 'badge-error',
                                    };
                                    $selisih = $b['selisih'];
                                    $selisihColor = $selisih > 0 ? 'text-brand-deep' : ($selisih < 0 ? 'text-error' : 'text-mute');
                                @endphp
                                <tr>
                                    <td class="text-mute font-mono" style="text-wrap-mode:nowrap;">
                                        {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                    <td class="text-ink fw-medium">{{ $b['produk'] }}</td>
                                    <td class="text-center tabular-nums fw-medium {{ $selisihColor }}">
                                        <span class="font-mono">{{ $b['qty_saat_ini'] }} − {{ $b['qty_sebelumnya'] }} =
                                            {{ $selisih }}</span>
                                    </td>
                                    <td class="text-center text-13 text-mute tabular-nums">
                                        @if($b['batas_rendah'] !== null)
                                            ≤{{ $b['batas_rendah'] }} / ≤{{ $b['batas_sedang'] }} / {{ $b['batas_tinggi'] }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $pjBadge }}">{{ $b['kondisi_penjualan'] }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $trBadge }}">{{ $b['tren'] }}</span>
                                    </td>
                                    <td class="text-13 text-ink-soft" style="max-width:22rem;">
                                        <div class="mb-1">{{ $b['alasan_penjualan'] }}</div>
                                        <div class="text-mute">{{ $b['alasan_tren'] }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f8faf9;">
                                <td colspan="4" class="text-end text-13 text-mute fw-medium">Rekap chart</td>
                                <td class="text-13">
                                    <span class="badge badge-error">R {{ $penjualanRendah }}</span>
                                    <span class="badge badge-amber">S {{ $penjualanSedang }}</span>
                                    <span class="badge badge-success">T {{ $penjualanTinggi }}</span>
                                </td>
                                <td class="text-13">
                                    <span class="badge badge-success">N {{ $trenNaik }}</span>
                                    <span class="badge badge-amber">S {{ $trenStabil }}</span>
                                    <span class="badge badge-error">T {{ $trenTurun }}</span>
                                </td>
                                <td class="text-13 text-mute">
                                    Jumlah = {{ $totalProduk }} produk (sama dengan chart)
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </section>

        <details class="surface-1 p-4">
            <summary class="text-ink fw-medium cursor-pointer" style="list-style:none;">
                <span class="mono-eyebrow">Info lanjutan</span>
                <span class="ms-2 text-13 text-mute">Batas penentuan kondisi (klik untuk buka)</span>
            </summary>
            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('products.index') }}" class="btn-ghost border">Kelola Ambang Batas di Detail
                    Produk</a>
            </div>
            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <p class="text-13 text-mute mb-2">Batas jumlah penjualan (unit) per produk:</p>
                    <div class="table-responsive">
                        <table class="tbl mb-0" style="font-size:12.5px;">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Rendah</th>
                                    <th class="text-center">Sedang</th>
                                    <th class="text-center">Tinggi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aturanPenjualan as $produk => $batas)
                                    <tr>
                                        <td class="text-ink">{{ $produk }}</td>
                                        <td class="text-center tabular-nums">&le; {{ $batas['rendah'] }}</td>
                                        <td class="text-center tabular-nums">{{ $batas['rendah'] + 1 }} –
                                            {{ $batas['sedang'] }}
                                        </td>
                                        <td class="text-center tabular-nums">{{ $batas['tinggi'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-6">
                    <p class="text-13 text-mute mb-2">Batas jumlah stok per produk (label mengikuti PLAN.md):</p>
                    <div class="table-responsive">
                        <table class="tbl mb-0" style="font-size:12.5px;">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center">Level 1</th>
                                    <th class="text-center">Level 2</th>
                                    <th class="text-center">Level 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aturanStok as $produk => $levels)
                                    <tr>
                                        <td class="text-ink">{{ $produk }}</td>
                                        <td class="text-center">
                                            <span class="text-ink-soft fw-medium">{{ $levels[0]['label'] }}</span>
                                            <span class="text-mute d-block tabular-nums" style="font-size:11px;">&le;
                                                {{ $levels[0]['batas'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-ink-soft fw-medium">{{ $levels[1]['label'] }}</span>
                                            <span class="text-mute d-block tabular-nums"
                                                style="font-size:11px;">{{ $levels[0]['batas'] + 1 }} –
                                                {{ $levels[1]['batas'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-ink-soft fw-medium">{{ $levels[2]['label'] }}</span>
                                            <span class="text-mute d-block tabular-nums" style="font-size:11px;">&gt;
                                                {{ $levels[1]['batas'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <p class="text-13 text-mute mb-2">Batas tren vs periode sebelumnya:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline">
                            <span class="badge badge-success">Naik</span>
                            <span class="text-13 text-mute ms-2">{{ $aturanTren['naik']['label'] }}</span>
                        </div>
                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline">
                            <span class="badge badge-amber">Stabil</span>
                            <span class="text-13 text-mute ms-2">{{ $aturanTren['stabil']['label'] }}</span>
                        </div>
                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline">
                            <span class="badge badge-error">Turun</span>
                            <span class="text-13 text-mute ms-2">{{ $aturanTren['turun']['label'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </details>
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
                <p class="text-ink fw-medium mb-2">Ringkasan dalam bahasa sederhana</p>
                <div id="traceSimple"
                    class="p-3 rounded-app-md bg-canvas-paper border border-hairline text-13 lh-base text-ink-soft">
                </div>
            </div>

            <div class="mb-4">
                <p class="text-ink fw-medium mb-2">Aturan yang dicek</p>
                <p class="text-13 text-mute mb-2">Sistem membandingkan data produk dengan setiap aturan. Aturan pertama
                    yang cocok menjadi saran.</p>
                <div id="ruleTable" class="table-responsive surface-2"></div>
            </div>

            <div>
                <p class="text-ink fw-medium mb-2">Catatan langkah demi langkah (untuk laporan)</p>
                <pre id="traceContent" class="font-mono text-13 lh-base p-4 rounded-app-md mb-0"
                    style="white-space:pre-wrap; background:#0b0b0b; color:#b9b9b9; overflow-x:auto;"></pre>
            </div>
        </div>
    </div>

    <script>
        @php
            $traceData = [];
            foreach ($todayResults as $result) {
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
                <p class="mb-2">Produk <strong class="text-ink">${d.product}</strong> saat ini:</p>
                <ul class="mb-2 ps-3">
                    <li>Penjualan <strong>${d.penjualan}</strong></li>
                    <li>Stok <strong>${d.stok}</strong></li>
                    <li>Tren <strong>${d.tren}</strong></li>
                </ul>
                <p class="mb-0">Karena itu sistem menyarankan: <strong class="text-brand">${d.rekomendasi}</strong>
                (dari aturan <strong>${d.rule ?? '-'}</strong>).</p>
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartFont = "'IBM Plex Mono', monospace";
        const chartGrid = 'rgba(11,11,11,0.06)';
        const chartTick = '#797979';

        new Chart(document.getElementById('penjualanChart'), {
            type: 'bar',
            data: {
                labels: ['Rendah', 'Sedang', 'Tinggi'],
                datasets: [{
                    label: 'Jumlah Produk',
                    data: [{{ $penjualanRendah }}, {{ $penjualanSedang }}, {{ $penjualanTinggi }}],
                    backgroundColor: ['#dd0000', '#93670b', '#057047'],
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 56,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0b0b0b', titleColor: '#fff', bodyColor: '#b9b9b9', padding: 12, cornerRadius: 8, titleFont: { family: chartFont }, bodyFont: { family: chartFont } } },
                scales: {
                    y: { beginAtZero: true, ticks: { color: chartTick, font: { family: chartFont, size: 11 }, precision: 0 }, grid: { color: chartGrid }, border: { display: false } },
                    x: { ticks: { color: '#353535', font: { family: chartFont, size: 12 } }, grid: { display: false }, border: { color: '#ededed' } }
                }
            }
        });

        new Chart(document.getElementById('trenChart'), {
            type: 'pie',
            data: {
                labels: ['Naik', 'Stabil', 'Turun'],
                datasets: [{
                    data: [{{ $trenNaik }}, {{ $trenStabil }}, {{ $trenTurun }}],
                    backgroundColor: ['#057047', '#93670b', '#dd0000'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#353535', font: { family: chartFont, size: 12 }, padding: 14, usePointStyle: true, pointStyle: 'rectRounded' } },
                    tooltip: { backgroundColor: '#0b0b0b', titleColor: '#fff', bodyColor: '#b9b9b9', padding: 12, cornerRadius: 8, titleFont: { family: chartFont }, bodyFont: { family: chartFont } }
                }
            }
        });
    </script>
</x-app-layout>
