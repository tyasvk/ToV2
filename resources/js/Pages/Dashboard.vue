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
        return date.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':') + ' WIB';
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
        <!-- Konten Utama Dashboard -->
        <div class="max-w-5xl mx-auto animate-in fade-in slide-in-from-top-4 duration-700 pt-4 md:pt-6">

            <div class="px-4 flex flex-col gap-5">
                
                <!-- CARD 1: PROFILE IDENTITAS + SALDO AKTIF DI DALAMNYA -->
                <div class="order-1 bg-white rounded-2xl md:rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative p-5 md:p-6">
                    <!-- Efek cahaya Latar -->
                    <div class="absolute top-0 right-0 w-[200px] h-[200px] bg-blue-50/60 rounded-full blur-[60px] pointer-events-none -mr-16 -mt-16"></div>
                    
                    <!-- Bagian Atas: Profil -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="relative shrink-0">
                                <div class="relative w-16 h-16 md:w-20 md:h-20 bg-white rounded-2xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                    <img :src="userAvatar" :alt="user.name" class="w-full h-full object-cover" />
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-emerald-500 p-1 rounded-full shadow-sm border-2 border-white" title="Akun Aktif">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-3.5 md:w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>

                            <div class="space-y-1 w-full flex-1">
                                <h2 class="text-lg md:text-xl text-slate-800 tracking-tight font-bold line-clamp-1">
                                    Halo, <span class="text-blue-600">{{ user.name }}</span> 👋
                                </h2>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-0.5">
                                    <p class="text-[10px] md:text-[11px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-md w-max">
                                        #{{ user.participant_number || 'PENDING' }}
                                    </p>
                                    <p class="text-[10px] md:text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                        Bergabung: {{ formatDate(user.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Bawah: Saldo Aktif & Top Up (Clean & Modern Design) -->
                    <div class="mt-5 md:mt-6 bg-slate-50 border border-slate-100 rounded-2xl p-4 flex items-center justify-between gap-3 relative z-10">
                        <div class="flex items-center gap-3 md:gap-4">
                            <!-- Icon Wallet -->
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-blue-600 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <!-- Balance Text -->
                            <div class="min-w-0">
                                <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Saldo Aktif</p>
                                <p class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight truncate">{{ formatCurrency(balance) }}</p>
                            </div>
                        </div>

                        <!-- Button Top Up -->
                        <Link :href="route('wallet.index')" class="shrink-0 flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white transition-colors px-4 py-2.5 md:px-5 md:py-3 rounded-xl text-[10px] md:text-xs font-bold uppercase tracking-wider shadow-sm hover:shadow-md active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 md:h-4 md:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            <span class="hidden sm:inline">Top Up</span>
                            <span class="sm:hidden">Isi</span>
                        </Link>
                    </div>
                </div>

                <!-- CARD 2: KUMPULAN WIDGET PENDEK (Total User, Total Ujian, Rata-rata) -->
                <div class="order-2 grid grid-cols-3 gap-2.5 md:gap-4">
                    <!-- Total User -->
                    <div class="bg-white py-3 px-2 md:p-4 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:shadow-md transition-shadow">
                        <span class="text-xl md:text-2xl mb-0.5">👥</span>
                        <p class="text-base md:text-xl font-bold text-slate-800 leading-none truncate w-full">{{ total_user_display }}</p>
                        <p class="text-[8px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate w-full">Total User</p>
                    </div>

                    <!-- Total Ujian -->
                    <div class="bg-white py-3 px-2 md:p-4 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:shadow-md transition-shadow">
                        <span class="text-xl md:text-2xl mb-0.5">📝</span>
                        <p class="text-base md:text-xl font-bold text-slate-800 leading-none truncate w-full">{{ stats?.completed_count || 0 }}</p>
                        <p class="text-[8px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate w-full">Total Ujian</p>
                    </div>

                    <!-- Rata-rata Skor -->
                    <div class="bg-white py-3 px-2 md:p-4 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center text-center gap-1 hover:shadow-md transition-shadow relative overflow-hidden">
                        <span class="text-xl md:text-2xl mb-0.5 relative z-10">🎯</span>
                        <p class="text-base md:text-xl font-bold text-slate-800 leading-none truncate w-full relative z-10">{{ stats?.average_score || 0 }}</p>
                        <p class="text-[8px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 truncate w-full relative z-10">Rata-Rata</p>
                        
                        <!-- Circular progress pudar di latar belakang rata-rata -->
                        <div class="absolute -right-3 -bottom-3 w-16 h-16 opacity-5 pointer-events-none">
                            <svg class="w-full h-full transform -rotate-90"><circle cx="32" cy="32" r="24" stroke="currentColor" stroke-width="8" fill="transparent" stroke-dasharray="150.7" :stroke-dashoffset="150.7 - (Math.min(stats?.average_score || 0, 100) / 100) * 150.7" class="text-amber-600" /></svg>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: MENU SKD & SKB CPNS -->
                <div class="order-3 grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                    <Link :href="route('tryout.index')" class="bg-white border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm hover:shadow-md hover:border-blue-400 transition-all group flex items-center justify-between overflow-hidden active:scale-[0.98]">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-blue-50 group-hover:bg-blue-600 transition-colors rounded-xl flex items-center justify-center text-xl md:text-2xl border border-blue-100">
                                <span class="group-hover:scale-110 transition-transform duration-300">📚</span>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm md:text-base font-bold text-slate-800 group-hover:text-blue-600 transition-colors tracking-tight">Tryout SKD CPNS</h3>
                                <p class="text-[10px] md:text-[11px] text-slate-500 font-medium mt-0.5">Akses katalog simulasi SKD</p>
                            </div>
                        </div>
                        <div class="text-slate-300 group-hover:text-blue-500 transition-colors transform group-hover:translate-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </Link>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm opacity-80 cursor-not-allowed flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-200 rounded-xl flex items-center justify-center text-xl md:text-2xl grayscale border border-slate-300">
                                💼
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm md:text-base font-bold text-slate-700 tracking-tight">Tryout SKB CPNS</h3>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-slate-200 text-slate-500 text-[8px] md:text-[9px] font-bold uppercase tracking-wider rounded">Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: UJIAN AKTIF (BERJALAN) -->
                <div v-if="activeExam && activeTimeLeft > 0" class="order-4 bg-orange-50/50 border border-orange-200 p-4 md:p-5 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-orange-100 rounded-xl text-orange-600 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-1 overflow-hidden">
                            <span class="text-[9px] md:text-[10px] font-bold text-orange-600 uppercase tracking-widest block flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                Ujian Sedang Berjalan
                            </span>
                            <h4 class="text-sm md:text-base font-bold text-slate-800 line-clamp-1 tracking-tight">
                                {{ activeExam.title }}
                            </h4>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 border-t sm:border-t-0 border-orange-200/60 pt-3 sm:pt-0 shrink-0">
                        <div class="font-mono text-xs md:text-sm font-bold text-slate-700 bg-white px-3 py-1.5 rounded-lg border border-orange-100 shadow-sm flex flex-col items-center">
                            <span class="text-[8px] md:text-[9px] text-slate-400 font-sans uppercase tracking-widest font-semibold leading-none mb-1">Sisa Waktu</span>
                            <span class="tabular-nums" :class="{ 'text-red-600 animate-pulse': activeTimeLeft <= 300 }">⏱️ {{ formattedActiveTimeLeft }}</span>
                        </div>
                        <Link :href="route('tryout.exam', activeExam.id)" class="px-4 md:px-5 py-2.5 md:py-3 bg-orange-500 hover:bg-orange-600 text-white text-[10px] md:text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition active:scale-95">
                            Lanjutkan
                        </Link>
                    </div>
                </div>

                <!-- CARD 5: PENGUMUMAN -->
                <div v-if="announcement" class="order-5 bg-sky-50/50 border border-sky-200 rounded-2xl p-4 shadow-sm flex items-start gap-3 md:gap-4">
                    <div class="shrink-0 text-sky-600 bg-sky-100 p-2 md:p-2.5 rounded-xl">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0 mt-0.5 md:mt-0">
                        <h3 class="text-[9px] md:text-[10px] text-sky-800 font-bold uppercase tracking-widest">Informasi Pusat</h3>
                        <p class="text-[11px] md:text-xs text-sky-900 font-medium leading-relaxed mt-1" v-html="formattedAnnouncement"></p>
                    </div>
                </div>

                <!-- CARD 6: BUNDLING PROMO -->
                <div class="order-6 bg-slate-900 rounded-2xl p-5 md:p-6 shadow-md relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4 md:gap-5">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500 opacity-30 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-purple-500 opacity-30 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 text-center sm:text-left w-full sm:w-auto flex-1">
                        <h3 class="text-white font-bold text-base md:text-lg flex items-center justify-center sm:justify-start gap-2 tracking-tight">
                            📦 Paket Bundling Tryout
                        </h3>
                        <p class="text-slate-300 text-[11px] md:text-xs mt-1.5 leading-relaxed font-medium">
                            Ketinggalan tryout? Beli minimal 3 tryout arsip sekaligus untuk memaksimalkan latihanmu dengan harga yang lebih hemat.
                        </p>
                    </div>
                    
                    <div class="relative z-10 w-full sm:w-auto shrink-0 mt-2 sm:mt-0">
                        <Link :href="route('user.bundling.index')" class="flex items-center justify-center w-full sm:w-auto bg-indigo-500 text-white px-5 md:px-6 py-2.5 md:py-3 rounded-xl font-bold text-[10px] md:text-[11px] uppercase tracking-wider hover:bg-indigo-600 transition-colors shadow-sm active:scale-95">
                            Lihat Bundling
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.animate-in {
    animation-duration: 0.5s;
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes pulseSlow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.95; }
}
.animate-pulse-slow {
    animation: pulseSlow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>