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
        <!-- Background bersih khas iCloud (#F8FAFC) -->
        <div class="w-full bg-[#F8FAFC] min-h-screen pb-20 md:pb-28 font-sans animate-in fade-in duration-500 relative overflow-hidden">
            
            <!-- Pendaran Latar Belakang Sangat Halus -->
            <div class="fixed top-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-50/50 rounded-full blur-[100px] pointer-events-none z-0"></div>
            <div class="fixed bottom-[-10%] left-[-10%] w-[450px] h-[450px] bg-slate-100/50 rounded-full blur-[100px] pointer-events-none z-0"></div>

            <!-- Menggunakan max-w-6xl agar 3 kolom terlihat pas dan lega -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 md:pt-8 space-y-5 md:space-y-6 relative z-10">
                
                <!-- HEADER -->
                <div class="flex flex-col gap-1.5 border-b border-slate-200 pb-5">
                    <h1 class="text-[24px] sm:text-[30px] font-bold text-slate-900 tracking-tight leading-tight">
                        Riwayat Pengerjaan
                    </h1>
                    <p class="text-[14px] text-slate-500 font-medium">
                        Evaluasi hasil, skor, dan pembahasan simulasi ujian Anda.
                    </p>
                </div>

                <!-- GRID CARDS (3 Kolom untuk Desktop agar kartu lebih kecil) -->
                <div v-if="histories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 pb-10">
                    <div 
                        v-for="history in histories" 
                        :key="history.id"
                        class="group bg-white border border-slate-200 rounded-[24px] p-5 sm:p-6 flex flex-col h-full shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden"
                    >
                        
                        <!-- Header Card: Badge Attempt & Ranking -->
                        <div class="relative z-10 flex flex-wrap items-center justify-between mb-4 gap-2">
                            <!-- Badge Pengerjaan -->
                            <span class="inline-flex px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] uppercase tracking-widest font-bold border border-blue-100/50">
                                Ke-{{ history.attempt_number || 1 }}
                            </span>

                            <!-- BADGE RANKING (Hanya tampil jika pengerjaan ke-1) -->
                            <span 
                                v-if="(history.attempt_number || 1) === 1" 
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] uppercase tracking-widest font-bold shadow-sm border border-amber-100/50"
                            >
                                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99-2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                                Rank {{ history.rank || '-' }}
                            </span>
                            
                            <!-- BADGE LATIHAN (Jika pengerjaan > 1) -->
                            <span 
                                v-else 
                                class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-widest border border-slate-200"
                            >
                                Latihan
                            </span>
                        </div>

                        <!-- Judul Tryout -->
                        <h2 class="text-[16px] sm:text-[18px] text-slate-900 leading-tight font-bold mb-5 break-words">
                            {{ history.tryout?.title || 'Simulasi Ujian CAT' }}
                        </h2>

                        <!-- Box Rincian Skor -->
                        <div class="bg-slate-50 border border-slate-200 rounded-[16px] p-4 mb-5 flex justify-between items-center text-center">
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">TWK</p>
                                <p class="text-[14px] font-bold text-slate-900">{{ history.twk_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-8 bg-slate-200 mx-2"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">TIU</p>
                                <p class="text-[14px] font-bold text-slate-900">{{ history.tiu_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-8 bg-slate-200 mx-2"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">TKP</p>
                                <p class="text-[14px] font-bold text-slate-900">{{ history.tkp_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-10 bg-slate-300 mx-2.5"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[10px] uppercase tracking-wider text-blue-600 font-bold mb-1">Total</p>
                                <p class="text-[18px] font-black text-blue-600 leading-none">{{ history.total_score || 0 }}</p>
                            </div>
                        </div>

                        <!-- Footer: Status & Tombol Pembahasan -->
                        <div class="mt-auto pt-4 border-t border-slate-100 flex flex-col gap-4">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Status Akhir</span>
                                    <span 
                                        class="text-[14px] font-bold"
                                        :class="history.is_passed ? 'text-emerald-600' : 'text-red-500'"
                                    >
                                        {{ history.is_passed ? 'Lulus' : 'Gagal' }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Selesai Pada</span>
                                    <span class="text-[12px] font-semibold text-slate-700">
                                        {{ formatDateTime(history.end_time || history.created_at) }}
                                    </span>
                                </div>
                            </div>

                            <Link 
                                :href="route('tryout.history.detail', history.tryout?.id || history.tryout_id)"
                                class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-full text-[13px] font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-2 group shadow-sm shadow-slate-900/10"
                            >
                                Lihat Pembahasan
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7M21 12H3" />
                                </svg>
                            </Link>
                        </div>

                    </div>
                </div>

                <!-- EMPTY STATE (SaaS Style) -->
                <div v-else class="bg-white rounded-[24px] p-12 sm:p-20 flex flex-col items-center text-center shadow-sm border border-slate-200 max-w-2xl mx-auto mt-6">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 text-slate-400 border border-slate-100">
                        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-[20px] sm:text-[24px] text-slate-900 mb-3 font-bold tracking-tight">Belum Ada Riwayat</h3>
                    <p class="text-[14px] sm:text-[15px] text-slate-500 font-medium mb-8 leading-relaxed max-w-md">
                        Anda belum menyelesaikan simulasi ujian apa pun. Mulai asah kemampuan Anda sekarang.
                    </p>
                    <Link 
                        :href="route('tryout.index')"
                        class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-[14px] font-bold transition-all active:scale-[0.98] shadow-sm"
                    >
                        Mulai Simulasi Baru
                    </Link>
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