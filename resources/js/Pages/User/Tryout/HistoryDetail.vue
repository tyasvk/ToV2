<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    tryout: Object,
    attempts: Array,
    hasFullAccess: Boolean, 
    maxAttempts: Number 
});

const showUpgradeModal = ref(false);

const dynamicBackUrl = computed(() => {
    const type = props.tryout?.type || 'general';
    if (type === 'adidaya') return route('tryout.adidaya');
    if (type === 'akbar') return route('tryout-akbar.index');
    return route('tryout.index');
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    }).replace(/\./g, ':');
};

const getDuration = (start, end) => {
    if (!start || !end) return '0d';
    const startTime = new Date(start);
    const endTime = new Date(end);
    const seconds = Math.floor((endTime - startTime) / 1000);
    
    if (seconds <= 0) return '0 detik';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    
    if (h > 0) return `${h}j ${m}m ${s}d`;
    if (m > 0) return `${m}m ${s}d`;
    return `${s} detik`;
};

const highestScore = computed(() => {
    if (!props.attempts || props.attempts.length === 0) return 0;
    return Math.max(...props.attempts.map(a => a.total_score));
});
</script>

<template>
    <Head title="Riwayat Pengerjaan" />

    <AuthenticatedLayout>
        <!-- Background transparan menyatu dengan layout utama -->
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden">
            
            <!-- Padding dan margin disamakan dengan Result.vue dan Wait.vue -->
            <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4">

                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
                    <div>
                        <Link :href="dynamicBackUrl" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali
                        </Link>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Detail Riwayat</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1 uppercase tracking-wide">
                            {{ tryout.title }}
                        </p>
                    </div>
                    
                    <!-- Tombol Mulai Baru diletakkan di Header jika masih ada sisa kesempatan -->
                    <div v-if="attempts.length < maxAttempts" class="w-full md:w-auto mt-2 md:mt-0 shrink-0">
                        <Link :href="route('tryout.wait', tryout.id)" class="w-full md:w-auto px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full font-semibold text-[13px] transition-colors flex items-center justify-center gap-2 shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Mulai Pengerjaan Baru
                        </Link>
                    </div>
                </div>

                <!-- OVERVIEW CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    
                    <!-- Card 1: Identitas & Tipe Akses -->
                    <div class="bg-white p-5 rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col justify-center relative">
                        <div class="flex justify-between items-start mb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Akses Anda</span>
                            <span class="px-2.5 py-0.5 rounded-[6px] text-[9px] font-bold tracking-wide uppercase" 
                                  :class="hasFullAccess ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'">
                                {{ hasFullAccess ? 'Premium' : 'Gratis' }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-slate-900 text-[15px] md:text-[16px] leading-snug tracking-tight pr-4 line-clamp-2">
                            {{ tryout.title }}
                        </h3>
                    </div>

                    <!-- Card 2: Skor Tertinggi -->
                    <div class="bg-white p-5 rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col justify-center relative overflow-hidden">
                        <!-- Ornamen -->
                        <div class="absolute -right-6 -top-6 w-20 h-20 bg-blue-50 rounded-full blur-xl opacity-60"></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1 relative z-10">Skor Tertinggi</span>
                        <div class="text-[32px] md:text-[36px] font-black tracking-tighter text-[#007AFF] leading-none relative z-10 tabular-nums">
                            {{ highestScore }}
                        </div>
                    </div>

                    <!-- Card 3: Sisa Kesempatan -->
                    <div class="bg-white p-5 rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Sisa Kesempatan</span>
                            <div class="flex items-baseline gap-1.5 mt-1">
                                <div class="text-[28px] md:text-[32px] font-black text-slate-800 leading-none tabular-nums">{{ maxAttempts - attempts.length }}</div>
                                <span class="text-[12px] text-slate-500 font-medium">dari {{ maxAttempts }}x</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-[#E3E3E8] rounded-full h-2 mt-3 overflow-hidden">
                            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-700" :style="`width: ${(attempts.length / maxAttempts) * 100}%`"></div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR RIWAYAT PENGERJAAN -->
                <div class="pt-4 space-y-3">
                    <h3 class="text-[15px] font-semibold text-slate-900 tracking-tight ml-1">Daftar Pengerjaan</h3>

                    <div v-if="attempts.length === 0" class="bg-white rounded-[20px] border border-dashed border-slate-300 p-10 text-center shadow-sm">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-[13px] text-slate-500 font-medium">Belum ada riwayat. Silakan mulai ujian pertama Anda.</p>
                    </div>

                    <!-- List Iteration -->
                    <div v-for="(attempt, index) in attempts" :key="attempt.id" 
                        class="bg-white rounded-[20px] border border-slate-100/50 p-4 md:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_4px_16px_rgba(0,0,0,0.06)] transition-all duration-300 flex flex-col md:flex-row md:items-center gap-4 md:gap-6"
                    >
                        
                        <!-- Kiri: Nomor & Waktu -->
                        <div class="flex items-center gap-4 md:w-1/3">
                            <div class="bg-[#F0F4FF] text-[#007AFF] font-bold text-[15px] w-11 h-11 shrink-0 flex items-center justify-center rounded-full">
                                #{{ attempts.length - index }}
                            </div>
                            <div class="flex flex-col gap-1 w-full">
                                <div>
                                    <div class="text-[12px] font-semibold text-slate-800">{{ formatDate(attempt.created_at) }}</div>
                                    <div class="text-[11px] font-medium text-slate-500 mt-0.5">Durasi: {{ getDuration(attempt.created_at, attempt.completed_at) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Tengah: Rincian Skor -->
                        <div class="flex-1 w-full border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-5">
                            <div class="flex flex-wrap items-center gap-y-3 gap-x-5">
                                <div class="flex items-center gap-2 mr-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Skor</span>
                                    <span class="text-[22px] font-black text-slate-900 tabular-nums leading-none">{{ attempt.total_score }}</span>
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    <div class="px-2.5 py-1 bg-[#F5F5F7] rounded-[8px] text-[10px] font-medium text-slate-600">
                                        TWK: <span class="font-bold text-slate-900">{{ attempt.twk_score }}</span>
                                    </div>
                                    <div class="px-2.5 py-1 bg-[#F5F5F7] rounded-[8px] text-[10px] font-medium text-slate-600">
                                        TIU: <span class="font-bold text-slate-900">{{ attempt.tiu_score }}</span>
                                    </div>
                                    <div class="px-2.5 py-1 bg-[#F5F5F7] rounded-[8px] text-[10px] font-medium text-slate-600">
                                        TKP: <span class="font-bold text-slate-900">{{ attempt.tkp_score }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: Tombol Aksi -->
                        <div class="w-full md:w-auto flex flex-row gap-2 mt-1 md:mt-0 shrink-0">
                            <!-- Tombol Rapor -->
                            <Link :href="route('tryout.result', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 text-[12px] font-semibold rounded-full transition-colors active:scale-95"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Rapor
                            </Link>

                            <!-- Tombol Pembahasan -->
                            <button v-if="!hasFullAccess" @click="showUpgradeModal = true" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#F2F2F7] text-slate-400 text-[12px] font-semibold rounded-full transition-colors active:scale-95"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Pembahasan
                            </button>
                            
                            <Link v-else :href="route('tryout.review', attempt.id)" 
                                class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold rounded-full transition-colors active:scale-95 shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Pembahasan
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL POP-UP UPGRADE PREMIUM                   -->
        <!-- ============================================== -->
        <Teleport to="body">
            <div v-if="showUpgradeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showUpgradeModal = false"></div>
                
                <div class="relative bg-white rounded-[24px] p-6 md:p-8 max-w-sm w-full text-center shadow-2xl animate-in zoom-in-95 duration-200 z-10">
                    <div class="w-14 h-14 bg-[#F5F5F7] rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        🔒
                    </div>
                    <h3 class="text-[17px] font-bold text-slate-900 mb-2 tracking-tight">Fitur Terkunci</h3>
                    <p class="text-[13px] text-slate-500 mb-6 leading-relaxed font-medium">
                        Anda mengerjakan paket ini secara gratis. Beli akses premium untuk membuka <strong class="text-slate-700">Pembahasan, Peringkat Nasional, dan Sertifikat</strong>.
                    </p>
                    <div class="flex flex-col gap-2.5">
                        <Link :href="route('tryout.show', tryout.id)" class="w-full py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[13px] font-semibold transition-all active:scale-95">
                            Beli Akses Premium
                        </Link>
                        <button @click="showUpgradeModal = false" class="w-full py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 rounded-full text-[13px] font-semibold transition-colors active:scale-95">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>