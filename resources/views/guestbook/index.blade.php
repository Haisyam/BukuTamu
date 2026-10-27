@extends('layouts.base')

@section('title', 'Buku Tamu Digital')

@section('content')
<div 
    class="min-h-screen flex flex-col justify-between py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
    x-data="{
        darkMode: document.documentElement.classList.contains('dark'),
        stream: null,
        cameraReady: false,
        cameraError: '',
        facingMode: 'user',
        capturedImage: null,
        lookingUp: false,
        isReturningGuest: false,
        submitting: false,
        formData: {
            phone: '',
            name: '',
            institution: '',
            division_id: '',
            person_to_meet: '',
            purpose: '',
            photo: ''
        },
        realtimeClock: '',
        updateClock() {
            const now = new Date();
            const options = { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            this.realtimeClock = new Intl.DateTimeFormat('id-ID', options).format(now).replace(/\./g, ':') + ' WIB';
        },
        async startCamera() {
            this.cameraError = '';
            try {
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                }
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        facingMode: this.facingMode, 
                        width: { ideal: 1280 }, 
                        height: { ideal: 960 } 
                    },
                    audio: false
                });
                const video = this.$refs.video;
                if (video) {
                    video.srcObject = this.stream;
                    await video.play();
                }
                this.cameraReady = true;
            } catch (e) {
                this.cameraError = 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diaktifkan pada browser.';
                this.cameraReady = false;
            }
        },
        async flipCamera() {
            this.facingMode = this.facingMode === 'user' ? 'environment' : 'user';
            await this.startCamera();
        },
        capture() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            if (!video) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            if (this.facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.capturedImage = canvas.toDataURL('image/jpeg', 0.88);
            this.formData.photo = this.capturedImage;

            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
        },
        async retake() {
            this.capturedImage = null;
            this.formData.photo = '';
            await this.$nextTick();
            await this.startCamera();
        },
        async lookupPhone() {
            const p = this.formData.phone.trim();
            if (p.length < 5) {
                this.isReturningGuest = false;
                return;
            }
            this.lookingUp = true;
            try {
                const res = await fetch(`/api/guest-lookup?phone=${encodeURIComponent(p)}`);
                const data = await res.json();
                if (data.found && data.data) {
                    this.isReturningGuest = true;
                    if (!this.formData.name) this.formData.name = data.data.name;
                    if (!this.formData.institution) this.formData.institution = data.data.institution;
                } else {
                    this.isReturningGuest = false;
                }
            } catch (err) {
                this.isReturningGuest = false;
            } finally {
                this.lookingUp = false;
            }
        },
        escapeHtml(text) {
            const p = document.createElement('p');
            p.textContent = text || '';
            return p.innerHTML;
        },
        async submitGuestLog() {
            if (!this.formData.photo) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Foto Belum Diambil',
                    text: 'Silakan ambil foto wajah melalui kamera webcam terlebih dahulu sebelum menyimpan.',
                    confirmButtonColor: '#ea580c',
                    confirmButtonText: 'Mengerti'
                });
                return;
            }

            if (!this.formData.name || !this.formData.phone || !this.formData.institution || !this.formData.division_id || !this.formData.purpose) {
                window.Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Pastikan seluruh kolom isian bertanda bintang (*) telah diisi dengan benar.',
                    confirmButtonColor: '#ea580c',
                    confirmButtonText: 'Lengkapi Data'
                });
                return;
            }

            this.submitting = true;

            try {
                const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                const res = await fetch('/guest-book', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(this.formData)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    const safeName = this.escapeHtml(this.formData.name);
                    window.Swal.fire({
                        icon: 'success',
                        title: 'Kunjungan Berhasil Dicatat!',
                        html: `<p class='text-sm text-slate-600 dark:text-slate-300'>Selamat datang di Ekraf Majalengka, <strong>${safeName}</strong>.<br>Data Anda telah tersimpan dengan aman.</p>`,
                        timer: 3500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        iconColor: '#ea580c'
                    });

                    // Reset form and restart camera
                    this.formData = {
                        phone: '',
                        name: '',
                        institution: '',
                        division_id: '',
                        person_to_meet: '',
                        purpose: '',
                        photo: ''
                    };
                    this.capturedImage = null;
                    this.isReturningGuest = false;
                    await this.$nextTick();
                    await this.startCamera();
                } else {
                    let errMsg = data.message || 'Gagal menyimpan buku tamu. Silakan periksa kembali isian formulir.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        errMsg = data.errors[firstKey][0];
                    }
                    window.Swal.fire({
                        icon: 'error',
                        title: 'Pencatatan Gagal',
                        text: errMsg,
                        confirmButtonColor: '#ea580c'
                    });
                }
            } catch (err) {
                window.Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Tidak dapat terhubung ke server. Silakan periksa koneksi dan coba lagi.',
                    confirmButtonColor: '#ea580c'
                });
            } finally {
                this.submitting = false;
            }
        }
    }"
    x-init="
        updateClock(); 
        setInterval(() => updateClock(), 1000);
        startCamera();
    "
    x-on:theme-changed.window="darkMode = $event.detail.isDark"
>

    <!-- Top Navigation & Brand Header -->
    <header class="mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Brand Info -->
            <div class="flex items-center gap-3.5 w-full sm:w-auto">
                <div class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-orange-950/40 p-2 flex items-center justify-center border border-orange-200/60 dark:border-orange-800/60 shrink-0 shadow-sm">
                    <img src="{{ asset('images/ekraf.png') }}" alt="Logo Ekraf" class="w-full h-full object-contain drop-shadow" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">BUKU TAMU DIGITAL</h1>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-100 text-orange-800 dark:bg-orange-950/80 dark:text-orange-300 border border-orange-200 dark:border-orange-800">
                            KIOSK
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Pusat Pelayanan Terpadu Ekraf Majalengka</p>
                </div>
            </div>

            <!-- Header Actions: Clock, Theme, Login -->
            <div class="flex items-center justify-between sm:justify-end gap-2.5 w-full sm:w-auto shrink-0">
                <!-- Realtime WIB Clock -->
                <div class="flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 text-slate-700 dark:text-slate-200 shadow-inner">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-600"></span>
                    </span>
                    <span class="text-xs font-mono font-bold tracking-tight" x-text="realtimeClock">00:00:00 WIB</span>
                </div>

                <!-- Theme Switcher -->
                <button 
                    type="button" 
                    onclick="window.toggleAppTheme()" 
                    class="p-2 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                    title="Ganti Tema Tampilan"
                >
                    <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    <svg x-show="darkMode" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </button>

                <!-- Admin Login Button -->
                <a 
                    href="{{ route('login') }}" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-orange-600 hover:bg-orange-700 text-white font-extrabold text-xs shadow-md shadow-orange-600/20 transition active:scale-95"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span>Login Petugas</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Grid -->
    <main class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start flex-1 mb-6">
        
        <!-- Left Side: Camera Module (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none space-y-4">
                
                <!-- Camera Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Swafoto Wajah Tamu</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Bukti foto verifikasi kehadiran langsung di lokasi</p>
                    </div>

                    <span 
                        class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border shrink-0"
                        :class="capturedImage ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border-orange-200'"
                    >
                        <span x-text="capturedImage ? 'FOTO SIAP' : 'LIVE KAMERA'"></span>
                    </span>
                </div>

                <!-- Camera Viewport Box -->
                <div class="relative bg-black rounded-2xl overflow-hidden shadow-2xl border border-slate-800 aspect-[4/3] flex items-center justify-center">

                    <!-- Live Video Element -->
                    <video
                        x-show="!capturedImage"
                        x-ref="video"
                        autoplay
                        playsinline
                        muted
                        class="w-full h-full object-cover"
                        :class="facingMode === 'user' ? 'scale-x-[-1]' : ''"
                    ></video>

                    <!-- Hidden Canvas for Snapshot -->
                    <canvas x-ref="canvas" class="hidden"></canvas>

                    <!-- Snapshot Frozen Image Preview -->
                    <img 
                        x-show="capturedImage" 
                        :src="capturedImage" 
                        alt="Hasil Foto Tamu" 
                        class="w-full h-full object-cover"
                    />

                    <!-- Camera Error State -->
                    <div x-show="cameraError && !capturedImage" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/90 text-white p-6 text-center space-y-3 z-20">
                        <svg class="w-12 h-12 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <p class="text-xs font-bold text-rose-200" x-text="cameraError"></p>
                        <button 
                            type="button" 
                            @click="startCamera()" 
                            class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-extrabold cursor-pointer transition shadow"
                        >
                            Coba Akses Lagi
                        </button>
                    </div>

                    <!-- Viewfinder Overlay (Only when Live) -->
                    <div x-show="!capturedImage && !cameraError" class="absolute inset-0 pointer-events-none">
                        <!-- Corner brackets -->
                        <div class="absolute top-4 left-4 w-10 h-10 border-t-3 border-l-3 border-orange-500/80 rounded-tl-xl"></div>
                        <div class="absolute top-4 right-4 w-10 h-10 border-t-3 border-r-3 border-orange-500/80 rounded-tr-xl"></div>
                        <div class="absolute bottom-20 left-4 w-10 h-10 border-b-3 border-l-3 border-orange-500/80 rounded-bl-xl"></div>
                        <div class="absolute bottom-20 right-4 w-10 h-10 border-b-3 border-r-3 border-orange-500/80 rounded-br-xl"></div>

                        <!-- Center Face Guide Text -->
                        <div class="absolute top-6 left-0 right-0 text-center">
                            <span class="px-3 py-1 rounded-full bg-black/60 backdrop-blur text-white text-[10px] font-bold border border-white/20">
                                Posisikan wajah Anda di tengah bingkai
                            </span>
                        </div>
                    </div>

                    <!-- Bottom Camera Controls -->
                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex items-center justify-center gap-6 z-10">
                        <!-- If in Live Mode -->
                        <template x-if="!capturedImage">
                            <div class="flex items-center gap-6">
                                <!-- Flip Camera Button -->
                                <button
                                    @click="flipCamera()"
                                    type="button"
                                    class="w-10 h-10 rounded-full bg-white/20 backdrop-blur border border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition active:scale-90 cursor-pointer"
                                    title="Balik Kamera Depan / Belakang"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </button>

                                <!-- Shutter Button -->
                                <button
                                    @click="capture()"
                                    type="button"
                                    class="w-16 h-16 rounded-full bg-white border-4 border-white/60 shadow-2xl flex items-center justify-center hover:scale-105 active:scale-95 transition-transform duration-150 cursor-pointer group"
                                    title="Ambil Foto Tamu"
                                >
                                    <div class="w-12 h-12 rounded-full bg-orange-600 group-hover:bg-orange-500 transition shadow-inner"></div>
                                </button>

                                <div class="w-10 h-10"></div>
                            </div>
                        </template>

                        <!-- If Photo Already Captured -->
                        <template x-if="capturedImage">
                            <button
                                @click="retake()"
                                type="button"
                                class="px-5 py-2.5 rounded-2xl bg-white/20 hover:bg-white/30 backdrop-blur border border-white/30 text-white font-extrabold text-xs flex items-center gap-2 cursor-pointer transition active:scale-95"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span>Foto Ulang (Retake)</span>
                            </button>
                        </template>
                    </div>

                </div>

                <!-- Guidance Info Box -->
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 flex items-start gap-3">
                    <div class="w-7 h-7 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-xs space-y-0.5">
                        <p class="font-extrabold text-slate-800 dark:text-slate-200">Panduan Pengambilan Foto</p>
                        <p class="text-slate-500 dark:text-slate-400 leading-relaxed">
                            Pastikan wajah Anda menghadap ke kamera dengan pencahayaan yang cukup. Foto webcam digunakan sebagai dokumen resmi buku tamu.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Side: Guest Log Form (7 Cols) -->
        <div class="lg:col-span-7">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none space-y-6">
                
                <!-- Form Header -->
                <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">Formulir Kunjungan Tamu</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-1">
                        Silakan isi identitas dan keperluan kunjungan Anda di bawah ini secara lengkap.
                    </p>
                </div>

                <form @submit.prevent="submitGuestLog()" class="space-y-4 sm:space-y-5">
                    
                    <!-- Row 1: Phone (with Autofill) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="phone" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Nomor WhatsApp / HP <span class="text-rose-500">*</span>
                            </label>
                            
                            <!-- Autofill Badge -->
                            <div class="flex items-center gap-1.5">
                                <span x-show="lookingUp" class="text-[11px] text-orange-600 font-bold flex items-center gap-1">
                                    <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Mengecek...
                                </span>
                                <span x-show="isReturningGuest && !lookingUp" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    Tamu Terdaftar (Auto-fill)
                                </span>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </div>
                            <input 
                                type="tel" 
                                id="phone" 
                                x-model="formData.phone"
                                @input.debounce.500ms="lookupPhone()"
                                required
                                placeholder="Contoh: 081234567890" 
                                class="w-full pl-10 pr-4 min-h-[48px] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 font-mono transition-all"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Name & Institution -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <input 
                                    type="text" 
                                    id="name" 
                                    x-model="formData.name"
                                    required
                                    placeholder="Masukkan nama lengkap Anda" 
                                    class="w-full pl-10 pr-4 min-h-[48px] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Institution / Address -->
                        <div>
                            <label for="institution" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Asal Instansi / Alamat <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <input 
                                    type="text" 
                                    id="institution" 
                                    x-model="formData.institution"
                                    required
                                    placeholder="Contoh: Universitas / PT / Alamat" 
                                    class="w-full pl-10 pr-4 min-h-[48px] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Division & Person to Meet -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Division Dropdown -->
                        <div>
                            <label for="division_id" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Tujuan Bagian / Bidang <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <select 
                                    id="division_id" 
                                    x-model="formData.division_id"
                                    required
                                    class="w-full pl-10 pr-8 min-h-[48px] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all cursor-pointer appearance-none"
                                >
                                    <option value="" disabled selected>-- Pilih Bagian / Bidang --</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Person to Meet -->
                        <div>
                            <label for="person_to_meet" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Bertemu Dengan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <input 
                                    type="text" 
                                    id="person_to_meet" 
                                    x-model="formData.person_to_meet"
                                    placeholder="Nama pejabat atau staf yang dituju" 
                                    class="w-full pl-10 pr-4 min-h-[48px] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Purpose of Visit -->
                    <div>
                        <label for="purpose" class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Keperluan Kunjungan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <textarea 
                                id="purpose" 
                                x-model="formData.purpose"
                                rows="3"
                                required
                                placeholder="Jelaskan maksud dan tujuan keperluan kunjungan Anda secara rinci..." 
                                class="w-full p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 transition-all resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            :disabled="submitting"
                            class="w-full min-h-[52px] bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white font-black text-sm uppercase tracking-wider rounded-2xl shadow-lg shadow-orange-500/25 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <template x-if="!submitting">
                                <span class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Simpan Buku Tamu</span>
                                </span>
                            </template>
                            <template x-if="submitting">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Menyimpan Data Kunjungan...</span>
                                </span>
                            </template>
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="text-center text-xs text-slate-400 dark:text-slate-500 py-2">
        <p>© {{ date('Y') }} Sistem Informasi Buku Tamu Digital • Ekraf Majalengka</p>
    </footer>

</div>
@endsection
