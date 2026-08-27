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
        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-700 underline transition-colors break-all font-semibold">${url}</a>`
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
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 md:space-y-8">

                <!-- 1. GREETING PROFILE (Modern SaaS Card) -->
                <Link :href="route('profile.edit')" class="block bg-white border border-slate-200 rounded-[24px] p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                            <div class="relative shrink-0">
                                <img :src="userAvatar" :alt="user.name" class="w-14 h-14 sm:w-[64px] sm:h-[64px] rounded-full object-cover border border-slate-100 shadow-sm" />
                            </div>
                            <div class="min-w-0">
                                <h1 class="text-[20px] sm:text-[24px] font-bold text-slate-900 tracking-tight leading-tight truncate">
                                    Halo, {{ user.name }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-md tracking-wider">
                                        #{{ user.participant_number || 'PENDING' }}
                                    </span>
                                    <span class="text-[13px] text-slate-400 font-medium hidden sm:inline">
                                        Bergabung {{ formatDate(user.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 group-hover:bg-slate-100 group-hover:text-slate-900 flex items-center justify-center transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </div>
                    </div>
                </Link>

                <!-- 2. ALERTS (Ujian & Pengumuman) -->
                <div v-if="(activeExam && activeTimeLeft > 0) || announcement" class="space-y-4">
                    
                    <!-- Ujian Aktif -->
                    <div v-if="activeExam && activeTimeLeft > 0" class="bg-white border border-amber-200 rounded-[24px] p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row items-center gap-4 sm:gap-5 relative overflow-hidden">
                        <!-- Aksen Warna Latar -->
                        <div class="absolute inset-0 bg-amber-50/40 pointer-events-none"></div>

                        <div class="w-full sm:flex-1 flex items-center gap-4 relative z-10">
                            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold text-amber-600 uppercase tracking-wider mb-0.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Ujian Berjalan
                                </span>
                                <h4 class="text-[15px] sm:text-[16px] font-bold text-slate-900 truncate">{{ activeExam.title }}</h4>
                            </div>
                        </div>
                        
                        <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-5 border-t sm:border-t-0 border-slate-200/60 pt-4 sm:pt-0 relative z-10">
                            <div class="flex flex-col items-start sm:items-end min-w-[80px]">
                                <span class="text-[11px] text-slate-500 font-medium mb-0.5">Sisa Waktu</span>
                                <span class="text-[16px] font-bold font-mono text-slate-900 tabular-nums leading-none" :class="{'text-red-500 animate-pulse': activeTimeLeft <= 300}">
                                    {{ formattedActiveTimeLeft }}
                                </span>
                            </div>
                            <Link :href="route('tryout.exam', activeExam.id)" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-full font-semibold text-[13px] transition-all active:scale-[0.98] shadow-sm shrink-0">
                                Lanjutkan
                            </Link>
                        </div>
                    </div>

                    <!-- Pengumuman Pusat -->
                    <div v-if="announcement" class="bg-blue-50/80 border border-blue-100 rounded-[24px] p-4 sm:p-5 flex items-start gap-4">
                        <div class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center shrink-0 shadow-sm border border-blue-100/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                        </div>
                        <div class="pt-0.5">
                            <h3 class="text-[12px] font-bold text-blue-700 uppercase tracking-wider mb-1">Informasi Pusat</h3>
                            <p class="text-[14px] text-slate-800 font-medium leading-relaxed" v-html="formattedAnnouncement"></p>
                        </div>
                    </div>
                </div>

                <!-- 3. WALLET & UNIFIED STATS WIDGET (Perbaikan Ukuran Lebih Compact) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 items-stretch">
                    
                    <!-- Wallet (Compact Black Card) -->
                    <div class="bg-[#1D1D1F] rounded-[24px] p-5 sm:p-6 flex flex-col justify-between shadow-xl shadow-slate-300/30 relative overflow-hidden h-full min-h-[140px]">
                        
                        <div class="relative z-10 mb-5">
                            <p class="text-[11px] font-medium text-slate-400 mb-1 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H4.5A2.25 2.25 0 002.25 12v6.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V12z" /></svg>
                                Saldo Dompet
                            </p>
                            <p class="text-[28px] sm:text-[32px] font-bold text-white tracking-tight leading-none truncate">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                        <Link :href="route('wallet.index')" class="relative z-10 w-max inline-flex px-5 py-2.5 bg-white text-slate-900 hover:bg-slate-100 font-semibold text-[12px] rounded-full transition-all active:scale-[0.98]">
                            Isi Saldo
                        </Link>
                    </div>

                    <!-- Unified Stats (Compact Size) -->
                    <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 flex flex-col justify-center divide-y divide-slate-100 h-full">
                        
                        <!-- Row 1: Total User & Total Ujian -->
                        <div class="flex flex-row items-center divide-x divide-slate-100 flex-1">
                            <div class="flex-1 flex items-center gap-3 p-4">
                                <div class="w-9 h-9 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-base shrink-0">👥</div>
                                <div class="min-w-0">
                                    <p class="text-[18px] font-bold text-slate-900 leading-none mb-0.5 truncate">{{ total_user_display }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 truncate">Total User</p>
                                </div>
                            </div>
                            <div class="flex-1 flex items-center gap-3 p-4">
                                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-base shrink-0">📝</div>
                                <div class="min-w-0">
                                    <p class="text-[18px] font-bold text-slate-900 leading-none mb-0.5 truncate">{{ stats?.completed_count || 0 }}</p>
                                    <p class="text-[11px] font-medium text-slate-500 truncate">Total Ujian</p>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Rata-rata Skor -->
                        <div class="flex items-center gap-3 p-4 flex-1">
                            <div class="w-9 h-9 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center text-base shrink-0">🎯</div>
                            <div class="min-w-0">
                                <p class="text-[18px] font-bold text-slate-900 leading-none mb-0.5 truncate">{{ stats?.average_score || 0 }}</p>
                                <p class="text-[11px] font-medium text-slate-500 truncate">Rata-Rata Skor Anda</p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- 4. MENUS (Grouped List Style) -->
                <div>
                    <h2 class="text-[13px] font-bold text-slate-500 uppercase tracking-wider mb-2 ml-4">Latihan Ujian</h2>
                    <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 overflow-hidden">
                        
                        <!-- Menu SKD -->
                        <Link :href="route('tryout.index')" class="flex items-center justify-between p-4 sm:p-5 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-[14px] flex items-center justify-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                    📚
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[16px] font-bold text-slate-900 truncate">Tryout SKD CPNS</h3>
                                    <p class="text-[13px] text-slate-500 font-medium mt-0.5 truncate">Akses katalog simulasi SKD terbaru</p>
                                </div>
                            </div>
                            <div class="text-slate-300 group-hover:text-blue-600 transition-colors shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </Link>

                        <div class="h-px bg-slate-100 mx-4 sm:mx-5"></div>

                        <!-- Menu SKB (Disabled) -->
                        <div class="flex items-center justify-between p-4 sm:p-5 opacity-70 cursor-not-allowed bg-white">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-[14px] flex items-center justify-center text-xl shrink-0 grayscale">
                                    💼
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[16px] font-bold text-slate-900 truncate">Tryout SKB CPNS</h3>
                                        <span class="inline-block px-2 py-0.5 bg-slate-200 text-slate-600 text-[10px] font-bold uppercase tracking-wider rounded">Segera Hadir</span>
                                    </div>
                                    <p class="text-[13px] text-slate-500 font-medium mt-0.5 truncate">Simulasi Bidang Teknis</p>
                                </div>
                            </div>
                            <div class="text-slate-300 shrink-0 ml-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 5. BUNDLING PROMO -->
                <div class="relative w-full rounded-[24px] overflow-hidden shadow-md group cursor-pointer border border-slate-200" @click="router.visit(route('user.bundling.index'))">
                    <!-- Background Gradient Modern -->
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-600"></div>
                    
                    <div class="relative z-10 p-8 sm:p-10 flex flex-col md:flex-row items-center justify-between gap-6 md:gap-10">
                        <div class="text-center md:text-left text-white max-w-lg">
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-[11px] font-bold uppercase tracking-widest mb-3 backdrop-blur-sm">
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
                            <div class="w-full md:w-auto px-6 py-3.5 bg-white text-blue-600 rounded-full font-bold text-[14px] text-center shadow-sm transition-transform active:scale-[0.98]">
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