@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'icon' => 'dashboard'],
    ];
    if (auth()->user()->role === 'pegawai') {
        $navItems[] = ['label' => 'Kasir', 'route' => route('sales.create'), 'active' => request()->routeIs('sales.create'), 'icon' => 'pos'];
        $navItems[] = ['label' => 'Riwayat Transaksi', 'route' => route('sales.history'), 'active' => request()->routeIs('sales.history'), 'icon' => 'history'];
    }
    if (auth()->user()->role === 'admin') {
        $navItems[] = ['label' => 'Kelola Produk', 'route' => route('products.index'), 'active' => request()->routeIs('products.*'), 'icon' => 'product'];
        $navItems[] = ['label' => 'Kelola Pengguna', 'route' => route('users.index'), 'active' => request()->routeIs('users.*'), 'icon' => 'user'];
        $navItems[] = ['label' => 'Riwayat Transaksi', 'route' => route('sales.history'), 'active' => request()->routeIs('sales.history'), 'icon' => 'history'];
        $navItems[] = ['label' => 'Laporan Penjualan', 'route' => route('reports.index'), 'active' => request()->routeIs('reports.*'), 'icon' => 'report'];
        $navItems[] = ['label' => 'Analisis Rule Based', 'route' => route('analysis.index'), 'active' => request()->routeIs('analysis.*'), 'icon' => 'ai'];
        $navItems[] = ['label' => 'Kelola Aturan', 'route' => route('rules.index'), 'active' => request()->routeIs('rules.*'), 'icon' => 'ai'];
    }
@endphp

<svg width="0" height="0" class="position-absolute" aria-hidden="true">
    <defs>
        <symbol id="i-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h14V10" />
        </symbol>
        <symbol id="i-pos" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v18H3zM3 9h18M9 21V9" />
        </symbol>
        <symbol id="i-product" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8 8-4-4M3 5h18v14H3z" />
        </symbol>
        <symbol id="i-report" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19l-6-2V4l6 2" />
        </symbol>
        <symbol id="i-ai" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </symbol>
        <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11a4 4 0 11-8 0 4 4 0 018 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0116 0" />
        </symbol>
        <symbol id="i-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 109-9 9 9 0 00-7.59 4.18" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v6h6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
        </symbol>
    </defs>
</svg>

<aside class="app-sidebar" :class="sidebarOpen ? 'is-open' : ''">
    <div class="app-sidebar-brand">
        <span class="text-ink text-18 fw-semibold">Simaan Water</span>
        <button type="button" @click="sidebarOpen = false" class="btn-icon d-lg-none ms-auto" aria-label="Tutup menu">
            <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="app-sidebar-accent"></div>

    <nav class="app-sidebar-nav">
        <p class="nav-section-label">Menu</p>
        @foreach($navItems as $item)
            <a href="{{ $item['route'] }}" class="nav-item-app {{ $item['active'] ? 'active' : '' }}">
                <svg class="nav-icon">
                    <use href="#i-{{ $item['icon'] }}" />
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>


    <div class="px-2 pb-3 pt-2 border-top border-hairline">
        <div class="d-flex align-items-center gap-2 px-1">
            <span class="avatar avatar-md">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <p class="text-13 fw-semibold text-ink text-truncate mb-0">{{ Auth::user()->name }}</p>
                <p class="font-mono text-mute text-truncate mb-0" style="font-size:10px;">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>