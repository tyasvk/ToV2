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

// Mengecek apakah statusnya sedang Berlangsung / Aktif untuk animasi LIVE
const isLive = (status) => {
    const s = (status || 'Berlangsung').toLowerCase();
    return s === 'aktif' || s === 'berlangsung' || s === 'ongoing';
};

// Warna Status Badge (Luxurious Gold Style)
const getStatusBadge = (status) => {
    if (isLive(status)) {
        return 'bg-amber-50 text-amber-700 border-amber-200 shadow-sm';
    }
    
    switch (status?.toLowerCase()) {
        case 'mendatang': 
        case 'upcoming':
            return 'bg-orange-50 text-orange-700 border-orange-200 shadow-sm';
        default: 
            return 'bg-slate-100 text-slate-600 border-slate-200 shadow-sm';
    }
};

// Gradasi Premium (Kombinasi Black VIP & Gold) untuk Fallback Image
const cardGradients = [
    'from-slate-900 via-slate-800 to-black', // Premium Black
    'from-amber-400 via-amber-500 to-orange-500', // Pure Gold
    'from-slate-800 via-slate-900 to-stone-900', // Deep Slate
    'from-orange-400 via-orange-500 to-red-500' // Sunset Orange
];

const getCardGradient = (index) => {
    return cardGradients[index % cardGradients.length];
};

// HELPER: Memformat Tanggal Secara Spesifik
const formatSpecificDate = (dateRaw) => {
    if (!dateRaw) return 'Belum Ditentukan';
    
    const d = new Date(dateRaw);
    if (isNaN(d.getTime())) return 'Belum Ditentukan';

    const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit' };
    
    const dStr = d.toLocaleDateString('id-ID', optionsDate);
    const tStr = d.toLocaleTimeString('id-ID', optionsTime).replace('.', ':');

    return `${dStr} • ${tStr} WIB`;
};

const formatStart = (event) => formatSpecificDate(event.started_at || event.start_date);
const formatEnd = (event) => formatSpecificDate(event.end_date || event.ended_at);

const hasRegistered = (event) => {
    return event.user_transaction || event.transaction || event.is_registered;
};
</script>

<template>
    <Head title="Event Tryout Akbar - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="w-full bg-transparent pb-32 md:pb-24 animate-in fade-in duration-500 font-sans">

            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-8 md:space-y-10">

                <!-- HEADER & PENCARIAN -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
                    <div class="space-y-2 max-w-2xl">
                        <h1 class="text-[26px] sm:text-[32px] lg:text-[38px] font-bold text-slate-900 tracking-tight leading-tight">
                            Tryout Akbar Nusantara
                        </h1>
                        <p class="text-[14px] sm:text-[16px] text-slate-500 font-medium leading-relaxed">
                            Uji kemampuan terbaikmu bersama puluhan ribu pejuang seleksi kedinasan dan CPNS lainnya dalam satu simulasi serentak berskala nasional.
                        </p>
                    </div>

                    <!-- Search Input (Golden Focus) -->
                    <div class="relative w-full md:w-[300px] shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari event simulasi..."
                            class="w-full bg-slate-100 border border-slate-200 rounded-[14px] pl-10 pr-4 py-3 text-[14px] font-medium focus:bg-white focus:ring-4 focus:ring-amber-500/15 focus:border-amber-500 transition-all duration-300 text-slate-900 placeholder:text-slate-400 outline-none shadow-sm"
                        >
                    </div>
                </div>

                <!-- DAFTAR EVENT -->
                <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 gap-6 pb-10">

                    <div 
                        v-for="(event, index) in filteredEvents" 
                        :key="event.id"
                        class="group relative bg-white rounded-[24px] shadow-sm hover:shadow-md border border-slate-200 overflow-hidden flex flex-col md:flex-row transition-all duration-300"
                    >
                        
                        <!-- Gambar Cover -->
                        <div class="relative h-52 md:h-auto md:w-[38%] overflow-hidden shrink-0 bg-slate-900">
                            <img v-if="event.image" :src="'/storage/' + event.image" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out opacity-90 group-hover:opacity-100" alt="Banner Event">

                            <!-- Fallback Gradient VIP -->
                            <div v-else class="absolute inset-0 w-full h-full bg-gradient-to-br flex flex-col items-center justify-center p-6 group-hover:scale-105 transition-transform duration-500 ease-out overflow-hidden text-white" :class="getCardGradient(index)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-12 w-12 text-white/90 mb-2 drop-shadow-md">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.974 0-5.699-1.088-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                <h3 class="text-[20px] text-center font-bold tracking-tight drop-shadow-md" :class="(index % 2 === 0) ? 'text-amber-400' : 'text-white'">
                                    Tryout Akbar Nasional
                                </h3>
                            </div>

                            <!-- Badge Status -->
                            <div class="absolute top-4 left-4 z-10">
                                <span :class="getStatusBadge(event.status)" class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border backdrop-blur-md inline-flex items-center gap-1.5 shadow-sm">
                                    <span v-if="isLive(event.status)" class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-500 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-600"></span>
                                    </span>
                                    {{ event.status || 'Berlangsung' }}
                                </span>
                            </div>
                        </div>

                        <!-- Informasi Event -->
                        <div class="p-6 sm:p-7 flex-1 flex flex-col bg-white justify-between">

                            <div>
                                <h2 class="text-[18px] sm:text-[22px] font-bold text-slate-900 leading-tight tracking-tight mb-2">
                                    {{ event.title }}
                                </h2>

                                <p class="text-[13px] sm:text-[14px] text-slate-500 leading-relaxed font-normal mb-6">
                                    {{ event.description || 'Persiapkan mental dan strategi terbaikmu. Hadapi soal-soal berkualitas tinggi berstandar CAT BKN dengan sistem penilaian akurat.' }}
                                </p>
                            </div>

                            <div class="space-y-5">
                                <!-- Waktu Pelaksanaan Box -->
                                <div class="bg-slate-50 rounded-2xl p-4 flex flex-col xl:flex-row xl:items-center justify-between gap-4 border border-slate-100">
                                    
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
                                        <!-- Mulai -->
                                        <div class="flex flex-col flex-1">
                                            <span class="text-[10px] text-amber-600 font-bold uppercase tracking-wider mb-0.5">Mulai</span>
                                            <span class="text-[13px] text-slate-800 font-semibold leading-tight">
                                                {{ formatStart(event) }}
                                            </span>
                                        </div>

                                        <div class="hidden sm:block h-8 w-px bg-slate-200"></div>
                                        <div class="w-full h-px bg-slate-200 sm:hidden"></div>

                                        <!-- Selesai -->
                                        <div class="flex flex-col flex-1">
                                            <span class="text-[10px] text-amber-600 font-bold uppercase tracking-wider mb-0.5">Selesai</span>
                                            <span class="text-[13px] text-slate-800 font-semibold leading-tight">
                                                {{ formatEnd(event) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Detail Soal & Durasi -->
                                    <div class="flex xl:flex-col gap-3 xl:gap-1 text-slate-500 text-[12px] font-semibold border-t xl:border-t-0 xl:border-l border-slate-200 pt-3 xl:pt-0 xl:pl-4 shrink-0">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                            <span>{{ event.questions_count || 110 }} Soal</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            <span>{{ event.duration || 100 }} Menit</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Harga & Tombol -->
                                <div class="flex items-center justify-between gap-4 pt-1">
                                    <div class="flex flex-col shrink-0">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">Tiket Masuk</span>
                                        <span class="text-[18px] sm:text-[22px] text-slate-900 font-extrabold tracking-tight leading-none">
                                            {{ event.price > 0 ? `Rp ${event.price.toLocaleString('id-ID')}` : 'Gratis' }}
                                        </span>
                                    </div>

                                    <Link 
                                        :href="route('tryout-akbar.register', event.id)"
                                        class="px-6 sm:px-8 py-3 rounded-xl text-[13px] font-bold transition-all active:scale-[0.98] text-center flex items-center justify-center gap-2 shadow-sm"
                                        :class="hasRegistered(event) ? 'bg-slate-900 hover:bg-black text-white shadow-slate-900/20' : 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white shadow-orange-500/25'"
                                    >
                                        <span v-if="hasRegistered(event)">Masuk Kelas</span>
                                        <span v-else>Daftar Sekarang</span>
                                    </Link>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- EMPTY STATE -->
                <div v-else class="bg-white rounded-[24px] p-16 sm:p-20 flex flex-col items-center text-center shadow-sm border border-slate-200 mt-4">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[20px] text-slate-900 mb-1 font-bold">Tidak Ada Event Tersedia</h3>
                    <p class="text-[13px] sm:text-[14px] text-slate-500 font-normal max-w-sm px-4 leading-relaxed">
                        Saat ini belum ada jadwal simulasi akbar. Pastikan akunmu sudah terdaftar untuk mendapat notifikasi saat event baru dibuka.
                    </p>
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