@extends('layouts.admin')

@section('title', 'Data Buku Tamu')

@section('admin_content')
<div 
    class="space-y-6"
    x-data="{
        showDetailModal: false,
        showEditModal: false,
        showDeleteModal: false,
        activeGuest: {},
        editGuest: {},
        deleteActionUrl: '',
        openDetail(guest) {
            this.activeGuest = guest;
            this.showDetailModal = true;
        },
        openEdit(guest) {
            this.editGuest = { ...guest };
            this.showEditModal = true;
        },
        openDelete(url, name) {
            this.deleteActionUrl = url;
            this.activeGuest = { name: name };
            this.showDeleteModal = true;
        }
    }"
>
    <!-- Header with Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Riwayat Buku Tamu</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Kelola pencatatan kunjungan, periksa foto wajah tamu, dan unduh laporan resmi.
            </p>
        </div>

        <!-- Export Buttons (PDF & Excel) -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Export Excel -->
            <a 
                href="{{ route('admin.guest-logs.export.excel', request()->query()) }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export Excel</span>
            </a>

            <!-- Export PDF -->
            <a 
                href="{{ route('admin.guest-logs.export.pdf', request()->query()) }}" 
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md shadow-orange-600/20 transition active:scale-95"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak PDF Resmi</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.guest-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
            <!-- Search Keyword -->
            <div class="lg:col-span-2">
                <label for="q" class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input 
                        type="text" 
                        id="q" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Cari nama, instansi, atau keperluan..." 
                        class="w-full pl-9 pr-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/10"
                    />
                </div>
            </div>

            <!-- Division Filter -->
            <div>
                <label for="division_id" class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Bagian / Bidang</label>
                <select 
                    id="division_id" 
                    name="division_id" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-orange-500"
                >
                    <option value="">Semua Bagian</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->id }}" {{ request('division_id') == $div->id ? 'selected' : '' }}>
                            {{ $div->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label for="start_date" class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                <input 
                    type="date" 
                    id="start_date" 
                    name="start_date" 
                    value="{{ request('start_date') }}" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-orange-500 font-mono"
                />
            </div>

            <!-- End Date & Submit -->
            <div class="flex items-center gap-2">
                <div class="flex-1">
                    <label for="end_date" class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="{{ request('end_date') }}" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-orange-500 font-mono"
                    />
                </div>
                <button 
                    type="submit" 
                    class="h-[38px] px-3.5 mt-auto rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-extrabold text-xs transition cursor-pointer flex items-center justify-center shrink-0"
                    title="Terapkan Filter"
                >
                    Filter
                </button>
                @if(request()->hasAny(['q', 'division_id', 'start_date', 'end_date']))
                    <a 
                        href="{{ route('admin.guest-logs.index') }}" 
                        class="h-[38px] px-3 mt-auto rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 text-xs font-bold transition flex items-center justify-center shrink-0"
                        title="Reset Filter"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Guest Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Foto Wajah</th>
                        <th class="py-3.5 px-4">Waktu Kunjungan</th>
                        <th class="py-3.5 px-4">Nama Tamu & HP</th>
                        <th class="py-3.5 px-4">Instansi / Alamat</th>
                        <th class="py-3.5 px-4">Tujuan Bagian</th>
                        <th class="py-3.5 px-4">Keperluan</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($guestLogs as $guest)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <!-- Thumbnail Photo (Clickable) -->
                            <td class="py-3 px-4">
                                <button 
                                    type="button" 
                                    @click="openDetail({{ json_encode([
                                        'id' => $guest->id,
                                        'name' => $guest->name,
                                        'phone' => $guest->phone,
                                        'institution' => $guest->institution,
                                        'division_name' => $guest->division ? $guest->division->name : '-',
                                        'person_to_meet' => $guest->person_to_meet,
                                        'purpose' => $guest->purpose,
                                        'visit_date' => \Carbon\Carbon::parse($guest->visit_date)->locale('id')->isoFormat('dddd, D MMMM Y'),
                                        'visit_time' => substr($guest->visit_time, 0, 5) . ' WIB',
                                        'photo_url' => $guest->photo_url,
                                    ]) }})"
                                    class="group relative block w-12 h-12 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer"
                                    title="Klik untuk melihat foto ukuran penuh"
                                >
                                    <img 
                                        src="{{ $guest->photo_url }}" 
                                        alt="{{ $guest->name }}" 
                                        class="w-full h-full object-cover group-hover:scale-110 transition duration-200"
                                    />
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                    </div>
                                </button>
                            </td>

                            <!-- Visit Date & Time -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <p class="font-bold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($guest->visit_date)->locale('id')->isoFormat('D MMM Y') }}</p>
                                <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ substr($guest->visit_time, 0, 5) }} WIB</p>
                            </td>

                            <!-- Guest Name & Phone -->
                            <td class="py-3 px-4">
                                <p class="font-black text-slate-900 dark:text-white">{{ $guest->name }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $guest->phone) }}" target="_blank" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono font-bold hover:underline flex items-center gap-1 mt-0.5">
                                    <span>{{ $guest->phone }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </td>

                            <!-- Institution -->
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                                {{ $guest->institution }}
                            </td>

                            <!-- Division & Person to Meet -->
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border border-orange-200/80">
                                    {{ $guest->division ? $guest->division->name : '-' }}
                                </span>
                                @if($guest->person_to_meet)
                                    <p class="text-[10px] text-slate-400 mt-1 font-medium">Bertemu: <strong>{{ $guest->person_to_meet }}</strong></p>
                                @endif
                            </td>

                            <!-- Purpose -->
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $guest->purpose }}">
                                {{ $guest->purpose }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Detail Modal Button -->
                                    <button 
                                        type="button" 
                                        @click="openDetail({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'phone' => $guest->phone,
                                            'institution' => $guest->institution,
                                            'division_name' => $guest->division ? $guest->division->name : '-',
                                            'person_to_meet' => $guest->person_to_meet,
                                            'purpose' => $guest->purpose,
                                            'visit_date' => \Carbon\Carbon::parse($guest->visit_date)->locale('id')->isoFormat('dddd, D MMMM Y'),
                                            'visit_time' => substr($guest->visit_time, 0, 5) . ' WIB',
                                            'photo_url' => $guest->photo_url,
                                        ]) }})"
                                        class="p-2 rounded-xl text-slate-500 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950 transition cursor-pointer"
                                        title="Lihat Detail Lengkap"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        @click="openEdit({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'phone' => $guest->phone,
                                            'institution' => $guest->institution,
                                            'division_id' => $guest->division_id,
                                            'person_to_meet' => $guest->person_to_meet,
                                            'purpose' => $guest->purpose,
                                            'update_url' => route('admin.guest-logs.update', $guest),
                                        ]) }})"
                                        class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 transition cursor-pointer"
                                        title="Edit Koreksi Data"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button 
                                        type="button" 
                                        @click="openDelete('{{ route('admin.guest-logs.destroy', $guest) }}', '{{ addslashes($guest->name) }}')"
                                        class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition cursor-pointer"
                                        title="Hapus Data Tamu"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Tidak ada data buku tamu yang sesuai dengan filter atau kata kunci pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($guestLogs->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $guestLogs->links() }}
            </div>
        @endif
    </div>

    <!-- 1. POPUP DETAIL MODAL (FULL PHOTO & INFO) -->
    <div 
        x-show="showDetailModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showDetailModal = false"
    >
        <div 
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden"
            @click.outside="showDetailModal = false"
        >
            <!-- Modal Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Detail Kunjungan Tamu</h3>
                    <p class="text-xs text-slate-400 font-medium" x-text="activeGuest.visit_date + ' • ' + activeGuest.visit_time"></p>
                </div>
                <button @click="showDetailModal = false" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                <!-- Large Photo Viewport -->
                <div class="relative rounded-2xl overflow-hidden aspect-[4/3] bg-black border border-slate-800 shadow-inner flex items-center justify-center">
                    <img 
                        :src="activeGuest.photo_url" 
                        :alt="activeGuest.name" 
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur px-3 py-1 rounded-full text-white text-[11px] font-bold">
                        Swafoto Webcam Tamu
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase">Nama Lengkap</span>
                            <p class="font-black text-slate-900 dark:text-white text-sm mt-0.5" x-text="activeGuest.name"></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase">WhatsApp / HP</span>
                            <p class="font-mono font-bold text-slate-900 dark:text-white text-sm mt-0.5" x-text="activeGuest.phone"></p>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">Asal Instansi / Alamat</span>
                        <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5" x-text="activeGuest.institution"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase">Bagian / Bidang</span>
                            <p class="font-bold text-orange-600 dark:text-orange-400 mt-0.5" x-text="activeGuest.division_name"></p>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase">Bertemu Dengan</span>
                            <p class="font-bold text-slate-800 dark:text-slate-200 mt-0.5" x-text="activeGuest.person_to_meet || '-'"></p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase">Keperluan Kunjungan</span>
                        <p class="text-slate-700 dark:text-slate-300 mt-1 leading-relaxed whitespace-pre-wrap" x-text="activeGuest.purpose"></p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button 
                    type="button" 
                    @click="showDetailModal = false"
                    class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs hover:bg-slate-200 transition cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- 2. EDIT MODAL -->
    <div 
        x-show="showEditModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showEditModal = false"
    >
        <div 
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden"
            @click.outside="showEditModal = false"
        >
            <form :action="editGuest.update_url" method="POST">
                @csrf
                @method('PUT')

                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Koreksi Data Tamu</h3>
                    <button type="button" @click="showEditModal = false" class="p-1 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" x-model="editGuest.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 font-bold" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">No HP</label>
                            <input type="text" name="phone" x-model="editGuest.phone" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 font-mono" />
                        </div>
                        <div>
                            <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">Tujuan Bagian</label>
                            <select name="division_id" x-model="editGuest.division_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950">
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">Asal Instansi / Alamat</label>
                        <input type="text" name="institution" x-model="editGuest.institution" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950" />
                    </div>

                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">Bertemu Siapa</label>
                        <input type="text" name="person_to_meet" x-model="editGuest.person_to_meet" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950" />
                    </div>

                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1">Keperluan Kunjungan</label>
                        <textarea name="purpose" x-model="editGuest.purpose" rows="3" required class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950"></textarea>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. DELETE CONFIRMATION MODAL -->
    <div 
        x-show="showDeleteModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showDeleteModal = false"
    >
        <div 
            class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-sm w-full border border-slate-200 dark:border-slate-800 shadow-2xl text-center space-y-4"
            @click.outside="showDeleteModal = false"
        >
            <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Hapus Data Buku Tamu?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    Data kunjungan atas nama <strong x-text="activeGuest.name"></strong> beserta foto webcam akan dihapus permanen.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                    Batal
                </button>
                <form :action="deleteActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                        Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
