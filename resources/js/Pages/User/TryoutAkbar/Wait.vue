<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object,
    has_attempted: Boolean,
    attempt_id: Number,
});

// PENGURAI TANGGAL ANTI-GAGAL 
const getValidDate = (dateStr) => {
    if (!dateStr) return null;
    const cleanStr = String(dateStr).replace('T', ' ').replace('Z', '').split('.')[0].replace(/-/g, '/');
    const d = new Date(cleanStr);
    return isNaN(d.getTime()) ? null : d;
};

const formatEventDateTime = (event) => {
    const start = getValidDate(event?.started_at || event?.start_date);
    if (!start) return 'Jadwal Belum Ditentukan';
    
    const optsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optsTime = { hour: '2-digit', minute: '2-digit' };
    return `${start.toLocaleDateString('id-ID', optsDate)} • ${start.toLocaleTimeString('id-ID', optsTime).replace(/\./g, ':')} WIB`;
};

// LOGIKA HITUNG MUNDUR REALTIME
const isOpen = ref(false);
const timeRemaining = ref('Menghitung...');
let intervalId = null;

const checkSchedules = () => {
    const start = getValidDate(props.tryout?.started_at || props.tryout?.start_date);
    if (!start) {
        isOpen.value = false;
        timeRemaining.value = 'Jadwal belum tersedia';
        return;
    }

    const diff = start.getTime() - new Date().getTime();

    if (diff <= 0) {
        isOpen.value = true;
        timeRemaining.value = 'Waktu Habis'; 
    } else {
        isOpen.value = false;
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        const hStr = hours > 0 ? `${hours} Jam ` : '';
        const mStr = minutes > 0 ? `${minutes} Menit ` : '';
        timeRemaining.value = `${hStr}${mStr}${seconds} Detik`;
    }
};

const isApproved = computed(() => {
    const status = props.transaction?.status?.toLowerCase() || '';
    return ['paid', 'success', 'settlement', 'approved', 'sukses', 'lunas'].includes(status);
});

onMounted(() => {
    checkSchedules();
    intervalId = setInterval(checkSchedules, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

const refreshLobby = () => {
    window.location.reload();
};
</script>

<template>
    <Head title="Ruang Tunggu Event - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background Apple Default (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent min-h-[calc(100vh-80px)] flex flex-col items-center justify-center py-10 md:py-16 animate-in fade-in duration-500 font-sans px-4 sm:px-6">
            
            <!-- Lebar maksimal dibatasi agar tampil fokus di tengah seperti Dialog iCloud -->
            <div class="w-full max-w-2xl relative z-10">
                
                <div class="bg-white rounded-[32px] shadow-[0_8px_40px_rgba(0,0,0,0.06)] border border-black/5 overflow-hidden">
                    
                    <!-- HEADER KARTU -->
                    <div class="px-5 sm:px-8 py-4 sm:py-5 border-b border-black/5 flex justify-between items-center bg-white/80 backdrop-blur-xl">
                        
                        <Link :href="route('tryout-akbar.index')" class="inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#007AFF] hover:opacity-80 transition-opacity">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Kembali
                        </Link>
                        
                        <!-- Badge Status Singkat -->
                        <div v-if="isApproved" class="bg-[#E5F5EA] text-[#34C759] px-3.5 py-1.5 rounded-full border border-[#34C759]/20 shadow-sm">
                            <span class="text-[11px] font-bold uppercase tracking-wider">Terverifikasi</span>
                        </div>
                        <div v-else class="bg-[#FFF9E6] text-[#FF9500] px-3.5 py-1.5 rounded-full border border-[#FF9500]/20 shadow-sm">
                            <span class="text-[11px] font-bold uppercase tracking-wider">Menunggu Admin</span>
                        </div>
                    </div>

                    <!-- BODY KARTU -->
                    <div class="p-8 sm:p-12 text-center">
                        
                        <!-- KONDISI 1: SUDAH MENGERJAKAN -->
                        <div v-if="has_attempted" class="space-y-4 animate-in fade-in slide-in-from-bottom-2 duration-500">
                            <div class="w-20 h-20 bg-[#E5F5EA] text-[#34C759] rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            
                            <span class="text-[12px] text-[#86868B] font-bold uppercase tracking-widest block mb-2">
                                Simulasi Selesai
                            </span>
                            <h2 class="text-[24px] sm:text-[28px] font-bold text-[#1D1D1F] leading-tight">
                                Ujian Telah Diselesaikan
                            </h2>
                            <p class="text-[#86868B] text-[14px] sm:text-[15px] mt-3 max-w-md mx-auto leading-relaxed">
                                Anda telah menyelesaikan <strong class="text-[#1D1D1F] font-semibold">{{ tryout?.title }}</strong>. Terima kasih atas dedikasi dan partisipasi Anda.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-8 pt-4 w-full max-w-md mx-auto">
                                <Link v-if="attempt_id" :href="route('tryout.result', attempt_id)" class="flex-1 flex items-center justify-center py-3.5 bg-[#007AFF] text-white font-semibold text-[15px] rounded-full hover:bg-[#0062CC] transition-all shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98]">
                                    Lihat Skor & Hasil
                                </Link>
                                
                                <Link v-if="tryout?.id" :href="route('tryout.leaderboard', tryout.id)" class="flex-1 flex items-center justify-center py-3.5 bg-[#F2F2F7] border border-black/5 text-[#1D1D1F] font-semibold text-[15px] rounded-full hover:bg-[#E3E3E8] transition-all active:scale-[0.98]">
                                    Papan Peringkat
                                </Link>
                            </div>
                        </div>

                        <!-- KONDISI 2: LOBBY / BELUM MENGERJAKAN -->
                        <div v-else class="animate-in fade-in slide-in-from-bottom-2 duration-500 flex flex-col items-center">
                            
                            <!-- Indikator Live Pulse -->
                            <div class="inline-flex items-center justify-center gap-2 mb-6 bg-[#F0F4FF] border border-[#007AFF]/20 px-4 py-1.5 rounded-full shadow-sm">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#007AFF] opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#007AFF]"></span>
                                </span>
                                <span class="text-[11px] text-[#007AFF] font-bold uppercase tracking-widest">Ruang Tunggu Aktif</span>
                            </div>

                            <div class="mb-8 space-y-2">
                                <span class="text-[12px] text-[#86868B] font-bold uppercase tracking-widest block">
                                    Tryout Akbar Nasional
                                </span>
                                <h1 class="text-[26px] sm:text-[32px] font-bold text-[#1D1D1F] leading-tight max-w-lg mx-auto">
                                    {{ tryout?.title }}
                                </h1>
                            </div>

                            <!-- Info Waktu Box iCloud Style -->
                            <div class="bg-[#F5F5F7] border border-black/5 rounded-[24px] p-5 mb-10 w-full max-w-sm mx-auto shadow-sm">
                                <div class="flex items-center justify-center gap-3 mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#86868B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[11px] text-[#86868B] font-bold uppercase tracking-wider">Jadwal Pelaksanaan</span>
                                </div>
                                <p class="text-[15px] text-[#1D1D1F] font-semibold">
                                    {{ formatEventDateTime(tryout) }}
                                </p>
                            </div>

                            <div class="w-full max-w-md mx-auto">
                                
                                <!-- Belum Disetujui -->
                                <div v-if="!isApproved" class="text-left bg-[#FFF9E6] p-5 sm:p-6 rounded-[24px] border border-[#FF9500]/20 shadow-sm mb-4">
                                    <div class="flex items-start gap-3 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#FF9500] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <div>
                                            <h4 class="text-[15px] font-bold text-[#1D1D1F] mb-1">Pendaftaran Belum Tervalidasi</h4>
                                            <p class="text-[13px] text-[#86868B] leading-relaxed font-medium">Akun atau bukti pembayaran Anda masih dalam proses peninjauan oleh admin.</p>
                                        </div>
                                    </div>
                                    <button @click="refreshLobby" class="w-full py-3.5 bg-white hover:bg-amber-50 border border-amber-200 text-[#FF9500] transition-colors font-semibold text-[14px] rounded-full active:scale-[0.98]">
                                        Segarkan Status
                                    </button>
                                </div>
                                
                                <!-- Disetujui Tapi Belum Mulai (Hitung Mundur) -->
                                <div v-else-if="isApproved && !isOpen" class="space-y-6">
                                    <div>
                                        <p class="text-[#86868B] text-[14px] font-semibold mb-3 uppercase tracking-wider">Ujian Dimulai Dalam</p>
                                        <!-- Angka Monospace Besar iCloud -->
                                        <div class="text-[32px] sm:text-[42px] font-semibold text-[#1D1D1F] font-mono tracking-tight animate-pulse tabular-nums leading-none">
                                            {{ timeRemaining }}
                                        </div>
                                    </div>
                                    <!-- Disabled Button -->
                                    <button disabled class="w-full py-3.5 bg-[#E3E3E8] text-[#86868B] font-semibold text-[15px] rounded-full cursor-not-allowed border border-black/5">
                                        Menunggu Waktu Ujian
                                    </button>
                                </div>

                                <!-- Disetujui & Waktu Sudah Mulai -->
                                <div v-else-if="isApproved && isOpen" class="space-y-6">
                                    <div class="text-left bg-[#E5F5EA] p-5 sm:p-6 rounded-[24px] border border-[#34C759]/20 shadow-sm flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-white text-[#34C759] flex items-center justify-center shrink-0 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-[15px] font-bold text-[#1D1D1F] mb-1">Akses Ujian Dibuka</h4>
                                            <p class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                                                Waktu pengerjaan telah aktif. Silakan masuk ke ruang ujian sekarang dan kerjakan dengan teliti.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <Link 
                                        v-if="tryout?.id"
                                        :href="route('tryout.exam', tryout.id)"
                                        class="w-full flex items-center justify-center py-3.5 sm:py-4 bg-[#007AFF] hover:bg-[#0062CC] text-white font-semibold text-[15px] rounded-full transition-all shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98] gap-2"
                                    >
                                        Masuk Ruang Ujian
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </Link>
                                    
                                    <!-- Fallback Link jika route Inertia bermasalah -->
                                    <a 
                                        v-else
                                        :href="`/tryouts/${tryout?.id}/exam`"
                                        class="w-full flex items-center justify-center py-3.5 sm:py-4 bg-[#007AFF] hover:bg-[#0062CC] text-white font-semibold text-[15px] rounded-full transition-all shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98] gap-2"
                                    >
                                        Masuk Ruang Ujian
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>