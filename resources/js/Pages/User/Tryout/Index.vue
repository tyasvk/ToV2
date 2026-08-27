<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    catalogTryouts: {
        type: Array,
        default: () => []
    },
    myTryouts: {
        type: Array,
        default: () => []
    },
    isPremiumMember: {
        type: Boolean,
        default: false
    }
});

const searchQuery = ref('');
const activeTab = ref('catalog'); 
const isClaiming = ref(null); // Menyimpan ID tryout yang sedang loading klaim

// --- 1. SINKRONISASI DATA KATALOG ---
const availableCatalogTryouts = computed(() => {
    const ownedTryoutIds = (props.myTryouts || []).map(t => t.id);
    return (props.catalogTryouts || []).filter(t => !ownedTryoutIds.includes(t.id));
});

// --- 2. FITUR PENCARIAN ---
const filteredTryouts = computed(() => {
    const baseData = activeTab.value === 'catalog' ? availableCatalogTryouts.value : (props.myTryouts || []);
    
    if (!searchQuery.value) return baseData;

    return baseData.filter(t => {
        return t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

// --- 3. HELPER & FORMATTER ---
const getAttemptCount = (tryout) => {
    if (tryout.attempts && Array.isArray(tryout.attempts)) {
        return tryout.attempts.length;
    }
    return Number(tryout.attempts_count) || 0;
};

const getStartDate = (tryout) => {
    const dateStr = tryout.start_time || tryout.start_date || tryout.started_at;
    if (!dateStr) return null;
    return dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr;
};

const isUpcoming = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return false;
    const startTimeMs = new Date(startStr).getTime();
    const nowMs = new Date().getTime();
    return startTimeMs > nowMs;
};

const formatTime = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return '-';
    const date = new Date(startStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(date).replace(/\./g, ':').replace(',', ' •') + ' WIB';
};

const formatOnlyDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    }).format(date);
};

// --- 4. AKSI KLAIM ---
const claimTryout = (tryoutId) => {
    isClaiming.value = tryoutId;
    router.post(route('tryout.claim', tryoutId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isClaiming.value = null;
            // Pindahkan tampilan ke tab Milik Saya setelah sukses klaim
            activeTab.value = 'my_tryouts';
        },
        onError: () => {
            isClaiming.value = null;
        }
    });
};
</script>

<template>
    <Head title="Katalog Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background menyatu dengan layout utama -->
        <div class="w-full pb-24 md:pb-12 animate-in fade-in duration-500">
            
            <div class="max-w-[1400px] mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4">
                
                <!-- HEADER & SEARCH -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight">Tryout</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-0.5">Simulasi Ujian CPNS Nusantara</p>
                    </div>

                    <div class="relative w-full md:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari..."
                            class="w-full bg-[#E3E3E8] border-transparent rounded-xl pl-10 pr-4 py-2 text-[13px] md:text-sm focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-slate-900 placeholder:text-slate-500 outline-none font-medium"
                        >
                    </div>
                </div>

                <!-- CONTROL BAR (Tabs) -->
                <div class="bg-white rounded-[16px] p-1.5 flex flex-col md:flex-row items-center justify-between gap-2 shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-slate-100">
                    
                    <!-- Segmented Control -->
                    <div class="flex bg-[#F2F2F7] p-0.5 rounded-[12px] w-full md:w-auto">
                        <button 
                            @click="activeTab = 'catalog'"
                            :class="[activeTab === 'catalog' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
                            class="flex-1 md:w-36 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all"
                        >
                            Tersedia
                        </button>
                        <button 
                            @click="activeTab = 'my_tryouts'"
                            :class="[activeTab === 'my_tryouts' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700']"
                            class="flex-1 md:w-36 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all"
                        >
                            Milik Saya
                        </button>
                    </div>
                </div>

                <!-- GRID CARDS -->
                <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4 pt-1">
                    
                    <div 
                        v-for="tryout in filteredTryouts" 
                        :key="tryout.id"
                        class="bg-white rounded-[20px] p-4 shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_6px_20px_rgba(0,0,0,0.06)] transition-all duration-300 flex flex-col items-center text-center h-full border border-slate-100/50 relative overflow-hidden"
                    >
                        
                        <!-- ============================================== -->
                        <!-- KARTU KATALOG                                  -->
                        <!-- ============================================== -->
                        <template v-if="activeTab === 'catalog'">
                            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-3 bg-transparent">
                                <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain drop-shadow-sm" />
                            </div>

                            <h3 class="text-[14px] md:text-[15px] font-semibold text-slate-900 leading-snug mb-1 tracking-tight w-full px-1">
                                {{ tryout.title }}
                            </h3>
                            
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-2.5">
                                Rilis: {{ formatOnlyDate(tryout.created_at || tryout.start_time) }}
                            </p>
                            
                            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium mb-5 bg-[#F5F5F7] px-3 py-1.5 rounded-lg">
                                <span>{{ tryout.questions_count || 110 }} Soal</span>
                                <span class="text-slate-300">•</span>
                                <span>{{ tryout.duration || 100 }} Menit</span>
                            </div>

                            <!-- Footer: Logika Tombol Klaim / Daftar -->
                            <div class="mt-auto w-full flex flex-col items-center justify-center">
                                <!-- Jika Member Premium/Adidaya -->
                                <template v-if="isPremiumMember">
                                    <button 
                                        @click="claimTryout(tryout.id)"
                                        :disabled="isClaiming === tryout.id"
                                        class="w-[95%] md:w-[90%] py-2 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-amber-950 rounded-full text-[12px] font-bold transition-all active:scale-95 text-center flex items-center justify-center gap-2 shadow-sm disabled:opacity-70 disabled:cursor-not-allowed"
                                    >
                                        <svg v-if="isClaiming === tryout.id" class="animate-spin h-3.5 w-3.5 text-amber-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span v-else>✨ Klaim Tryout</span>
                                    </button>
                                </template>

                                <!-- Jika Member Gratis Biasa -->
                                <template v-else>
                                    <Link :href="route('tryout.show', tryout.id)" 
                                          class="w-[95%] md:w-[90%] py-2 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center shadow-sm">
                                        Daftar
                                    </Link>
                                </template>
                            </div>
                        </template>

                        <!-- ============================================== -->
                        <!-- KARTU TRYOUT SAYA                              -->
                        <!-- ============================================== -->
                        <template v-else>
                            
                            <!-- BADGE AKSES (GRATIS/PREMIUM) -->
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-0.5 rounded-[6px] text-[9px] font-bold uppercase tracking-wide"
                                      :class="tryout.user_access_type === 'Premium' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'">
                                    {{ tryout.user_access_type || 'Gratis' }}
                                </span>
                            </div>

                            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-3 bg-transparent mt-2">
                                <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain drop-shadow-sm opacity-80" />
                            </div>

                            <h3 class="text-[14px] md:text-[15px] font-semibold text-slate-900 leading-snug mb-1 tracking-tight w-full px-1">
                                {{ tryout.title }}
                            </h3>
                            
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-2.5">
                                Rilis: {{ formatOnlyDate(tryout.created_at || tryout.start_time) }}
                            </p>
                            
                            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium mb-5 bg-[#F5F5F7] px-3 py-1.5 rounded-lg">
                                <span>{{ tryout.questions_count || 110 }} Soal</span>
                                <span class="text-slate-300">•</span>
                                <span>{{ tryout.duration || 100 }} Menit</span>
                            </div>

                            <div class="mt-auto w-full flex flex-col items-center justify-center">
                                <span class="text-[11px] font-medium text-slate-500 mb-2">
                                    <template v-if="isUpcoming(tryout)">Mulai: {{ formatTime(tryout) }}</template>
                                    <template v-else-if="tryout.remaining_attempts <= 0">Selesai Dikerjakan</template>
                                    <template v-else>Sisa {{ tryout.remaining_attempts }} dari {{ tryout.max_attempts }}x</template>
                                </span>
                                
                                <div class="w-full flex flex-col gap-2 items-center justify-center">
                                    <!-- Jika Jadwal Belum Mulai -->
                                    <button v-if="isUpcoming(tryout)" 
                                            disabled 
                                            class="w-[95%] md:w-[90%] py-2 bg-[#E3E3E8] text-slate-400 rounded-full text-[12px] font-semibold cursor-not-allowed">
                                        Tunggu
                                    </button>
                                    
                                    <!-- Jika Jatah Habis -->
                                    <Link v-else-if="tryout.remaining_attempts <= 0" 
                                          :href="route('tryout.history.detail', tryout.id)" 
                                          class="w-[95%] md:w-[90%] py-2 bg-[#E3E3E8] hover:bg-[#D1D1D6] text-slate-800 rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                        Riwayat
                                    </Link>
                                    
                                    <!-- Jika Masih Bisa Dikerjakan -->
                                    <div v-else class="w-[95%] md:w-[90%] flex gap-2">
                                        <Link :href="route('tryout.wait', tryout.id)" 
                                              class="flex-1 py-2 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                            Mulai
                                        </Link>
                                        
                                        <!-- Tombol Riwayat Khusus Premium Yang Sudah Pernah Dikerjakan -->
                                        <Link v-if="tryout.user_access_type === 'Premium' && getAttemptCount(tryout) > 0" 
                                              :href="route('tryout.history.detail', tryout.id)" 
                                              class="flex-1 py-2 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                            Riwayat
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div v-else class="bg-white rounded-[20px] p-8 md:p-10 text-center shadow-[0_2px_10px_rgba(0,0,0,0.02)] flex flex-col items-center justify-center border border-slate-100/50 mt-4">
                    <div class="w-14 h-14 bg-transparent flex items-center justify-center mb-3 opacity-30 grayscale">
                        <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain" />
                    </div>
                    <h3 class="text-[15px] md:text-[16px] font-semibold text-slate-900 mb-1">
                        {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Semua Berhasil Diklaim' : 'Tidak Ada Hasil' }}
                    </h3>
                    <p class="text-[11px] md:text-[12px] text-slate-500 max-w-sm mx-auto">
                        <template v-if="activeTab === 'catalog' && props.isPremiumMember">
                            Anda sudah mengklaim semua simulasi yang tersedia saat ini. Silakan kerjakan di tab Milik Saya.
                        </template>
                        <template v-else>
                            Kami tidak dapat menemukan simulasi yang Anda cari.
                        </template>
                    </p>
                    <button 
                        v-if="activeTab === 'catalog' && props.isPremiumMember"
                        @click="activeTab = 'my_tryouts'"
                        class="mt-4 px-5 py-2 bg-gradient-to-r from-amber-400 to-amber-500 text-amber-950 rounded-full text-[11px] md:text-[12px] font-bold shadow-sm transition-all active:scale-95"
                    >
                        Lihat Milik Saya
                    </button>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>