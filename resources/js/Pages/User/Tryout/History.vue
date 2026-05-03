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
            average: 0,
            highest: 0
        })
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusClass = (score) => {
    // Asumsi ambang batas lulus 350, sesuaikan dengan logika backend Anda
    return score >= 350 
        ? 'text-emerald-500 bg-emerald-50 border-emerald-100' 
        : 'text-rose-500 bg-rose-50 border-rose-100';
};
</script>

<template>
    <Head title="Riwayat Tryout" />

    <AuthenticatedLayout>
        <div class="space-y-8 md:space-y-12 animate-in fade-in duration-700">
            
            <div class="space-y-6">
                <div class="px-1">
                    <h1 class="text-2xl font-medium text-slate-900 tracking-tight uppercase italic">Riwayat Belajar</h1>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Pantau perkembangan skor dan evaluasi kemampuanmu</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                    <div v-for="(val, label) in { 'Total Ujian': stats.total, 'Skor Rata-rata': stats.average, 'Skor Tertinggi': stats.highest }" 
                         :key="label"
                         class="bg-white border border-slate-100 p-5 md:p-8 rounded-[1.5rem] md:rounded-[2rem] shadow-sm"
                    >
                        <p class="text-[8px] md:text-[9px] font-medium text-slate-400 uppercase tracking-widest mb-1">{{ label }}</p>
                        <p class="text-xl md:text-2xl font-medium text-slate-900 tracking-tighter leading-none">{{ val }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4 md:space-y-6 pb-20">
                <div class="flex items-center justify-between px-2">
                    <h2 class="text-[10px] font-medium text-slate-400 uppercase tracking-[0.2em]">Daftar Sesi Terakhir</h2>
                </div>

                <div v-if="histories.length > 0" class="grid grid-cols-1 gap-4">
                    <div 
                        v-for="item in histories" 
                        :key="item.id"
                        class="group bg-white border border-slate-100 p-6 md:p-8 rounded-[2rem] transition-all duration-500 hover:shadow-xl hover:shadow-slate-200/50 flex flex-col md:flex-row md:items-center justify-between gap-6"
                    >
                        <div class="flex items-start gap-5">
                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-xl shrink-0">
                                📝
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm md:text-base font-medium text-slate-900 uppercase tracking-tight mb-1 truncate">
                                    {{ item.tryout?.title || 'Simulasi Ujian' }}
                                </h3>
                                <div class="flex items-center gap-3">
                                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wider">{{ formatDate(item.created_at) }}</p>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <p class="text-[9px] font-medium text-indigo-500 uppercase tracking-wider italic">{{ item.tryout?.type || 'Reguler' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-6 border-t md:border-t-0 pt-4 md:pt-0">
                            <div class="text-left md:text-right">
                                <p class="text-[8px] font-medium text-slate-400 uppercase tracking-widest mb-0.5">Skor Akhir</p>
                                <p class="text-xl font-medium text-slate-900 leading-none">{{ item.total_score }}</p>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <span :class="getStatusClass(item.total_score)" class="px-3 py-1.5 rounded-lg text-[8px] font-medium uppercase tracking-widest border">
                                    {{ item.total_score >= 350 ? 'Lulus' : 'Di Bawah Target' }}
                                </span>
                                
                                <Link 
                                    :href="route('tryout.result', item.id)"
                                    class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-indigo-600 transition-all active:scale-90 shadow-lg shadow-slate-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-slate-50 rounded-[2.5rem] p-16 md:p-24 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-6 text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em]">Belum Ada Sesi Ujian</p>
                    <Link :href="route('tryout.index')" class="mt-6 text-[10px] font-medium text-indigo-600 uppercase tracking-widest border-b border-indigo-100 pb-1">Mulai Tryout Pertama</Link>
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

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: slideUp 0.6s ease-out forwards;
}
</style>