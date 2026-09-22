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

<body>
    <div class="app-shell" x-data="{ sidebarOpen: false }">

        @include('layouts.navigation')

        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak x-transition.opacity
            class="sidebar-backdrop d-lg-none">
        </div>

        <div class="app-main">
            <header class="app-topbar">
                <div class="app-topbar-inner">
                    <button type="button" @click="sidebarOpen = true" class="btn-icon d-lg-none" aria-label="Buka menu">
                        <svg class="icon-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <a href="{{ route('dashboard') }}"
                        class="d-lg-none d-flex align-items-center gap-2 text-decoration-none">
                        <!-- <span class="brand-dot"></span> -->
                        <span class="text-ink text-14 fw-semibold">Simaan Water</span>
                    </a>

                    <div class="d-none d-lg-block flex-grow-1"></div>

                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <span class="badge badge-brand d-none d-sm-inline-flex">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>

                        <x-dropdown align="right" width="56">
                            <x-slot name="trigger">
                                <button type="button" class="user-menu-trigger">
                                    <span class="avatar avatar-sm">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span
                                        class="text-13 fw-medium text-ink d-none d-sm-inline">{{ Auth::user()->name }}</span>
                                    <svg class="icon-sm text-mute" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <div class="px-3 py-2 border-bottom border-hairline">
                                    <p class="text-13 fw-semibold text-ink mb-0">{{ Auth::user()->name }}</p>
                                    <p class="font-mono text-mute mb-0 text-truncate" style="font-size:11px;">
                                        {{ Auth::user()->email }}
                                    </p>
                                </div>
                                <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        Log Out
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            @if (isset($header))
                <section class="app-page-header">
                    <div class="app-page-header-inner">
                        <div>{{ $header }}</div>
                        @php
                            $headerActionsSlot = $__data['headerActions']
                                ?? $__data['headeractions']
                                ?? $__data['header-actions']
                                ?? null;
                        @endphp
                        @if ($headerActionsSlot)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{ $headerActionsSlot }}
                            </div>
                        @endif
                    </div>
                </section>
            @endif


            <main class="app-content">
                {{ $slot }}
                @isset($mobileCta)
                    <div class="mobile-cta-bar">{{ $mobileCta }}</div>
                @endisset
            </main>

            <footer class="app-footer">
                <div class="app-footer-inner">
                    <div class="d-flex align-items-center gap-2">
                        <!-- <span class="brand-dot"></span> -->
                        <span class="font-mono text-mute"
                            style="font-size:10px; letter-spacing:0.08em; text-transform:uppercase;">Simaan Water</span>
                    </div>
                    <p class="font-mono text-mute mb-0" style="font-size:10px; letter-spacing:0.06em;">
                        &copy; {{ date('Y') }} · POS & Analisis Rule Based
                    </p>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>