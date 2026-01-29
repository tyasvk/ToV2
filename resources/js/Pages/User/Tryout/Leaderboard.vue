<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object,
    rankings: Array,
    my_rank: Object
});
</script>

<template>
    <Head :title="'Leaderboard ' + tryout.title" />

    <AuthenticatedLayout>
        <div class="bg-indigo-950 min-h-screen pb-24 text-white">
            <div class="bg-indigo-900 pt-8 pb-12 px-6 rounded-b-[3rem] shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500 rounded-full blur-[100px] opacity-20 -mr-20 -mt-20"></div>
                <Link :href="route('tryout.index')" class="relative z-10 text-indigo-300 text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                    ← Kembali
                </Link>
                <h2 class="relative z-10 text-2xl font-black tracking-tighter uppercase italic">{{ tryout.title }}</h2>
                <p class="relative z-10 text-[10px] text-indigo-300 font-bold uppercase tracking-widest mt-1">Peringkat Nasional Peserta</p>
            </div>

            <div class="px-4 -mt-8 flex items-end justify-center gap-2 mb-10 relative z-20">
                <div v-if="rankings[1]" class="flex flex-col items-center flex-1">
                    <div class="w-12 h-12 bg-slate-300 rounded-2xl mb-2 flex items-center justify-center border-2 border-white/20 shadow-lg text-lg">🥈</div>
                    <div class="bg-white/10 backdrop-blur-md w-full rounded-t-2xl p-3 text-center border-x border-t border-white/10 h-24 flex flex-col justify-end">
                        <p class="text-[9px] font-black truncate uppercase">{{ rankings[1].name }}</p>
                        <p class="text-xs font-black text-indigo-400">{{ rankings[1].score }}</p>
                    </div>
                </div>

                <div v-if="rankings[0]" class="flex flex-col items-center flex-1">
                    <div class="w-16 h-16 bg-amber-400 rounded-3xl mb-2 flex items-center justify-center border-4 border-amber-300 shadow-xl shadow-amber-500/20 text-2xl animate-bounce">👑</div>
                    <div class="bg-indigo-600 w-full rounded-t-3xl p-4 text-center shadow-2xl h-32 flex flex-col justify-end ring-4 ring-indigo-500/30">
                        <p class="text-[10px] font-black truncate uppercase">{{ rankings[0].name }}</p>
                        <p class="text-lg font-black text-white">{{ rankings[0].score }}</p>
                    </div>
                </div>

                <div v-if="rankings[2]" class="flex flex-col items-center flex-1">
                    <div class="w-12 h-12 bg-orange-400/80 rounded-2xl mb-2 flex items-center justify-center border-2 border-white/20 shadow-lg text-lg">🥉</div>
                    <div class="bg-white/10 backdrop-blur-md w-full rounded-t-2xl p-3 text-center border-x border-t border-white/10 h-20 flex flex-col justify-end">
                        <p class="text-[9px] font-black truncate uppercase">{{ rankings[2].name }}</p>
                        <p class="text-xs font-black text-indigo-400">{{ rankings[2].score }}</p>
                    </div>
                </div>
            </div>

            <div class="px-4 space-y-3">
                <div v-for="user in rankings.slice(3)" :key="user.rank" 
                    :class="user.is_me ? 'bg-indigo-600 ring-2 ring-indigo-400' : 'bg-white/5'"
                    class="flex items-center justify-between p-4 rounded-2xl border border-white/5 shadow-sm">
                    <div class="flex items-center gap-4">
                        <span class="text-[10px] font-black text-white/30 w-5">#{{ user.rank }}</span>
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center font-black text-xs uppercase">{{ user.name.substring(0,2) }}</div>
                        <p class="text-[10px] font-black uppercase tracking-wide truncate w-32">{{ user.name }}</p>
                    </div>
                    <p class="text-sm font-black text-indigo-400" :class="{'text-white': user.is_me}">{{ user.score }}</p>
                </div>
            </div>

            <div v-if="my_rank" class="fixed bottom-6 left-6 right-6 z-[100] animate-in slide-in-from-bottom-10 duration-500">
                <div class="bg-emerald-500 p-5 rounded-[2rem] shadow-2xl shadow-emerald-500/40 border border-emerald-400 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center font-black text-xs">#{{ my_rank.rank }}</div>
                        <div>
                            <p class="text-[8px] font-black uppercase text-emerald-900/60 tracking-widest">Peringkat Kamu</p>
                            <p class="text-[11px] font-black uppercase">{{ my_rank.name }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[8px] font-black uppercase text-emerald-900/60 tracking-widest">Skor Akhir</p>
                        <p class="text-lg font-black">{{ my_rank.score }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>