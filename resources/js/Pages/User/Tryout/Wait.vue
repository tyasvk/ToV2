<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryout: Object
});

// Logic untuk menentukan rute kembali berdasarkan tipe tryout
const backUrl = computed(() => {
    if (props.tryout.type === 'adidaya') {
        return route('tryout.adidaya');
    } else if (props.tryout.type === 'akbar') {
        return route('tryout-akbar.index');
    }
    return route('tryout.index'); // Default untuk tryout umum
});

// Utility untuk format angka
const formatNum = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <Head :title="'Persiapan: ' + tryout.title" />

    <AuthenticatedLayout>
        <div class="bg-slate-50 min-h-screen py-4 md:py-8">
            <div class="max-w-2xl mx-auto px-4 sm:px-6">
                
                <div class="mb-4 flex items-center">
                    <Link :href="backUrl" class="inline-flex items-center gap-1.5 p-1.5 -ml-1.5 rounded-lg text-slate-500 hover:bg-slate-200 transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="text-xs font-medium">Kembali</span>
                    </Link>
                </div>

                <div class="space-y-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden relative">
                        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-amber-500 to-blue-600 absolute top-0 left-0"></div>
                        
                        <div class="p-4 sm:p-6 text-center">
                            <div class="mb-5">
                                <h1 class="text-lg sm:text-xl font-medium text-slate-800 leading-tight mb-1">
                                    {{ tryout.title }}
                                </h1>
                                <p class="text-slate-500 text-[11px] sm:text-xs">Simulasi Computer Assisted Test (CAT) BKN</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex flex-col items-center justify-center">
                                    <div class="p-1.5 bg-amber-100 text-amber-600 rounded-full mb-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Durasi</p>
                                    <p class="text-base sm:text-lg font-medium text-slate-800">
                                        {{ tryout.duration }} <span class="text-[10px] text-slate-500">Menit</span>
                                    </p>
                                </div>
                                
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex flex-col items-center justify-center">
                                    <div class="p-1.5 bg-blue-100 text-blue-600 rounded-full mb-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">Total Soal</p>
                                    <p class="text-base sm:text-lg font-medium text-slate-800">
                                        {{ formatNum(tryout.questions_count) }} <span class="text-[10px] text-slate-500">Butir</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50 rounded-xl border border-amber-200/80 p-3.5 flex gap-3 items-start shadow-sm">
                        <div class="p-1 bg-amber-100 rounded-full shrink-0 mt-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-medium text-amber-900 uppercase tracking-wider mb-0.5">Perhatian Sebelum Memulai</h3>
                            <p class="text-[11px] text-amber-800/80 leading-relaxed">
                                Waktu ujian akan otomatis berjalan mundur segera setelah Anda memilih mode. Pastikan koneksi stabil dan Anda sudah siap.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6">
                        <div class="text-center mb-4">
                            <h2 class="text-base font-medium text-slate-800">Pilih Mode Ujian</h2>
                            <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5">Pilih sesuai dengan perangkat yang sedang Anda gunakan</p>
                        </div>

                        <div class="space-y-3">
                            <Link :href="route('tryout.exam.bkn', tryout.id)"
                                class="group relative w-full flex items-center p-3 border border-slate-800 bg-slate-900 hover:bg-slate-800 rounded-lg transition-all active:scale-[0.98] overflow-hidden"
                            >
                                <div class="bg-white/10 p-2 rounded-md mr-3 shrink-0">
                                    <span class="text-lg">🖥️</span>
                                </div>
                                <div class="text-left flex-1">
                                    <h3 class="text-white text-sm font-medium tracking-wide">Mode CAT BKN</h3>
                                    <p class="text-slate-400 text-[10px] sm:text-[11px] mt-0.5">Rekomendasi untuk Laptop / PC</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>

                            <Link :href="route('tryout.exam', tryout.id)"
                                class="group w-full flex items-center p-3 bg-white border border-slate-200 hover:border-blue-600 hover:bg-blue-50/50 rounded-lg transition-all active:scale-[0.98]"
                            >
                                <div class="bg-slate-100 group-hover:bg-blue-100 p-2 rounded-md mr-3 shrink-0 transition-colors">
                                    <span class="text-lg">📱</span>
                                </div>
                                <div class="text-left flex-1">
                                    <h3 class="text-slate-800 group-hover:text-blue-700 text-sm font-medium tracking-wide transition-colors">Mode Modern (Lite)</h3>
                                    <p class="text-slate-500 text-[10px] sm:text-[11px] mt-0.5">Lebih ringan, cocok untuk HP / Tablet</p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>