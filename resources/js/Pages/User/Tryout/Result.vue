<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    attempt: Object,
    tryout: Object,
    totalScore: Number,
    scoreDetails: Array,
    ranking: Object,
    timeStats: Object, 
    backUrl: String,
    hasFullAccess: Boolean, 
});

const showUpgradeModal = ref(false);

const dynamicBackUrl = computed(() => {
    const type = props.tryout?.type || 'general';
    if (type === 'adidaya') return route('tryout.adidaya');
    if (type === 'akbar') return route('tryout-akbar.index');
    return route('tryout.index');
});

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

    <AuthenticatedLayout>
        <!-- Background transparan menyatu dengan layout utama -->
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden">
            
            <!-- Padding dan margin disamakan dengan Katalog -->
            <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4">

                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
                    <div>
                        <Link :href="dynamicBackUrl" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali
                        </Link>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Rapor Kelulusan</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1 uppercase tracking-wide">
                            {{ tryout.title }}
                        </p>
                    </div>
                </div>

                <!-- CARD 1: TOTAL SKOR & STATUS -->
                <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 overflow-hidden relative">
                    <!-- Efek Gradasi Halus di Pojok -->
                    <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full blur-[60px] opacity-20 pointer-events-none" :class="attempt.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"></div>

                    <div class="p-5 md:p-6 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                        <div class="text-center md:text-left flex-1">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest mb-3 border shadow-sm" 
                                 :class="attempt.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'">
                                <svg v-if="attempt.is_passed" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                {{ attempt.is_passed ? 'Selamat, Memenuhi Passing Grade!' : 'Maaf, Belum Memenuhi Passing Grade' }}
                            </div>
                            <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ tryout.title }}</h2>
                            <p class="text-[12px] text-slate-500 mt-1 font-medium">Sistem CAT (TWK, TIU, TKP)</p>
                        </div>

                        <div class="shrink-0 flex flex-col items-center justify-center w-28 h-28 rounded-full border-4 shadow-sm bg-white" :class="attempt.is_passed ? 'border-emerald-400' : 'border-rose-400'">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total Skor</span>
                            <span class="text-3xl font-black tabular-nums tracking-tighter mt-0.5" :class="attempt.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                {{ totalScore }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 border-t border-slate-100 bg-[#F5F5F7]/50 divide-y md:divide-y-0 md:divide-x divide-slate-100/80">
                        <div class="p-3 text-center border-r border-slate-100 md:border-r-0">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Peringkat Anda</p>
                            <p v-if="hasFullAccess" class="text-[17px] font-bold text-slate-800 tabular-nums">#{{ ranking.rank }} <span class="text-[11px] font-medium text-slate-400">/ {{ ranking.total_participants }}</span></p>
                            <button v-else @click="showUpgradeModal = true" class="text-[17px] font-bold text-slate-300 w-full text-center hover:text-slate-400 transition-colors">🔒</button>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Soal Dikerjakan</p>
                            <p class="text-[17px] font-bold text-slate-800 tabular-nums">
                                {{ Object.keys(attempt.answers || {}).length }} 
                                <span class="text-[11px] font-medium text-slate-400">/ {{ timeStats.total_questions }}</span>
                            </p>
                        </div>
                        <div class="p-3 text-center border-r border-slate-100 md:border-r-0">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Total Waktu</p>
                            <p class="text-[17px] font-bold text-[#007AFF] tabular-nums">{{ formatTime(timeStats.total_seconds) }}</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Rata-rata / Soal</p>
                            <p v-if="hasFullAccess" class="text-[17px] font-bold text-amber-500 tabular-nums">{{ formatTime(timeStats.average_seconds) }}</p>
                            <button v-else @click="showUpgradeModal = true" class="text-[17px] font-bold text-slate-300 w-full text-center hover:text-slate-400 transition-colors">🔒</button>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: RINCIAN NILAI -->
                <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 p-5 md:p-6">
                    <h3 class="text-[12px] font-bold text-slate-900 uppercase tracking-widest border-b border-slate-100 pb-2.5 mb-4">Rincian Ambang Batas</h3>
                    
                    <div class="space-y-4">
                        <div v-for="(detail, index) in scoreDetails" :key="index" class="bg-[#F5F5F7]/80 rounded-[16px] p-3.5 border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-4">
                            
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1.5">
                                    <h4 class="font-semibold text-slate-900 text-[13px] md:text-[14px]">{{ detail.category }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-sm" :class="detail.is_passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                        {{ detail.is_passed ? 'Lulus' : 'Gagal' }}
                                    </span>
                                </div>
                                
                                <div class="w-full bg-[#E3E3E8] rounded-full h-2.5 mb-1.5 relative overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all duration-1000" 
                                         :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'" 
                                         :style="`width: ${Math.min(100, (detail.score / (detail.passing_grade * 1.5)) * 100)}%`">
                                    </div>
                                    <!-- Garis Passing Grade -->
                                    <div class="absolute top-0 bottom-0 border-l-[3px] border-slate-900 z-10" :style="`left: ${(detail.passing_grade / (detail.passing_grade * 1.5)) * 100}%`"></div>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium">Garis hitam menunjukkan Passing Grade ({{ detail.passing_grade }})</p>
                            </div>

                            <div class="shrink-0 text-right md:text-center md:w-24 flex flex-row md:flex-col justify-between items-center md:border-l border-slate-200/60 md:pl-4">
                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Skor Anda</span>
                                <span class="text-2xl font-black tabular-nums tracking-tight" :class="detail.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ detail.score }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- TOMBOL AKSI (TERKUNCI JIKA AKSES GRATIS)   -->
                <!-- ========================================== -->
                <div class="flex flex-col sm:flex-row gap-2.5 pt-2 pb-6">
                    
                    <!-- TOMBOL PEMBAHASAN -->
                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Pembahasan
                    </button>
                    <Link v-else :href="route('tryout.review', attempt.id)" class="flex-1 flex justify-center items-center py-3 bg-slate-900 hover:bg-slate-800 text-white text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        Lihat Pembahasan
                    </Link>
                    
                    <!-- TOMBOL PERINGKAT -->
                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Papan Peringkat
                    </button>
                    <Link v-else :href="route('tryout.leaderboard', tryout.id)" class="flex-1 flex justify-center items-center py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        Lihat Peringkat
                    </Link>

                    <!-- TOMBOL SERTIFIKAT -->
                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Sertifikat
                    </button>
                    <a v-else :href="route('tryout.certificate', attempt.id)" target="_blank" class="flex-1 flex justify-center items-center py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Unduh Sertifikat
                    </a>
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