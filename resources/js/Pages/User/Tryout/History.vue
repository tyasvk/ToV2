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
        <!-- Background khas Apple (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent pb-20 md:pb-28 font-sans animate-in fade-in duration-500">
            
            <!-- Menggunakan max-w-6xl agar 3 kolom terlihat pas dan lega -->
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 md:pt-8 space-y-5 md:space-y-6">
                
                <!-- HEADER (Lebih Padat) -->
                <div class="flex flex-col gap-1 border-b border-black/5 pb-4">
                    <h1 class="text-[22px] sm:text-[28px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                        Riwayat Pengerjaan
                    </h1>
                    <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium">
                        Evaluasi hasil, skor, dan pembahasan simulasi ujian Anda.
                    </p>
                </div>

                <!-- GRID CARDS (3 Kolom untuk Desktop agar kartu lebih kecil) -->
                <div v-if="histories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 pb-10">
                    <div 
                        v-for="history in histories" 
                        :key="history.id"
                        class="group bg-white border border-black/5 rounded-[20px] p-4 sm:p-5 flex flex-col h-full shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)] transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden"
                    >
                        
                        <!-- Header Card: Badge Attempt & Ranking -->
                        <div class="relative z-10 flex flex-wrap items-center justify-between mb-3 gap-2">
                            <!-- Badge Pengerjaan -->
                            <span class="inline-flex px-2.5 py-1 rounded-full bg-[#F0F4FF] text-[#007AFF] text-[9px] sm:text-[10px] uppercase tracking-widest font-bold">
                                Ke-{{ history.attempt_number || 1 }}
                            </span>

                            <!-- BADGE RANKING (Hanya tampil jika pengerjaan ke-1) -->
                            <span 
                                v-if="(history.attempt_number || 1) === 1" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#FFF9E6] text-[#FF9500] text-[9px] sm:text-[10px] uppercase tracking-widest font-bold shadow-sm"
                            >
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99-2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                                </svg>
                                Rank {{ history.rank || '-' }}
                            </span>
                            
                            <!-- BADGE LATIHAN (Jika pengerjaan > 1) -->
                            <span 
                                v-else 
                                class="inline-flex items-center px-2.5 py-1 rounded-full bg-[#F5F5F7] text-[#86868B] text-[9px] sm:text-[10px] font-bold uppercase tracking-widest"
                            >
                                Latihan
                            </span>
                        </div>

                        <!-- Judul Tryout (Kecil tapi Jelas) -->
                        <h2 class="text-[15px] sm:text-[16px] text-[#1D1D1F] leading-snug font-bold mb-4 break-words">
                            {{ history.tryout?.title || 'Simulasi Ujian CAT' }}
                        </h2>

                        <!-- Box Rincian Skor -->
                        <div class="bg-[#F5F5F7] border border-black/5 rounded-[14px] p-3 mb-4 flex justify-between items-center text-center">
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[9px] uppercase tracking-wider text-[#86868B] font-bold mb-0.5">TWK</p>
                                <p class="text-[13px] font-bold text-[#1D1D1F]">{{ history.twk_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-6 bg-black/5 mx-1"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[9px] uppercase tracking-wider text-[#86868B] font-bold mb-0.5">TIU</p>
                                <p class="text-[13px] font-bold text-[#1D1D1F]">{{ history.tiu_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-6 bg-black/5 mx-1"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[9px] uppercase tracking-wider text-[#86868B] font-bold mb-0.5">TKP</p>
                                <p class="text-[13px] font-bold text-[#1D1D1F]">{{ history.tkp_score || 0 }}</p>
                            </div>
                            
                            <div class="w-px h-8 bg-black/10 mx-1.5"></div>
                            
                            <div class="w-full flex flex-col items-center">
                                <p class="text-[9px] uppercase tracking-wider text-[#007AFF] font-bold mb-0.5">Total</p>
                                <p class="text-[16px] font-black text-[#007AFF] leading-none">{{ history.total_score || 0 }}</p>
                            </div>
                        </div>

                        <!-- Footer: Status & Tombol Pembahasan Full Width -->
                        <div class="mt-auto pt-3 border-t border-black/5 flex flex-col gap-3.5">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[9px] text-[#86868B] font-semibold uppercase tracking-wider mb-0.5">Status Akhir</span>
                                    <span 
                                        class="text-[13px] font-bold"
                                        :class="history.is_passed ? 'text-[#34C759]' : 'text-[#FF3B30]'"
                                    >
                                        {{ history.is_passed ? 'Lulus' : 'Gagal' }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[9px] text-[#86868B] font-semibold uppercase tracking-wider mb-0.5">Selesai Pada</span>
                                    <span class="text-[11px] font-medium text-[#1D1D1F]">
                                        {{ formatDateTime(history.end_time || history.created_at) }}
                                    </span>
                                </div>
                            </div>

                            <!-- PERBAIKAN: Menggunakan tryout_id alih-alih history.id -->
                            <Link 
                                :href="route('tryout.history.detail', history.tryout?.id || history.tryout_id)"
                                class="w-full py-2.5 bg-[#F0F4FF] hover:bg-[#007AFF] text-[#007AFF] hover:text-white rounded-full text-[12px] font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-1.5 group border border-[#007AFF]/10 hover:border-transparent"
                            >
                                Lihat Pembahasan
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7M21 12H3" />
                                </svg>
                            </Link>
                        </div>

                    </div>
                </div>

                <!-- EMPTY STATE (Minimalis ala Apple) -->
                <div v-else class="bg-white rounded-[24px] p-12 sm:p-20 flex flex-col items-center text-center shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 max-w-xl mx-auto mt-4">
                    <div class="w-16 h-16 bg-[#F5F5F7] rounded-full flex items-center justify-center mb-5 text-[#86868B]">
                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[22px] text-[#1D1D1F] mb-2 font-bold">Belum Ada Riwayat</h3>
                    <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium mb-6 leading-relaxed">
                        Anda belum menyelesaikan simulasi ujian apa pun. Mulai asah kemampuan Anda sekarang.
                    </p>
                    <Link 
                        :href="route('tryouts.index')"
                        class="px-6 py-3 bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-full text-[13px] font-semibold transition-all active:scale-[0.98] shadow-sm"
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