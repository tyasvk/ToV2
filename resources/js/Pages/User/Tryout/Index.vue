<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tryouts: Array,
    stats: Object,
});

const search = ref('');

// Filter & Sorting: Mengurutkan tryout terbaru ke paling atas
const filteredTryouts = computed(() => {
    return (props.tryouts || [])
        .filter(t => t.title?.toLowerCase().includes(search.value.toLowerCase()))
        .sort((a, b) => b.id - a.id);
});
</script>

<template>
    <Head title="Tryout Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-3 -mt-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-white p-4 rounded-[1.5rem] border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <span class="block text-[7px] font-black text-slate-400 uppercase tracking-widest">Selesai Dikerjakan</span>
                            <span class="text-lg font-black text-slate-900 leading-none">{{ stats?.total_completed ?? 0 }} <small class="text-[9px] text-slate-300 font-medium uppercase text-xs">Kali</small></span>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-[1.5rem] border border-slate-100 shadow-sm flex items-center gap-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div>
                            <span class="block text-[7px] font-black text-slate-400 uppercase tracking-widest">Rata-Rata Skor</span>
                            <span class="text-lg font-black text-slate-900 leading-none">{{ stats?.average_score ?? 0 }} <small class="text-[9px] text-slate-300 font-medium uppercase text-xs">Poin</small></span>
                        </div>
                    </div>
                </div>

                <div class="relative group">
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Cari paket simulasi ujian..." 
                        class="w-full bg-white border border-slate-100 py-3 pl-12 rounded-xl text-sm font-bold shadow-sm focus:ring-4 focus:ring-indigo-500/5 transition-all placeholder-slate-300"
                    >
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-200 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
        </template>

        <div class="pt-1 pb-10 bg-slate-50/30 min-h-screen">
            <div class="max-w-7xl mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                    <div v-for="tryout in filteredTryouts" :key="tryout.id" class="group bg-white rounded-[1.8rem] border border-slate-100 hover:border-black transition-all duration-500 flex flex-col shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 overflow-hidden p-5 md:p-6">
                        
                        <div class="flex justify-between items-start mb-3 md:mb-4">
                            <div class="invisible text-[9px]">Spacer</div> 
                            
                            <div v-if="tryout.high_score" class="flex flex-col items-end">
                                <span class="text-[7px] font-black text-indigo-500 uppercase tracking-widest mb-0.5 italic">Highest_Score</span>
                                <span class="text-lg font-black text-indigo-600 leading-none">{{ tryout.high_score }}</span>
                            </div>
                        </div>

                        <h3 class="text-base md:text-lg font-black text-slate-900 leading-tight uppercase tracking-tight group-hover:text-indigo-600 transition-colors mb-3 md:mb-5">
                            {{ tryout.title }}
                        </h3>

                        <div class="grid grid-cols-2 gap-2.5 mb-4 md:mb-5">
                            <div class="bg-slate-50/50 p-2.5 md:p-3 rounded-xl border border-slate-100/50">
                                <span class="block text-[7px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Waktu</span>
                                <span class="text-xs font-black text-slate-900 tracking-tighter">110 <small class="text-[9px] font-medium uppercase">Menit</small></span>
                            </div>
                            <div class="bg-slate-50/50 p-2.5 md:p-3 rounded-xl border border-slate-100/50">
                                <span class="block text-[7px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Materi</span>
                                <span class="text-xs font-black text-slate-900 tracking-tighter">{{ tryout.questions_count }} <small class="text-[9px] font-medium uppercase">Soal</small></span>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2">
                                <div v-if="tryout.high_score" class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                                <span v-if="tryout.high_score" class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Sudah Diikuti</span>
                            </div>

                            <Link :href="'/tryouts/' + tryout.id" class="px-6 md:px-7 py-3 md:py-3.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-100 active:scale-95">
                                Mulai
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="filteredTryouts.length === 0" class="py-20 text-center">
                    <p class="text-[10px] font-black text-slate-200 uppercase tracking-[0.5em]">No_Packages_Found</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-all {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>