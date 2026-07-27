<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    histories: {
        type: Array,
        default: () => []
    }
});

// Format Tanggal Minimalis: "27 Jul 2026 • 08:00 WIB"
const formatDateTime = (dateStr) => {
    if (!dateStr) return '-';
    // Mengatasi bug Safari dengan mengubah spasi menjadi T
    const safeDateStr = dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr;
    const date = new Date(safeDateStr);
    
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(date).replace(/\./g, ':').replace(',', ' •') + ' WIB';
};
</script>

<template>
    <Head title="Riwayat Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="animate-in fade-in duration-500 max-w-5xl mx-auto px-4 py-6 md:py-10 space-y-6">
            
            <!-- HEADER -->
            <div class="flex flex-col gap-1 border-b border-slate-200 pb-4">
                <h1 class="text-lg md:text-xl font-medium text-slate-900 tracking-tight uppercase">Riwayat Pengerjaan</h1>
                <p class="text-[11px] md:text-xs text-slate-500 font-normal">Evaluasi hasil, skor, dan pembahasan simulasi ujian Anda.</p>
            </div>

            <!-- GRID CARDS -->
            <div v-if="histories.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 pb-12">
                <div 
                    v-for="history in histories" 
                    :key="history.id"
                    class="group bg-white border border-slate-200 rounded-xl p-4 flex flex-col h-full hover:border-slate-300 hover:shadow-sm transition-all relative overflow-hidden"
                >
                    <!-- Header Card: Badge Attempt & Ranking -->
                    <div class="relative z-10 flex items-center justify-between mb-3 gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded border border-blue-100 bg-blue-50 text-blue-600 text-[9px] uppercase tracking-widest font-medium">
                            Pengerjaan Ke-{{ history.attempt_number || 1 }}
                        </span>

                        <!-- BADGE RANKING (Hanya tampil jika pengerjaan ke-1) -->
                        <span 
                            v-if="(history.attempt_number || 1) === 1" 
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded border border-amber-100 bg-amber-50 text-amber-600 text-[9px] uppercase tracking-widest font-medium"
                        >
                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            Rank {{ history.rank || '-' }}
                        </span>
                        <!-- BADGE LATIHAN (Jika pengerjaan > 1) -->
                        <span 
                            v-else 
                            class="inline-flex items-center px-2 py-0.5 rounded border border-slate-100 bg-slate-50 text-slate-400 text-[9px] font-medium uppercase tracking-widest"
                        >
                            Hanya Latihan
                        </span>
                    </div>

                    <!-- Judul Tryout -->
                    <h2 class="text-sm text-slate-900 leading-snug font-medium mb-3 line-clamp-2">
                        {{ history.tryout?.title || 'Simulasi Ujian CAT' }}
                    </h2>

                    <!-- Box Rincian Skor (TWK, TIU, TKP, Total) -->
                    <div class="bg-slate-50 border border-slate-100 rounded-lg p-2 mb-4 flex justify-between items-center text-center divide-x divide-slate-200">
                        <div class="px-1 w-full">
                            <p class="text-[8px] uppercase tracking-widest text-slate-400 font-normal mb-0.5">TWK</p>
                            <p class="text-[11px] font-medium text-slate-700">{{ history.twk_score || 0 }}</p>
                        </div>
                        <div class="px-1 w-full">
                            <p class="text-[8px] uppercase tracking-widest text-slate-400 font-normal mb-0.5">TIU</p>
                            <p class="text-[11px] font-medium text-slate-700">{{ history.tiu_score || 0 }}</p>
                        </div>
                        <div class="px-1 w-full">
                            <p class="text-[8px] uppercase tracking-widest text-slate-400 font-normal mb-0.5">TKP</p>
                            <p class="text-[11px] font-medium text-slate-700">{{ history.tkp_score || 0 }}</p>
                        </div>
                        <div class="px-1 w-full">
                            <p class="text-[8px] uppercase tracking-widest text-slate-500 font-medium mb-0.5">Total</p>
                            <p class="text-xs font-medium text-slate-900">{{ history.total_score || 0 }}</p>
                        </div>
                    </div>

                    <!-- Footer: Status, Waktu & Tombol -->
                    <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        
                        <div class="flex flex-col">
                            <span class="text-[8px] text-slate-400 uppercase tracking-widest mb-0.5 font-normal">Keterangan</span>
                            <div class="flex items-center gap-1.5">
                                <span 
                                    class="text-[10px] font-medium uppercase tracking-wider"
                                    :class="history.is_passed ? 'text-emerald-600' : 'text-rose-500'"
                                >
                                    {{ history.is_passed ? 'Lulus' : 'Gagal' }}
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-[9px] font-medium text-slate-500 truncate max-w-[90px] md:max-w-none">
                                    {{ formatDateTime(history.end_time || history.created_at) }}
                                </span>
                            </div>
                        </div>

                        <!-- Tombol Pembahasan -->
                        <Link 
                            :href="route('tryout.history.detail', history.id)"
                            class="shrink-0 px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-md text-[9px] uppercase tracking-wider transition-colors font-medium flex items-center gap-1"
                        >
                            Pembahasan
                            <span class="text-xs leading-none">&rarr;</span>
                        </Link>
                    </div>

                </div>
            </div>

            <!-- EMPTY STATE -->
            <div v-else class="border border-dashed border-slate-200 rounded-2xl p-10 flex flex-col items-center text-center max-w-lg mx-auto">
                <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-400">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-sm text-slate-900 mb-1 font-medium">Belum Ada Riwayat</h3>
                <p class="text-[11px] text-slate-500 font-normal mb-4">
                    Anda belum menyelesaikan simulasi ujian apa pun.
                </p>
                <Link 
                    :href="route('tryouts.index')"
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[10px] uppercase tracking-wider font-medium transition-colors"
                >
                    Mulai Tryout
                </Link>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>