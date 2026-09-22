<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Simaan Water'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600,700|ibm-plex-mono:400,500,600&display=swap"
        rel="stylesheet" />

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="min-h-screen">
    <div class="guest-shell">

        <div class="guest-panel guest-hero">
            <div class="dot-grid position-absolute top-0 start-0 w-100 h-100 opacity-25"
                style="opacity:0.04; background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.2) 1px, transparent 0); background-size: 16px 16px;">
            </div>

            <a href="/" class="position-relative d-flex align-items-center gap-2_5 text-decoration-none">
                <!-- <span class="brand-dot"></span> -->
                <span class="text-on-primary text-18 fw-medium tracking-tight">Simaan Water</span>
            </a>

            <div class="position-relative">
                <span class="mono-eyebrow" style="color:#b9b9b9;">POS · RULE BASE INSIGHT</span>
                <h1 class="text-on-primary mt-3 mb-0"
                    style="color:#fff; font-size:1.75rem; font-weight:600; line-height:1.25; max-width:16ch;">
                    Kelola depot air dengan keputusan berbasis aturan.
                </h1>
                <p class="mt-3 mb-0" style="color:#b9b9b9; font-size:0.875rem; line-height:1.6; max-width:22rem;">
                    Catat penjualan, pantau stok, dan jalankan forward chaining
                    untuk rekomendasi restock maupun promosi.
                </p>
            </div>

            <div
                class="position-relative d-flex align-items-center gap-4 font-mono text-13 text-uppercase tracking-wide text-mute">
                <span class="d-flex align-items-center gap-2"><span class="rounded-circle bg-brand d-inline-block"
                        style="width:6px;height:6px;"></span> Rule-Based</span>
                <span class="d-flex align-items-center gap-2"><span class="rounded-circle bg-ash d-inline-block"
                        style="width:6px;height:6px;background:#b9b9b9;"></span> Forward Chaining</span>
                <span class="d-flex align-items-center gap-2"><span class="rounded-circle d-inline-block"
                        style="width:6px;height:6px;background:#b9b9b9;"></span> Laravel</span>
            </div>
        </div>

        <div class="guest-form-col">
            <header class="px-4 px-sm-5 pt-4">
                <a href="/" class="d-lg-none d-flex align-items-center gap-2_5 text-decoration-none">
                    <!-- <span class="brand-dot"></span> -->
                    <span class="text-ink text-18 fw-medium tracking-tight">Simaan Water</span>
                </a>
            </header>

            <div class="guest-form-center">
                <div class="guest-form-inner animate-fade-up">
                    {{ $slot }}
                </div>
            </div>

            <footer class="px-4 px-sm-5 py-4">
                <p class="font-mono text-13 text-uppercase tracking-wide text-mute mb-0">
                    &copy; {{ date('Y') }} &middot; Simaan Water
                </p>
            </footer>
        </div>
    </div>
</body>

</html>