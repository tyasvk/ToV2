<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    attempts: Array,
});

const search = ref('');

const filteredAttempts = computed(() => {
    if (!search.value) return props.attempts;
    return props.attempts.filter(attempt => 
        attempt.tryout.title.toLowerCase().includes(search.value.toLowerCase())
    );
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Riwayat Ujian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-center md:text-left">
                    <h2 class="font-black text-2xl text-slate-900 tracking-tighter uppercase">Riwayat Ujian</h2>
                    <div class="flex items-center gap-2 mt-1 justify-center md:justify-start">
                        <span class="w-1.5 h-1.5 bg-black rounded-full"></span>
                        <p class="text-[10px] text-slate-400 font-black tracking-[0.2em] uppercase">Arsip Performa Anda</p>
                    </div>
                </div>
                
                <div class="flex gap-4 items-center">
                    <div class="px-6 py-2 bg-black text-white rounded-sm text-center min-w-[120px]">
                        <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Total Sesi</div>
                        <div class="text-xl font-black leading-none">{{ attempts.length }}</div>
                    </div>
                    <div class="px-6 py-2 border border-black rounded-sm text-center min-w-[120px]">
                        <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Best Score</div>
                        <div class="text-xl font-black text-slate-900 leading-none">
                            {{ attempts.length > 0 ? Math.max(...attempts.map(a => a.total_score)) : 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8">
                    <div class="relative group max-w-2xl mx-auto">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform group-focus-within:scale-110">
                            <svg class="h-5 w-5 text-gray-400 group-focus-within:text-black transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="CARI BERDASARKAN NAMA ATAU ID UJIAN..." 
                            class="w-full bg-white border border-gray-200 focus:border-black py-5 pl-12 pr-20 text-[11px] font-black tracking-widest text-slate-900 placeholder-gray-300 focus:ring-4 focus:ring-gray-900/5 transition-all rounded-xl shadow-sm"
                        >
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <kbd class="hidden sm:inline-flex items-center px-2 py-1 border border-gray-200 rounded text-[9px] font-black text-gray-300 bg-gray-50 group-focus-within:border-black group-focus-within:text-black transition-all">
                                SEARCH_CMD
                            </kbd>
                        </div>
                    </div>
                    
                    <div v-if="search" class="text-center mt-3 animate-pulse">
                        <span class="text-[9px] font-black text-slate-400 tracking-[0.3em] uppercase">
                            MENAMPILKAN {{ filteredAttempts.length }} HASIL DARI "{{ search }}"
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div 
                        v-for="attempt in filteredAttempts" 
                        :key="attempt.id" 
                        class="bg-white border border-gray-100 p-6 flex flex-col lg:flex-row items-center gap-6 hover:shadow-2xl hover:shadow-gray-200/50 transition-all duration-300"
                    >
                        <div class="w-full lg:w-48 shrink-0">
                            <div class="text-[9px] font-black text-gray-400 uppercase mb-1">{{ formatDate(attempt.created_at) }}</div>
                            <div class="text-xs font-mono font-bold text-gray-300">#{{ String(attempt.id).padStart(6, '0') }}</div>
                        </div>

                        <div class="flex-1 w-full">
                            <h3 class="text-lg font-black text-slate-900 uppercase leading-tight tracking-tight">{{ attempt.tryout.title }}</h3>
                        </div>

                        <div class="flex gap-8 items-center bg-gray-50/50 px-6 py-3 rounded-sm border border-gray-100/50">
                            <div class="text-center">
                                <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">TWK</div>
                                <div class="font-black text-slate-900">{{ attempt.twk_score }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">TIU</div>
                                <div class="font-black text-slate-900">{{ attempt.tiu_score }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">TKP</div>
                                <div class="font-black text-slate-900">{{ attempt.tkp_score }}</div>
                            </div>
                            <div class="text-center border-l border-gray-200 pl-8">
                                <div class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">TOTAL</div>
                                <div class="text-xl font-black text-slate-900">{{ attempt.total_score }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 w-full lg:w-auto shrink-0">
                            <a :href="`/results/${attempt.id}/certificate`" target="_blank" class="flex-1 lg:flex-none px-4 py-2 border border-gray-200 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition">
                                Sertifikat
                            </a>
                            <Link :href="route('tryout.leaderboard', attempt.tryout_id)" class="flex-1 lg:flex-none px-4 py-2 border border-black text-[10px] font-black uppercase tracking-widest hover:bg-black hover:text-white transition">
                                Ranking
                            </Link>
                            <Link :href="route('tryout.review', attempt.id)" class="flex-1 lg:flex-none px-4 py-2 bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition">
                                Review
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="attempts.length > 0" class="mt-12 text-center border-t border-gray-100 pt-8">
                    <span class="text-[10px] font-black text-gray-300 uppercase tracking-[0.5em]">CPNS Nusantara Learning Center</span>
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