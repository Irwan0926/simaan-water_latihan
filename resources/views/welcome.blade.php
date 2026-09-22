<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Simaan Water — POS & Analisis Rule Based')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600&display=swap"
        rel="stylesheet" />

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="landing bg-canvas-light">

    <nav class="landing-nav">
        <div class="container-app">
            <div class="d-flex justify-content-between align-items-center" style="height:4rem;">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-2_5 text-decoration-none flex-shrink-0">
                    <!-- <span class="brand-dot"></span> -->
                    <span class="text-ink text-18 fw-medium tracking-tight">Simaan Water</span>
                </a>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="btn-primary">Masuk</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="landing-hero position-relative overflow-hidden">
        <div class="dot-grid position-absolute top-0 start-0 w-100 h-100 opacity-40" style="pointer-events:none;"></div>
        <div class="container-app position-relative py-5"
            style="padding-top:3.5rem!important;padding-bottom:3.5rem!important;">
            <div class="row g-4 g-lg-5 align-items-center">
                <div class="col-12 col-lg-6 animate-fade-up">
                    <span class="badge badge-success mb-3">POS &middot; Analisis Rule Based &middot; Depot Air</span>
                    <h1 class="display-lg text-ink mb-0" style="max-width:16ch; letter-spacing:-0.03em;">
                        Kelola depot air lebih rapi &amp; terarah
                    </h1>
                    <p class="text-ink-soft text-18 lh-lg mt-3 mb-0" style="max-width:36rem;">
                        Catat penjualan di kasir, pantau stok per produk, unduh laporan PDF,
                        dan dapatkan saran restock / promosi berbasis aturan (forward chaining).
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary">
                                Buka Dashboard
                                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </a>
                            <a href="{{ route('analysis.index') }}" class="btn-ghost border">Lihat Analisis Rule Based</a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="btn-primary">
                                    Masuk ke sistem
                                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </a>
                            @endif
                        @endauth
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4 pt-1">
                        <div class="d-flex align-items-center gap-2 text-13 text-mute">
                            <span class="badge badge-success"
                                style="width:0.5rem;height:0.5rem;padding:0;border-radius:999px;"></span>
                            Multi-role admin &amp; kasir
                        </div>
                        <div class="d-flex align-items-center gap-2 text-13 text-mute">
                            <span class="badge badge-success"
                                style="width:0.5rem;height:0.5rem;padding:0;border-radius:999px;"></span>
                            Ambang batas per produk
                        </div>
                        <div class="d-flex align-items-center gap-2 text-13 text-mute">
                            <span class="badge badge-success"
                                style="width:0.5rem;height:0.5rem;padding:0;border-radius:999px;"></span>
                            Laporan harian–bulanan
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6 animate-fade-up" style="animation-delay:0.08s;">
                    <div class="landing-hero-card surface-1 p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="mono-eyebrow">Preview ringkas</span>
                                <p class="heading-sm text-ink mt-1 mb-0">Alur kerja depot</p>
                            </div>
                            <span class="badge badge-filled">Rule-based</span>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="stat h-100 p-3">
                                    <span class="mono-caps">Kasir</span>
                                    <p class="text-ink fw-medium mt-2 mb-0 text-14">Transaksi</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat h-100 p-3">
                                    <span class="mono-caps">Stok</span>
                                    <p class="text-ink fw-medium mt-2 mb-0 text-14">Per produk</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat h-100 p-3">
                                    <span class="mono-caps">Rule Base</span>
                                    <p class="text-ink fw-medium mt-2 mb-0 text-14">Saran</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-app-md bg-canvas-paper border border-hairline">
                            <div class="d-flex align-items-start gap-3">
                                <div class="icon-box flex-shrink-0">
                                    <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-ink fw-medium mb-1">Contoh saran Rule Base</p>
                                    <p class="text-13 text-mute mb-2">
                                        Penjualan tinggi + stok sedikit + tren naik →
                                        <span class="text-brand-deep fw-medium">Restock Prioritas</span>
                                    </p>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge badge-success">Penjualan: Tinggi</span>
                                        <span class="badge badge-error">Stok: Sedikit</span>
                                        <span class="badge badge-amber">Tren: Naik</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <div class="landing-pill">
                                <span class="mono-micro text-mute">Invoice</span>
                                <span class="text-13 text-ink fw-medium">INV-YYYYMMDD-XXXX</span>
                            </div>
                            <div class="landing-pill">
                                <span class="mono-micro text-mute">Metode</span>
                                <span class="text-13 text-ink fw-medium">Forward Chaining</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats strip --}}
    <div class="container-app">
        <div class="landing-stats surface-1 p-3 p-md-4">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <span class="mono-caps">Modul</span>
                    <p class="display-md text-ink mt-2 mb-0">4</p>
                    <p class="text-13 text-mute mt-1 mb-0">Kasir, produk, laporan, Analisis Rule Base</p>
                </div>
                <div class="col-6 col-md-3">
                    <span class="mono-caps">Inferensi</span>
                    <p class="display-md text-ink mt-2 mb-0">FC</p>
                    <p class="text-13 text-mute mt-1 mb-0">First-match rule base</p>
                </div>
                <div class="col-6 col-md-3">
                    <span class="mono-caps">Laporan</span>
                    <p class="display-md text-ink mt-2 mb-0">PDF</p>
                    <p class="text-13 text-mute mt-1 mb-0">Harian, mingguan, bulanan</p>
                </div>
                <div class="col-6 col-md-3">
                    <span class="mono-caps">Peran</span>
                    <p class="display-md text-ink mt-2 mb-0">2</p>
                    <p class="text-13 text-mute mt-1 mb-0">Admin &amp; pegawai kasir</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modules --}}
    <section class="py-5">
        <div class="container-app">
            <div class="d-flex align-items-end justify-content-between gap-3 mb-4 flex-wrap">
                <div style="max-width:28rem;">
                    <span class="mono-eyebrow">Fitur utama</span>
                    <h2 class="display-md text-ink mt-2 mb-0">Semua yang dibutuhkan depot air</h2>
                </div>
                <p class="text-14 text-mute mb-0" style="max-width:22rem;">
                    Tampilan sederhana, alur jelas — dari transaksi harian sampai rekomendasi stok &amp; promosi.
                </p>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="surface-1 surface-step-hover p-4 h-100 d-flex flex-column">
                        <div class="icon-box mb-3">
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M3 3h18v18H3zM3 9h18M9 21V9" />
                            </svg>
                        </div>
                        <span class="mono-caps">01 · POS</span>
                        <h3 class="heading-sm text-ink mt-2">Kasir cepat</h3>
                        <p class="text-ink-soft text-14 lh-base mt-2 mb-0 flex-grow-1">
                            Multi-item, cek stok otomatis, invoice otomatis. Cocok untuk antrian depot harian.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="surface-1 surface-step-hover p-4 h-100 d-flex flex-column">
                        <div class="icon-box mb-3">
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M20 7l-8 8-4-4" />
                            </svg>
                        </div>
                        <span class="mono-caps">02 · Produk</span>
                        <h3 class="heading-sm text-ink mt-2">Stok &amp; ambang batas</h3>
                        <p class="text-ink-soft text-14 lh-base mt-2 mb-0 flex-grow-1">
                            Atur stok dan batas kondisi per produk (sedikit / cukup / banyak) tanpa hardcode.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="surface-1 surface-step-hover p-4 h-100 d-flex flex-column">
                        <div class="icon-box mb-3">
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M9 19V6l12-3v13M9 19l-6-2V4l6 2m0 13a3 3 0 11-6 0m0 0a3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="mono-caps">03 · Laporan</span>
                        <h3 class="heading-sm text-ink mt-2">PDF &amp; riwayat</h3>
                        <p class="text-ink-soft text-14 lh-base mt-2 mb-0 flex-grow-1">
                            Filter periode, unduh laporan harian / mingguan / bulanan, dan lacak transaksi kasir.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="feature-brand p-4 h-100 d-flex flex-column">
                        <div class="icon-box mb-3"
                            style="background:rgba(255,255,255,0.18);border-color:rgba(255,255,255,0.25);color:#fff;">
                            <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="mono-caps" style="color:rgba(255,255,255,0.8);">04 · Analisis Rule Base</span>
                        <h3 class="heading-sm mt-2" style="color:#fff;">Analisis Rule Based</h3>
                        <p class="text-14 lh-base mt-2 mb-0 flex-grow-1" style="color:rgba(255,255,255,0.9);">
                            Forward chaining: fakta penjualan, stok, tren → saran restock atau promosi yang bisa
                            diaudit.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-canvas-paper border-top border-bottom border-hairline py-5">
        <div class="container-app">
            <div class="row g-4 align-items-start">
                <div class="col-12 col-lg-4">
                    <span class="mono-eyebrow">Cara kerja Analisis Rule Base</span>
                    <h2 class="display-md text-ink mt-2 mb-0">Dari data ke keputusan</h2>
                    <p class="text-14 text-mute mt-3 mb-0 lh-base">
                        Setiap saran dilengkapi alasan: qty, ambang batas per produk, dan perbandingan tren.
                        Cocok untuk skripsi dan operasional UMKM.
                    </p>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="surface-1 p-4 h-100">
                                <span class="badge badge-filled">01</span>
                                <h3 class="heading-sm text-ink mt-3">Baca fakta</h3>
                                <p class="text-ink-soft text-14 lh-base mt-2 mb-0">
                                    Qty penjualan periode, stok saat ini, dan qty periode sebelumnya.
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="surface-1 p-4 h-100">
                                <span class="badge badge-filled">02</span>
                                <h3 class="heading-sm text-ink mt-3">Ubah ke kondisi</h3>
                                <p class="text-ink-soft text-14 lh-base mt-2 mb-0">
                                    Penjualan Rendah/Sedang/Tinggi · Stok Sedikit/Cukup/Banyak · Tren Naik/Stabil/Turun.
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="surface-1 p-4 h-100">
                                <span class="badge badge-filled">03</span>
                                <h3 class="heading-sm text-ink mt-3">Cocokkan rule</h3>
                                <p class="text-ink-soft text-14 lh-base mt-2 mb-0">
                                    Forward chaining first-match: aturan pertama yang cocok jadi saran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-5">
        <div class="container-app">
            <div class="cta-dark">
                <div class="dot-grid position-absolute top-0 start-0 w-100 h-100"
                    style="opacity:0.06; background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.35) 1px, transparent 0); background-size: 16px 16px;">
                </div>
                <div class="position-relative mx-auto text-center" style="max-width:40rem;">
                    <span class="font-mono text-uppercase tracking-wide"
                        style="font-size:11px;color:rgba(255,255,255,0.55);">Siap dipakai</span>
                    <h2 class="display-md mt-3 mb-0" style="color:#fff;">
                        Mulai kelola depot air hari ini
                    </h2>
                    <p class="mt-3 mb-0 text-14" style="color:rgba(255,255,255,0.72);">
                        Login sebagai admin atau kasir, catat transaksi, lalu buka Analisis Rule Based untuk saran stok
                        &amp; promosi.
                    </p>
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mt-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary">Buka Dashboard</a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="btn-primary">Masuk sekarang</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="app-footer">
        <div class="container-app py-4 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2_5">
                <!-- <span class="brand-dot"></span> -->
                <span class="font-mono text-uppercase tracking-wide text-ash" style="font-size:11px;">Simaan
                    Water</span>
            </div>
            <p class="font-mono text-uppercase tracking-wide text-mute mb-0" style="font-size:11px;">
                &copy; {{ date('Y') }} &middot; POS &amp; Analisis Rule Based untuk UMKM
            </p>
        </div>
    </footer>
</body>

</html>