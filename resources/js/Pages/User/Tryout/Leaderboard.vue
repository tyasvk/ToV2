<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    rankings: Array, 
    auth: Object
});

// --- STATE ---
const searchQuery = ref('');
const regionFilter = ref('Nasional');
const perPage = ref(10);

// --- CONSTANTS ---
const PASSING_GRADE = { TWK: 65, TIU: 80, TKP: 166 };

// --- LOGIC ---
const isPassed = (rank) => {
    return rank.twk_score >= PASSING_GRADE.TWK &&
           rank.tiu_score >= PASSING_GRADE.TIU &&
           rank.tkp_score >= PASSING_GRADE.TKP;
};

// Filter Logic
const filteredRankings = computed(() => {
    let data = props.rankings;
    if (searchQuery.value) {
        data = data.filter(item => 
            item.user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    if (regionFilter.value === 'Provinsi') {
        data = data.filter(item => item.user.province === props.auth.user.province);
    }
    return data;
});

// Pagination Logic
const displayedRankings = computed(() => {
    return filteredRankings.value.slice(0, perPage.value);
});

const formatDate = (dateString) => {
    const d = new Date(dateString);
    const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
};
</script>

<template>
    <Head :title="'Ranking - ' + tryout.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="pb-14 pt-0 flex justify-between items-start">
                <div class="space-y-0.5">
                    <h2 class="font-black text-2xl text-slate-800 tracking-tighter uppercase leading-none">
                        Leaderboard
                    </h2>
                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em]">
                        {{ tryout.title }} • {{ regionFilter }}
                    </p>
                </div>
                <Link :href="route('tryout.index')" class="bg-slate-100 px-4 py-1.5 rounded-xl text-[9px] font-black uppercase text-slate-500 hover:bg-slate-200 transition-all border border-slate-200">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto px-4 -mt-16 pb-12 space-y-6">
            
            <div class="flex flex-col md:flex-row items-end justify-center gap-3 pt-10 mb-8 max-w-3xl mx-auto">
                
                <div v-if="filteredRankings[1]" 
                     class="w-full md:w-56 !bg-slate-500 rounded-t-[2rem] p-6 text-center shadow-xl h-52 flex flex-col justify-center relative order-2 md:order-1 border-b-8 !border-slate-700 z-0 group">
                    
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 w-12 h-12 !bg-white rounded-full border-4 !border-slate-200 flex items-center justify-center font-black !text-slate-600 shadow-md text-lg group-hover:scale-110 transition-transform">2</div>
                    
                    <div class="mt-5 !text-white">
                        <p class="text-[10px] font-black !text-slate-200 uppercase truncate mb-1">{{ filteredRankings[1].user.name }}</p>
                        <div class="text-5xl font-black tracking-tighter leading-none !text-white">{{ filteredRankings[1].total_score }}</div>
                        <span class="text-[9px] font-black !text-slate-100 uppercase tracking-[0.3em] mt-3 block !bg-slate-800/30 rounded-full py-1 mx-4">Silver</span>
                    </div>
                </div>

                <div v-if="filteredRankings[0]" 
                     class="w-full md:w-64 !bg-amber-500 rounded-t-[2.5rem] p-8 text-center shadow-2xl h-64 flex flex-col justify-center relative z-10 order-1 md:order-2 border-b-8 !border-amber-700 scale-110 group">
                    
                    <div class="absolute -top-7 left-1/2 -translate-x-1/2 w-14 h-14 !bg-white rounded-full border-4 !border-amber-200 flex items-center justify-center text-2xl shadow-lg !text-amber-600 animate-bounce">👑</div>
                    
                    <div class="mt-5 !text-white">
                        <p class="text-[10px] font-black !text-amber-100 uppercase truncate mb-1">{{ filteredRankings[0].user.name }}</p>
                        <div class="text-7xl font-black tracking-tighter leading-none !text-white">{{ filteredRankings[0].total_score }}</div>
                        <span class="text-[9px] font-black !text-amber-100 uppercase tracking-[0.3em] mt-3 block !bg-amber-800/30 rounded-full py-1 mx-4">Champion</span>
                    </div>
                </div>

                <div v-if="filteredRankings[2]" 
                     class="w-full md:w-56 !bg-orange-600 rounded-t-[2rem] p-6 text-center shadow-xl h-52 flex flex-col justify-center relative order-3 border-b-8 !border-orange-800 z-0 group">
                    
                    <div class="absolute -top-5 left-1/2 -translate-x-1/2 w-12 h-12 !bg-white rounded-full border-4 !border-orange-200 flex items-center justify-center font-black !text-orange-600 shadow-md text-lg group-hover:scale-110 transition-transform">3</div>
                    
                    <div class="mt-5 !text-white">
                        <p class="text-[10px] font-black !text-orange-200 uppercase truncate mb-1">{{ filteredRankings[2].user.name }}</p>
                        <div class="text-5xl font-black tracking-tighter leading-none !text-white">{{ filteredRankings[2].total_score }}</div>
                        <span class="text-[9px] font-black !text-orange-100 uppercase tracking-[0.3em] mt-3 block !bg-orange-900/30 rounded-full py-1 mx-4">Bronze</span>
                    </div>
                </div>

            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row gap-4 items-center justify-between max-w-4xl mx-auto">
                <div class="relative w-full lg:w-64">
                    <input v-model="searchQuery" type="text" placeholder="CARI NAMA..." 
                           class="w-full bg-slate-50 border-slate-200 rounded-xl py-2 px-4 text-[9px] font-black tracking-widest focus:ring-[#334155] focus:border-[#334155] transition-all uppercase placeholder:text-slate-300">
                </div>

                <div class="flex items-center gap-4 w-full lg:w-auto justify-between lg:justify-end">
                    <div class="flex bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <button v-for="mode in ['Nasional', 'Provinsi']" :key="mode"
                                @click="regionFilter = mode"
                                :class="regionFilter === mode ? 'bg-[#334155] text-white' : 'text-slate-400'"
                                class="px-4 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all">
                            {{ mode }}
                        </button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Show:</span>
                        <div class="flex gap-1">
                            <button v-for="count in [10, 50, 100]" :key="count"
                                    @click="perPage = count"
                                    :class="perPage === count ? 'bg-[#334155] text-white' : 'bg-white text-slate-400 border-slate-200'"
                                    class="w-7 h-7 rounded-lg border text-[8px] font-black transition-all flex items-center justify-center">
                                {{ count }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-[2rem] shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center w-16">Rank</th>
                                <th class="px-4 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest">Peserta</th>
                                <th class="px-2 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">TWK</th>
                                <th class="px-2 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">TIU</th>
                                <th class="px-2 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">TKP</th>
                                <th class="px-4 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">Total</th>
                                <th class="px-6 py-4 text-[8px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="(rank, index) in displayedRankings" :key="rank.id" 
                                :class="{'bg-amber-50 ring-1 ring-inset ring-amber-100': rank.user_id === auth.user.id}"
                                class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3.5 text-center">
                                    <span :class="index < 3 ? 'text-slate-800 font-black' : 'text-slate-400 font-bold'" class="text-[10px]">#{{ index + 1 }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-slate-800 uppercase leading-none tracking-tight">{{ rank.user.name }}</span>
                                        <span class="text-[6.5px] font-bold text-slate-400 uppercase mt-1 leading-none tracking-tighter">
                                            {{ rank.user.province || 'NASIONAL' }} • {{ formatDate(rank.created_at) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-2 py-3.5 text-center text-[10px] font-bold text-slate-500">{{ rank.twk_score }}</td>
                                <td class="px-2 py-3.5 text-center text-[10px] font-bold text-slate-500">{{ rank.tiu_score }}</td>
                                <td class="px-2 py-3.5 text-center text-[10px] font-bold text-slate-500">{{ rank.tkp_score }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="text-[12px] font-black text-slate-900 tracking-tighter">{{ rank.total_score }}</span>
                                </td>
                                <td class="px-6 py-3.5 text-center">
                                    <div :class="isPassed(rank) ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-rose-400 bg-rose-50 border-rose-100'"
                                         class="inline-flex px-2 py-0.5 rounded-full text-[6.5px] font-black uppercase border tracking-widest leading-none">
                                        {{ isPassed(rank) ? 'Lulus' : 'Gagal' }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="displayedRankings.length === 0" class="text-center py-12">
                    <p class="text-slate-300 text-[9px] font-black uppercase tracking-[0.2em]">Data tidak ditemukan</p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Force No Italic */
* { font-style: normal !important; }
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>