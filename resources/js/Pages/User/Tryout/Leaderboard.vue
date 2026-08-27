<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryout: Object,
    rankings: Array,
    my_rank: Object,
    filters: Object,
});

const safeRankings = computed(() => props.rankings || []);
const safeTryout = computed(() => props.tryout || {});

const search = ref('');
const scope = ref('nasional'); // nasional, provinsi, instansi
const itemsPerPage = ref(20); 
const currentPage = ref(1);

// --- HELPER DATA ---
const getAgency = (user) => {
    if (!user) return 'Instansi belum diatur';
    return user.agency_name || user.instansi || (user.user && user.user.agency_name) || 'Instansi belum diatur';
};

const getDuration = (dur) => {
    if (!dur || dur === 0 || dur === '0') return '-';
    if (!isNaN(dur) && Number(dur) > 0) {
        const val = Number(dur);
        const m = Math.floor(val / 60);
        const s = val % 60;
        return `${m}m ${s}s`;
    }
    return '-';
};

const goBack = () => {
    if (window.history.length > 1) window.history.back();
    else router.visit('/dashboard');
};

// --- LOGIKA PERINGKAT KETAT ATURAN BKN ---
const baseRanked = computed(() => {
    let sorted = [...safeRankings.value];
    sorted.sort((a, b) => {
        // 1. Status Passing Grade (Lulus di atas Gagal)
        if (a.is_passed !== b.is_passed) return a.is_passed ? -1 : 1; 
        
        // 2. Nilai Total Tertinggi
        if (b.score !== a.score) return b.score - a.score;
        
        // 3. Nilai TKP Tertinggi
        if (b.tkp !== a.tkp) return b.tkp - a.tkp;
        
        // 4. Nilai TIU Tertinggi
        if (b.tiu !== a.tiu) return b.tiu - a.tiu;
        
        // 5. Nilai TWK Tertinggi
        if (b.twk !== a.twk) return b.twk - a.twk;
        
        // 6. Waktu Pengerjaan Tercepat (Durasi terkecil di atas)
        return a.duration - b.duration; 
    });
    return sorted;
});

// Saring Scope (Nasional/Provinsi/Instansi) & Set Rank Number Asli
const scopeRanked = computed(() => {
    let list = baseRanked.value;
    
    if (scope.value === 'provinsi' && props.my_rank?.province_code) {
        list = list.filter(u => u.province_code === props.my_rank.province_code);
    } else if (scope.value === 'instansi' && props.my_rank) {
        const myAgency = getAgency(props.my_rank).toLowerCase();
        list = list.filter(u => getAgency(u).toLowerCase() === myAgency);
    }
    
    // Beri nomor urut statis sesuai kategori scope aktif
    return list.map((user, index) => ({
        ...user,
        displayRank: index + 1 
    }));
});

// Terapkan Pencarian
const finalRankings = computed(() => {
    let list = scopeRanked.value;
    if (search.value) {
        const q = search.value.toLowerCase().trim();
        list = list.filter(user => 
            (user.name && user.name.toLowerCase().includes(q)) ||
            (getAgency(user).toLowerCase().includes(q))
        );
    }
    return list;
});

// Pagination 
watch([itemsPerPage, search, scope], () => { currentPage.value = 1; });

const totalPages = computed(() => Math.ceil(finalRankings.value.length / itemsPerPage.value) || 1);

const paginatedRankings = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return finalRankings.value.slice(start, start + itemsPerPage.value);
});

// Sinkronisasi Peringkat Anda secara Realtime (Untuk Floating Bar)
const activeMyRank = computed(() => {
    const me = scopeRanked.value.find(u => u.is_me);
    return me || null;
});
</script>

<template>
    <Head :title="`Peringkat - ${safeTryout?.title}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent w-full pb-36 animate-in fade-in duration-500 overflow-x-hidden">
            
            <div class="max-w-5xl mx-auto px-3 sm:px-6 pt-4 md:pt-8 space-y-5 relative">
                
                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2 relative z-10 px-1 sm:px-0">
                    <div>
                        <button @click="goBack" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali ke Riwayat
                        </button>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Papan Peringkat</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1 uppercase tracking-wide">
                            {{ safeTryout?.title || 'Memuat Data...' }}
                        </p>
                    </div>

                    <!-- Pencarian -->
                    <div class="relative w-full md:w-72 shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari nama / instansi..."
                            class="w-full bg-white border-none shadow-[0_2px_12px_rgba(0,0,0,0.05)] rounded-[16px] pl-10 pr-4 py-3 sm:py-3.5 text-[13px] font-medium focus:ring-2 focus:ring-[#007AFF]/20 transition-all text-slate-900 placeholder:text-slate-400 outline-none"
                        >
                    </div>
                </div>

                <!-- CONTROL BAR SCOPE (Nasional, Provinsi, Instansi) -->
                <div class="bg-white rounded-[16px] p-1.5 flex flex-col md:flex-row items-center justify-between shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 w-full sm:w-fit mx-auto relative z-10">
                    <div class="flex bg-[#F2F2F7] p-0.5 rounded-[12px] w-full md:w-auto">
                        <button @click="scope = 'nasional'" :class="[scope === 'nasional' ? 'bg-white text-[#007AFF] shadow-[0_1px_4px_rgba(0,0,0,0.05)]' : 'text-slate-500 hover:text-slate-700']" class="flex-1 md:w-32 py-2.5 sm:py-2 rounded-[10px] text-[12px] font-bold transition-all">
                            Nasional
                        </button>
                        <button @click="scope = 'provinsi'" :class="[scope === 'provinsi' ? 'bg-white text-[#007AFF] shadow-[0_1px_4px_rgba(0,0,0,0.05)]' : 'text-slate-500 hover:text-slate-700']" class="flex-1 md:w-32 py-2.5 sm:py-2 rounded-[10px] text-[12px] font-bold transition-all">
                            Provinsi
                        </button>
                        <button @click="scope = 'instansi'" :class="[scope === 'instansi' ? 'bg-white text-[#007AFF] shadow-[0_1px_4px_rgba(0,0,0,0.05)]' : 'text-slate-500 hover:text-slate-700']" class="flex-1 md:w-32 py-2.5 sm:py-2 rounded-[10px] text-[12px] font-bold transition-all">
                            Instansi
                        </button>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- DAFTAR PERINGKAT KARTU (CARD LIST)             -->
                <!-- ============================================== -->
                <div class="space-y-3 relative z-10 pt-2">
                    <div v-if="paginatedRankings.length === 0" class="text-center py-12 bg-white rounded-[24px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-slate-100">
                        <p class="text-[13px] text-slate-500 font-medium">Tidak ada data peserta ditemukan pada kategori ini.</p>
                    </div>

                    <div v-for="(rank) in paginatedRankings" :key="'rank-'+rank.id" 
                         class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all"
                         :class="rank.is_me ? 'ring-2 ring-[#007AFF] bg-[#F0F4FF] border-transparent' : 'border-slate-100/80 hover:shadow-[0_4px_16px_rgba(0,0,0,0.04)]'">
                        
                        <!-- Info Peserta -->
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 shrink-0 rounded-full flex items-center justify-center font-black text-[14px] sm:text-[16px] shadow-sm tabular-nums"
                                 :class="{
                                     'bg-[#FBBF24] text-amber-900 border border-amber-300': rank.displayRank === 1 && !search,
                                     'bg-[#E2E8F0] text-slate-700 border border-slate-300': rank.displayRank === 2 && !search,
                                     'bg-[#F97316] text-orange-900 border border-orange-300': rank.displayRank === 3 && !search,
                                     'bg-[#007AFF] text-white border-transparent': rank.is_me && (rank.displayRank > 3 || search),
                                     'bg-[#F5F5F7] text-slate-600 border-transparent': !rank.is_me && (rank.displayRank > 3 || search)
                                 }">
                                {{ rank.displayRank }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h4 class="text-[14px] sm:text-[15px] font-bold text-slate-900 truncate" :class="rank.is_me ? 'text-[#007AFF]' : ''">
                                        {{ rank.name }}
                                        <span v-if="rank.displayRank === 1 && !search" class="text-amber-500 ml-1">👑</span>
                                    </h4>
                                    <span v-if="rank.is_me" class="px-1.5 py-0.5 bg-[#007AFF] text-white text-[8px] sm:text-[9px] font-bold rounded uppercase tracking-wider shrink-0">Anda</span>
                                </div>
                                <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-[11px] font-medium text-slate-500">
                                    <span class="truncate max-w-[160px] sm:max-w-full">{{ getAgency(rank) }}</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                                    <span class="shrink-0">Waktu: {{ getDuration(rank.duration) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Nilai & Status -->
                        <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-6 pl-[3.8rem] sm:pl-0 pt-3 sm:pt-0 border-t border-slate-100 sm:border-0">
                            <div class="flex gap-1.5 sm:gap-2">
                                <div class="flex flex-col items-center justify-center w-11 sm:w-14 h-11 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TWK</span>
                                    <span class="text-[12px] sm:text-[14px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.twk }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-11 sm:w-14 h-11 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TIU</span>
                                    <span class="text-[12px] sm:text-[14px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.tiu }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-11 sm:w-14 h-11 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TKP</span>
                                    <span class="text-[12px] sm:text-[14px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.tkp }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col items-end min-w-[60px] sm:min-w-[80px]">
                                <span class="text-[20px] sm:text-[24px] font-black tracking-tight tabular-nums leading-none" :class="rank.is_passed ? 'text-emerald-600' : 'text-slate-800'">
                                    {{ rank.score }}
                                </span>
                                <span class="text-[8px] sm:text-[9px] font-bold px-1.5 py-0.5 rounded mt-1.5 uppercase tracking-widest border" 
                                      :class="rank.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-500 border-rose-100'">
                                    {{ rank.is_passed ? 'Lulus' : 'Gagal' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ============================================== -->
                <!-- PAGINATION                                     -->
                <!-- ============================================== -->
                <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between bg-white p-3 sm:p-4 rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-slate-100/80 gap-4 relative z-10">
                    <div class="flex items-center gap-2 text-[11px] sm:text-[12px] text-slate-500 font-semibold">
                        <span>Tampilkan:</span>
                        <select v-model="itemsPerPage" class="bg-[#F5F5F7] border-transparent rounded-[8px] text-[11px] sm:text-[12px] py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-[#007AFF]/20 focus:bg-white focus:border-[#007AFF] outline-none cursor-pointer transition-all">
                            <option :value="20">20 Baris</option>
                            <option :value="50">50 Baris</option>
                            <option :value="100">100 Baris</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                        <button @click="currentPage--" :disabled="currentPage === 1" class="px-4 py-2 bg-[#F2F2F7] hover:bg-[#E3E3E8] disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 text-[12px] font-bold rounded-xl transition-colors active:scale-95">Prev</button>
                        <span class="text-[11px] sm:text-[12px] font-semibold text-slate-500">Hal {{ currentPage }} / {{ totalPages }}</span>
                        <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-4 py-2 bg-[#F2F2F7] hover:bg-[#E3E3E8] disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 text-[12px] font-bold rounded-xl transition-colors active:scale-95">Next</button>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- STICKY BOTTOM BAR : PERINGKAT SAYA             -->
                <!-- ============================================== -->
                <div v-if="activeMyRank && activeMyRank.displayRank > 3" class="sticky bottom-4 sm:bottom-6 z-50 pt-2 pb-4">
                    <div class="bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-[0_8px_30px_rgba(0,0,0,0.12)] rounded-[24px] p-3 sm:p-4 w-full flex items-center justify-between gap-3 sm:gap-4 ring-1 ring-white/50">
                        
                        <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-black text-[14px] sm:text-[17px] shadow-sm shrink-0 tabular-nums px-1">
                                {{ activeMyRank.displayRank }}
                            </div>
                            <div class="min-w-0 flex flex-col justify-center">
                                <div class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-0.5">
                                    Peringkat {{ scope === 'nasional' ? 'Nasional' : (scope === 'provinsi' ? 'Provinsi' : 'Instansi') }}
                                </div>
                                <h4 class="text-[13px] sm:text-[14px] font-bold text-slate-900 truncate leading-tight">{{ activeMyRank.name }}</h4>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 font-medium truncate mt-0.5">{{ getAgency(activeMyRank) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 sm:gap-5 shrink-0">
                            <div class="hidden md:flex gap-2 sm:gap-3 border-r border-slate-200 pr-4 sm:pr-5">
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[8px]">
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">TWK</span>
                                    <span class="text-[12px] font-bold text-slate-700 leading-none mt-0.5">{{ activeMyRank.twk }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[8px]">
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">TIU</span>
                                    <span class="text-[12px] font-bold text-slate-700 leading-none mt-0.5">{{ activeMyRank.tiu }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[8px]">
                                    <span class="text-[8px] font-bold text-slate-400 uppercase">TKP</span>
                                    <span class="text-[12px] font-bold text-slate-700 leading-none mt-0.5">{{ activeMyRank.tkp }}</span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end min-w-[70px]">
                                <span class="text-[20px] sm:text-[26px] font-black tracking-tight tabular-nums leading-none text-[#007AFF]">
                                    {{ activeMyRank.score }}
                                </span>
                                <span class="text-[8px] sm:text-[9px] font-bold px-1.5 sm:px-2 py-0.5 rounded mt-1 uppercase tracking-widest border" 
                                      :class="activeMyRank.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-500 border-rose-100'">
                                    {{ activeMyRank.is_passed ? 'Lulus' : 'Gagal' }}
                                </span>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>