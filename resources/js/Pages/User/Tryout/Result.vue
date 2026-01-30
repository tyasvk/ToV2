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

// --- LOGIKA TOMBOL TUTUP DIPERBAIKI DI SINI ---
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

    <div class="min-h-screen bg-[#F8FAFC] font-sans text-slate-600 pb-20">
        
        <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-30 shadow-sm/50 backdrop-blur-xl bg-white/90">
            <div class="max-w-6xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md shadow-indigo-200">TO</div>
                    <div>
                        <h1 class="font-bold text-slate-900 leading-tight">Hasil Ujian</h1>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">Computer Assisted Test</p>
                    </div>
                </div>
                
                <Link :href="closeRoute" class="text-xs font-bold text-slate-500 hover:text-slate-900 transition-colors uppercase tracking-wider flex items-center gap-2 px-3 py-1.5 hover:bg-slate-100 rounded-lg">
                    <span>Tutup</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </Link>
            </div>
        </nav>

        <main class="max-w-6xl mx-auto p-4 lg:p-8 space-y-6">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-2">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">{{ tryout.title }}</h2>
                    <p class="text-sm font-medium text-slate-400 flex items-center gap-2 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Selesai pada {{ formatDate(attempt.finished_at) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <div class="lg:col-span-4 space-y-5">
                    
                    <div class="relative overflow-hidden rounded-[2rem] shadow-xl transition-all group p-6 text-center flex flex-col items-center justify-center min-h-[260px]"
                         :class="isOverallPassed ? 'bg-emerald-500 text-white shadow-emerald-200' : 'bg-white text-slate-900 border border-slate-200 shadow-slate-200'">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                        <div class="mb-3 p-2.5 rounded-full inline-flex items-center justify-center shadow-sm scale-90"
                             :class="isOverallPassed ? 'bg-white/20 text-white' : 'bg-rose-50 text-rose-500'">
                            <svg v-if="isOverallPassed" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>

                        <h2 class="text-3xl font-black mb-0.5 tracking-tight" :class="isOverallPassed ? 'text-white' : 'text-slate-900'">
                            {{ isOverallPassed ? 'LULUS' : 'TIDAK LULUS' }}
                        </h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mb-5">Status Akhir</p>

                        <div class="w-full border-t pt-4 mt-auto" :class="isOverallPassed ? 'border-white/20' : 'border-slate-100'">
                            <p class="text-[10px] uppercase tracking-widest opacity-60 mb-1">Total Skor SKD</p>
                            <p class="text-5xl font-black tracking-tighter">{{ totalScore }}</p>
                        </div>
                    </div>

                    <Link :href="route('tryout.leaderboard', tryout.id)" 
                        class="group flex items-center justify-between bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all relative overflow-hidden">
                        
                        <div class="flex items-center gap-3 relative z-10">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Peringkat Kamu</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xl font-black text-slate-900">#{{ ranking.rank }}</span>
                                    <span class="text-xs font-medium text-slate-400">/ {{ ranking.total_participants }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>

                    <div class="grid grid-cols-1 gap-3">
                        <Link :href="route('tryout.review', attempt.id)" 
                              class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-indigo-600 text-white font-bold text-sm uppercase tracking-wide hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all hover:-translate-y-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Pembahasan
                        </Link>
                        
                        <Link :href="closeRoute" 
                              class="w-full block py-3.5 rounded-2xl bg-white border border-slate-200 text-slate-600 font-bold text-center text-sm uppercase tracking-wide hover:bg-slate-50 transition-all">
                            Menu Utama
                        </Link>
                    </div>

                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-6">
                        
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-900 text-lg">Statistik Nilai</h3>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Rincian Sub-Tes</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div v-for="(detail, index) in scoreDetails" :key="index" 
                                 class="p-5 rounded-2xl border transition-all hover:shadow-md flex flex-col justify-between min-h-[140px]"
                                 :class="detail.is_passed ? 'bg-emerald-50/40 border-emerald-100 hover:border-emerald-200' : 'bg-rose-50/40 border-rose-100 hover:border-rose-200'">
                                
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black border shrink-0"
                                         :class="detail.is_passed ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-rose-100 text-rose-600 border-rose-200'">
                                        {{ detail.category.substring(0, 3) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <h4 class="font-bold text-slate-700 text-sm truncate" :title="detail.category">{{ detail.category }}</h4>
                                        <p class="text-[10px] font-medium text-slate-400">PG: {{ detail.passing_grade }}</p>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-end justify-between mb-2">
                                        <span class="text-3xl font-black leading-none" :class="detail.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ detail.score }}
                                        </span>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                              :class="detail.is_passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                            {{ detail.is_passed ? 'Lolos' : 'Gagal' }}
                                        </span>
                                    </div>

                                    <div class="h-1.5 w-full bg-slate-200/50 rounded-full overflow-hidden relative">
                                        <div class="absolute top-0 bottom-0 w-0.5 bg-slate-400/50 z-10" style="left: 60%"></div>
                                        <div class="h-full rounded-full transition-all duration-1000 ease-out relative"
                                             :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"
                                             :style="{ width: getBarWidth(detail.score, (index === 2 ? 225 : (index === 1 ? 175 : 150))) }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-slate-50 rounded-xl border border-slate-100 flex gap-3 items-center">
                            <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold shrink-0">i</div>
                            <p class="text-xs text-slate-500 leading-tight">
                                Kelulusan ditentukan jika nilai Anda memenuhi <strong>semua</strong> ambang batas (Passing Grade).
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </main>
    </div>
</template>