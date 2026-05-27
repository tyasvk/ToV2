<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryouts: { 
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');

const filteredEvents = computed(() => {
    return (props.tryouts || []).filter(event => {
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

const formatEventDateTime = (event) => {
    const startDateRaw = event.started_at || event.start_date;
    const endDateRaw = event.end_date || event.ended_at;

    if (!startDateRaw) return 'Jadwal Belum Ditentukan';
    
    const start = new Date(startDateRaw);
    if (isNaN(start.getTime())) return 'Jadwal Belum Ditentukan';

    const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit' };
    
    const startDateStr = start.toLocaleDateString('id-ID', optionsDate);
    const startTimeStr = start.toLocaleTimeString('id-ID', optionsTime).replace('.', ':');

    if (!endDateRaw) {
        return `${startDateStr} • ${startTimeStr} WIB`;
    }

    const end = new Date(endDateRaw);
    if (isNaN(end.getTime())) {
        return `${startDateStr} • ${startTimeStr} WIB`;
    }

    const endDateStr = end.toLocaleDateString('id-ID', optionsDate);
    const endTimeStr = end.toLocaleTimeString('id-ID', optionsTime).replace('.', ':');

    if (startDateStr === endDateStr) {
        return `${startDateStr} • ${startTimeStr} - ${endTimeStr} WIB`;
    } else {
        return `${startDateStr} ${startTimeStr} - ${endDateStr} ${endTimeStr} WIB`;
    }
};

// HELPER: Cek apakah user sudah daftar/upload bukti (punya transaksi)
const hasRegistered = (event) => {
    return event.user_transaction || event.transaction || event.is_registered;
};
</script>

<template>
    <Head title="Event Tryout Akbar - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-4 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 px-4 md:px-0">
            
            <div class="bg-white p-5 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden mt-4 md:mt-0">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5 text-center md:text-left">
                    <h1 class="text-xl md:text-3xl text-slate-900 tracking-tight uppercase font-normal">Tryout Akbar Nusantara</h1>
                    <p class="text-sm text-slate-500 font-normal">Uji kemampuanmu bersama ribuan peserta lainnya secara serentak.</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari event..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-1 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-slate-700 placeholder:text-slate-400 shadow-sm outline-none font-normal"
                        >
                    </div>
                </div>
            </div>

            <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 pb-10">
                <div 
                    v-for="event in filteredEvents" 
                    :key="event.id"
                    class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col"
                >
                    <div class="relative h-48 md:h-60 bg-slate-100 overflow-hidden border-b border-slate-100 shrink-0">
                        
                        <div v-if="event.image" class="w-full h-full">
                            <img 
                                :src="'/storage/' + event.image" 
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                alt="Banner Event"
                            >
                        </div>
                        <div v-else class="w-full h-full bg-gradient-to-br from-[#004a87] to-blue-400 flex flex-col items-center justify-center p-4 group-hover:scale-105 transition-transform duration-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white/40 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                            <h3 class="text-white text-lg md:text-xl text-center uppercase tracking-widest drop-shadow-sm font-normal">
                                Tryout Akbar<br>Nasional
                            </h3>
                        </div>

                        <div class="absolute top-3 left-3 md:top-4 md:left-4">
                            <span :class="getStatusColor(event.status)" class="px-3 py-1 md:px-4 md:py-1.5 rounded-lg md:rounded-xl text-[10px] uppercase tracking-widest shadow-sm backdrop-blur-md border border-white/20 font-normal">
                                {{ event.status || 'Berlangsung' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 flex-1 flex flex-col">
                        
                        <div class="bg-blue-50/50 border border-blue-100/50 rounded-xl p-2.5 md:p-3 flex items-center gap-2.5 md:gap-3 mb-3 md:mb-4">
                            <div class="w-7 h-7 md:w-8 md:h-8 bg-white rounded-md md:rounded-lg flex items-center justify-center text-blue-600 shrink-0 shadow-sm border border-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 md:w-4 md:h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[9px] text-blue-400 uppercase tracking-widest font-normal">Pelaksanaan (Mulai - Selesai)</span>
                                <span class="text-[11px] md:text-xs text-blue-900 truncate tracking-wide font-normal">
                                    {{ formatEventDateTime(event) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 space-y-2.5 md:space-y-3">
                            <h2 class="text-base md:text-xl text-slate-900 leading-snug tracking-tight group-hover:text-blue-600 transition-colors uppercase line-clamp-2 font-normal">
                                {{ event.title }}
                            </h2>
                            
                            <p class="text-xs md:text-sm text-slate-500 line-clamp-2 leading-relaxed italic font-normal">
                                {{ event.description || 'Siapkan diri Anda untuk menghadapi simulasi ujian kompetitif bersama ribuan peserta.' }}
                            </p>

                            <div class="flex flex-wrap gap-2 py-1.5 md:py-2">
                                <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md">
                                    <svg class="w-3.5 h-3.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <span class="text-[10px] uppercase tracking-widest font-normal">{{ event.questions_count || 110 }} Soal</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-100 px-2 py-1 rounded-md">
                                    <svg class="w-3.5 h-3.5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-[10px] uppercase tracking-widest font-normal">{{ event.duration || 100 }} Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 md:mt-6 pt-4 md:pt-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="flex flex-col shrink-0 text-center md:text-left">
                                <span class="text-[9px] text-slate-400 uppercase tracking-widest mb-0.5 font-normal">Tiket Masuk</span>
                                <span class="text-sm md:text-base text-slate-900 tracking-tight leading-none font-normal">
                                    {{ event.price > 0 ? `Rp ${event.price.toLocaleString('id-ID')}` : 'GRATIS' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout-akbar.register', event.id)"
                                class="w-full md:w-auto px-6 md:px-8 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center font-normal flex items-center justify-center gap-2"
                                :class="hasRegistered(event) ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                            >
                                <span v-if="hasRegistered(event)">Masuk</span>
                                <span v-else>Daftar</span>

                                <svg v-if="hasRegistered(event)" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-10 md:p-16 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-base text-slate-900 mb-1 font-normal">Belum Ada Event</h3>
                <p class="text-sm text-slate-500 max-w-sm font-normal">Nantikan informasi event Tryout Akbar selanjutnya melalui channel resmi kami.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>