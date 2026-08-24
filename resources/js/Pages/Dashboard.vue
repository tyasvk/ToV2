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

// --- LOGIKA HIDE BOTTOM NAV MOBILE SAAT SCROLL ---
const isScrolled = ref(false);
let lastScrollPosition = 0;

const handleScroll = () => {
    const currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScrollPosition > lastScrollPosition && currentScrollPosition > 50) {
        isScrolled.value = true;
    } else {
        isScrolled.value = false;
    }
    lastScrollPosition = currentScrollPosition;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
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
    window.removeEventListener('scroll', handleScroll);
    if (countdownTimer) clearInterval(countdownTimer);
});
</script>

<template>
    <Head title="Dashboard - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Container Utama -->
        <div class="max-w-5xl mx-auto animate-in fade-in slide-in-from-top-4 duration-700 pb-24 md:pb-10 pt-4 md:pt-6">

            <div class="px-4 flex flex-col gap-6 md:gap-5">
                
                <!-- CARD 1: PROFILE IDENTITAS -->
                <div class="order-1 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-[200px] h-[200px] bg-blue-50/50 rounded-full blur-[50px] pointer-events-none -mr-16 -mt-16"></div>
                    <div class="p-6 md:p-5 flex flex-col md:flex-row items-center gap-5 relative z-10 text-center md:text-left">
                        
                        <div class="relative shrink-0">
                            <div class="relative w-20 h-20 bg-white rounded-2xl overflow-hidden border-2 border-slate-100 shadow-sm">
                                <img :src="userAvatar" :alt="user.name" class="w-full h-full object-cover" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 bg-emerald-500 p-1 rounded-full shadow-sm border-2 border-white" title="Akun Aktif">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-1.5 w-full md:flex-1">
                            <h2 class="text-xl md:text-lg text-slate-800 tracking-tight font-bold">
                                Halo, <span class="text-blue-600">{{ user.name }}</span> 👋
                            </h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 items-center mt-1">
                                <p class="text-[11px] font-mono font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-md">
                                    #{{ user.participant_number || 'PENDING' }}
                                </p>
                                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                    Bergabung: {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: KUMPULAN WIDGET (Saldo, Total User, Total Ujian, Rata-rata) -->
                <div class="order-2 grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-4">
                    
                    <!-- Widget 1: Saldo Aktif & Top Up -->
                    <div class="col-span-2 md:col-span-1 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 shadow-sm text-white flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute -right-8 -top-8 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl pointer-events-none transition-transform group-hover:scale-110 duration-500"></div>
                        
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center backdrop-blur-sm text-sm border border-white/20">💳</div>
                                <h3 class="text-[10px] font-semibold text-blue-100 uppercase tracking-widest">Saldo Aktif</h3>
                            </div>
                            <p class="text-2xl lg:text-3xl font-bold tracking-tight truncate">{{ formatCurrency(balance) }}</p>
                        </div>
                        
                        <!-- Tombol Top Up -->
                        <Link :href="route('wallet.index')" class="mt-5 w-full flex items-center justify-center gap-2 bg-white/20 hover:bg-white/30 border border-white/20 transition-colors py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider backdrop-blur-md shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Top Up
                        </Link>
                    </div>

                    <!-- Widget 2: Total User -->
                    <div class="col-span-2 md:col-span-1 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center gap-2 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-lg mb-1 border border-indigo-100">👥</div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total User</p>
                            <p class="text-2xl lg:text-3xl font-bold text-slate-800 leading-none mt-1 truncate">{{ total_user_display }}</p>
                        </div>
                    </div>

                    <!-- Widget 3: Total Ujian -->
                    <div class="col-span-1 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center gap-2 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-lg mb-1 border border-emerald-100">📝</div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Ujian</p>
                            <p class="text-2xl lg:text-3xl font-bold text-slate-800 leading-none mt-1 truncate">{{ stats?.completed_count || 0 }}</p>
                        </div>
                    </div>

                    <!-- Widget 4: Rata-rata Skor -->
                    <div class="col-span-1 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center gap-2 hover:shadow-md transition-shadow">
                        <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-lg mb-1 border border-amber-100">🎯</div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-Rata</p>
                            <p class="text-2xl lg:text-3xl font-bold text-slate-800 leading-none mt-1 truncate">{{ stats?.average_score || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: MENU SKD & SKB CPNS -->
                <div class="order-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- SKD CPNS -->
                    <Link :href="route('tryout.index')" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-blue-400 transition-all group flex items-center justify-between overflow-hidden active:scale-[0.98]">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-blue-50 group-hover:bg-blue-600 transition-colors rounded-xl flex items-center justify-center text-2xl border border-blue-100">
                                <span class="group-hover:scale-110 transition-transform duration-300">📚</span>
                            </div>
                            <div class="text-left">
                                <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition-colors tracking-tight">Tryout SKD CPNS</h3>
                                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Akses katalog simulasi SKD</p>
                            </div>
                        </div>
                        <div class="text-slate-300 group-hover:text-blue-500 transition-colors transform group-hover:translate-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </Link>

                    <!-- SKB CPNS -->
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 shadow-sm opacity-80 cursor-not-allowed flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-slate-200 rounded-xl flex items-center justify-center text-2xl grayscale border border-slate-300">
                                💼
                            </div>
                            <div class="text-left">
                                <h3 class="text-base font-bold text-slate-700 tracking-tight">Tryout SKB CPNS</h3>
                                <span class="inline-block mt-1 px-2 py-0.5 bg-slate-200 text-slate-500 text-[9px] font-bold uppercase tracking-wider rounded">Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: UJIAN AKTIF (BERJALAN) -->
                <div v-if="activeExam && activeTimeLeft > 0" class="order-4 bg-orange-50/50 border border-orange-200 p-4 md:p-5 rounded-2xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-orange-100 rounded-xl text-orange-600 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-1 overflow-hidden">
                            <span class="text-[10px] font-bold text-orange-600 uppercase tracking-widest block flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                Ujian Sedang Berjalan
                            </span>
                            <h4 class="text-sm md:text-base font-bold text-slate-800 line-clamp-1 tracking-tight">
                                {{ activeExam.title }}
                            </h4>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 border-orange-200/60 pt-4 sm:pt-0 shrink-0">
                        <div class="font-mono text-sm font-bold text-slate-700 bg-white px-3 py-1.5 rounded-lg border border-orange-100 shadow-sm flex flex-col items-center">
                            <span class="text-[9px] text-slate-400 font-sans uppercase tracking-widest font-semibold leading-none mb-1">Sisa Waktu</span>
                            <span class="tabular-nums" :class="{ 'text-red-600 animate-pulse': activeTimeLeft <= 300 }">⏱️ {{ formattedActiveTimeLeft }}</span>
                        </div>
                        <Link :href="route('tryout.exam', activeExam.id)" class="px-5 py-3 bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition active:scale-95">
                            Lanjutkan
                        </Link>
                    </div>
                </div>

                <!-- CARD 5: PENGUMUMAN -->
                <div v-if="announcement" class="order-5 bg-sky-50/50 border border-sky-200 rounded-2xl p-4 md:p-5 shadow-sm flex items-start gap-4">
                    <div class="shrink-0 text-sky-600 bg-sky-100 p-2.5 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[10px] text-sky-800 font-bold uppercase tracking-widest">Informasi Pusat</h3>
                        <p class="text-xs text-sky-900 font-medium leading-relaxed mt-1" v-html="formattedAnnouncement"></p>
                    </div>
                </div>

                <!-- CARD 6: BUNDLING PROMO -->
                <div class="order-6 bg-slate-900 rounded-2xl p-5 md:p-6 shadow-md relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-5">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500 opacity-30 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-purple-500 opacity-30 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 text-center sm:text-left w-full sm:w-auto flex-1">
                        <h3 class="text-white font-bold text-lg flex items-center justify-center sm:justify-start gap-2 tracking-tight">
                            📦 Paket Bundling Tryout
                        </h3>
                        <p class="text-slate-300 text-xs mt-1.5 leading-relaxed font-medium">
                            Ketinggalan tryout? Beli minimal 3 tryout arsip sekaligus untuk memaksimalkan latihanmu dengan harga yang lebih hemat.
                        </p>
                    </div>
                    
                    <div class="relative z-10 w-full sm:w-auto shrink-0">
                        <Link :href="route('user.bundling.index')" class="flex items-center justify-center w-full sm:w-auto bg-indigo-500 text-white px-6 py-3 rounded-xl font-bold text-[11px] uppercase tracking-wider hover:bg-indigo-600 transition-colors shadow-sm active:scale-95">
                            Lihat Bundling
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- MOBILE BOTTOM TAB NAVIGATION (Hanya 3 Tab) -->
        <div 
            class="md:hidden fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] transition-transform duration-300 z-50 px-10 py-3 flex justify-between items-center"
            :class="{ 'translate-y-full': isScrolled }"
        >
            <!-- 1. Home -->
            <Link :href="route('dashboard')" class="flex flex-col items-center gap-1 transition-colors" :class="{ 'text-blue-600': $page.url === '/dashboard', 'text-slate-400 hover:text-blue-600': $page.url !== '/dashboard' }">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[10px] font-bold">Home</span>
            </Link>
            
            <!-- 2. Tryout -->
            <Link :href="route('tryout.index')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <span class="text-[10px] font-bold">Tryout</span>
            </Link>
            
            <!-- 3. Dompet -->
            <Link :href="route('wallet.index')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-blue-600 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <span class="text-[10px] font-bold">Dompet</span>
            </Link>
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