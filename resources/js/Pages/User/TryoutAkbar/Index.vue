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
        case 'aktif': return 'bg-emerald-500';
        case 'mendatang': return 'bg-amber-500';
        default: return 'bg-slate-400';
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
    <Head title="Event Tryout Akbar" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-2 px-1">
                    <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight uppercase">Tryout Akbar Nusantara</h1>
                    <p class="text-[11px] md:text-sm text-slate-500 font-medium leading-relaxed">
                        Uji kemampuanmu bersama ribuan peserta lainnya secara serentak.
                    </p>
                </div>

                <div class="w-full md:w-72">
                    <div class="relative group">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari event..."
                            class="w-full bg-white border-slate-200 rounded-2xl px-5 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium placeholder:text-slate-400 shadow-sm"
                        >
                    </div>
                </div>
            </div>

            <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <div 
                    v-for="event in filteredEvents" 
                    :key="event.id"
                    class="group bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] overflow-hidden hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 flex flex-col"
                >
                    <div class="relative h-48 md:h-56 bg-slate-900 overflow-hidden">
                        <img 
                            :src="event.image_url || '/images/default-akbar.png'" 
                            class="w-full h-full object-cover opacity-80 group-hover:scale-105 group-hover:opacity-100 transition-all duration-1000"
                        >
                        <div class="absolute top-4 left-4 md:top-6 md:left-6">
                            <span :class="getStatusColor(event.status)" class="px-3 py-1 md:px-4 md:py-1.5 rounded-xl text-[8px] md:text-[9px] font-medium text-white uppercase tracking-[0.2em] shadow-xl backdrop-blur-md">
                                {{ event.status || 'Berlangsung' }}
                            </span>
                        </div>
                        
                        <div class="absolute bottom-4 left-4 right-4 md:bottom-6 md:left-6 md:right-6">
                            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl md:rounded-2xl p-3 md:p-4 flex items-center gap-3">
                                <div class="w-8 h-8 md:w-10 md:h-10 bg-white/20 rounded-lg md:rounded-xl flex items-center justify-center text-white shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[7px] md:text-[8px] text-white/60 uppercase tracking-widest font-medium">Tanggal Pelaksanaan</span>
                                    <span class="text-[10px] md:text-xs text-white font-medium truncate">{{ formatEventDate(event.start_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 flex-1 flex flex-col">
                        <div class="flex-1 space-y-3 md:space-y-4">
                            <h2 class="text-lg md:text-xl font-medium text-slate-900 leading-snug tracking-tight group-hover:text-indigo-600 transition-colors uppercase">
                                {{ event.title }}
                            </h2>
                            
                            <p class="text-xs md:text-sm text-slate-500 font-medium line-clamp-2 leading-relaxed italic opacity-80">
                                {{ event.description || 'Siapkan diri Anda untuk menghadapi simulasi ujian paling kompetitif tahun ini.' }}
                            </p>

                            <div class="flex flex-wrap gap-x-4 gap-y-2 py-2">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <div class="w-1 h-1 bg-indigo-400 rounded-full"></div>
                                    <span class="text-[9px] md:text-[10px] uppercase tracking-widest font-medium">{{ event.questions_count || 110 }} Soal</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-400">
                                    <div class="w-1 h-1 bg-orange-400 rounded-full"></div>
                                    <span class="text-[9px] md:text-[10px] uppercase tracking-widest font-medium">{{ event.duration || 100 }} Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 md:mt-8 pt-5 md:pt-6 border-t border-slate-50 flex items-center justify-between gap-4">
                            <div class="flex flex-col shrink-0">
                                <span class="text-[7px] md:text-[8px] text-slate-400 uppercase tracking-widest font-medium mb-1">Tiket Masuk</span>
                                <span class="text-base md:text-lg font-medium text-indigo-600 tracking-tighter leading-none">
                                    {{ event.price > 0 ? `Rp ${event.price.toLocaleString('id-ID')}` : 'FREE ACCESS' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout-akbar.register', event.id)"
                                class="bg-slate-900 hover:bg-indigo-600 text-white px-5 md:px-8 py-3 md:py-4 rounded-xl md:rounded-2xl text-[9px] md:text-[10px] font-medium uppercase tracking-[0.2em] transition-all active:scale-95 shadow-xl shadow-slate-100 text-center"
                            >
                                Daftar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-100 rounded-[2rem] md:rounded-[3rem] p-12 md:p-20 flex flex-col items-center text-center">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-50 rounded-2xl md:rounded-[1.5rem] flex items-center justify-center mb-6 text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 h-8 md:w-10 md:h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-base md:text-lg font-medium text-slate-900 uppercase tracking-widest">Belum Ada Event</h3>
                <p class="text-[11px] md:text-sm text-slate-500 font-medium mt-2">Nantikan informasi event selanjutnya melalui channel resmi kami.</p>
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

/* Custom shadow for clean modern look */
.hover\:shadow-2xl:hover {
    shadow-color: rgba(226, 232, 240, 0.4);
}
</style>