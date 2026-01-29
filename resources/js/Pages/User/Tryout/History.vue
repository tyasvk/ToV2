<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    attempts: Array,
});

// Format Tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Hitung Statistik
const stats = computed(() => {
    if (!props.attempts.length) return { total: 0, highest: 0, average: 0 };
    
    const scores = props.attempts.map(a => a.total_score);
    const total = scores.length;
    const highest = Math.max(...scores);
    const sum = scores.reduce((a, b) => a + b, 0);
    const average = Math.round(sum / total);

    return { total, highest, average };
});

// Cek Kelulusan (Passing Grade: TWK 65, TIU 80, TKP 166)
const isPassed = (attempt) => {
    return attempt.twk_score >= 65 && attempt.tiu_score >= 80 && attempt.tkp_score >= 166;
};
</script>

<template>
    <Head title="Riwayat Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Tryout Saya</h2>
        </template>

        <div class="py-8 md:py-12 bg-slate-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Ujian Dikerjakan</span>
                        <div class="flex items-end gap-2">
                            <h3 class="text-3xl font-black text-slate-800">{{ stats.total }}</h3>
                            <span class="text-sm font-bold text-slate-500 mb-1.5">Kali</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-[#004a87] to-blue-700 p-5 rounded-2xl shadow-md text-white flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </div>
                        <span class="text-xs font-bold text-blue-100 uppercase tracking-wider mb-1">Skor Tertinggi Global</span>
                        <div class="text-3xl font-black tracking-tight">{{ stats.highest }}</div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rata-rata Skor</span>
                        <div class="flex items-end gap-2">
                            <h3 class="text-3xl font-black text-slate-800">{{ stats.average }}</h3>
                            <span class="text-sm font-bold text-slate-500 mb-1.5">Poin</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="font-bold text-slate-700 text-lg mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Semua Aktivitas
                    </h3>

                    <div v-if="attempts.length === 0" class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 mx-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <h3 class="text-slate-900 font-bold text-lg">Belum Ada Riwayat</h3>
                        <p class="text-slate-500 text-sm mt-1 mb-4">Anda belum mengerjakan tryout apapun.</p>
                        <Link :href="route('tryout.index')" class="inline-block px-6 py-2.5 bg-[#004a87] text-white rounded-xl text-sm font-bold shadow-md hover:bg-blue-800 transition">
                            Cari Tryout
                        </Link>
                    </div>

                    <div v-for="attempt in attempts" :key="attempt.id" 
                        class="group bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-300 flex flex-col md:flex-row items-start md:items-center gap-6"
                    >
                        
                        <div class="flex items-start gap-4 md:w-1/3">
                            <div class="flex-shrink-0 mt-1">
                                <div v-if="isPassed(attempt)" class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center border border-emerald-200" title="Lulus Passing Grade">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div v-else class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200" title="Belum Lulus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>

                            <div>
                                <Link :href="route('tryout.history.detail', attempt.tryout.id)" class="text-sm md:text-base font-bold text-slate-800 hover:text-[#004a87] transition line-clamp-1 group-hover:underline">
                                    {{ attempt.tryout.title }}
                                </Link>
                                <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDate(attempt.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 w-full border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                            <div class="flex flex-wrap items-center gap-y-2 gap-x-6">
                                <div class="flex items-center gap-3 mr-4">
                                    <span class="text-xs font-bold text-slate-400 uppercase">Total</span>
                                    <span class="text-2xl font-black" :class="isPassed(attempt) ? 'text-emerald-600' : 'text-slate-700'">
                                        {{ attempt.total_score }}
                                    </span>
                                </div>

                                <div class="flex gap-2 flex-wrap">
                                    <div class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600" :class="{'bg-red-50 text-red-600 border-red-100': attempt.twk_score < 65}">
                                        TWK: <span class="font-bold">{{ attempt.twk_score }}</span>
                                    </div>
                                    <div class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600" :class="{'bg-red-50 text-red-600 border-red-100': attempt.tiu_score < 80}">
                                        TIU: <span class="font-bold">{{ attempt.tiu_score }}</span>
                                    </div>
                                    <div class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600" :class="{'bg-red-50 text-red-600 border-red-100': attempt.tkp_score < 166}">
                                        TKP: <span class="font-bold">{{ attempt.tkp_score }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-auto flex gap-2 mt-2 md:mt-0">
                            <Link :href="route('tryout.result', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50 text-slate-600 hover:text-blue-700 text-xs font-bold rounded-lg transition-all whitespace-nowrap"
                            >
                                Rapor
                            </Link>
                            <Link :href="route('tryout.review', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-[#004a87] hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition-all shadow-sm whitespace-nowrap"
                            >
                                Pembahasan
                            </Link>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>