<script setup>
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

// Format tanggal menjadi format Indonesia yang rapi
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).replace('.', ':');
};
</script>

<template>
    <Head title="Riwayat Tryout" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Riwayat Simulasi</h1>
                        <p class="text-[11px] text-slate-500 font-medium tracking-wide mt-1">
                            Lacak perkembangan nilai dan analisis evaluasi belajar Anda.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 mt-2">
                    <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 shadow-sm transition-all hover:shadow-md">
                        <p class="text-[9px] text-slate-400 uppercase tracking-[0.25em] font-medium mb-1">Total Dikerjakan</p>
                        <p class="text-2xl md:text-3xl font-medium text-indigo-600 tracking-tighter">{{ stats.total || 0 }}</p>
                    </div>
                    
                    <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 shadow-sm transition-all hover:shadow-md">
                        <p class="text-[9px] text-slate-400 uppercase tracking-[0.25em] font-medium mb-1">Rata-rata Nilai</p>
                        <p class="text-2xl md:text-3xl font-medium text-emerald-500 tracking-tighter">{{ stats.average_score || '0' }}</p>
                    </div>

                    <div class="hidden md:block bg-white border border-slate-100 rounded-[1.5rem] p-5 shadow-sm transition-all hover:shadow-md">
                        <p class="text-[9px] text-slate-400 uppercase tracking-[0.25em] font-medium mb-1">Status Lulus</p>
                        <p class="text-2xl md:text-3xl font-medium text-amber-500 tracking-tighter">{{ stats.passed || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div v-if="histories.length === 0" class="bg-white border border-slate-100 rounded-[2rem] p-10 text-center shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-slate-900 uppercase tracking-widest mb-2">Belum Ada Riwayat</h3>
                    <p class="text-xs text-slate-500 font-medium tracking-wide max-w-sm mx-auto mb-6">
                        Anda belum pernah menyelesaikan tryout. Ayo mulai uji kemampuan Anda sekarang!
                    </p>
                    <Link :href="route('tryout.index')" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-medium uppercase tracking-[0.2em] transition-all hover:bg-indigo-600 active:scale-95 shadow-lg shadow-slate-200">
                        Lihat Katalog Tryout
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 gap-4">
                    <div v-for="history in histories" :key="history.id" class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-5 transition-all hover:shadow-lg hover:border-indigo-100 group relative overflow-hidden">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-[10px] font-medium text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ formatDate(history.created_at || history.start_time) }} WIB
                                </span>
                            </div>

                            <h3 class="text-base md:text-lg font-medium text-slate-900 tracking-tight truncate pr-4">
                                {{ history.tryout?.title || 'Tryout Nusantara' }}
                            </h3>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 border-slate-50 pt-4 md:pt-0 shrink-0">
                            
                            <div class="text-left md:text-right">
                                <p class="text-[9px] font-medium text-slate-400 uppercase tracking-[0.2em] mb-0.5">Skor Akhir</p>
                                <p class="text-xl md:text-2xl font-medium tracking-tighter" :class="history.is_passed ? 'text-emerald-500' : 'text-slate-800'">
                                    {{ history.score || 0 }}
                                </p>
                            </div>

                            <Link :href="route('tryout.history.detail', history.id)" class="inline-flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-slate-50 text-slate-600 rounded-xl transition-all group-hover:bg-indigo-50 group-hover:text-indigo-600 active:scale-90 shrink-0 border border-slate-100 group-hover:border-indigo-100">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
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
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.space-y-6 > div, .space-y-8 > div {
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.space-y-6 > div:nth-child(1), .space-y-8 > div:nth-child(1) { animation-delay: 0s; }
.space-y-6 > div:nth-child(2), .space-y-8 > div:nth-child(2) { animation-delay: 0.1s; }
</style>