@extends('layouts.admin')

@section('title', 'Dashboard Administrator')

@section('admin_content')
<div class="space-y-6">

    <!-- Admin Welcome Header (Matches AbsensiWangkelang Gradient & Aesthetics) -->
    <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-zinc-900 rounded-2xl sm:rounded-3xl p-5 sm:p-7 md:p-8 text-white shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/10 backdrop-blur-md p-2.5 flex items-center justify-center border border-white/20 shadow-inner shrink-0">
                    <img src="{{ asset('images/ekraf.png') }}" alt="Logo Ekraf" class="w-full h-full object-contain drop-shadow" />
                </div>
                <div class="space-y-1">
                    <span class="inline-block px-3 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 backdrop-blur border border-white/30 text-white uppercase tracking-wider">
                        Panel Kontrol Administrator
                    </span>
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-black">Buku Tamu Ekraf Majalengka</h1>
                    <p class="text-xs sm:text-sm text-orange-100 font-medium">
                        Pantau riwayat kunjungan tamu, statistik bidang tujuan, dan kelola laporan resmi.
                    </p>
                </div>
            </div>

            <!-- Realtime WIB Clock & Kiosk Shortcut -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 shrink-0"
                x-data="{ 
                    clock: '', 
                    tick() { 
                        const now = new Date();
                        const opt = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                        this.clock = new Intl.DateTimeFormat('id-ID', opt).format(now).replace(/\./g, ':') + ' WIB';
                    } 
                }"
                x-init="tick(); setInterval(() => tick(), 1000)"
            >
                <!-- Realtime Badge -->
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur border border-white/20 text-white shadow-inner">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-300 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-300"></span>
                    </span>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-orange-200 uppercase font-black tracking-wider">Waktu Realtime</span>
                        <span class="text-xs sm:text-sm font-black font-mono" x-text="clock">00:00:00 WIB</span>
                    </div>
                </div>

                <!-- Kiosk Link -->
                <a 
                    href="{{ route('guestbook.index') }}" 
                    target="_blank"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white text-orange-600 hover:bg-orange-50 font-black text-xs transition shadow-md active:scale-95"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>Buka Layar Kiosk</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        <!-- Tamu Hari Ini -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Tamu Hari Ini</span>
                <span class="p-2 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $totalToday }}</p>
            <p class="text-[11px] text-slate-400 font-medium">Kunjungan tercatat hari ini</p>
        </div>

        <!-- Tamu Bulan Ini -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-orange-600 dark:text-orange-400 uppercase tracking-wider">Tamu Bulan Ini</span>
                <span class="p-2 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $totalMonth }}</p>
            <p class="text-[11px] text-slate-400 font-medium">Bulan {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM Y') }}</p>
        </div>

        <!-- Total Keseluruhan Tamu -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Keseluruhan</span>
                <span class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $totalAll }}</p>
            <p class="text-[11px] text-slate-400 font-medium">Semua riwayat kunjungan</p>
        </div>

        <!-- Bagian Aktif -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Bagian / Bidang</span>
                <span class="p-2 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">{{ $totalDivisions }}</p>
            <p class="text-[11px] text-slate-400 font-medium">Bagian aktif melayani</p>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Chart 1: Kunjungan 7 Hari Terakhir (7 Cols) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Tren Kunjungan 7 Hari Terakhir</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Grafik pergerakan jumlah kunjungan harian</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300">
                    7 Hari
                </span>
            </div>

            <!-- Visual Bar Chart -->
            @php
                $maxCount = max(array_column($sevenDays, 'count'));
                $maxHeight = $maxCount > 0 ? $maxCount : 1;
            @endphp
            <div class="pt-4 flex items-end justify-between gap-2 sm:gap-4 h-48 px-2 border-b border-slate-100 dark:border-slate-800">
                @foreach($sevenDays as $day)
                    @php
                        $percentage = ($day['count'] / $maxHeight) * 100;
                        $barHeight = $percentage > 0 ? max($percentage, 10) : 4;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-2 group h-full justify-end">
                        <div class="text-[11px] font-bold text-slate-600 dark:text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity">
                            {{ $day['count'] }}
                        </div>
                        <div 
                            class="w-full max-w-[42px] rounded-t-xl transition-all duration-300 {{ $day['count'] > 0 ? 'bg-gradient-to-t from-orange-600 to-orange-400 group-hover:from-orange-500 group-hover:to-orange-300 shadow-sm' : 'bg-slate-100 dark:bg-slate-800' }}"
                            style="height: {{ $barHeight }}%;"
                            title="{{ $day['label'] }}: {{ $day['count'] }} tamu"
                        ></div>
                        <span class="text-[10px] font-semibold text-slate-400 truncate max-w-full text-center">
                            {{ $day['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chart 2: Proporsi Bagian / Bidang (5 Cols) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-white">Bagian Sering Dikunjungi</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Proporsi volume tujuan tamu</p>
                </div>
            </div>

            <div class="space-y-3.5 pt-2">
                @forelse($divisionStats as $div)
                    @php
                        $sharePercent = $totalAll > 0 ? round(($div->guest_logs_count / $totalAll) * 100) : 0;
                    @endphp
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700 dark:text-slate-300 truncate max-w-[200px]">{{ $div->name }}</span>
                            <span class="text-orange-600 dark:text-orange-400 font-black">{{ $div->guest_logs_count }} <span class="text-[10px] font-normal text-slate-400">({{ $sharePercent }}%)</span></span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-orange-500 rounded-full transition-all duration-500" style="width: {{ $sharePercent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada data bagian yang dicatat.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Recent Guest Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white">Kunjungan Tamu Terbaru</h2>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Daftar tamu yang baru saja mencatatkan kehadiran di kiosk</p>
            </div>
            <a 
                href="{{ route('admin.guest-logs.index') }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 hover:bg-orange-100 text-xs font-bold transition"
            >
                <span>Lihat Semua Tamu</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-3">Tamu</th>
                        <th class="py-3 px-3">Waktu</th>
                        <th class="py-3 px-3">Instansi / Alamat</th>
                        <th class="py-3 px-3">Tujuan Bagian</th>
                        <th class="py-3 px-3">Keperluan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($recentGuests as $guest)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-3">
                                    <img 
                                        src="{{ $guest->photo_url }}" 
                                        alt="{{ $guest->name }}" 
                                        class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm shrink-0"
                                    />
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $guest->name }}</p>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $guest->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($guest->visit_date)->locale('id')->isoFormat('D MMM Y') }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ substr($guest->visit_time, 0, 5) }} WIB</p>
                            </td>
                            <td class="py-3 px-3 text-slate-600 dark:text-slate-300 font-medium">
                                {{ $guest->institution }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $guest->division ? $guest->division->name : '-' }}
                                </span>
                                @if($guest->person_to_meet)
                                    <p class="text-[10px] text-slate-400 mt-1">Staf: {{ $guest->person_to_meet }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                {{ $guest->purpose }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Belum ada data kunjungan tamu tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
