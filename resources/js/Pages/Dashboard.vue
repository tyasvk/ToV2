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
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=007AFF&background=F0F4FF`;
});

// --- PENGUMUMAN LINK FORMATTER ---
const formattedAnnouncement = computed(() => {
    if (!props.announcement) return '';
    const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return props.announcement.replace(urlPattern, (url) => 
        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-[#007AFF] hover:text-[#0062CC] underline transition-colors break-all font-semibold">${url}</a>`
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
        <!-- Background transparan, menyatu dengan layout abu-abu lembut bawaan sistem -->
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 md:space-y-8">

                <!-- 1. GREETING PROFILE (Clean Header Style) -->
                <div class="flex items-center gap-4 sm:gap-5 mb-2">
                    <div class="relative shrink-0">
                        <img :src="userAvatar" :alt="user.name" class="w-14 h-14 sm:w-[68px] sm:h-[68px] rounded-full object-cover shadow-[0_2px_8px_rgba(0,0,0,0.08)] border border-black/5" />
                    </div>
                    <div>
                        <h1 class="text-[24px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight line-clamp-1">
                            Halo, {{ user.name }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="font-mono text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-[6px] text-[11px] sm:text-[12px] font-bold tracking-wider">
                                #{{ user.participant_number || 'PENDING' }}
                            </span>
                            <span class="text-[13px] text-[#86868B] font-medium hidden sm:inline">•</span>
                            <span class="text-[12px] sm:text-[13px] text-[#86868B] font-medium">
                                Anggota sejak {{ formatDate(user.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. ALERTS (Ujian & Pengumuman) -->
                <div v-if="(activeExam && activeTimeLeft > 0) || announcement" class="space-y-4">
                    
                    <!-- Ujian Aktif (Live Activity Style) -->
                    <div v-if="activeExam && activeTimeLeft > 0" class="bg-white border-2 border-[#FF9500] rounded-[24px] p-1.5 shadow-[0_8px_20px_rgba(255,149,0,0.12)] flex flex-col sm:flex-row items-center gap-2 overflow-hidden">
                        <div class="w-full sm:flex-1 flex items-center gap-3 bg-[#FFF9E6] px-5 py-4 rounded-[20px]">
                            <div class="relative flex h-3 w-3 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FF9500] opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-[#FF9500]"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-[11px] font-bold text-[#FF9500] uppercase tracking-wider block mb-0.5">Ujian Berjalan</span>
                                <h4 class="text-[15px] font-bold text-[#1D1D1F] truncate">{{ activeExam.title }}</h4>
                            </div>
                        </div>
                        
                        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-3 px-3 sm:px-4 py-2 sm:py-0">
                            <div class="flex flex-col items-center sm:items-end min-w-[80px]">
                                <span class="text-[10px] text-[#86868B] font-bold uppercase tracking-wider mb-0.5">Sisa Waktu</span>
                                <span class="text-[16px] font-bold font-mono text-[#1D1D1F] tabular-nums leading-none" :class="{'text-[#FF3B30] animate-pulse': activeTimeLeft <= 300}">
                                    {{ formattedActiveTimeLeft }}
                                </span>
                            </div>
                            <Link :href="route('tryout.exam', activeExam.id)" class="px-6 py-3.5 bg-[#FF9500] hover:bg-[#E68600] text-white rounded-[16px] font-bold text-[14px] transition-all active:scale-[0.98] shadow-sm shrink-0">
                                Lanjutkan
                            </Link>
                        </div>
                    </div>

                    <!-- Pengumuman Pusat -->
                    <div v-if="announcement" class="bg-[#F0F4FF] border border-[#007AFF]/15 rounded-[24px] p-5 flex items-start gap-4">
                        <div class="w-10 h-10 bg-white text-[#007AFF] rounded-full flex items-center justify-center shrink-0 shadow-sm border border-black/5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div class="pt-0.5">
                            <h3 class="text-[12px] font-bold text-[#007AFF] uppercase tracking-wider mb-1">Informasi Pusat</h3>
                            <p class="text-[14px] text-[#1D1D1F] font-medium leading-relaxed" v-html="formattedAnnouncement"></p>
                        </div>
                    </div>
                </div>

                <!-- 3. WALLET & UNIFIED STATS WIDGET -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                    
                    <!-- Wallet (Apple Card Dark Style) -->
                    <div class="lg:col-span-5 bg-[#000000] rounded-[32px] p-8 flex flex-col justify-between shadow-[0_12px_40px_rgba(0,0,0,0.15)] relative overflow-hidden min-h-[220px]">
                        <div class="absolute right-[-20%] top-[-20%] w-[80%] h-[80%] bg-gradient-to-bl from-[#007AFF] to-[#AF52DE] rounded-full blur-[60px] opacity-40 pointer-events-none"></div>
                        <div class="absolute left-[-10%] bottom-[-10%] w-[60%] h-[60%] bg-[#34C759] rounded-full blur-[60px] opacity-20 pointer-events-none"></div>
                        
                        <div class="relative z-10 mb-6">
                            <p class="text-[12px] font-semibold text-[#86868B] uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H4.5A2.25 2.25 0 002.25 12v6.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V12z" /></svg>
                                Saldo Dompet
                            </p>
                            <p class="text-[36px] sm:text-[44px] font-bold text-white tracking-tight leading-none truncate">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                        <Link :href="route('wallet.index')" class="relative z-10 w-full text-center px-6 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-semibold text-[14px] rounded-full transition-all active:scale-[0.98] border border-white/10">
                            Isi Saldo
                        </Link>
                    </div>

                    <!-- Unified Stats (Single Card iOS Widget Style) -->
                    <div class="lg:col-span-7 bg-white rounded-[32px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 flex flex-row items-center divide-x divide-black/5 p-4 sm:p-6 h-full">
                        
                        <div class="flex-1 flex flex-col items-center justify-center p-2 text-center group">
                            <div class="w-12 h-12 bg-[#F0F4FF] text-[#007AFF] rounded-full flex items-center justify-center text-xl mb-3 transition-transform group-hover:scale-110">👥</div>
                            <p class="text-[22px] sm:text-[28px] font-bold text-[#1D1D1F] leading-none mb-1.5 truncate w-full">{{ total_user_display }}</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-[#86868B] uppercase tracking-wider">Total User</p>
                        </div>
                        
                        <div class="flex-1 flex flex-col items-center justify-center p-2 text-center group">
                            <div class="w-12 h-12 bg-[#E5F5EA] text-[#34C759] rounded-full flex items-center justify-center text-xl mb-3 transition-transform group-hover:scale-110">📝</div>
                            <p class="text-[22px] sm:text-[28px] font-bold text-[#1D1D1F] leading-none mb-1.5 truncate w-full">{{ stats?.completed_count || 0 }}</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-[#86868B] uppercase tracking-wider">Total Ujian</p>
                        </div>
                        
                        <div class="flex-1 flex flex-col items-center justify-center p-2 text-center group">
                            <div class="w-12 h-12 bg-[#FFF9E6] text-[#FF9500] rounded-full flex items-center justify-center text-xl mb-3 transition-transform group-hover:scale-110">🎯</div>
                            <p class="text-[22px] sm:text-[28px] font-bold text-[#1D1D1F] leading-none mb-1.5 truncate w-full">{{ stats?.average_score || 0 }}</p>
                            <p class="text-[10px] sm:text-[11px] font-bold text-[#86868B] uppercase tracking-wider">Rata-Rata</p>
                        </div>

                    </div>

                </div>

                <!-- 4. MENUS (Settings Style List) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    <!-- Menu SKD -->
                    <Link :href="route('tryout.index')" class="bg-white rounded-[28px] border border-black/5 p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-[0_12px_30px_rgba(0,0,0,0.06)] transition-all flex items-center justify-between group transform hover:-translate-y-1">
                        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                            <div class="w-14 h-14 bg-[#F0F4FF] text-[#007AFF] rounded-full flex items-center justify-center text-2xl shrink-0 group-hover:scale-105 transition-transform duration-300">
                                📚
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[16px] sm:text-[18px] font-bold text-[#1D1D1F] group-hover:text-[#007AFF] transition-colors truncate">Tryout SKD CPNS</h3>
                                <p class="text-[13px] text-[#86868B] font-medium mt-0.5 truncate">Akses katalog simulasi SKD terbaru</p>
                            </div>
                        </div>
                        <!-- Chevron Arrow bawaan Apple -->
                        <div class="text-[#C7C7CC] group-hover:text-[#007AFF] transition-colors shrink-0 ml-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </Link>

                    <!-- Menu SKB (Disabled) -->
                    <div class="bg-[#F5F5F7]/80 rounded-[28px] border border-black/5 p-5 sm:p-6 shadow-sm flex items-center justify-between opacity-80 cursor-not-allowed">
                        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                            <div class="w-14 h-14 bg-white text-[#86868B] rounded-full flex items-center justify-center text-2xl shrink-0 grayscale shadow-sm border border-black/5">
                                💼
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[16px] sm:text-[18px] font-bold text-[#1D1D1F] truncate">Tryout SKB CPNS</h3>
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-0.5 bg-[#E3E3E8] text-[#86868B] text-[10px] font-bold uppercase tracking-widest rounded-md">Segera Hadir</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-[#D1D1D6] shrink-0 ml-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>

                </div>

                <!-- 5. BUNDLING PROMO (App Store Editorial Card) -->
                <div class="relative w-full rounded-[32px] overflow-hidden shadow-[0_12px_40px_rgba(0,0,0,0.1)] group cursor-pointer border border-black/5" @click="router.visit(route('user.bundling.index'))">
                    <!-- Background gradient & Blur -->
                    <div class="absolute inset-0 bg-gradient-to-br from-[#007AFF] to-[#AF52DE] group-hover:scale-105 transition-transform duration-700 ease-out"></div>
                    <div class="absolute inset-0 bg-black/10"></div>
                    
                    <div class="relative z-10 p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6 md:gap-10">
                        <div class="text-center md:text-left text-white max-w-lg">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[11px] font-bold uppercase tracking-widest mb-3 border border-white/20">
                                Penawaran Spesial
                            </span>
                            <h3 class="text-[24px] sm:text-[32px] font-bold tracking-tight mb-2 leading-tight">
                                Paket Bundling Tryout
                            </h3>
                            <p class="text-[14px] sm:text-[15px] font-medium text-white/90 leading-relaxed">
                                Beli minimal 3 tryout arsip sekaligus untuk memaksimalkan latihan Anda dengan harga yang jauh lebih hemat.
                            </p>
                        </div>
                        
                        <div class="shrink-0 w-full md:w-auto">
                            <div class="w-full md:w-auto px-8 py-4 bg-white text-[#007AFF] rounded-full font-bold text-[15px] text-center shadow-lg transition-transform active:scale-[0.98]">
                                Lihat Bundel
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>