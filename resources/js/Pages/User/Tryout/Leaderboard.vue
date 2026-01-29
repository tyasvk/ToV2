<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { debounce } from 'lodash'; // Pastikan lodash ada (bawaan Laravel/Inertia biasanya ada)

const props = defineProps({
    tryout: Object,
    rankings: Array,
    my_rank: Object,
    filters: Object,
});

// State untuk Filter
const search = ref(props.filters.search || '');
const scope = ref(props.filters.scope || 'nasional');

// Watcher untuk Search & Filter (Auto Reload)
const updateParams = debounce(() => {
    router.get(route('tryout.leaderboard', props.tryout.id), { 
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

// Top 3 Logic
const topThree = computed(() => {
    // Hanya ambil top 3 jika tidak sedang searching (agar podium valid)
    if (search.value) return [];
    
    const top = [];
    if (props.rankings[1]) top.push(props.rankings[1]); // #2 Silver
    if (props.rankings[0]) top.push(props.rankings[0]); // #1 Gold
    if (props.rankings[2]) top.push(props.rankings[2]); // #3 Bronze
    return top;
});

// Sisanya (Mulai dari index 3 jika tidak search, atau semua jika search)
const listRankings = computed(() => {
    if (search.value) return props.rankings;
    return props.rankings.slice(3);
});
</script>

<template>
    <Head title="Leaderboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('tryout.my')" class="p-2 rounded-full hover:bg-slate-100 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">Leaderboard</h2>
                    <p class="text-xs text-slate-500">{{ tryout.title }}</p>
                </div>
            </div>
        </template>

        <div class="py-8 md:py-12 bg-slate-50 min-h-screen relative">
            <div class="absolute top-0 left-0 right-0 h-64 bg-gradient-to-b from-[#004a87] to-slate-50 z-0"></div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    
                    <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto">
                        <button 
                            @click="scope = 'nasional'"
                            class="px-6 py-2 rounded-lg text-sm font-bold transition-all w-1/2 md:w-auto"
                            :class="scope === 'nasional' ? 'bg-white text-[#004a87] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Nasional
                        </button>
                        <button 
                            @click="scope = 'provinsi'"
                            class="px-6 py-2 rounded-lg text-sm font-bold transition-all w-1/2 md:w-auto"
                            :class="scope === 'provinsi' ? 'bg-white text-[#004a87] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Provinsi {{ filters.user_province ? `(${filters.user_province})` : '' }}
                        </button>
                    </div>

                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search"
                            type="text" 
                            class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#004a87] focus:border-[#004a87] sm:text-sm transition duration-150 ease-in-out" 
                            placeholder="Cari nama peserta..." 
                        />
                    </div>
                </div>

                <div v-if="topThree.length > 0 && !search" class="flex flex-wrap justify-center items-end gap-4 mb-12">
                    
                    <div v-if="rankings[1]" class="order-1 md:order-none flex flex-col items-center">
                        <div class="relative">
                            <img :src="rankings[1].avatar" class="w-16 h-16 rounded-full border-4 border-slate-300 shadow-lg bg-white">
                            <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-slate-300 text-slate-800 text-xs font-black px-2 py-0.5 rounded-full shadow-sm border border-white">#2</div>
                        </div>
                        <div class="mt-4 bg-white/90 backdrop-blur-sm p-4 rounded-xl shadow-sm text-center border-t-4 border-slate-300 w-36 md:w-44">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ rankings[1].name }}</h3>
                            <div class="text-[#004a87] font-black text-xl">{{ rankings[1].score }}</div>
                            <span v-if="rankings[1].is_passed" class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Lulus</span>
                        </div>
                    </div>

                    <div v-if="rankings[0]" class="order-2 md:order-none flex flex-col items-center -mt-8 md:-mt-12 z-20">
                        <div class="relative">
                            <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 text-yellow-300 drop-shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </div>
                            <img :src="rankings[0].avatar" class="w-24 h-24 rounded-full border-4 border-yellow-400 shadow-xl bg-white">
                            <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-yellow-900 text-sm font-black px-3 py-0.5 rounded-full shadow-sm border border-white">#1</div>
                        </div>
                        <div class="mt-5 bg-white p-5 rounded-xl shadow-lg text-center border-t-4 border-yellow-400 w-44 md:w-52 transform scale-105">
                            <h3 class="font-bold text-slate-800 text-base truncate">{{ rankings[0].name }}</h3>
                            <div class="text-[#004a87] font-black text-3xl">{{ rankings[0].score }}</div>
                            <span v-if="rankings[0].is_passed" class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Lulus SKD</span>
                            <div class="flex justify-center gap-1 mt-2 pt-2 border-t border-slate-100 text-[10px] text-slate-500">
                                <span>TWK {{ rankings[0].twk }}</span> •
                                <span>TIU {{ rankings[0].tiu }}</span> •
                                <span>TKP {{ rankings[0].tkp }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="rankings[2]" class="order-3 md:order-none flex flex-col items-center">
                        <div class="relative">
                            <img :src="rankings[2].avatar" class="w-16 h-16 rounded-full border-4 border-orange-300 shadow-lg bg-white">
                            <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-orange-300 text-orange-900 text-xs font-black px-2 py-0.5 rounded-full shadow-sm border border-white">#3</div>
                        </div>
                        <div class="mt-4 bg-white/90 backdrop-blur-sm p-4 rounded-xl shadow-sm text-center border-t-4 border-orange-300 w-36 md:w-44">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ rankings[2].name }}</h3>
                            <div class="text-[#004a87] font-black text-xl">{{ rankings[2].score }}</div>
                            <span v-if="rankings[2].is_passed" class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">Lulus</span>
                        </div>
                    </div>
                </div>

                <div v-if="my_rank" class="mb-6 sticky top-20 z-30">
                    <div class="bg-[#004a87] text-white p-4 rounded-xl shadow-lg flex items-center justify-between border border-blue-400">
                        <div class="flex items-center gap-4">
                            <div class="bg-white/20 w-10 h-10 rounded-full flex items-center justify-center font-black text-lg backdrop-blur-sm">
                                {{ my_rank.rank }}
                            </div>
                            <div>
                                <div class="text-xs text-blue-200 uppercase font-bold tracking-wider">
                                    Peringkat {{ scope === 'nasional' ? 'Nasional' : 'Provinsi' }} Anda
                                </div>
                                <div class="font-bold text-white flex items-center gap-2">
                                    {{ my_rank.name }} (Saya)
                                    <span v-if="my_rank.is_passed" class="text-[9px] bg-emerald-500 text-white px-1.5 py-0.5 rounded font-bold">Lulus</span>
                                    <span v-else class="text-[9px] bg-red-500 text-white px-1.5 py-0.5 rounded font-bold">Tidak Lulus</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-black">{{ my_rank.score }}</div>
                            <div class="text-[10px] text-blue-200">{{ my_rank.duration }}</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="grid grid-cols-12 gap-4 p-4 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <div class="col-span-2 md:col-span-1 text-center">#</div>
                        <div class="col-span-6 md:col-span-5">Peserta</div>
                        <div class="hidden md:block md:col-span-4 text-center">Rincian Skor</div>
                        <div class="col-span-4 md:col-span-2 text-right">Total</div>
                    </div>

                    <div v-if="listRankings.length > 0">
                        <div v-for="user in listRankings" :key="user.rank" 
                            class="grid grid-cols-12 gap-4 p-4 items-center border-b border-slate-100 last:border-0 hover:bg-slate-50 transition"
                            :class="{'bg-blue-50/50': user.is_me}"
                        >
                            <div class="col-span-2 md:col-span-1 text-center">
                                <span class="font-bold text-slate-500">{{ user.rank }}</span>
                            </div>

                            <div class="col-span-6 md:col-span-5 flex items-center gap-3">
                                <img :src="user.avatar" class="w-8 h-8 rounded-full border border-slate-200 bg-white" alt="">
                                <div>
                                    <div class="text-sm font-bold text-slate-800 line-clamp-1 flex items-center gap-1">
                                        {{ user.name }}
                                        <span v-if="user.is_me" class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">Anda</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1">
                                        <span v-if="user.is_passed" class="text-emerald-600 font-bold">Lulus</span>
                                        <span v-else class="text-red-500 font-bold">Tidak Lulus</span>
                                        <span class="hidden md:inline">• {{ user.duration }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden md:block md:col-span-4 text-center">
                                <div class="flex justify-center gap-4">
                                    <div class="text-center w-10">
                                        <span class="block text-[9px] text-slate-400 font-bold">TWK</span>
                                        <span class="text-xs font-bold" :class="user.twk >= 65 ? 'text-slate-700' : 'text-red-500'">{{ user.twk }}</span>
                                    </div>
                                    <div class="text-center w-10">
                                        <span class="block text-[9px] text-slate-400 font-bold">TIU</span>
                                        <span class="text-xs font-bold" :class="user.tiu >= 80 ? 'text-slate-700' : 'text-red-500'">{{ user.tiu }}</span>
                                    </div>
                                    <div class="text-center w-10">
                                        <span class="block text-[9px] text-slate-400 font-bold">TKP</span>
                                        <span class="text-xs font-bold" :class="user.tkp >= 166 ? 'text-slate-700' : 'text-red-500'">{{ user.tkp }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-4 md:col-span-2 text-right">
                                <div class="text-lg font-black text-[#004a87]">{{ user.score }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-12 text-center text-slate-500 text-sm bg-slate-50">
                        <div class="mb-2">🤔</div>
                        Data peserta tidak ditemukan.
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>