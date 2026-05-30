<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    histories: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            average_score: 0,
            passed: 0
        })
    }
});

// Fitur Limit Data (10, 50, 100, Semua)
const itemsPerPage = ref(10);

const displayedHistories = computed(() => {
    if (itemsPerPage.value === 'all') {
        return props.histories;
    }
    return props.histories.slice(0, itemsPerPage.value);
});

// Format tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).replace('.', ':');
};
</script>

<template>
    <Head title="Riwayat Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-5xl mx-auto px-3 sm:px-4 py-4 md:py-8">
            
            <!-- Header Box -->
            <div class="bg-white p-6 rounded-[1.5rem] md:rounded-[2rem] border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-48 md:w-64 h-48 md:h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-10 md:-mr-20 -mt-10 md:-mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-xl md:text-2xl text-slate-900 tracking-tight uppercase font-medium">Riwayat Simulasi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 font-normal">Pantau nilai TWK, TIU, TKP dan peringkat Anda.</p>
                </div>

                <!-- Dropdown Limit Terintegrasi -->
                <div v-if="histories.length > 10" class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-2 py-2 w-full md:w-auto shadow-sm transition-all focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:bg-white focus-within:border-blue-500">
                        <span class="text-[10px] sm:text-xs text-slate-500 font-normal whitespace-nowrap">Tampilkan:</span>
                        <select v-model="itemsPerPage" class="text-[10px] sm:text-xs bg-transparent border-none text-slate-800 font-medium focus:ring-0 py-0 pl-1 pr-6 cursor-pointer outline-none w-full">
                            <option :value="10">10 Riwayat</option>
                            <option :value="50">50 Riwayat</option>
                            <option :value="100">100 Riwayat</option>
                            <option value="all">Semua Riwayat</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="bg-white border border-slate-200 rounded-[1.25rem] md:rounded-[1.5rem] p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-sm">
                    <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-1">Dikerjakan</span>
                    <span class="text-xl sm:text-3xl font-medium text-slate-800 tracking-tight">{{ stats.total || 0 }}</span>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-[1.25rem] md:rounded-[1.5rem] p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-sm">
                    <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-1">Rata-rata</span>
                    <span class="text-xl sm:text-3xl font-medium text-blue-600 tracking-tight">{{ stats.average_score || '0' }}</span>
                </div>

                <div class="bg-white border border-slate-200 rounded-[1.25rem] md:rounded-[1.5rem] p-4 sm:p-6 flex flex-col items-center justify-center text-center shadow-sm">
                    <span class="text-[9px] sm:text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-1">Lulus</span>
                    <span class="text-xl sm:text-3xl font-medium text-emerald-500 tracking-tight">{{ stats.passed || 0 }}</span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="histories.length === 0" class="bg-white border border-slate-200 rounded-[1.5rem] p-8 sm:p-12 flex flex-col items-center text-center shadow-sm max-w-xl mx-auto mt-4">
                <div class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center mb-3 text-lg">
                    📝
                </div>
                <h3 class="text-sm text-slate-800 font-medium mb-1">Belum Ada Riwayat</h3>
                <p class="text-xs text-slate-500 font-normal max-w-sm leading-relaxed mb-4">
                    Anda belum menyelesaikan tryout apapun. Ayo mulai latihan soal sekarang!
                </p>
                <Link :href="route('tryout.index')" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-medium uppercase tracking-wider hover:bg-slate-800 transition active:scale-95 shadow-sm">
                    Jelajahi Tryout
                </Link>
            </div>

            <!-- History Items List (SUPER COMPACT / SATU BARIS) -->
            <div v-else class="flex flex-col gap-2 pb-8">
                <Link 
                    v-for="history in displayedHistories" 
                    :key="history.id"
                    :href="route('tryout.history.detail', history.tryout_id || history.tryout?.id)"
                    class="group bg-white border border-slate-200 rounded-xl p-2 sm:p-3 shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden flex items-center"
                >
                    <!-- Indikator Warna Garis Kiri -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 transition-colors duration-300" 
                         :class="history.is_passed ? 'bg-emerald-400' : 'bg-amber-400'">
                    </div>

                    <div class="flex-1 min-w-0 pl-2 flex items-center justify-between gap-2 sm:gap-4">
                        
                        <!-- Kiri: Judul TO, Status, Tanggal -->
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="text-[11px] sm:text-sm font-medium text-slate-800 truncate group-hover:text-blue-600 transition-colors uppercase leading-tight">
                                {{ history.tryout?.title || 'Tryout Tidak Diketahui' }}
                            </h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-[7px] sm:text-[8px] font-medium uppercase tracking-widest px-1 py-0.5 rounded border"
                                      :class="history.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'">
                                    {{ history.is_passed ? 'LULUS' : 'GAGAL' }}
                                </span>
                                <span class="text-[8px] sm:text-[9px] text-slate-400 font-normal truncate">
                                    {{ formatDate(history.created_at || history.start_time) }} WIB
                                </span>
                            </div>
                        </div>

                        <!-- Kanan: Grid Skor & Peringkat (Sebaris Horizontal) -->
                        <div class="shrink-0 flex items-center gap-1 sm:gap-3 bg-slate-50 border border-slate-100 rounded-lg px-1.5 sm:px-3 py-1">
                            <div class="flex flex-col items-center justify-center min-w-[20px] sm:min-w-[28px]">
                                <span class="text-[6px] sm:text-[8px] text-slate-400 font-normal uppercase mb-0.5">TWK</span>
                                <span class="text-[9px] sm:text-[11px] font-medium text-slate-700 leading-none">{{ history.twk_score || 0 }}</span>
                            </div>
                            <div class="w-px h-5 bg-slate-200"></div>
                            <div class="flex flex-col items-center justify-center min-w-[20px] sm:min-w-[28px]">
                                <span class="text-[6px] sm:text-[8px] text-slate-400 font-normal uppercase mb-0.5">TIU</span>
                                <span class="text-[9px] sm:text-[11px] font-medium text-slate-700 leading-none">{{ history.tiu_score || 0 }}</span>
                            </div>
                            <div class="w-px h-5 bg-slate-200"></div>
                            <div class="flex flex-col items-center justify-center min-w-[20px] sm:min-w-[28px]">
                                <span class="text-[6px] sm:text-[8px] text-slate-400 font-normal uppercase mb-0.5">TKP</span>
                                <span class="text-[9px] sm:text-[11px] font-medium text-slate-700 leading-none">{{ history.tkp_score || 0 }}</span>
                            </div>
                            <div class="w-px h-5 bg-slate-200"></div>
                            <div class="flex flex-col items-center justify-center min-w-[24px] sm:min-w-[32px]">
                                <span class="text-[6px] sm:text-[8px] text-slate-400 font-normal uppercase mb-0.5">TOTAL</span>
                                <span class="text-[9px] sm:text-[11px] font-medium leading-none" :class="history.is_passed ? 'text-emerald-600' : 'text-amber-600'">
                                    {{ history.total_score || history.score || 0 }}
                                </span>
                            </div>
                            <div class="w-px h-5 bg-slate-200"></div>
                            <div class="flex flex-col items-center justify-center min-w-[24px] sm:min-w-[32px]">
                                <span class="text-[6px] sm:text-[8px] text-slate-400 font-normal uppercase mb-0.5">PERINGKAT</span>
                                <span class="text-[9px] sm:text-[11px] font-medium text-blue-600 leading-none">#{{ history.rank || '-' }}</span>
                            </div>
                        </div>

                        <!-- Icon Chevron -->
                        <div class="shrink-0 text-slate-300 group-hover:text-blue-500 transition-colors hidden sm:block pr-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>

                    </div>
                </Link>

                <!-- Load More Hint -->
                <div v-if="histories.length > 10 && itemsPerPage !== 'all' && displayedHistories.length < histories.length" class="text-center pt-3">
                    <button @click="itemsPerPage = 'all'" class="text-[11px] font-medium text-slate-500 hover:text-blue-600 transition-colors bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                        Muat Semua Riwayat Tersisa ({{ histories.length - displayedHistories.length }})
                    </button>
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