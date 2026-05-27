<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    tryout: Object,
    rankings: Array,
    my_rank: Object,
    filters: Object,
});

// --- PENGAMANAN DATA NULL ---
const safeRankings = computed(() => props.rankings || []);
const safeFilters = computed(() => props.filters || {});
const safeTryout = computed(() => props.tryout || {});

const search = ref(safeFilters.value.search || '');
const scope = ref(safeFilters.value.scope || 'nasional');

// Update request untuk pencarian/filter
const updateParams = debounce(() => {
    if (!safeTryout.value.id) return;
    
    router.get(route('tryout.leaderboard', safeTryout.value.id), { 
        search: search.value,
        scope: scope.value 
    }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, 500);

watch(search, updateParams);
watch(scope, updateParams);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('dashboard'));
    }
};

const getAgency = (user) => {
    if (!user) return 'Instansi belum diatur';
    return user.agency_name || user.instansi || (user.user && user.user.agency_name) || 'Instansi belum diatur';
};

const isFemale = (user) => {
    if (!user) return false;
    const g = user.gender || (user.user && user.user.gender);
    return g == 2 || g == '2' || g === 'Perempuan';
};

// --- HELPER PARSING TANGGAL LEBIH AKURAT ---
const parseSafeDate = (dateVal) => {
    if (!dateVal) return new Date(NaN);
    if (typeof dateVal === 'string') {
        // Jika format stringnya 'YYYY-MM-DD HH:mm:ss', ubah spasi jadi 'T' agar standar ISO
        if (dateVal.includes(' ') && !dateVal.includes('T')) {
            return new Date(dateVal.replace(' ', 'T'));
        }
        return new Date(dateVal);
    }
    return new Date(dateVal);
};

// --- HELPER DURASI PENGERJAAN ---
const getDuration = (user) => {
    if (!user) return '-';
    
    // PRIORITAS 1: Hitung manual dari created_at dan completed_at (Paling Akurat)
    if (user.created_at && user.completed_at) {
        const start = parseSafeDate(user.created_at);
        const end = parseSafeDate(user.completed_at);
        
        if (!isNaN(start.getTime()) && !isNaN(end.getTime())) {
            const diffSeconds = Math.floor((end.getTime() - start.getTime()) / 1000);
            if (diffSeconds > 0) {
                const h = Math.floor(diffSeconds / 3600);
                const m = Math.floor((diffSeconds % 3600) / 60);
                const s = diffSeconds % 60;
                return h > 0 ? `${h}j ${m}m ${s}d` : `${m}m ${s}d`;
            }
        }
    }

    // PRIORITAS 2: Gunakan data durasi bawaan backend (jika dikirim)
    let dur = user.duration || user.waktu_pengerjaan;
    
    if (!dur || dur === '00:00:00' || dur === 0 || dur === '0') return '-';

    // Jika format HH:MM:SS
    if (typeof dur === 'string' && dur.includes(':')) {
        const parts = dur.split(':');
        if (parts.length === 3) {
            const h = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            const s = parseInt(parts[2], 10);
            if (h === 0 && m === 0 && s === 0) return '-';
            return h > 0 ? `${h}j ${m}m ${s}d` : `${m}m ${s}d`;
        }
    }

    // Jika format detik (integer)
    if (!isNaN(dur) && Number(dur) > 0) {
        const val = Number(dur);
        const h = Math.floor(val / 3600);
        const m = Math.floor((val % 3600) / 60);
        const s = val % 60;
        return h > 0 ? `${h}j ${m}m ${s}d` : `${m}m ${s}d`;
    }

    return '-';
};

const sortedRankings = computed(() => {
    let sorted = [...safeRankings.value];
    
    sorted.sort((a, b) => {
        const aPassed = a.is_passed ? 1 : 0;
        const bPassed = b.is_passed ? 1 : 0;
        if (aPassed !== bPassed) return bPassed - aPassed;
        
        const scoreA = Number(a.score) || 0;
        const scoreB = Number(b.score) || 0;
        if (scoreB !== scoreA) return scoreB - scoreA;
        
        const tkpA = Number(a.tkp) || 0;
        const tkpB = Number(b.tkp) || 0;
        if (tkpB !== tkpA) return tkpB - tkpA;
        
        const tiuA = Number(a.tiu) || 0;
        const tiuB = Number(b.tiu) || 0;
        if (tiuB !== tiuA) return tiuB - tiuA;
        
        const twkA = Number(a.twk) || 0;
        const twkB = Number(b.twk) || 0;
        if (twkB !== twkA) return twkB - twkA;
        
        return 0; 
    });
    
    return sorted.map((user, index) => ({
        ...user,
        displayRank: index + 1
    }));
});

const itemsPerPage = ref(25);
const currentPage = ref(1);

watch([itemsPerPage, search, scope], () => {
    currentPage.value = 1;
});

const totalPages = computed(() => {
    return Math.ceil(sortedRankings.value.length / itemsPerPage.value);
});

const paginatedRankings = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return sortedRankings.value.slice(start, end);
});
</script>

<template>
    <Head title="Leaderboard" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-700 flex flex-col pb-16">
        
        <nav class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 sticky top-0 z-50 shadow-sm backdrop-blur-xl bg-white/90">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-2 sm:gap-3">
                    <button @click="goBack" class="sm:hidden p-1.5 -ml-1.5 text-slate-500 hover:bg-slate-100 rounded-md transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                    
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg hidden sm:flex items-center justify-center border border-blue-100 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-medium text-slate-800 text-[13px] sm:text-sm leading-none">Papan Peringkat Klasemen</h1>
                        <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wide truncate max-w-[200px] sm:max-w-md">
                            {{ safeTryout.title || 'Memuat Data...' }}
                        </p>
                    </div>
                </div>
                
                <button @click="goBack" class="text-[10px] sm:text-[11px] font-medium text-slate-600 hover:text-blue-600 transition-colors uppercase tracking-wide flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:border-blue-200 hover:bg-blue-50 rounded-md shadow-sm shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali Ke Hasil
                </button>
            </div>
        </nav>

        <main class="flex-1 max-w-5xl mx-auto w-full px-3 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6 relative">
            <div class="absolute top-0 left-0 right-0 h-48 sm:h-64 bg-gradient-to-b from-[#1e60aa] to-slate-50 z-0 -mx-4 sm:-mx-8"></div>

            <div class="relative z-10 space-y-6 pt-1">
                <div class="bg-white/95 backdrop-blur-sm p-3 sm:p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
                    
                    <div class="flex bg-slate-100 p-1 rounded-lg w-full md:w-auto border border-slate-200 shrink-0">
                        <button 
                            @click="scope = 'nasional'"
                            class="px-3 sm:px-5 py-2 rounded-md text-[11px] sm:text-sm font-medium transition-colors w-1/2 md:w-auto text-center"
                            :class="scope === 'nasional' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Nasional
                        </button>
                        <button 
                            @click="scope = 'provinsi'"
                            class="px-3 sm:px-5 py-2 rounded-md text-[11px] sm:text-sm font-medium transition-colors w-1/2 md:w-auto text-center truncate"
                            :class="scope === 'provinsi' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Provinsi {{ safeFilters.user_province ? `(${safeFilters.user_province})` : '' }}
                        </button>
                    </div>

                    <div class="relative w-full md:max-w-xs shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            class="block w-full pl-9 pr-4 py-2 sm:py-2.5 border border-slate-200 rounded-lg bg-slate-50 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:bg-white text-xs sm:text-sm transition-colors" 
                            placeholder="Cari nama peserta..." 
                        />
                    </div>
                </div>

                <div v-if="!search && sortedRankings.length > 0" class="flex flex-wrap sm:flex-nowrap justify-center items-stretch gap-3 sm:gap-6 pt-4 sm:pt-6 pb-2">
                    
                    <div v-if="sortedRankings[1]" class="order-2 sm:order-1 flex flex-col items-center w-[46%] sm:w-44 mt-auto">
                        <div class="relative mb-3 z-10">
                            <img v-if="sortedRankings[1].avatar" :src="'/storage/'+sortedRankings[1].avatar" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-[3px] border-slate-200 shadow-sm bg-white object-cover">
                            <div v-else class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-[3px] border-slate-200 shadow-sm flex items-center justify-center" :class="isFemale(sortedRankings[1]) ? 'bg-rose-50 text-rose-300' : 'bg-slate-100 text-slate-400'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="absolute -bottom-2 sm:-bottom-3 left-1/2 transform -translate-x-1/2 bg-slate-200 text-slate-700 text-[10px] sm:text-xs font-medium px-2.5 py-0.5 rounded-full shadow-sm border border-white">#2</div>
                        </div>
                        <div class="w-full h-full flex flex-col bg-white p-3 sm:p-4 rounded-xl shadow-sm text-center border border-slate-200 border-t-[3px] border-t-slate-300">
                            <h3 class="font-medium text-slate-800 text-xs sm:text-sm break-words">{{ sortedRankings[1].name }}</h3>
                            <p class="text-[9px] text-slate-500 mt-1 leading-tight break-words">{{ getAgency(sortedRankings[1]) }}</p>
                            
                            <div class="mt-auto pt-2">
                                <div class="text-slate-700 font-medium text-lg sm:text-xl tabular-nums leading-none">{{ sortedRankings[1].score }}</div>
                                <span v-if="sortedRankings[1].is_passed" class="inline-block mt-1.5 text-[9px] bg-emerald-50 border border-emerald-100 text-emerald-600 px-2 py-0.5 rounded uppercase tracking-wider font-medium">Lulus</span>
                                
                                <div class="flex items-center justify-center gap-1 mt-2 text-[9px] text-slate-400 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ getDuration(sortedRankings[1]) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="sortedRankings[0]" class="order-1 sm:order-2 flex flex-col items-center w-full sm:w-56 mb-4 sm:mb-0 sm:-mt-8 z-20">
                        <div class="relative mb-3">
                            <div class="absolute -top-6 sm:-top-8 left-1/2 transform -translate-x-1/2 text-amber-400 drop-shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 sm:h-8 sm:w-8" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </div>
                            <img v-if="sortedRankings[0].avatar" :src="'/storage/'+sortedRankings[0].avatar" class="w-20 h-20 sm:w-28 sm:h-28 rounded-full border-[3px] border-amber-300 shadow-md bg-white object-cover">
                            <div v-else class="w-20 h-20 sm:w-28 sm:h-28 rounded-full border-[3px] border-amber-300 shadow-md flex items-center justify-center" :class="isFemale(sortedRankings[0]) ? 'bg-rose-100 text-rose-400' : 'bg-amber-50 text-amber-400'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-14 sm:w-14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="absolute -bottom-2 sm:-bottom-3 left-1/2 transform -translate-x-1/2 bg-amber-300 text-amber-900 text-xs sm:text-sm font-medium px-3.5 py-0.5 rounded-full shadow-sm border border-white">#1</div>
                        </div>
                        <div class="w-full h-full flex flex-col bg-white p-4 sm:p-5 rounded-2xl shadow-md text-center border border-slate-200 border-t-[4px] border-t-amber-400 sm:scale-105 transition-transform">
                            <h3 class="font-medium text-slate-800 text-sm sm:text-base break-words">{{ sortedRankings[0].name }}</h3>
                            <p class="text-[10px] text-slate-500 mt-1.5 mb-1.5 leading-tight break-words">{{ getAgency(sortedRankings[0]) }}</p>
                            
                            <div class="mt-auto pt-2">
                                <div class="text-blue-700 font-medium text-2xl sm:text-3xl tabular-nums leading-none">{{ sortedRankings[0].score }}</div>
                                <span v-if="sortedRankings[0].is_passed" class="inline-block mt-2 text-[9px] sm:text-[10px] bg-emerald-50 border border-emerald-100 text-emerald-600 px-2 py-0.5 rounded uppercase tracking-wider font-medium">Lulus SKD</span>
                                
                                <div class="flex items-center justify-center gap-1 mt-2 text-[10px] text-slate-400 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ getDuration(sortedRankings[0]) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="sortedRankings[2]" class="order-3 flex flex-col items-center w-[46%] sm:w-44 mt-auto">
                        <div class="relative mb-3 z-10">
                            <img v-if="sortedRankings[2].avatar" :src="'/storage/'+sortedRankings[2].avatar" class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-[3px] border-orange-100 shadow-sm bg-white object-cover">
                            <div v-else class="w-16 h-16 sm:w-20 sm:h-20 rounded-full border-[3px] border-orange-100 shadow-sm flex items-center justify-center" :class="isFemale(sortedRankings[2]) ? 'bg-rose-50 text-rose-300' : 'bg-orange-50 text-orange-300'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-10 sm:w-10" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="absolute -bottom-2 sm:-bottom-3 left-1/2 transform -translate-x-1/2 bg-orange-100 text-orange-800 text-[10px] sm:text-xs font-medium px-2.5 py-0.5 rounded-full shadow-sm border border-white">#3</div>
                        </div>
                        <div class="w-full h-full flex flex-col bg-white p-3 sm:p-4 rounded-xl shadow-sm text-center border border-slate-200 border-t-[3px] border-t-orange-200">
                            <h3 class="font-medium text-slate-800 text-xs sm:text-sm break-words">{{ sortedRankings[2].name }}</h3>
                            <p class="text-[9px] text-slate-500 mt-1 leading-tight break-words">{{ getAgency(sortedRankings[2]) }}</p>
                            
                            <div class="mt-auto pt-2">
                                <div class="text-slate-700 font-medium text-lg sm:text-xl tabular-nums leading-none">{{ sortedRankings[2].score }}</div>
                                <span v-if="sortedRankings[2].is_passed" class="inline-block mt-1.5 text-[9px] bg-emerald-50 border border-emerald-100 text-emerald-600 px-2 py-0.5 rounded uppercase tracking-wider font-medium">Lulus</span>
                                
                                <div class="flex items-center justify-center gap-1 mt-2 text-[9px] text-slate-400 font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ getDuration(sortedRankings[2]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="my_rank" class="sticky top-[4.5rem] sm:top-[4.8rem] z-30 px-1 sm:px-0">
                    <div class="bg-white/95 backdrop-blur-sm border border-blue-200 text-slate-700 p-3.5 sm:p-4 rounded-xl shadow-[0_4px_15px_-3px_rgba(30,96,170,0.1)] flex items-center justify-between ring-1 ring-blue-50 transition-all">
                        <div class="flex items-center gap-3 sm:gap-4 overflow-hidden">
                            <div class="bg-blue-50 border border-blue-100 text-blue-700 w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center font-medium text-base sm:text-lg tabular-nums shrink-0 shadow-sm">
                                {{ my_rank.rank || '-' }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-[9px] sm:text-[10px] text-slate-500 uppercase font-medium tracking-wide">
                                    Peringkat {{ scope === 'nasional' ? 'Nasional' : 'Provinsi' }} Anda
                                </div>
                                <div class="font-medium text-slate-800 text-[11px] sm:text-sm flex flex-wrap items-center gap-1.5 sm:gap-2 mt-0.5 truncate">
                                    {{ my_rank.name }} (Saya)
                                    <span v-if="my_rank.is_passed" class="text-[9px] bg-emerald-100 border border-emerald-200 text-emerald-700 px-1.5 py-0.5 rounded tracking-wide uppercase">Lulus</span>
                                    <span v-else class="text-[9px] bg-rose-100 border border-rose-200 text-rose-700 px-1.5 py-0.5 rounded tracking-wide uppercase">Gagal</span>
                                </div>
                                <div class="text-[9px] sm:text-[10px] text-slate-400 mt-1 flex flex-wrap items-center gap-1 truncate">
                                    <span>Instansi: {{ getAgency(my_rank) }}</span>
                                    <span class="px-1 text-slate-300">•</span>
                                    <span class="flex items-center gap-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ getDuration(my_rank) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-2">
                            <div class="text-xl sm:text-2xl font-medium tabular-nums leading-none text-blue-700">{{ my_rank.score }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                    
                    <div class="hidden sm:grid grid-cols-12 gap-4 p-4 bg-slate-50/80 border-b border-slate-200 text-[11px] font-medium text-slate-500 uppercase tracking-wider items-center">
                        <div class="col-span-1 text-center">#</div>
                        <div class="col-span-5">Peserta & Instansi</div>
                        <div class="col-span-1 text-center">TWK</div>
                        <div class="col-span-1 text-center">TIU</div>
                        <div class="col-span-1 text-center">TKP</div>
                        <div class="col-span-1 text-center">Total</div>
                        <div class="col-span-2 text-right">Waktu</div>
                    </div>

                    <div v-if="paginatedRankings.length > 0">
                        <div v-for="(user, index) in paginatedRankings" :key="'rank-' + user.id + '-' + index" 
                            class="p-4 sm:px-4 sm:py-3 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors"
                            :class="{'bg-blue-50/30 hover:bg-blue-50/50': user.is_me}">
                            
                            <div class="flex flex-col sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center">
                                
                                <div class="hidden sm:block sm:col-span-1 text-center">
                                    <span class="font-medium text-slate-400 text-xs sm:text-sm tabular-nums">{{ user.displayRank || '-' }}</span>
                                </div>

                                <div class="flex items-center gap-2.5 sm:gap-3 sm:col-span-5 mb-2 sm:mb-0">
                                    <div class="sm:hidden w-6 text-center shrink-0">
                                        <span class="font-medium text-slate-400 text-xs tabular-nums">{{ user.displayRank || '-' }}</span>
                                    </div>
                                    
                                    <img v-if="user.avatar" :src="'/storage/'+user.avatar" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-slate-200 object-cover shrink-0">
                                    <div v-else class="w-8 h-8 sm:w-9 sm:h-9 rounded-full border border-slate-200 flex items-center justify-center shrink-0" :class="isFemale(user) ? 'bg-rose-50 text-rose-300' : 'bg-slate-100 text-slate-400'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                    </div>
                                    
                                    <div class="min-w-0 flex-1">
                                        <div class="text-[12px] sm:text-[13px] font-medium text-slate-700 truncate flex items-center gap-1.5">
                                            {{ user.name }}
                                            <span v-if="user.is_me" class="text-[9px] bg-blue-100 border border-blue-200 text-blue-700 px-1.5 py-0.5 rounded tracking-wide uppercase shrink-0">Anda</span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 truncate mt-0.5">
                                            {{ getAgency(user) }}
                                        </div>
                                        <div class="text-[9px] sm:text-[10px] flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span v-if="user.is_passed" class="text-emerald-600 font-medium border border-emerald-100 bg-emerald-50 px-1 rounded uppercase tracking-wide">Lulus SKD</span>
                                            <span v-else class="text-rose-500 font-medium border border-rose-100 bg-rose-50 px-1 rounded uppercase tracking-wide">Gagal</span>
                                        </div>
                                    </div>
                                    
                                    <div class="sm:hidden ml-auto text-right shrink-0">
                                        <div class="text-lg font-medium text-blue-700 tabular-nums leading-none">{{ user.score }}</div>
                                    </div>
                                </div>

                                <div class="hidden sm:block sm:col-span-1 text-center">
                                    <span class="text-xs font-medium tabular-nums" :class="user.twk >= 65 ? 'text-slate-600' : 'text-rose-500'" title="TWK">{{ user.twk }}</span>
                                </div>
                                <div class="hidden sm:block sm:col-span-1 text-center">
                                    <span class="text-xs font-medium tabular-nums" :class="user.tiu >= 80 ? 'text-slate-600' : 'text-rose-500'" title="TIU">{{ user.tiu }}</span>
                                </div>
                                <div class="hidden sm:block sm:col-span-1 text-center">
                                    <span class="text-xs font-medium tabular-nums" :class="user.tkp >= 166 ? 'text-slate-600' : 'text-rose-500'" title="TKP">{{ user.tkp }}</span>
                                </div>
                                
                                <div class="hidden sm:block sm:col-span-1 text-center">
                                    <div class="text-sm font-medium text-blue-700 tabular-nums">{{ user.score }}</div>
                                </div>

                                <div class="hidden sm:flex sm:col-span-2 items-center justify-end text-[10px] sm:text-[11px] text-slate-500 tabular-nums font-medium">
                                    {{ getDuration(user) }}
                                </div>

                                <div class="sm:hidden pl-[2.8rem] flex items-center justify-between pt-1">
                                    <div class="flex gap-4 items-center">
                                        <span class="text-[11px] font-medium tabular-nums" :class="user.twk >= 65 ? 'text-slate-600' : 'text-rose-500'" title="Skor TWK">{{ user.twk }}</span>
                                        <span class="text-[11px] font-medium tabular-nums" :class="user.tiu >= 80 ? 'text-slate-600' : 'text-rose-500'" title="Skor TIU">{{ user.tiu }}</span>
                                        <span class="text-[11px] font-medium tabular-nums" :class="user.tkp >= 166 ? 'text-slate-600' : 'text-rose-500'" title="Skor TKP">{{ user.tkp }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 tabular-nums text-right flex gap-1 font-medium items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ getDuration(user) }}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div v-else class="p-10 sm:p-12 text-center text-slate-500 text-sm bg-slate-50 flex flex-col items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium text-slate-600">Data peserta tidak ditemukan.</p>
                        <p class="text-[11px] sm:text-xs text-slate-400 mt-1">Belum ada nilai atau sesuaikan kata kunci pencarian Anda.</p>
                    </div>

                    <div v-if="sortedRankings.length > 0" class="flex flex-col sm:flex-row justify-between items-center gap-4 p-4 border-t border-slate-200 bg-slate-50">
                        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-slate-500 font-medium">
                            <span>Tampilkan:</span>
                            <select v-model="itemsPerPage" class="border border-slate-300 rounded-md text-[11px] sm:text-xs py-1.5 pl-2 pr-6 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm outline-none cursor-pointer">
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                            <span>Peserta</span>
                        </div>
                        
                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-[11px] sm:text-xs font-medium transition-colors bg-white shadow-sm">
                                Sebelumnya
                            </button>
                            <span class="text-[11px] sm:text-xs text-slate-500 font-medium px-2">
                                Hal {{ currentPage }} / {{ totalPages || 1 }}
                                <span class="hidden sm:inline">({{ sortedRankings.length }} Total)</span>
                            </span>
                            <button @click="currentPage++" :disabled="currentPage >= totalPages" class="px-3 py-1.5 border border-slate-300 rounded-md text-slate-600 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed text-[11px] sm:text-xs font-medium transition-colors bg-white shadow-sm">
                                Selanjutnya
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>