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

// Warna Status ala Badge Apple (Soft & Clean)
const getStatusBadge = (status) => {
    if (isLive(status)) {
        return 'bg-white/90 text-[#007AFF] border-white/40 shadow-sm'; // Biru khas Apple untuk Aktif
    }
    
    switch (status?.toLowerCase()) {
        case 'mendatang': 
        case 'upcoming':
            return 'bg-white/90 text-amber-600 border-white/40 shadow-sm';
        default: 
            return 'bg-white/90 text-slate-600 border-white/40 shadow-sm';
    }
};

// Gradasi ala Apple Music / App Store untuk Fallback Image
const cardGradients = [
    'from-[#FF2A54] via-[#FF5E3A] to-[#FF9B00]',
    'from-[#007AFF] via-[#33C1FF] to-[#5AC8FA]',
    'from-[#AF52DE] via-[#D53AF5] to-[#FF2A54]',
    'from-[#34C759] via-[#30D158] to-[#34C759]'
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
        <!-- Background Apple Default (#F5F5F7) -->
        <div class="w-full bg-transparent pb-32 md:pb-24 animate-in fade-in duration-500 font-sans">

            <!-- Dipersempit maksimal 5xl agar teks panjang tetap nyaman dibaca, tidak terlalu melebar ekstrem -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 md:pt-12 space-y-8 md:space-y-12">

                <!-- HEADER & PENCARIAN -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative z-10">
                    <div class="space-y-2 max-w-2xl">
                        <h1 class="text-[28px] sm:text-[36px] lg:text-[42px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                            Tryout Akbar Nusantara
                        </h1>
                        <p class="text-[15px] sm:text-[17px] text-[#86868B] font-medium leading-relaxed">
                            Uji kemampuan terbaikmu bersama puluhan ribu pejuang seleksi kedinasan dan CPNS lainnya dalam satu simulasi serentak berskala nasional.
                        </p>
                    </div>

                    <!-- Search Input ala Spotlight iOS -->
                    <div class="relative w-full md:w-[320px] shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-[#86868B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari event simulasi..."
                            class="w-full bg-[#E3E3E8]/80 hover:bg-[#E3E3E8] border-transparent rounded-[14px] pl-11 pr-4 py-3 sm:py-3.5 text-[15px] font-medium focus:bg-white focus:ring-4 focus:ring-[#007AFF]/20 focus:border-[#007AFF] transition-all duration-300 text-[#1D1D1F] placeholder:text-[#86868B] outline-none"
                        >
                    </div>
                </div>

                <!-- DAFTAR EVENT (Layout 1 Kolom Penuh - Melebar ke Samping di Desktop) -->
                <div v-if="filteredEvents.length > 0" class="grid grid-cols-1 gap-8 pb-10">

                    <div 
                        v-for="(event, index) in filteredEvents" 
                        :key="event.id"
                        class="group relative bg-white rounded-[28px] sm:rounded-[32px] shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] border border-black/[0.03] overflow-hidden flex flex-col md:flex-row transition-all duration-500 transform hover:-translate-y-1.5"
                    >
                        
                        <!-- Gambar Cover / Header App Store Style -->
                        <!-- Di Mobile: tinggi tetap. Di Desktop: lebar 40% dari kartu -->
                        <div class="relative h-60 md:h-auto md:w-[40%] overflow-hidden shrink-0 bg-[#F5F5F7]">
                            <img v-if="event.image" :src="'/storage/' + event.image" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out" alt="Banner Event">

                            <!-- Fallback Gradient Abstrak (Jika tidak ada gambar) -->
                            <div v-else class="absolute inset-0 w-full h-full bg-gradient-to-br flex flex-col items-center justify-center p-6 group-hover:scale-105 transition-transform duration-700 ease-out overflow-hidden" :class="getCardGradient(index)">
                                <!-- Ornamen Blur Cair -->
                                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/20 rounded-full blur-3xl mix-blend-overlay"></div>
                                <div class="absolute -left-12 -top-12 w-40 h-40 bg-black/10 rounded-full blur-2xl mix-blend-overlay"></div>
                                
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-16 w-16 text-white/90 mb-3 drop-shadow-sm">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.974 0-5.699-1.088-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                <h3 class="text-white text-[22px] sm:text-[26px] text-center tracking-tight font-semibold drop-shadow-sm leading-tight">
                                    Tryout Akbar<br>Nasional
                                </h3>
                            </div>

                            <!-- Badge Status Transparan (Dilengkapi Animasi Live) -->
                            <div class="absolute top-5 left-5 z-10">
                                <span :class="getStatusBadge(event.status)" class="px-3.5 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-widest backdrop-blur-xl border shadow-[0_2px_8px_rgba(0,0,0,0.08)] inline-flex items-center gap-2">
                                    <!-- Indikator Dot Pulsing (Hanya aktif jika status Live/Berlangsung) -->
                                    <span v-if="isLive(event.status)" class="relative flex h-2.5 w-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-current opacity-60"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-current"></span>
                                    </span>
                                    
                                    {{ event.status || 'Berlangsung' }}
                                </span>
                            </div>
                        </div>

                        <!-- Informasi Event (Sebelah Kanan pada Desktop) -->
                        <div class="p-6 sm:p-8 lg:p-10 flex-1 flex flex-col bg-white">

                            <h2 class="text-[20px] sm:text-[24px] lg:text-[28px] font-semibold text-[#1D1D1F] leading-tight tracking-tight mb-3 break-words">
                                {{ event.title }}
                            </h2>

                            <p class="text-[14px] sm:text-[15px] lg:text-[16px] text-[#86868B] leading-relaxed font-normal mb-8 break-words">
                                {{ event.description || 'Persiapkan mental dan strategi terbaikmu. Hadapi soal-soal berkualitas tinggi berstandar CAT BKN dengan sistem penilaian akurat.' }}
                            </p>

                            <div class="mt-auto space-y-6">
                                
                                <!-- Waktu & Info (iCloud Detail Box) -->
                                <div class="bg-[#F5F5F7] rounded-[20px] p-5 sm:p-6 flex flex-col xl:flex-row xl:items-start justify-between gap-6">
                                    
                                    <!-- Pelaksanaan Atas-Bawah (Vertical Stack) -->
                                    <div class="flex items-start gap-4 flex-1">
                                        <!-- Ikon Kalender -->
                                        <div class="w-11 h-11 rounded-full bg-white text-[#007AFF] flex items-center justify-center shrink-0 shadow-sm border border-black/5 hidden sm:flex">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                        </div>
                                        
                                        <!-- Informasi Mulai & Selesai -->
                                        <div class="flex flex-col gap-3 sm:gap-4 w-full min-w-0">
                                            <!-- Mulai -->
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-[11px] text-[#86868B] font-semibold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                                    Tryout Mulai
                                                </span>
                                                <span class="text-[14px] sm:text-[15px] text-[#1D1D1F] font-semibold leading-tight break-words">
                                                    {{ formatStart(event) }}
                                                </span>
                                            </div>

                                            <!-- Garis pemisah halus hanya di mobile -->
                                            <div class="w-full h-px bg-black/5 sm:hidden"></div>

                                            <!-- Selesai -->
                                            <div class="flex flex-col min-w-0">
                                                <span class="text-[11px] text-[#86868B] font-semibold uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                                    Tryout Selesai
                                                </span>
                                                <span class="text-[14px] sm:text-[15px] text-[#1D1D1F] font-semibold leading-tight break-words">
                                                    {{ formatEnd(event) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detail Soal & Waktu -->
                                    <div class="flex xl:flex-col gap-3 xl:gap-2 xl:items-end border-t xl:border-t-0 xl:border-l border-black/5 pt-4 xl:pt-0 xl:pl-6 shrink-0 mt-4 xl:mt-0">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#86868B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                            <span class="text-[12px] text-[#1D1D1F] font-semibold">{{ event.questions_count || 110 }} Soal</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#86868B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            <span class="text-[12px] text-[#1D1D1F] font-semibold">{{ event.duration || 100 }} Menit</span>
                                        </div>
                                    </div>

                                </div>

                                <!-- Footer (Harga & Tombol iCloud Style - Menyatu di dalam Body) -->
                                <div class="flex items-center justify-between gap-4 pt-2">
                                    <div class="flex flex-col shrink-0">
                                        <span class="text-[11px] text-[#86868B] font-semibold uppercase tracking-wider mb-0.5">Tiket Masuk</span>
                                        <span class="text-[18px] sm:text-[24px] text-[#1D1D1F] font-bold tracking-tight leading-none">
                                            {{ event.price > 0 ? `Rp ${event.price.toLocaleString('id-ID')}` : 'Gratis' }}
                                        </span>
                                    </div>

                                    <Link 
                                        :href="route('tryout-akbar.register', event.id)"
                                        class="px-8 sm:px-10 py-3 sm:py-3.5 rounded-full text-[14px] sm:text-[15px] font-semibold transition-all active:scale-[0.98] text-center flex items-center justify-center gap-2"
                                        :class="hasRegistered(event) ? 'bg-[#F2F2F7] hover:bg-[#E3E3E8] text-[#1D1D1F]' : 'bg-[#007AFF] hover:bg-[#0062CC] text-white shadow-[0_4px_14px_rgba(0,122,255,0.3)]'"
                                    >
                                        <span v-if="hasRegistered(event)">Masuk Kelas</span>
                                        <span v-else>Daftar Sekarang</span>
                                    </Link>
                                </div>
                            </div>
                            
                        </div>

                    </div>

                </div>

                <!-- EMPTY STATE (Sangat Clean ala Apple) -->
                <div v-else class="bg-white rounded-[32px] p-16 sm:p-24 flex flex-col items-center text-center shadow-[0_8px_30px_rgba(0,0,0,0.02)] border border-black/5 mt-4">
                    <div class="w-20 h-20 bg-[#F5F5F7] text-[#86868B] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="text-[20px] sm:text-[24px] text-[#1D1D1F] mb-2 font-semibold">Tidak Ada Event Tersedia</h3>
                    <p class="text-[14px] sm:text-[15px] text-[#86868B] font-normal max-w-md px-4 leading-relaxed">
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