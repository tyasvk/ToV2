<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryout: Object,
    attempts: Array,
});

// Format Tanggal (Contoh: 28 Jan 2026, 10:30)
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Menghitung Skor Tertinggi dari list attempts
const highestScore = computed(() => {
    if (!props.attempts || props.attempts.length === 0) return 0;
    return Math.max(...props.attempts.map(a => a.total_score));
});
</script>

<template>
    <Head title="Riwayat Pengerjaan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('tryout.my')" class="group p-2 rounded-full hover:bg-slate-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500 group-hover:text-[#004a87]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">Detail Riwayat</h2>
                    <p class="text-xs text-slate-500">{{ tryout.title }}</p>
                </div>
            </div>
        </template>

        <div class="py-8 md:py-12 bg-slate-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Paket Tryout</span>
                        <h3 class="font-bold text-slate-800 text-lg line-clamp-2">{{ tryout.title }}</h3>
                    </div>

                    <div class="bg-gradient-to-br from-[#004a87] to-blue-700 p-5 rounded-2xl shadow-md text-white flex flex-col justify-center relative overflow-hidden">
                        <div class="absolute right-0 top-0 opacity-10 transform translate-x-2 -translate-y-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </div>
                        <span class="text-xs font-bold text-blue-100 uppercase tracking-wider mb-1">Skor Tertinggi Anda</span>
                        <div class="text-3xl font-black tracking-tight">{{ highestScore }}</div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sisa Kesempatan</span>
                            <div class="flex items-center gap-2">
                                <div class="text-2xl font-black text-slate-800">{{ 3 - attempts.length }}</div>
                                <span class="text-sm text-slate-500 font-medium">dari 3x</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-slate-100 rounded-full h-2 mt-3">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" :style="`width: ${(attempts.length / 3) * 100}%`"></div>
                        </div>
                    </div>
                </div>

                <div v-if="attempts.length < 3" class="mb-8 flex justify-end">
                    <Link :href="route('tryout.wait', tryout.id)" 
                        class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold uppercase tracking-wider rounded-xl shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mulai Pengerjaan Baru
                    </Link>
                </div>

                <div class="space-y-4">
                    <h3 class="font-bold text-slate-700 text-lg mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Pengerjaan
                    </h3>

                    <div v-if="attempts.length === 0" class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                        <p class="text-slate-500">Belum ada riwayat. Silakan mulai ujian pertama Anda.</p>
                    </div>

                    <div v-for="(attempt, index) in attempts" :key="attempt.id" 
                        class="group bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-300 flex flex-col md:flex-row items-start md:items-center gap-6"
                    >
                        
                        <div class="flex items-center gap-4 md:w-1/4">
                            <div class="bg-blue-50 text-[#004a87] font-black text-xl w-12 h-12 flex items-center justify-center rounded-xl border border-blue-100">
                                #{{ attempts.length - index }}
                            </div>
                            <div>
                                <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tanggal</div>
                                <div class="text-sm font-bold text-slate-700">{{ formatDate(attempt.created_at) }}</div>
                            </div>
                        </div>

                        <div class="flex-1 w-full border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                            <div class="flex flex-wrap items-center gap-y-2 gap-x-6">
                                <div class="flex items-center gap-3 mr-4">
                                    <span class="text-xs font-bold text-slate-400 uppercase">Total SKD</span>
                                    <span class="text-2xl font-black text-[#004a87]">{{ attempt.total_score }}</span>
                                </div>

                                <div class="flex gap-2">
                                    <div class="px-3 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600">
                                        TWK: <span class="font-bold text-slate-800">{{ attempt.twk_score }}</span>
                                    </div>
                                    <div class="px-3 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600">
                                        TIU: <span class="font-bold text-slate-800">{{ attempt.tiu_score }}</span>
                                    </div>
                                    <div class="px-3 py-1 bg-slate-50 rounded-lg border border-slate-100 text-xs font-medium text-slate-600">
                                        TKP: <span class="font-bold text-slate-800">{{ attempt.tkp_score }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full md:w-auto flex gap-2 mt-2 md:mt-0">
                            <Link :href="route('tryout.result', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-white border border-slate-200 hover:border-blue-300 hover:bg-blue-50 text-slate-600 hover:text-blue-700 text-xs font-bold rounded-lg transition-all"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Lihat Rapor
                            </Link>
                            <Link :href="route('tryout.review', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-[#004a87] hover:bg-blue-800 text-white text-xs font-bold rounded-lg transition-all shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Pembahasan
                            </Link>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>