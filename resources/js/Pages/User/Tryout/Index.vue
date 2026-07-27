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

// HELPER: Mengambil tanggal mulai yang valid untuk semua browser (Mengatasi bug Safari/iOS)
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

// Cek apakah tryout belum bisa dikerjakan (waktu di masa depan)
const isUpcoming = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return false;
    
    const startTimeMs = new Date(startStr).getTime();
    const nowMs = new Date().getTime();
    
    return startTimeMs > nowMs;
};

// Format tanggal & jam untuk teks pengganti tombol mulai
const formatTime = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return '-';
    
    const date = new Date(startStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date).replace(/\./g, ':').replace(',', ' •') + ' WIB';
};

// Format waktu pelaksanaan (Mulai - Selesai) untuk Katalog
const formatTryoutDateTime = (tryout) => {
    const startStr = getStartDate(tryout);
    const endStr = getEndDate(tryout);

    if (!startStr) return 'Kapan Saja';
    
    const start = new Date(startStr);
    if (isNaN(start.getTime())) return 'Kapan Saja';

    const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit' };
    
    const startDateStr = start.toLocaleDateString('id-ID', optionsDate);
    const startTimeStr = start.toLocaleTimeString('id-ID', optionsTime).replace('.', ':');

    if (!endStr) {
        return `${startDateStr} • ${startTimeStr} WIB`;
    }

    const end = new Date(endStr);
    if (isNaN(end.getTime())) {
        return `${startDateStr} • ${startTimeStr} WIB`;
    }

    const endDateStr = end.toLocaleDateString('id-ID', optionsDate);
    const endTimeStr = end.toLocaleTimeString('id-ID', optionsTime).replace('.', ':');

    if (startDateStr === endDateStr) {
        return `${startDateStr} • ${startTimeStr} - ${endTimeStr} WIB`;
    } else {
        return `${startDateStr} ${startTimeStr} - ${endDateStr} ${endTimeStr} WIB`;
    }
};
</script>

<template>
    <Head title="Katalog Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="animate-in fade-in duration-500 max-w-6xl mx-auto px-4 py-6 md:py-8 space-y-6">
            
            <!-- HEADER & SEARCH -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="space-y-0.5">
                    <h1 class="text-lg md:text-xl font-medium text-slate-900 tracking-tight uppercase">Katalog Tryout</h1>
                    <p class="text-xs text-slate-500 font-normal">Pilih paket simulasi CAT untuk mengasah kemampuanmu.</p>
                </div>

                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                        </svg>
                    </div>
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari simulasi..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-xs focus:bg-white focus:ring-1 focus:ring-slate-300 focus:border-slate-300 transition-all text-slate-700 placeholder:text-slate-400 shadow-sm outline-none font-normal"
                    >
                </div>
            </div>

            <!-- TABS & FILTERS -->
            <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
                <!-- Tabs -->
                <div class="flex bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden w-full md:w-auto">
                    <button 
                        @click="activeTab = 'catalog'"
                        :class="[activeTab === 'catalog' ? 'bg-slate-50 text-slate-900 border-b-2 border-slate-900' : 'text-slate-500 hover:bg-slate-50 border-b-2 border-transparent']"
                        class="flex-1 md:w-36 py-2.5 text-center text-[11px] uppercase tracking-wider transition-all font-medium"
                    >
                        Katalog ({{ availableCatalogTryouts.length }})
                    </button>
                    <button 
                        @click="activeTab = 'my_tryouts'"
                        :class="[activeTab === 'my_tryouts' ? 'bg-slate-50 text-slate-900 border-b-2 border-slate-900' : 'text-slate-500 hover:bg-slate-50 border-b-2 border-transparent']"
                        class="flex-1 md:w-36 py-2.5 text-center text-[11px] uppercase tracking-wider transition-all font-medium"
                    >
                        Tryout Saya ({{ props.myTryouts.length }})
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar w-full md:w-auto">
                    <button 
                        v-for="cat in ['all', 'free', 'premium']"
                        :key="cat"
                        @click="selectedCategory = cat"
                        :class="[selectedCategory === cat ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50']"
                        class="px-4 py-1.5 rounded-lg text-[10px] uppercase tracking-wider transition-all whitespace-nowrap shrink-0 font-medium"
                    >
                        {{ cat === 'all' ? 'Semua Tipe' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                    </button>
                </div>
            </div>

            <!-- GRID CARDS SUPER PENDEK & MINIMALIS -->
            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 pb-8">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group bg-white border border-slate-200 rounded-xl p-3.5 flex flex-col h-full hover:border-slate-300 transition-colors"
                >
                    
                    <!-- ============================================== -->
                    <!-- KARTU KATALOG                                  -->
                    <!-- ============================================== -->
                    <template v-if="activeTab === 'catalog'">
                        
                        <!-- Header Card -->
                        <div class="flex items-center justify-between mb-2">
                            <span :class="tryout.price > 0 || tryout.is_paid ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100'" class="px-2 py-0.5 rounded border text-[9px] uppercase tracking-widest font-medium">
                                {{ tryout.price > 0 || tryout.is_paid ? 'Premium' : 'Gratis' }}
                            </span>
                        </div>

                        <!-- Judul -->
                        <h2 class="text-xs text-slate-900 leading-snug font-medium mb-1.5 group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ tryout.title }}
                        </h2>

                        <!-- Info Soal/Waktu -->
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-normal mb-2.5">
                            <span>{{ tryout.questions_count || 110 }} Soal</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ tryout.duration || 100 }} Menit</span>
                        </div>

                        <!-- Info Jadwal -->
                        <div class="bg-slate-50 rounded-md px-2.5 py-1.5 mb-2 border border-slate-100">
                            <p class="text-[8px] text-slate-400 uppercase tracking-widest font-normal">Pelaksanaan</p>
                            <p class="text-[10px] text-slate-700 font-medium truncate">{{ formatTryoutDateTime(tryout) }}</p>
                        </div>

                        <!-- Footer: Harga & Detail -->
                        <div class="mt-auto pt-2.5 border-t border-slate-100 flex items-end justify-between">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-400 uppercase tracking-wider font-normal mb-0.5">Biaya</span>
                                <span class="text-[11px] text-slate-900 font-medium">
                                    {{ tryout.price > 0 ? `Rp ${Number(tryout.price).toLocaleString('id-ID')}` : 'Gratis' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout.show', tryout.id)"
                                class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-md text-[10px] uppercase tracking-wider transition-colors font-medium"
                            >
                                Detail
                            </Link>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- KARTU TRYOUT SAYA                              -->
                    <!-- ============================================== -->
                    <template v-else>
                        
                        <!-- Header Card -->
                        <div class="flex items-center justify-between mb-2">
                            <span :class="tryout.price > 0 || tryout.is_paid ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100'" class="px-2 py-0.5 rounded border text-[9px] uppercase tracking-widest font-medium">
                                {{ tryout.price > 0 || tryout.is_paid ? 'Premium' : 'Gratis' }}
                            </span>
                            <span class="text-[8px] text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded uppercase tracking-widest font-medium">
                                Tersedia
                            </span>
                        </div>

                        <!-- Judul -->
                        <h2 class="text-xs text-slate-900 leading-snug font-medium mb-1.5 group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ tryout.title }}
                        </h2>

                        <!-- Info Soal/Waktu -->
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-normal mb-3">
                            <span>{{ tryout.questions_count || 110 }} Soal</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ tryout.duration || 100 }} Menit</span>
                        </div>

                        <!-- Footer: Progress & Aksi -->
                        <div class="mt-auto pt-2.5 border-t border-slate-100 flex items-center justify-between">
                            
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-400 uppercase tracking-wider font-normal mb-0.5">Status Ujian</span>
                                <span class="text-[10px] font-medium">
                                    <template v-if="isUpcoming(tryout)">
                                        <span class="text-amber-600">Belum Dimulai ⏳</span>
                                    </template>
                                    <template v-else-if="getAttemptCount(tryout) >= 3">
                                        <span class="text-emerald-600">Sudah Tuntas ✨</span>
                                    </template>
                                    <template v-else>
                                        <span class="text-slate-800">Sisa {{ 3 - getAttemptCount(tryout) }}x Lagi 🔥</span>
                                    </template>
                                </span>
                            </div>

                            <!-- Area Tombol / Jadwal -->
                            <div class="text-right flex flex-col justify-center h-[26px]">
                                <Link 
                                    v-if="getAttemptCount(tryout) >= 3"
                                    :href="route('tryout.history.detail', tryout.id)"
                                    class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md text-[10px] uppercase tracking-wider transition-colors font-medium"
                                >
                                    Riwayat
                                </Link>
                                
                                <!-- Jika belum waktunya, Tampilkan TANGGAL saja, Hilangkan Tombol -->
                                <div v-else-if="isUpcoming(tryout)" class="flex flex-col">
                                    <span class="text-[8px] uppercase tracking-widest text-slate-400 font-normal leading-tight">Dimulai Pada</span>
                                    <span class="text-[10px] font-medium text-slate-700 leading-tight">{{ formatTime(tryout) }}</span>
                                </div>
                                
                                <!-- Tombol Mulai (Blok tapi tulisan medium) -->
                                <Link 
                                    v-else
                                    :href="route('tryout.wait', tryout.id)"
                                    class="px-5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-[10px] uppercase tracking-wider transition-colors font-medium"
                                >
                                    Mulai
                                </Link>
                            </div>

                        </div>
                    </template>

                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-else class="bg-white border border-slate-200 rounded-xl p-8 flex flex-col items-center text-center shadow-sm">
                <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center mb-3">
                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <h3 class="text-sm text-slate-900 mb-1 font-medium">
                    {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Akses Premium Aktif' : 'Tidak Ditemukan' }}
                </h3>
                <p class="text-[11px] text-slate-500 max-w-xs font-normal">
                    <template v-if="activeTab === 'catalog' && props.isPremiumMember">
                        Seluruh simulasi telah masuk ke tab "Tryout Saya".
                    </template>
                    <template v-else>
                        Tidak ada simulasi yang sesuai dengan kategori ini.
                    </template>
                </p>
                <button 
                    v-if="activeTab === 'catalog' && props.isPremiumMember"
                    @click="activeTab = 'my_tryouts'"
                    class="mt-4 px-4 py-2 bg-slate-900 text-white rounded-lg text-[10px] uppercase tracking-wider font-medium"
                >
                    Buka Tryout Saya
                </button>
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