@extends('layouts.admin')

@section('title', 'Master Bagian & Bidang')

@section('admin_content')
<div 
    class="space-y-6"
    x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        activeDivision: {},
        editDivision: {},
        deleteActionUrl: '',
        openEdit(div) {
            this.editDivision = { ...div };
            this.showEditModal = true;
        },
        openDelete(url, name) {
            this.deleteActionUrl = url;
            this.activeDivision = { name: name };
            this.showDeleteModal = true;
        }
    }"
>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Master Bagian / Bidang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Kelola daftar unit kerja, bidang, atau perangkat instansi yang dapat dipilih oleh tamu pada form kiosk.
            </p>
        </div>

        <button 
            type="button" 
            @click="showCreateModal = true"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md shadow-orange-600/20 transition active:scale-95 cursor-pointer shrink-0"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Bagian Baru</span>
        </button>
    </div>

    <!-- Table of Divisions -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200/80 dark:border-slate-800 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-5">No</th>
                        <th class="py-3.5 px-5">Nama Bagian / Bidang</th>
                        <th class="py-3.5 px-5 text-center">Status</th>
                        <th class="py-3.5 px-5 text-center">Total Kunjungan</th>
                        <th class="py-3.5 px-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @forelse($divisions as $index => $division)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-5 font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3.5 px-5">
                                <p class="font-black text-slate-900 dark:text-white text-sm">{{ $division->name }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">Dibuat {{ \Carbon\Carbon::parse($division->created_at)->locale('id')->isoFormat('D MMM Y') }}</p>
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <form action="{{ route('admin.divisions.toggle', $division) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1 rounded-full text-[10px] font-extrabold cursor-pointer transition border {{ $division->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-200 hover:bg-slate-200' }}"
                                        title="Klik untuk mengubah status"
                                    >
                                        {{ $division->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-5 text-center font-bold text-slate-700 dark:text-slate-300">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 font-mono text-xs font-black">
                                    {{ $division->guest_logs_count }} Tamu
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        @click="openEdit({{ json_encode([
                                            'id' => $division->id,
                                            'name' => $division->name,
                                            'is_active' => (bool) $division->is_active,
                                            'update_url' => route('admin.divisions.update', $division),
                                        ]) }})"
                                        class="p-2 rounded-xl text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 transition cursor-pointer"
                                        title="Ubah Nama Bagian"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button 
                                        type="button" 
                                        @click="openDelete('{{ route('admin.divisions.destroy', $division) }}', '{{ addslashes($division->name) }}')"
                                        class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition cursor-pointer"
                                        title="Hapus Bagian"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                Belum ada data bagian / bidang yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. CREATE MODAL -->
    <div 
        x-show="showCreateModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @keydown.escape.window="showCreateModal = false"
    >
        <div 
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden"
            @click.outside="showCreateModal = false"
        >
            <form action="{{ route('admin.divisions.store') }}" method="POST">
                @csrf
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Tambah Bagian Baru</h3>
                    <button type="button" @click="showCreateModal = false" class="p-1 text-slate-400 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Nama Bagian / Bidang <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            required 
                            placeholder="Contoh: Bidang Inkubator Bisnis" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 font-bold text-sm text-slate-900 dark:text-white focus:outline-none focus:border-orange-500"
                        />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500 border-slate-300 dark:border-slate-700" />
                            <span class="font-bold text-slate-700 dark:text-slate-300">Langsung aktifkan untuk pilihan tamu</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md cursor-pointer">
                        Simpan Bagian
                    </button>
                </div>
            </form>
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
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden"
            @click.outside="showEditModal = false"
        >
            <form :action="editDivision.update_url" method="POST">
                @csrf
                @method('PUT')

                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Ubah Data Bagian</h3>
                    <button type="button" @click="showEditModal = false" class="p-1 text-slate-400 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-extrabold text-slate-700 dark:text-slate-300 uppercase mb-1.5">Nama Bagian / Bidang <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="name" 
                            x-model="editDivision.name"
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 font-bold text-sm text-slate-900 dark:text-white focus:outline-none focus:border-orange-500"
                        />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" :checked="editDivision.is_active" class="w-4 h-4 rounded text-orange-600 focus:ring-orange-500 border-slate-300 dark:border-slate-700" />
                            <span class="font-bold text-slate-700 dark:text-slate-300">Status Bagian Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2.5">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. DELETE MODAL -->
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
                <h3 class="text-base font-black text-slate-900 dark:text-white">Hapus Bagian Ini?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                    Anda akan menghapus bagian <strong x-text="activeDivision.name"></strong>. Riwayat tamu yang pernah memilih bagian ini tetap tersimpan.
                </p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <form :action="deleteActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md cursor-pointer">
                        Hapus Bagian
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
