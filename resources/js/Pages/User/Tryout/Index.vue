<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
const selectedCategory = ref('all'); 

// --- 1. SINKRONISASI DATA KATALOG ---
const availableCatalogTryouts = computed(() => {
    const ownedTryoutIds = (props.myTryouts || []).map(t => t.id);
    return (props.catalogTryouts || []).filter(t => !ownedTryoutIds.includes(t.id));
});

// --- 2. FITUR FILTER & PENCARIAN ---
const filteredTryouts = computed(() => {
    const baseData = activeTab.value === 'catalog' ? availableCatalogTryouts.value : (props.myTryouts || []);
    
    return baseData.filter(t => {
        const matchesSearch = t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        let matchesCategory = false;
        if (selectedCategory.value === 'all') {
            matchesCategory = true;
        } else if (selectedCategory.value === 'free') {
            matchesCategory = (t.price == 0 || t.is_paid == false || t.is_paid == 0);
        } else if (selectedCategory.value === 'premium') {
            matchesCategory = (t.price > 0 || t.is_paid == true || t.is_paid == 1);
        }
        
        return matchesSearch && matchesCategory;
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

const getEndDate = (tryout) => {
    const dateStr = tryout.end_time || tryout.end_date || tryout.ended_at;
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

                <!-- CONTROL BAR (Tabs & Filters) -->
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

                    <!-- Kategori Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar w-full md:w-auto px-1">
                        <button 
                            v-for="cat in ['all', 'free', 'premium']"
                            :key="cat"
                            @click="selectedCategory = cat"
                            :class="[selectedCategory === cat ? 'bg-slate-800 text-white' : 'bg-transparent text-slate-500 hover:bg-slate-100']"
                            class="px-4 py-1 rounded-full text-[11px] font-semibold transition-all whitespace-nowrap shrink-0"
                        >
                            {{ cat === 'all' ? 'Semua' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                        </button>
                    </div>
                </div>

                <!-- GRID CARDS -->
                <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4 pt-1">
                    
                    <div 
                        v-for="tryout in filteredTryouts" 
                        :key="tryout.id"
                        class="bg-white rounded-[20px] p-4 shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_6px_20px_rgba(0,0,0,0.06)] transition-all duration-300 flex flex-col items-center text-center h-full border border-slate-100/50"
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

                            <!-- Footer: Tombol Center Saja (Harga Dihapus) -->
                            <div class="mt-auto w-full flex flex-col items-center justify-center">
                                <Link :href="route('tryout.show', tryout.id)" 
                                      class="w-[95%] md:w-[90%] py-2 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                    Daftar
                                </Link>
                            </div>
                        </template>

                        <!-- ============================================== -->
                        <!-- KARTU TRYOUT SAYA                              -->
                        <!-- ============================================== -->
                        <template v-else>
                            <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-3 bg-transparent">
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
                                    <template v-else-if="getAttemptCount(tryout) >= 3">Selesai Dikerjakan</template>
                                    <template v-else>Sisa {{ 3 - getAttemptCount(tryout) }}x Kesempatan</template>
                                </span>
                                
                                <button v-if="isUpcoming(tryout)" 
                                        disabled 
                                        class="w-[95%] md:w-[90%] py-2 bg-[#E3E3E8] text-slate-400 rounded-full text-[12px] font-semibold cursor-not-allowed">
                                    Tunggu
                                </button>
                                
                                <Link v-else-if="getAttemptCount(tryout) >= 3" 
                                      :href="route('tryout.history.detail', tryout.id)" 
                                      class="w-[95%] md:w-[90%] py-2 bg-[#E3E3E8] hover:bg-[#D1D1D6] text-slate-800 rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                    Riwayat
                                </Link>
                                
                                <Link v-else 
                                      :href="route('tryout.wait', tryout.id)" 
                                      class="w-[95%] md:w-[90%] py-2 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[12px] font-semibold transition-colors active:scale-95 text-center">
                                    Mulai
                                </Link>
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
                        {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Semua Dimiliki' : 'Tidak Ada Hasil' }}
                    </h3>
                    <p class="text-[11px] md:text-[12px] text-slate-500 max-w-sm mx-auto">
                        <template v-if="activeTab === 'catalog' && props.isPremiumMember">
                            Anda sudah memiliki semua simulasi. Silakan periksa tab Milik Saya.
                        </template>
                        <template v-else>
                            Kami tidak dapat menemukan simulasi yang Anda cari.
                        </template>
                    </p>
                    <button 
                        v-if="activeTab === 'catalog' && props.isPremiumMember"
                        @click="activeTab = 'my_tryouts'"
                        class="mt-4 px-5 py-2 bg-[#007AFF] text-white rounded-full text-[11px] md:text-[12px] font-semibold transition-colors active:scale-95"
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