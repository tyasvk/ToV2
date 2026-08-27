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
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=007AFF&background=E5F0FF`;
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
        <!-- Background Abu-abu Sistem iCloud (#F5F5F7) murni tanpa glow -->
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <!-- max-w-4xl untuk desktop, px-0 (full-width) untuk mobile -->
            <div class="max-w-4xl mx-auto sm:px-6 pt-4 sm:pt-8 space-y-4 sm:space-y-6">

                <!-- 1. GREETING PROFILE (Apple ID Card Style) -->
                <Link :href="route('profile.edit')" class="block bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 p-4 sm:p-5 shadow-sm sm:hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)] transition-all active:bg-[#F5F5F7] group">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                            <div class="relative shrink-0">
                                <img :src="userAvatar" :alt="user.name" class="w-14 h-14 sm:w-[64px] sm:h-[64px] rounded-full object-cover border border-black/5 shadow-sm" />
                            </div>
                            <div class="min-w-0">
                                <h1 class="text-[20px] sm:text-[24px] font-bold text-[#1D1D1F] tracking-tight leading-tight truncate">
                                    Halo, {{ user.name }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 bg-[#F5F5F7] text-[#1D1D1F] text-[11px] font-bold rounded-md tracking-widest">
                                        #{{ user.participant_number || 'PENDING' }}
                                    </span>
                                    <span class="text-[13px] text-[#86868B] font-medium hidden sm:inline">
                                        Bergabung {{ formatDate(user.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- Chevron Apple Settings -->
                        <div class="text-[#C7C7CC] group-hover:text-[#1D1D1F] transition-colors shrink-0 mr-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                </Link>

                <!-- 2. ALERTS (Ujian & Pengumuman) -->
                <div v-if="(activeExam && activeTimeLeft > 0) || announcement" class="space-y-4">
                    
                    <!-- Ujian Aktif -->
                    <div v-if="activeExam && activeTimeLeft > 0" class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-center gap-4 sm:gap-5">
                        <div class="w-full sm:flex-1 flex items-center gap-4">
                            <div class="w-12 h-12 bg-[#FFF9E6] text-[#FF9500] rounded-full flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold text-[#FF9500] uppercase tracking-wider mb-0.5">
                                    <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-pulse"></span> Ujian Berjalan
                                </span>
                                <h4 class="text-[15px] sm:text-[16px] font-bold text-[#1D1D1F] truncate">{{ activeExam.title }}</h4>
                            </div>
                        </div>
                        
                        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-5 border-t sm:border-t-0 border-black/5 pt-4 sm:pt-0">
                            <div class="flex flex-col items-start sm:items-end min-w-[80px]">
                                <span class="text-[11px] text-[#86868B] font-medium mb-0.5">Sisa Waktu</span>
                                <span class="text-[16px] font-bold font-mono text-[#1D1D1F] tabular-nums leading-none" :class="{'text-[#FF3B30] animate-pulse': activeTimeLeft <= 300}">
                                    {{ formattedActiveTimeLeft }}
                                </span>
                            </div>
                            <Link :href="route('tryout.exam', activeExam.id)" class="px-6 py-3 bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-full font-semibold text-[13px] transition-all active:scale-[0.98] shrink-0">
                                Lanjutkan
                            </Link>
                        </div>
                    </div>

                    <!-- Pengumuman Pusat -->
                    <div v-if="announcement" class="bg-[#F0F4FF] sm:rounded-[24px] border-y sm:border-x border-[#007AFF]/10 p-4 sm:p-5 flex items-start gap-4">
                        <div class="w-10 h-10 bg-white text-[#007AFF] rounded-full flex items-center justify-center shrink-0 shadow-sm">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 items-stretch">
                    
                    <!-- Wallet (Apple Card Dark Style) -->
                    <div class="bg-[#1D1D1F] sm:rounded-[24px] border-y sm:border-x border-black/5 p-6 flex flex-col justify-between shadow-sm relative overflow-hidden h-full min-h-[140px]">
                        <div class="relative z-10 mb-5">
                            <p class="text-[11px] font-medium text-[#86868B] mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#86868B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H4.5A2.25 2.25 0 002.25 12v6.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V12z" /></svg>
                                Saldo Dompet
                            </p>
                            <p class="text-[28px] sm:text-[32px] font-bold text-white tracking-tight leading-none truncate">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                        <Link :href="route('wallet.index')" class="relative z-10 w-max inline-flex px-5 py-2.5 bg-white text-[#1D1D1F] hover:bg-[#F5F5F7] font-semibold text-[12px] rounded-full transition-all active:scale-[0.98]">
                            Isi Saldo
                        </Link>
                    </div>

                    <!-- Unified Stats -->
                    <div class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 flex flex-col justify-center divide-y divide-black/5 h-full">
                        
                        <!-- Row 1: Total User & Total Ujian -->
                        <div class="flex flex-row items-center divide-x divide-black/5 flex-1">
                            <div class="flex-1 flex items-center gap-3 p-4">
                                <div class="w-9 h-9 bg-[#F0F4FF] text-[#007AFF] rounded-full flex items-center justify-center text-base shrink-0">👥</div>
                                <div class="min-w-0">
                                    <p class="text-[18px] font-bold text-[#1D1D1F] leading-none mb-0.5 truncate">{{ total_user_display }}</p>
                                    <p class="text-[11px] font-medium text-[#86868B] truncate">Total User</p>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center gap-3 p-4">
                                <div class="w-9 h-9 bg-[#E5F5EA] text-[#34C759] rounded-full flex items-center justify-center text-base shrink-0">📝</div>
                                <div class="min-w-0">
                                    <p class="text-[18px] font-bold text-[#1D1D1F] leading-none mb-0.5 truncate">{{ stats?.completed_count || 0 }}</p>
                                    <p class="text-[11px] font-medium text-[#86868B] truncate">Total Ujian</p>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Rata-rata Skor -->
                        <div class="flex items-center gap-3 p-4 flex-1">
                            <div class="w-9 h-9 bg-[#FFF9E6] text-[#FF9500] rounded-full flex items-center justify-center text-base shrink-0">🎯</div>
                            <div class="min-w-0">
                                <p class="text-[18px] font-bold text-[#1D1D1F] leading-none mb-0.5 truncate">{{ stats?.average_score || 0 }}</p>
                                <p class="text-[11px] font-medium text-[#86868B] truncate">Rata-Rata Skor Anda</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 4. MENUS GRUP 1: LATIHAN UJIAN -->
                <div>
                    <h2 class="text-[13px] font-medium text-[#86868B] uppercase tracking-wider mb-2 ml-4 sm:ml-2">Latihan Ujian</h2>
                    <div class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 overflow-hidden">
                        
                        <!-- Menu SKD -->
                        <Link :href="route('tryout.index')" class="flex items-center justify-between p-4 sm:p-5 active:bg-[#F5F5F7] sm:hover:bg-[#F5F5F7] transition-colors group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-[#F0F4FF] text-[#007AFF] rounded-[10px] flex items-center justify-center text-lg shrink-0">
                                    📚
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[16px] font-semibold text-[#1D1D1F] truncate">Tryout SKD CPNS</h3>
                                    <p class="text-[13px] text-[#86868B] font-medium mt-0.5 truncate">Akses katalog simulasi SKD terbaru</p>
                                </div>
                            </div>
                            <div class="text-[#C7C7CC] shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </Link>

                        <div class="h-px bg-black/5 ml-[68px]"></div>

                        <!-- Menu SKB (Disabled) -->
                        <div class="flex items-center justify-between p-4 sm:p-5 opacity-60 bg-white">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-[#F5F5F7] text-[#86868B] rounded-[10px] flex items-center justify-center text-lg shrink-0 grayscale">
                                    💼
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[16px] font-semibold text-[#1D1D1F] truncate">Tryout SKB CPNS</h3>
                                        <span class="inline-block px-1.5 py-0.5 bg-[#E3E3E8] text-[#86868B] text-[10px] font-bold uppercase tracking-wider rounded">Segera Hadir</span>
                                    </div>
                                    <p class="text-[13px] text-[#86868B] font-medium mt-0.5 truncate">Simulasi Bidang Teknis</p>
                                </div>
                            </div>
                            <div class="text-[#E3E3E8] shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 5. MENUS GRUP 2: ANALITIK & EVALUASI (TAMBAHAN BARU) -->
                <div>
                    <h2 class="text-[13px] font-medium text-[#86868B] uppercase tracking-wider mb-2 ml-4 sm:ml-2">Analitik & Evaluasi</h2>
                    <div class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 overflow-hidden">
                        
                        <!-- Menu Klasemen Peringkat -->
                        <Link :href="route('ranking.index')" class="flex items-center justify-between p-4 sm:p-5 active:bg-[#F5F5F7] sm:hover:bg-[#F5F5F7] transition-colors group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-[#FFF9E6] text-[#FF9500] rounded-[10px] flex items-center justify-center text-lg shrink-0">
                                    🏆
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[16px] font-semibold text-[#1D1D1F] truncate">Klasemen Peringkat</h3>
                                    <p class="text-[13px] text-[#86868B] font-medium mt-0.5 truncate">Bandingkan skor secara nasional & instansi</p>
                                </div>
                            </div>
                            <div class="text-[#C7C7CC] shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </Link>

                        <div class="h-px bg-black/5 ml-[68px]"></div>

                        <!-- Menu Grafik Perkembangan -->
                        <Link :href="route('tryout.progress')" class="flex items-center justify-between p-4 sm:p-5 active:bg-[#F5F5F7] sm:hover:bg-[#F5F5F7] transition-colors group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 bg-[#E5F5EA] text-[#34C759] rounded-[10px] flex items-center justify-center text-lg shrink-0">
                                    📈
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[16px] font-semibold text-[#1D1D1F] truncate">Grafik Perkembangan</h3>
                                    <p class="text-[13px] text-[#86868B] font-medium mt-0.5 truncate">Pantau tren nilai & evaluasi materi subtes</p>
                                </div>
                            </div>
                            <div class="text-[#C7C7CC] shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </Link>

                    </div>
                </div>

                <!-- 6. BUNDLING PROMO (App Store Style Card) -->
                <div class="w-full bg-[#007AFF] sm:rounded-[24px] overflow-hidden border-y sm:border-x border-black/5 cursor-pointer active:opacity-90 transition-opacity mt-2" @click="router.visit(route('user.bundling.index'))">
                    <div class="p-8 sm:p-10 flex flex-col md:flex-row items-center justify-between gap-6 md:gap-10">
                        <div class="text-center md:text-left text-white max-w-lg">
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-[11px] font-bold uppercase tracking-widest mb-3">
                                Penawaran Spesial
                            </span>
                            <h3 class="text-[22px] sm:text-[28px] font-bold tracking-tight mb-2 leading-tight">
                                Paket Bundling Tryout
                            </h3>
                            <p class="text-[14px] sm:text-[15px] font-medium text-white/90 leading-relaxed">
                                Beli minimal 3 tryout arsip sekaligus untuk memaksimalkan latihan Anda dengan harga yang jauh lebih hemat.
                            </p>
                        </div>
                        
                        <div class="shrink-0 w-full md:w-auto">
                            <div class="w-full md:w-auto px-6 py-3.5 bg-white text-[#007AFF] rounded-full font-bold text-[14px] text-center active:scale-[0.98] transition-transform">
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