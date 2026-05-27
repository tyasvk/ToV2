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
    return route('tryout.index');
});

// Utility untuk format angka
const formatNum = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <Head :title="'Persiapan: ' + tryout.title" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                
                <div class="mb-6">
                    <Link :href="backUrl" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </Link>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-10 shadow-sm">
                    
                    <div class="mb-8">
                        <h1 class="text-2xl sm:text-3xl text-slate-900 tracking-tight mb-2">Persiapan Ujian</h1>
                        <p class="text-slate-500 text-sm leading-relaxed max-w-md">
                            Silakan periksa detail simulasi di bawah ini sebelum memilih mode pengerjaan.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-6 mb-8 border border-slate-100">
                        <h2 class="text-base text-slate-800 mb-6 tracking-tight">{{ tryout.title }}</h2>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Durasi</p>
                                <p class="text-lg text-slate-900">{{ tryout.duration }} Menit</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-400 uppercase tracking-widest mb-1">Total Soal</p>
                                <p class="text-lg text-slate-900">{{ formatNum(tryout.questions_count) }} Butir</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 p-4 rounded-lg bg-amber-50 border border-amber-100 mb-8">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-amber-900 leading-relaxed">
                            Pastikan Anda sudah siap. Setelah menekan tombol mulai, waktu akan berjalan otomatis dan tidak dapat dihentikan.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm text-slate-900">Pilih mode tampilan ujian:</p>
                        
                        <div class="grid sm:grid-cols-2 gap-4">
                            <Link :href="route('tryout.exam.bkn', tryout.id)"
                                class="flex items-center gap-4 p-4 border border-slate-200 rounded-xl hover:border-slate-900 hover:bg-slate-50 transition-all group"
                            >
                                <span class="text-xl">🖥️</span>
                                <div>
                                    <span class="block text-sm text-slate-900">Mode CAT BKN</span>
                                    <span class="text-[10px] text-slate-400 tracking-wide">Pengalaman ujian asli</span>
                                </div>
                            </Link>

                            <Link :href="route('tryout.exam', tryout.id)"
                                class="flex items-center gap-4 p-4 border border-slate-200 rounded-xl hover:border-slate-900 hover:bg-slate-50 transition-all group"
                            >
                                <span class="text-xl">📱</span>
                                <div>
                                    <span class="block text-sm text-slate-900">Mode Modern</span>
                                    <span class="text-[10px] text-slate-400 tracking-wide">Tampilan mobile ringan</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>