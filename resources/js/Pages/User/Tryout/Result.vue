<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    attempt: Object,
    tryout: Object,
    totalScore: Number,
    scoreDetails: Array,
    ranking: Object,
    timeStats: Object // Data waktu dari backend
});

// Helper untuk format detik menjadi "Xm Ys" atau "Xj Ym Zs"
const formatTime = (seconds) => {
    if (!seconds || seconds <= 0) return '0d';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    
    if (h > 0) return `${h}j ${m}m ${s}d`;
    if (m > 0) return `${m}m ${s}d`;
    return `${s} detik`;
};
</script>

<template>
    <Head title="Hasil Tryout" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-700 pb-16">
        
        <nav class="bg-white border-b border-slate-200 px-4 md:px-6 py-3 sticky top-0 z-50 shadow-sm backdrop-blur-xl bg-white/90">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center border border-blue-100 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-medium text-slate-800 text-sm leading-none">Rapor Kelulusan</h1>
                        <p class="text-[10px] text-slate-500 mt-1 uppercase tracking-wide truncate max-w-[200px] sm:max-w-md">
                            {{ tryout.title }}
                        </p>
                    </div>
                </div>
                
                <Link :href="route('tryout.index')" class="text-[11px] font-medium text-slate-600 hover:text-blue-600 transition-colors uppercase tracking-wide flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:border-blue-200 hover:bg-blue-50 rounded-md shadow-sm">
                    Kembali
                </Link>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-[80px] opacity-30 pointer-events-none" :class="attempt.is_passed ? 'bg-emerald-400' : 'bg-rose-400'"></div>

                <div class="p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                    <div class="text-center md:text-left">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-widest mb-3 border shadow-sm" :class="attempt.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
                            <svg v-if="attempt.is_passed" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            {{ attempt.is_passed ? 'Selamat, Anda Memenuhi Passing Grade!' : 'Maaf, Anda Belum Memenuhi Passing Grade' }}
                        </div>
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">{{ tryout.title }}</h2>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Sistem CAT (TWK, TIU, TKP)</p>
                    </div>

                    <div class="shrink-0 flex flex-col items-center justify-center w-32 h-32 rounded-full border-4 shadow-inner bg-white" :class="attempt.is_passed ? 'border-emerald-400' : 'border-rose-400'">
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">Total Skor</span>
                        <span class="text-4xl font-black tabular-nums tracking-tighter mt-1" :class="attempt.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                            {{ totalScore }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 border-t border-slate-100 bg-slate-50/50 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                    <div class="p-4 text-center border-r border-slate-100 md:border-r-0">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Peringkat Anda</p>
                        <p class="text-xl font-bold text-slate-800 tabular-nums">#{{ ranking.rank }} <span class="text-xs font-medium text-slate-400">/ {{ ranking.total_participants }}</span></p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Target Soal</p>
                        <p class="text-xl font-bold text-slate-800 tabular-nums">{{ timeStats.total_questions }} <span class="text-xs font-medium text-slate-400">Soal</span></p>
                    </div>
                    <div class="p-4 text-center border-r border-slate-100 md:border-r-0">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Total Waktu</p>
                        <p class="text-xl font-bold text-blue-600 tabular-nums">{{ formatTime(timeStats.total_seconds) }}</p>
                    </div>
                    <div class="p-4 text-center">
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-1">Rata-rata Per Soal</p>
                        <p class="text-xl font-bold text-orange-500 tabular-nums">{{ formatTime(timeStats.average_seconds) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-3 mb-5">Rincian Nilai Ambang Batas</h3>
                
                <div class="space-y-5">
                    <div v-for="(detail, index) in scoreDetails" :key="index" class="bg-slate-50 rounded-xl p-4 border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1.5">
                                <h4 class="font-semibold text-slate-800 text-sm">{{ detail.category }}</h4>
                                <span class="text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wider" :class="detail.is_passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                    {{ detail.is_passed ? 'Lulus' : 'Gagal' }}
                                </span>
                            </div>
                            
                            <div class="w-full bg-slate-200 rounded-full h-2.5 mb-1.5 relative overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-1000" 
                                     :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'" 
                                     :style="`width: ${Math.min(100, (detail.score / (detail.passing_grade * 1.5)) * 100)}%`">
                                </div>
                                <div class="absolute top-0 bottom-0 border-l-2 border-slate-800 z-10" :style="`left: ${(detail.passing_grade / (detail.passing_grade * 1.5)) * 100}%`"></div>
                            </div>
                            <p class="text-[10px] text-slate-500 font-medium">Garis hitam menunjukkan Passing Grade ({{ detail.passing_grade }})</p>
                        </div>

                        <div class="shrink-0 text-right md:text-center md:w-28 flex flex-row md:flex-col justify-between items-center md:border-l border-slate-200 md:pl-4">
                            <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Skor Anda</span>
                            <span class="text-2xl font-black tabular-nums tracking-tight" :class="detail.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                {{ detail.score }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <Link :href="route('tryout.review', attempt.id)" class="flex-1 flex justify-center items-center px-6 py-3.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors uppercase tracking-wider">
                    Lihat Pembahasan Soal
                </Link>
                <Link :href="route('tryout.leaderboard', tryout.id)" class="flex-1 flex justify-center items-center px-6 py-3.5 bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl shadow-sm transition-colors uppercase tracking-wider">
                    Lihat Papan Peringkat
                </Link>
            </div>

        </main>
    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>