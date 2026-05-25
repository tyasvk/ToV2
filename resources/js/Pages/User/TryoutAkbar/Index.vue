<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryoutAkbars: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');

const filteredEvents = computed(() => {
    return (props.tryoutAkbars || []).filter(event => {
        return event.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

const getStatusColor = (status) => {
    switch (status?.toLowerCase()) {
        case 'aktif': return 'bg-emerald-500 text-white';
        case 'mendatang': return 'bg-amber-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};

const formatEventDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Event Tryout Akbar - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight uppercase">Tryout Akbar Nusantara</h1>
                    <p class="text-sm text-slate-500 font-medium">Uji kemampuanmu bersama ribuan peserta lainnya secara serentak.</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari event..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none"
                        >
                    </div>
                </div>
            </div>

            <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 pb-10">
                <div 
                    v-for="event in filteredEvents" 
                    :key="event.id"
                    class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col"
                >
                    <div class="relative h-48 md:h-56 bg-slate-100 overflow-hidden border-b border-slate-100">
                        <img 
                            :src="event.image_url || '/images/default-akbar.png'" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                        >
                        <div class="absolute top-4 left-4">
                            <span :class="getStatusColor(event.status)" class="px-3 py-1.5 md:px-4 md:py-1.5 rounded-lg md:rounded-xl text-[9px] font-bold uppercase tracking-widest shadow-sm backdrop-blur-md border border-white/20">
                                {{ event.status || 'Berlangsung' }}
                            </span>
                        </div>
                        
                        <div class="absolute bottom-4 left-4 right-4 md:bottom-5 md:left-5 md:right-5">
                            <div class="bg-slate-900/70 backdrop-blur-md border border-white/10 rounded-xl p-3 flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-white shrink-0 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[8px] text-slate-300 uppercase tracking-widest font-semibold">Pelaksanaan</span>
                                    <span class="text-xs text-white font-bold truncate tracking-wide">{{ formatEventDate(event.start_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 md:p-6 flex-1 flex flex-col">
                        <div class="flex-1 space-y-3">
                            <h2 class="text-lg md:text-xl font-bold text-slate-900 leading-snug tracking-tight group-hover:text-blue-600 transition-colors uppercase line-clamp-2">
                                {{ event.title }}
                            </h2>
                            
                            <p class="text-sm text-slate-500 font-medium line-clamp-2 leading-relaxed italic">
                                {{ event.description || 'Siapkan diri Anda untuk menghadapi simulasi ujian kompetitif bersama ribuan peserta.' }}
                            </p>

                            <div class="flex flex-wrap gap-3 py-2">
                                <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md">
                                    <svg class="w-3.5 h-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <span class="text-[10px] uppercase tracking-widest font-bold">{{ event.questions_count || 110 }} Soal</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md">
                                    <svg class="w-3.5 h-3.5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-[10px] uppercase tracking-widest font-bold">{{ event.duration || 100 }} Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex flex-col shrink-0">
                                <span class="text-[9px] text-slate-400 uppercase tracking-widest font-semibold mb-0.5">Tiket Masuk</span>
                                <span class="text-base font-bold text-slate-900 tracking-tight leading-none">
                                    {{ event.price > 0 ? `Rp ${event.price.toLocaleString('id-ID')}` : 'GRATIS' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout-akbar.register', event.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 md:px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center"
                            >
                                Daftar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-16 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Belum Ada Event</h3>
                <p class="text-sm text-slate-500 font-medium max-w-sm">Nantikan informasi event Tryout Akbar selanjutnya melalui channel resmi kami.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>