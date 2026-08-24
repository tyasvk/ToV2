<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    announcement: String,
    balance: {
        type: Number,
        default: 0
    },
    stats: Object,
    activeExam: { 
        type: Object,
        default: null
    },
    total_user_display: {
        type: [Number, String],
        default: 0
    }
});

const page = usePage();

// --- SAFE USER ACCESS ---
const user = computed(() => page.props.auth?.user || {});

// --- LOGIC FOTO PROFIL ---
const userAvatar = computed(() => {
    const u = user.value;
    if (u.profile_photo_url && !u.profile_photo_url.includes('ui-avatars.com')) return u.profile_photo_url;
    const rawPath = u.profile_photo_path || u.avatar;
    if (rawPath) return `/storage/${rawPath.replace(/^\//, '')}`;
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=2563EB&background=EFF6FF`;
});

// --- PENGUMUMAN LINK FORMATTER ---
const formattedAnnouncement = computed(() => {
    if (!props.announcement) return '';
    const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return props.announcement.replace(urlPattern, (url) => 
        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline transition-colors break-all font-medium">${url}</a>`
    );
});

// --- FORMAT FORMATTER ---
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);

const formatDate = (dateString) => {
    if (!dateString) return 'Memuat...'; 
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Format salah';
        return date.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch (e) {
        return '-';
    }
};

// --- LOGIKA TIMER BERGERAK UNTUK DASHBOARD ---
const activeTimeLeft = ref(props.activeExam?.time_left_seconds || 0);
let countdownTimer = null;

const formattedActiveTimeLeft = computed(() => {
    const safeSeconds = Math.max(0, Math.floor(activeTimeLeft.value));
    const h = Math.floor(safeSeconds / 3600);
    const m = Math.floor((safeSeconds % 3600) / 60);
    const s = safeSeconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
});

onMounted(() => {
    if (props.activeExam && activeTimeLeft.value > 0) {
        countdownTimer = setInterval(() => {
            if (activeTimeLeft.value > 0) {
                activeTimeLeft.value--;
            } else {
                clearInterval(countdownTimer);
                router.reload({ only: ['activeExam', 'stats'] }); 
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});
</script>

<template>
    <Head title="Dashboard - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-500 pt-4 md:pt-6">

            <!-- Gap diperkecil menjadi 4 (16px) agar lebih compact -->
            <div class="px-4 flex flex-col gap-4 md:gap-5">
                
                <!-- CARD 1: PROFILE IDENTITAS & SALDO AKTIF -->
                <div class="order-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative p-4 md:p-5">
                    <div class="absolute top-0 right-0 w-[200px] h-[200px] bg-gradient-to-bl from-blue-100/50 to-transparent rounded-full blur-[40px] pointer-events-none -mr-16 -mt-16"></div>
                    
                    <!-- Bagian Atas: Profil User -->
                    <div class="flex items-center gap-4 relative z-10">
                        <div class="relative shrink-0">
                            <!-- Ukuran avatar diperkecil dari w-16 ke w-14 di mobile -->
                            <div class="relative w-14 h-14 md:w-16 md:h-16 bg-white rounded-xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                <img :src="userAvatar" :alt="user.name" class="w-full h-full object-cover" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 bg-emerald-500 p-1 md:p-1 rounded-full shadow-sm border-2 border-white" title="Akun Aktif">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-1 w-full flex-1">
                            <h2 class="text-base md:text-lg text-slate-800 tracking-tight font-extrabold line-clamp-1">
                                Halo, <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">{{ user.name }}</span> 👋
                            </h2>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                <span class="inline-flex items-center justify-center px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 text-[9px] md:text-[10px] font-mono font-bold rounded-md w-max">
                                    #{{ user.participant_number || 'PENDING' }}
                                </span>
                                <span class="text-[9px] md:text-[10px] font-semibold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Bergabung: {{ formatDate(user.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pemisah -->
                    <hr class="border-slate-100 my-4 relative z-10" />

                    <!-- Bagian Bawah: Saldo Aktif -->
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl p-3 md:p-4 shadow-md text-white flex items-center justify-between gap-3 relative z-10 overflow-hidden group">
                        <div class="absolute -left-6 -bottom-6 w-20 h-20 bg-blue-500 opacity-20 rounded-full blur-xl"></div>

                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center backdrop-blur-sm text-lg border border-white/10 shrink-0">
                                💎
                            </div>
                            <div class="min-w-0">
                                <p class="text-[8px] md:text-[9px] font-bold text-indigo-200 uppercase tracking-widest mb-0.5 opacity-90">Saldo Dompet</p>
                                <!-- Teks saldo diperkecil -->
                                <p class="text-xl md:text-2xl font-extrabold text-white tracking-tight truncate">{{ formatCurrency(balance) }}</p>
                            </div>
                        </div>

                        <Link :href="route('wallet.index')" class="relative z-10 shrink-0 flex items-center gap-1 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-400 hover:to-indigo-400 transition-all px-3 py-2 md:px-4 md:py-2.5 rounded-lg text-[9px] md:text-[10px] font-bold uppercase tracking-widest shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Top Up Saldo</span>
                            <span class="sm:hidden">Isi</span>
                        </Link>
                    </div>
                </div>

                <!-- CARD 2: KUMPULAN WIDGET STATISTIK (Lebih Compact) -->
                <div class="order-2 grid grid-cols-3 gap-2.5 md:gap-4">
                    <!-- Total User -->
                    <div class="bg-white p-3 md:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:-translate-y-0.5 hover:shadow-md transition-all">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-indigo-50 text-indigo-500 rounded-lg flex items-center justify-center text-sm md:text-lg mb-0.5 border border-indigo-100">👥</div>
                        <p class="text-lg md:text-xl font-extrabold text-slate-800 leading-none truncate w-full">{{ total_user_display }}</p>
                        <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate w-full">Total User</p>
                    </div>

                    <!-- Total Ujian -->
                    <div class="bg-white p-3 md:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:-translate-y-0.5 hover:shadow-md transition-all">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-emerald-50 text-emerald-500 rounded-lg flex items-center justify-center text-sm md:text-lg mb-0.5 border border-emerald-100">📝</div>
                        <p class="text-lg md:text-xl font-extrabold text-slate-800 leading-none truncate w-full">{{ stats?.completed_count || 0 }}</p>
                        <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate w-full">Total Ujian</p>
                    </div>

                    <!-- Rata-rata Skor -->
                    <div class="bg-white p-3 md:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:-translate-y-0.5 hover:shadow-md transition-all relative overflow-hidden group">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-amber-50 text-amber-500 rounded-lg flex items-center justify-center text-sm md:text-lg mb-0.5 border border-amber-100 relative z-10">🎯</div>
                        <p class="text-lg md:text-xl font-extrabold text-slate-800 leading-none truncate w-full relative z-10">{{ stats?.average_score || 0 }}</p>
                        <p class="text-[8px] md:text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate w-full relative z-10">Rata-Rata</p>
                        
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 opacity-5 pointer-events-none">
                            <svg class="w-full h-full transform -rotate-90"><circle cx="50%" cy="50%" r="40%" stroke="currentColor" stroke-width="15%" fill="transparent" stroke-dasharray="100" stroke-dashoffset="0" class="text-amber-900" /></svg>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: MENU SKD & SKB CPNS -->
                <div class="order-3 grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                    <Link :href="route('tryout.index')" class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl p-4 md:p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all group flex items-center justify-between overflow-hidden active:scale-[0.98] relative">
                        <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-full blur-xl transform translate-x-5 -translate-y-5"></div>
                        
                        <div class="flex items-center gap-3 md:gap-4 relative z-10">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center text-xl md:text-2xl border border-white/20">
                                <span class="group-hover:scale-110 transition-transform duration-300">📚</span>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm md:text-base font-bold text-white tracking-tight">Tryout SKD CPNS</h3>
                                <p class="text-[9px] md:text-[10px] text-blue-100 font-medium mt-0.5">Akses katalog simulasi SKD</p>
                            </div>
                        </div>
                        <div class="text-white/60 group-hover:text-white transition-colors transform group-hover:translate-x-1 relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </Link>

                    <div class="bg-white border border-slate-200 rounded-xl p-4 md:p-5 shadow-sm opacity-80 cursor-not-allowed flex items-center justify-between">
                        <div class="flex items-center gap-3 md:gap-4">
                            <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center text-xl md:text-2xl grayscale border border-slate-200">
                                💼
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm md:text-base font-bold text-slate-700 tracking-tight">Tryout SKB CPNS</h3>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-slate-200 text-slate-500 text-[8px] font-bold uppercase tracking-widest rounded">Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: UJIAN AKTIF -->
                <div v-if="activeExam && activeTimeLeft > 0" class="order-4 bg-white border-l-4 border-orange-500 p-4 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-24 h-full bg-orange-50 opacity-50 skew-x-12 translate-x-5 pointer-events-none"></div>
                    
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg text-orange-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-0.5 overflow-hidden">
                            <span class="text-[9px] font-bold text-orange-600 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-ping"></span>
                                Ujian Berjalan
                            </span>
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1 tracking-tight">{{ activeExam.title }}</h4>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0 shrink-0 relative z-10">
                        <div class="font-mono text-xs md:text-sm font-bold text-slate-800 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 flex flex-col items-center">
                            <span class="text-[8px] text-slate-400 font-sans uppercase tracking-widest font-bold leading-none mb-1">Sisa Waktu</span>
                            <span class="tabular-nums" :class="{ 'text-red-500 animate-pulse': activeTimeLeft <= 300 }">⏱️ {{ formattedActiveTimeLeft }}</span>
                        </div>
                        <Link :href="route('tryout.exam', activeExam.id)" class="px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-[9px] md:text-[10px] font-extrabold uppercase tracking-widest rounded-lg shadow-sm transition-colors active:scale-95">Lanjutkan</Link>
                    </div>
                </div>

                <!-- CARD 5: PENGUMUMAN -->
                <div v-if="announcement" class="order-5 bg-sky-50/70 border border-sky-100 rounded-xl p-4 shadow-sm flex items-start gap-3">
                    <div class="shrink-0 text-sky-600 bg-white p-2 rounded-lg shadow-sm border border-sky-100">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0 mt-0.5">
                        <h3 class="text-[9px] text-sky-700 font-extrabold uppercase tracking-widest">Informasi Pusat</h3>
                        <p class="text-[11px] md:text-xs text-slate-700 font-medium leading-relaxed mt-1" v-html="formattedAnnouncement"></p>
                    </div>
                </div>

                <!-- CARD 6: BUNDLING PROMO -->
                <div class="order-6 bg-slate-900 rounded-xl p-5 shadow-sm relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="absolute -right-10 -top-10 w-24 h-24 bg-indigo-500 opacity-40 rounded-full blur-[40px] pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-24 h-24 bg-fuchsia-500 opacity-30 rounded-full blur-[40px] pointer-events-none"></div>
                    
                    <div class="relative z-10 text-center sm:text-left w-full sm:w-auto flex-1">
                        <h3 class="text-white font-extrabold text-sm md:text-base flex items-center justify-center sm:justify-start gap-2 tracking-tight">
                            <span class="text-lg drop-shadow-sm">📦</span> Paket Bundling Tryout
                        </h3>
                        <p class="text-slate-300 text-[10px] md:text-[11px] mt-1.5 leading-relaxed font-medium">
                            Beli minimal 3 tryout arsip sekaligus untuk memaksimalkan latihanmu dengan harga yang lebih hemat.
                        </p>
                    </div>
                    <div class="relative z-10 w-full sm:w-auto shrink-0">
                        <Link :href="route('user.bundling.index')" class="flex items-center justify-center w-full sm:w-auto bg-white text-slate-900 px-4 py-2.5 rounded-lg font-extrabold text-[9px] md:text-[10px] uppercase tracking-widest hover:bg-indigo-50 hover:text-indigo-600 transition-colors shadow-sm active:scale-95">
                            Lihat Bundling
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.5s;
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-in-from-bottom-4 {
    animation-name: slideInFromBottom;
}

@keyframes slideInFromBottom {
    0% {
        opacity: 0;
        transform: translateY(1rem);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulseSlow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.85; }
}
.animate-pulse-slow {
    animation: pulseSlow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>