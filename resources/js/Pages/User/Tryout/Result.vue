<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.locale('id');
dayjs.extend(relativeTime);

const props = defineProps({
    attempt: Object,
    tryout: Object,
    totalScore: Number,
    scoreDetails: Array,
    ranking: Object
});

const isOverallPassed = computed(() => props.attempt.status === 'lulus');

const formatDate = (dateString) => {
    return dayjs(dateString).format('DD MMMM YYYY • HH:mm WIB');
};

const getBarWidth = (score, max) => {
    let percentage = (score / max) * 100;
    return `${Math.min(percentage, 100)}%`;
};

// --- LOGIKA TOMBOL TUTUP ---
const closeRoute = computed(() => {
    // Jika tipe Tryout Akbar, kembali ke Index Akbar
    if (props.tryout.type === 'akbar') {
        return route('tryout-akbar.index');
    }
    // Jika Umum/Regular, kembali ke Index Umum
    return route('tryout.index');
});
</script>

<template>
    <Head :title="'Hasil - ' + tryout.title" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-700 pb-16">
        
        <nav class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 sticky top-0 z-30 shadow-sm backdrop-blur-xl bg-white/90">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center text-white text-xs font-medium shadow-sm">TO</div>
                    <div>
                        <h1 class="font-medium text-slate-800 leading-none">Hasil Ujian</h1>
                        <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wide">Computer Assisted Test</p>
                    </div>
                </div>
                
                <Link :href="closeRoute" class="text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wide flex items-center gap-1.5 px-3 py-1.5 hover:bg-slate-100 rounded-md">
                    <span class="hidden sm:inline">Tutup</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </Link>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto p-4 sm:p-6 md:p-8 space-y-6">

            <div class="flex flex-col gap-1 text-center sm:text-left pt-2 pb-2">
                <h2 class="text-xl sm:text-2xl font-medium text-slate-900 tracking-wide">{{ tryout.title }}</h2>
                <p class="text-xs font-medium text-slate-500 flex items-center justify-center sm:justify-start gap-1.5 mt-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Selesai pada {{ formatDate(attempt.finished_at) }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                
                <div class="md:col-span-5 lg:col-span-4 space-y-4">
                    
                    <div class="relative overflow-hidden rounded-2xl shadow-sm transition-all p-6 text-center flex flex-col items-center justify-center min-h-[220px]"
                         :class="isOverallPassed ? 'bg-emerald-500 text-white' : 'bg-white text-slate-800 border border-slate-200'">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                        <div class="mb-3 p-2.5 rounded-full inline-flex items-center justify-center shadow-sm"
                             :class="isOverallPassed ? 'bg-white/20 text-white' : 'bg-rose-50 border border-rose-100 text-rose-500'">
                            <svg v-if="isOverallPassed" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>

                        <h2 class="text-2xl font-medium mb-1 tracking-wide" :class="isOverallPassed ? 'text-white' : 'text-slate-800'">
                            {{ isOverallPassed ? 'LULUS' : 'TIDAK LULUS' }}
                        </h2>
                        <p class="text-[10px] font-medium uppercase tracking-widest opacity-75 mb-4">Status Akhir</p>

                        <div class="w-full border-t pt-4 mt-auto" :class="isOverallPassed ? 'border-white/20' : 'border-slate-100'">
                            <p class="text-[10px] font-medium uppercase tracking-wider opacity-75 mb-1">Total Skor SKD</p>
                            <p class="text-4xl font-medium tabular-nums">{{ totalScore }}</p>
                        </div>
                    </div>

                    <Link :href="route('tryout.leaderboard', tryout.id)" 
                        class="group flex items-center justify-between bg-white rounded-xl p-4 border border-slate-200 shadow-sm hover:border-blue-300 transition-colors relative overflow-hidden">
                        
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wide mb-0.5">Peringkat Kamu</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-medium text-slate-800 tabular-nums">#{{ ranking.rank }}</span>
                                    <span class="text-xs font-medium text-slate-400 tabular-nums">/ {{ ranking.total_participants }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-slate-400 group-hover:text-blue-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-3 pt-2">
                        <Link :href="route('tryout.review', attempt.id)" 
                              class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-blue-600 text-white font-medium text-[11px] sm:text-xs uppercase tracking-wide hover:bg-blue-700 transition-colors shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Pembahasan Ujian
                        </Link>
                        
                        <Link :href="closeRoute" 
                              class="w-full flex items-center justify-center py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-medium text-[11px] sm:text-xs uppercase tracking-wide hover:bg-slate-50 transition-colors shadow-sm">
                            Kembali ke Menu
                        </Link>
                    </div>

                </div>

                <div class="md:col-span-7 lg:col-span-8">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 h-full">
                        
                        <div class="flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-slate-800 text-base">Statistik Nilai</h3>
                                <p class="text-[10px] text-slate-400 uppercase tracking-wide font-medium">Rincian Per Sub-Tes</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(detail, index) in scoreDetails" :key="index" 
                                 class="p-4 rounded-xl border transition-all flex flex-col min-h-[130px] justify-between relative overflow-hidden"
                                 :class="detail.is_passed ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100'">
                                
                                <div class="flex items-start gap-2.5 mb-2 relative z-10">
                                    <div class="w-8 h-8 rounded-md flex items-center justify-center text-[10px] font-medium border shrink-0"
                                         :class="detail.is_passed ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-rose-100 text-rose-700 border-rose-200'">
                                        {{ detail.category.substring(0, 3) }}
                                    </div>
                                    <div class="overflow-hidden mt-0.5">
                                        <h4 class="font-medium text-slate-700 text-[11px] uppercase tracking-wide truncate" :title="detail.category">{{ detail.category }}</h4>
                                        <p class="text-[10px] font-medium text-slate-500 mt-0.5">PG: {{ detail.passing_grade }}</p>
                                    </div>
                                </div>

                                <div class="relative z-10">
                                    <div class="flex items-end justify-between mb-2">
                                        <span class="text-2xl font-medium leading-none tabular-nums" :class="detail.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ detail.score }}
                                        </span>
                                        <span class="text-[9px] font-medium uppercase px-2 py-0.5 rounded border"
                                              :class="detail.is_passed ? 'bg-emerald-100 border-emerald-200 text-emerald-700' : 'bg-rose-100 border-rose-200 text-rose-700'">
                                            {{ detail.is_passed ? 'Lolos' : 'Gagal' }}
                                        </span>
                                    </div>

                                    <div class="h-1.5 w-full bg-slate-200/70 rounded-full overflow-hidden relative">
                                        <div class="absolute top-0 bottom-0 w-[1px] bg-slate-500 z-10" :style="{ left: getBarWidth(detail.passing_grade, (index === 2 ? 225 : (index === 1 ? 175 : 150))) }"></div>
                                        
                                        <div class="h-full rounded-full transition-all duration-1000 ease-out"
                                             :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"
                                             :style="{ width: getBarWidth(detail.score, (index === 2 ? 225 : (index === 1 ? 175 : 150))) }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-3 sm:p-4 bg-slate-50 rounded-xl border border-slate-100 flex gap-3 items-start sm:items-center">
                            <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-[10px] font-medium shrink-0 mt-0.5 sm:mt-0">i</div>
                            <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed font-normal">
                                Status kelulusan didapatkan hanya jika nilai Anda memenuhi nilai ambang batas (Passing Grade / Garis vertikal) pada seluruh tipe sub-tes.
                            </p>
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