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
const itemsPerPage = ref(25);
const currentPage = ref(1);

// --- HELPER DATA ---
const getAgency = (user) => {
    if (!user) return 'Instansi belum diatur';
    return user.agency_name || user.instansi || (user.user && user.user.agency_name) || 'Instansi belum diatur';
};

const isFemale = (user) => {
    if (!user) return false;
    const g = user.gender || (user.user && user.user.gender);
    return g == 2 || g == '2' || g === 'Perempuan';
};

const getInitials = (name) => {
    if (!name) return 'U';
    const words = name.trim().split(' ');
    if (words.length >= 2) return (words[0][0] + words[1][0]).toUpperCase();
    return name.substring(0, 2).toUpperCase();
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

// --- LOGIKA PERINGKAT (ANTI-BUG) ---

// 1. Urutkan Nilai Secara Absolut (Aturan BKN)
const baseRanked = computed(() => {
    let sorted = [...safeRankings.value];
    sorted.sort((a, b) => {
        // Yang lulus ada di atas yang gagal
        if (a.is_passed !== b.is_passed) return a.is_passed ? -1 : 1; 
        // Jika status lulus sama, urutkan berdasarkan skor tertinggi
        if (b.score !== a.score) return b.score - a.score;
        if (b.tkp !== a.tkp) return b.tkp - a.tkp;
        if (b.tiu !== a.tiu) return b.tiu - a.tiu;
        if (b.twk !== a.twk) return b.twk - a.twk;
        return a.duration - b.duration; // Yang lebih cepat di atas
    });
    return sorted;
});

// 2. Saring Scope (Nasional/Provinsi/Instansi) & Set Rank Number Asli
const scopeRanked = computed(() => {
    let list = baseRanked.value;
    
    if (scope.value === 'provinsi' && props.my_rank?.province_code) {
        list = list.filter(u => u.province_code === props.my_rank.province_code);
    } else if (scope.value === 'instansi' && props.my_rank) {
        const myAgency = getAgency(props.my_rank).toLowerCase();
        list = list.filter(u => getAgency(u).toLowerCase() === myAgency);
    }
    
    // Beri nomor urut statis setelah difilter scope
    return list.map((user, index) => ({
        ...user,
        displayRank: index + 1 
    }));
});

// 3. Terapkan Pencarian (Hanya menyaring yang tampil, nomor urut tetap)
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

// 4. Pembagian Data (Podium vs Tabel)
const topThree = computed(() => {
    // Jika sedang mencari data, sembunyikan podium
    if (search.value) return [];
    return finalRankings.value.slice(0, 3);
});

const listToPaginate = computed(() => {
    // Jika mencari data, tampilkan semuanya di tabel
    if (search.value) return finalRankings.value;
    // Jika tidak mencari, potong 3 teratas karena sudah di podium
    return finalRankings.value.slice(3);
});

// 5. Pagination
watch([itemsPerPage, search, scope], () => { currentPage.value = 1; });

const totalPages = computed(() => Math.ceil(listToPaginate.value.length / itemsPerPage.value) || 1);

const paginatedRankings = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return listToPaginate.value.slice(start, start + itemsPerPage.value);
});

// 6. Sinkronisasi Peringkat Anda secara Realtime
const activeMyRank = computed(() => {
    const me = scopeRanked.value.find(u => u.is_me);
    return me || null;
});
</script>

<template>
    <Head :title="`Peringkat - ${safeTryout?.title}`" />

    <AuthenticatedLayout>
        <!-- Background transparan menyatu dengan layout utama -->
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
                <!-- PODIUM TOP 3 PESERTA                           -->
                <!-- ============================================== -->
                <div v-if="topThree.length > 0 && !search" class="pt-8 pb-4 px-2 flex items-end justify-center gap-2 sm:gap-6 relative z-10">
                    
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full sm:w-3/4 h-full bg-blue-400/10 blur-[60px] rounded-full pointer-events-none z-0"></div>

                    <!-- RANK 2 (KIRI) -->
                    <div v-if="topThree[1]" class="flex flex-col items-center w-28 sm:w-40 relative z-10 mb-4 sm:mb-8">
                        <div class="relative mb-3">
                            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full border-[3px] border-[#E2E8F0] shadow-md bg-slate-100 flex items-center justify-center overflow-hidden">
                                <img v-if="topThree[1].avatar" :src="'/storage/'+topThree[1].avatar" class="w-full h-full object-cover" />
                                <span v-else class="text-lg sm:text-xl font-black text-slate-400" :class="isFemale(topThree[1]) ? 'text-rose-400' : ''">{{ getInitials(topThree[1].name) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-slate-400 text-white w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center font-black text-[12px] sm:text-[14px] shadow-sm border-2 border-white">2</div>
                        </div>
                        <div class="w-full bg-white/80 backdrop-blur border border-slate-200/50 shadow-sm rounded-xl p-2.5 sm:p-3 text-center border-t-[3px] border-t-slate-400">
                            <h3 class="text-[11px] sm:text-[13px] font-bold text-slate-800 truncate w-full">{{ topThree[1].name }}</h3>
                            <p class="text-[9px] sm:text-[10px] text-slate-500 font-medium truncate w-full mt-0.5">{{ getAgency(topThree[1]) }}</p>
                            <div class="mt-2 text-slate-700 font-black text-[15px] sm:text-[18px] tabular-nums leading-none">{{ topThree[1].score }}</div>
                        </div>
                    </div>

                    <!-- RANK 1 (TENGAH) -->
                    <div v-if="topThree[0]" class="flex flex-col items-center w-32 sm:w-48 relative z-20">
                        <div class="absolute -top-6 sm:-top-8 text-2xl sm:text-3xl animate-bounce drop-shadow-md">👑</div>
                        <div class="relative mb-3">
                            <div class="w-20 h-20 sm:w-28 sm:h-28 rounded-full border-[4px] border-[#FBBF24] shadow-xl shadow-amber-500/20 bg-amber-50 flex items-center justify-center overflow-hidden">
                                <img v-if="topThree[0].avatar" :src="'/storage/'+topThree[0].avatar" class="w-full h-full object-cover" />
                                <span v-else class="text-2xl sm:text-3xl font-black text-amber-500" :class="isFemale(topThree[0]) ? 'text-rose-400' : ''">{{ getInitials(topThree[0].name) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-amber-500 text-white w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-black text-[14px] sm:text-[16px] shadow-md border-[2.5px] border-white">1</div>
                        </div>
                        <div class="w-full bg-white/90 backdrop-blur border border-amber-100 shadow-md rounded-[16px] p-3 sm:p-4 text-center border-t-[4px] border-t-amber-400">
                            <h3 class="text-[13px] sm:text-[15px] font-bold text-slate-900 truncate w-full">{{ topThree[0].name }}</h3>
                            <p class="text-[9px] sm:text-[11px] text-slate-500 font-medium truncate w-full mt-0.5">{{ getAgency(topThree[0]) }}</p>
                            <div class="mt-2.5 text-[#007AFF] font-black text-[20px] sm:text-[24px] tabular-nums leading-none">{{ topThree[0].score }}</div>
                        </div>
                    </div>

                    <!-- RANK 3 (KANAN) -->
                    <div v-if="topThree[2]" class="flex flex-col items-center w-28 sm:w-40 relative z-10 mb-4 sm:mb-8">
                        <div class="relative mb-3">
                            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full border-[3px] border-[#F97316] shadow-md bg-orange-50 flex items-center justify-center overflow-hidden">
                                <img v-if="topThree[2].avatar" :src="'/storage/'+topThree[2].avatar" class="w-full h-full object-cover" />
                                <span v-else class="text-lg sm:text-xl font-black text-orange-500" :class="isFemale(topThree[2]) ? 'text-rose-400' : ''">{{ getInitials(topThree[2].name) }}</span>
                            </div>
                            <div class="absolute -bottom-1 -right-1 sm:-bottom-2 sm:-right-2 bg-orange-500 text-white w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center font-black text-[12px] sm:text-[14px] shadow-sm border-2 border-white">3</div>
                        </div>
                        <div class="w-full bg-white/80 backdrop-blur border border-slate-200/50 shadow-sm rounded-xl p-2.5 sm:p-3 text-center border-t-[3px] border-t-orange-400">
                            <h3 class="text-[11px] sm:text-[13px] font-bold text-slate-800 truncate w-full">{{ topThree[2].name }}</h3>
                            <p class="text-[9px] sm:text-[10px] text-slate-500 font-medium truncate w-full mt-0.5">{{ getAgency(topThree[2]) }}</p>
                            <div class="mt-2 text-slate-700 font-black text-[15px] sm:text-[18px] tabular-nums leading-none">{{ topThree[2].score }}</div>
                        </div>
                    </div>

                </div>

                <!-- ============================================== -->
                <!-- DAFTAR PERINGKAT LAINNYA                       -->
                <!-- ============================================== -->
                <div class="space-y-3 relative z-10">
                    <div v-if="listToPaginate.length === 0" class="text-center py-12 bg-white rounded-[20px] shadow-sm border border-slate-100">
                        <p class="text-[13px] text-slate-500 font-medium">Tidak ada data peserta ditemukan.</p>
                    </div>

                    <div v-for="(rank) in paginatedRankings" :key="'rank-'+rank.id" 
                         class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all"
                         :class="rank.is_me ? 'ring-2 ring-[#007AFF] bg-[#F0F4FF] border-transparent' : 'border-slate-100/80 hover:shadow-[0_4px_16px_rgba(0,0,0,0.04)]'">
                        
                        <!-- Info Peserta -->
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-full flex items-center justify-center font-black text-[13px] sm:text-[14px]"
                                 :class="rank.is_me ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-[#F5F5F7] text-slate-500'">
                                {{ rank.displayRank }}
                            </div>

                            <div class="w-10 h-10 shrink-0 rounded-full bg-[#E2E8F0] overflow-hidden flex items-center justify-center text-[12px] font-bold text-slate-500 border border-slate-200/50">
                                <img v-if="rank.avatar" :src="'/storage/'+rank.avatar" class="w-full h-full object-cover" />
                                <span v-else>{{ getInitials(rank.name) }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h4 class="text-[13px] sm:text-[14px] font-bold text-slate-900 truncate" :class="rank.is_me ? 'text-[#007AFF]' : ''">{{ rank.name }}</h4>
                                    <span v-if="rank.is_me" class="px-1.5 py-0.5 bg-[#007AFF] text-white text-[8px] sm:text-[9px] font-bold rounded uppercase tracking-wider shrink-0">Anda</span>
                                </div>
                                <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-[11px] font-medium text-slate-500">
                                    <span class="truncate max-w-[140px] sm:max-w-full">{{ getAgency(rank) }}</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                                    <span>{{ getDuration(rank.duration) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Nilai & Status -->
                        <div class="flex items-center justify-between sm:justify-end gap-4 sm:gap-5 pl-[3.2rem] sm:pl-0 pt-2 sm:pt-0 border-t border-slate-100 sm:border-0">
                            <!-- Rincian TWK TIU TKP -->
                            <div class="flex gap-1.5 sm:gap-2">
                                <div class="flex flex-col items-center justify-center w-10 sm:w-12 h-10 sm:h-11 bg-white border border-slate-100 rounded-[10px] shadow-sm">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TWK</span>
                                    <span class="text-[11px] sm:text-[13px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.twk }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-10 sm:w-12 h-10 sm:h-11 bg-white border border-slate-100 rounded-[10px] shadow-sm">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TIU</span>
                                    <span class="text-[11px] sm:text-[13px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.tiu }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-10 sm:w-12 h-10 sm:h-11 bg-white border border-slate-100 rounded-[10px] shadow-sm">
                                    <span class="text-[8px] sm:text-[9px] font-bold text-slate-400 uppercase">TKP</span>
                                    <span class="text-[11px] sm:text-[13px] font-bold text-slate-700 tabular-nums leading-none mt-0.5">{{ rank.tkp }}</span>
                                </div>
                            </div>

                            <!-- Total & Lulus/Gagal -->
                            <div class="flex flex-col items-end min-w-[60px] sm:min-w-[70px]">
                                <span class="text-[18px] sm:text-[22px] font-black tracking-tight tabular-nums leading-none" :class="rank.is_passed ? 'text-emerald-600' : 'text-slate-800'">
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
                            <option :value="25">25 Baris</option>
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
                <div v-if="activeMyRank" class="sticky bottom-4 sm:bottom-6 z-50 pt-2 pb-4">
                    <div class="bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-[0_8px_30px_rgba(0,0,0,0.12)] rounded-[24px] p-3 sm:p-4 w-full flex items-center justify-between gap-3 sm:gap-4 ring-1 ring-white/50">
                        
                        <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                            <!-- Bulatan Biru Menampilkan Nomor Peringkat (Misal: 100, 5, dll) -->
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-black text-[14px] sm:text-[17px] shadow-sm shrink-0 tabular-nums">
                                {{ activeMyRank.rank }}
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