@extends('layouts.base')

@section('content')
<div 
    class="min-h-screen flex flex-col md:flex-row pb-20 md:pb-0"
    x-data="{ 
        darkMode: document.documentElement.classList.contains('dark'),
        showLogoutModal: false,
        mobileMenuOpen: false
    }"
    x-on:theme-changed.window="darkMode = $event.detail.isDark"
>
    <!-- Desktop Sidebar (Hidden on Mobile) -->
    <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 border-r border-slate-200 dark:border-slate-800 sticky top-0 h-screen shrink-0 z-30 shadow-sm transition-colors duration-200">
        <!-- Sidebar Brand -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/ekraf.png') }}" alt="Logo Ekraf" class="h-10 w-auto object-contain shrink-0 drop-shadow" />
                <div>
                    <h1 class="font-black text-base tracking-wide text-slate-900 dark:text-white leading-tight">BUKU TAMU</h1>
                    <p class="text-xs text-orange-600 dark:text-orange-400 font-semibold">Ekraf Majalengka</p>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <div class="text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2">Menu Utama</div>
            
            <!-- Dashboard -->
            <a 
                href="{{ route('admin.dashboard') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-md shadow-orange-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>

            <!-- Guest Logs -->
            <a 
                href="{{ route('admin.guest-logs.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request()->routeIs('admin.guest-logs.*') ? 'bg-orange-600 text-white shadow-md shadow-orange-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.35c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zM12 14c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
                <span>Data Tamu</span>
            </a>

            <!-- Divisions -->
            <a 
                href="{{ route('admin.divisions.index') }}" 
                class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request()->routeIs('admin.divisions.*') ? 'bg-orange-600 text-white shadow-md shadow-orange-600/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Master Bagian</span>
            </a>

            <div class="pt-4 text-[11px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider px-3 mb-2">Tautan Luar</div>

            <!-- Public Kiosk Link -->
            <a 
                href="{{ route('guestbook.index') }}" 
                target="_blank"
                class="flex items-center justify-between px-4 py-3 rounded-2xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>Layar Kiosk Publik</span>
                </div>
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </nav>

        <!-- Sidebar User Footer -->
        <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mode Tema</span>
                <button 
                    type="button" 
                    onclick="window.toggleAppTheme()" 
                    class="p-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
                    title="Ganti Tema Tampilan"
                >
                    <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </button>
            </div>

            <div class="flex items-center justify-between gap-2 p-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-orange-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-black text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email ?? 'admin' }}</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    @click="showLogoutModal = true"
                    class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition cursor-pointer"
                    title="Keluar"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- Mobile Top Navigation Header -->
    <header class="md:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 p-4 sticky top-0 z-30 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/ekraf.png') }}" alt="Logo Ekraf" class="h-8 w-auto object-contain shrink-0" />
            <div>
                <h1 class="font-black text-sm text-slate-900 dark:text-white leading-tight">BUKU TAMU</h1>
                <p class="text-[10px] text-orange-600 font-bold">Ekraf Majalengka</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                onclick="window.toggleAppTheme()" 
                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
            >
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </button>

            <button 
                type="button" 
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>
    </header>

    <!-- Mobile Drawer Menu -->
    <div 
        x-show="mobileMenuOpen" 
        x-cloak 
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"
        @click="mobileMenuOpen = false"
    >
        <div 
            class="w-3/4 max-w-xs bg-white dark:bg-slate-900 h-full p-5 flex flex-col justify-between"
            @click.stop
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/ekraf.png') }}" alt="Logo" class="h-8 w-auto" />
                        <span class="font-black text-sm">BUKU TAMU</span>
                    </div>
                    <button @click="mobileMenuOpen = false" class="p-1 rounded-lg text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.guest-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('admin.guest-logs.*') ? 'bg-orange-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                        <span>Data Tamu</span>
                    </a>
                    <a href="{{ route('admin.divisions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold {{ request()->routeIs('admin.divisions.*') ? 'bg-orange-600 text-white' : 'text-slate-700 dark:text-slate-200' }}">
                        <span>Master Bagian</span>
                    </a>
                    <a href="{{ route('guestbook.index') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-orange-600">
                        <span>Layar Kiosk Publik ↗</span>
                    </a>
                </nav>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <button 
                    @click="showLogoutModal = true" 
                    type="button" 
                    class="w-full py-2.5 rounded-xl bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 font-bold text-xs"
                >
                    Keluar dari Sistem
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs sm:text-sm font-bold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('admin_content')

        </main>
    </div>

    <!-- Logout Confirmation Modal -->
    <div 
        x-show="showLogoutModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showLogoutModal = false"
    >
        <div 
            class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-2xl text-center space-y-4"
            @click.outside="showLogoutModal = false"
        >
            <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 mx-auto flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Konfirmasi Keluar</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">Apakah Anda yakin ingin keluar dari sesi administrator?</p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button 
                    type="button" 
                    @click="showLogoutModal = false"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 transition cursor-pointer"
                >
                    Batal
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition cursor-pointer"
                    >
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
