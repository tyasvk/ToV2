<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object,    // <--- Duplikat dihapus
    has_attempted: Boolean, // <--- Penanda sudah dikerjakan
    attempt_id: Number,     // <--- ID attempt untuk route hasil
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
        timeRemaining.value = 'WAKTU HABIS'; 
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
    <Head title="Ruang Tunggu Event" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50/50 font-sans flex flex-col justify-start md:justify-center relative overflow-hidden md:py-12">
            
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-indigo-100/40 rounded-full blur-3xl opacity-60 pointer-events-none hidden md:block"></div>

            <div class="w-full md:max-w-lg lg:max-w-xl mx-auto relative z-10">
                
                <div class="bg-white/95 backdrop-blur-sm rounded-none md:rounded-3xl shadow-xl shadow-slate-200/40 border-b md:border border-slate-100 overflow-hidden">
                    
                    <div class="px-4 py-3 md:px-6 md:py-4 border-b border-slate-50 flex justify-between items-center bg-white/80">
                        
                        <Link :href="route('tryout-akbar.index')" class="inline-flex items-center gap-1.5 text-[10px] md:text-xs text-slate-500 hover:text-indigo-600 transition-colors font-normal bg-slate-50 hover:bg-slate-100 px-3 py-1.5 md:px-4 md:py-2 rounded-full border border-slate-200/60 shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Keluar</span>
                        </Link>
                        
                        <div v-if="isApproved" class="bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-md border border-emerald-100/50">
                            <span class="text-[9px] md:text-[10px] font-normal uppercase tracking-wider">Terverifikasi</span>
                        </div>
                        <div v-else class="bg-amber-50 text-amber-600 px-3 py-1.5 rounded-md border border-amber-100/50">
                            <span class="text-[9px] md:text-[10px] font-normal uppercase tracking-wider">Menunggu Admin</span>
                        </div>
                    </div>

                    <div class="p-6 md:p-10 text-center">
                        
                        <div v-if="has_attempted" class="space-y-2 relative z-10 animate-in fade-in slide-in-from-bottom-2 duration-500">
                            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            
                            <span class="text-[10px] md:text-xs text-indigo-500 font-normal uppercase tracking-[0.2em] block mb-2">
                                Simulasi Akbar
                            </span>
                            <h2 class="text-xl md:text-2xl font-medium text-slate-800 leading-snug">
                                Ujian Telah Selesai
                            </h2>
                            <p class="text-slate-500 text-xs md:text-sm mt-2 px-4">
                                Anda telah menyelesaikan tryout <strong class="text-slate-700 font-medium">{{ tryout?.title }}</strong>. Terima kasih atas partisipasi Anda.
                            </p>
                            
                            <div class="flex flex-col gap-3 mt-8 pt-4">
                                <Link v-if="attempt_id" :href="route('tryout.result', attempt_id)" class="w-full flex items-center justify-center py-3.5 md:py-4 bg-indigo-600 text-white font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95">
                                    Lihat Hasil Ujian
                                </Link>
                                
                                <Link v-if="tryout?.id" :href="route('tryout.leaderboard', tryout.id)" class="w-full flex items-center justify-center py-3.5 md:py-4 bg-white border border-slate-200 text-slate-700 font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                                    Lihat Peringkat
                                </Link>
                            </div>
                        </div>

                        <div v-else class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                            <div class="flex items-center justify-center gap-2 mb-5 md:mb-6">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                <span class="text-[10px] md:text-xs text-slate-400 font-normal uppercase tracking-widest">Lobby Ujian Aktif</span>
                            </div>

                            <div class="mb-7">
                                <span class="text-[10px] md:text-xs text-indigo-500 font-normal uppercase tracking-[0.2em] block mb-2 md:mb-3">
                                    Simulasi Akbar
                                </span>
                                <h1 class="text-xl md:text-3xl font-normal text-slate-800 leading-snug">
                                    {{ tryout?.title }}
                                </h1>
                            </div>

                            <div class="inline-flex flex-col items-center justify-center bg-slate-50/80 border border-slate-100 rounded-xl px-5 py-3 mb-8 w-full sm:w-auto">
                                <span class="text-[9px] md:text-[10px] text-slate-400 font-normal uppercase tracking-widest mb-1 block">Jadwal Pelaksanaan</span>
                                <p class="text-xs md:text-sm text-slate-700 font-medium">
                                    {{ formatEventDateTime(tryout) }}
                                </p>
                            </div>

                            <div class="w-full flex flex-col justify-center min-h-[80px]">
                                
                                <div v-if="!isApproved" class="text-amber-700 bg-amber-50 p-4 md:p-5 rounded-2xl text-xs md:text-sm font-normal leading-relaxed border border-amber-100/50">
                                    <p class="mb-4">Akun Anda belum disetujui oleh admin atau pembayaran belum divalidasi.</p>
                                    <button @click="refreshLobby" class="w-full py-3 bg-white border border-amber-200/60 hover:border-amber-300 text-amber-700 transition-colors font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl shadow-sm">
                                        Segarkan Ruangan
                                    </button>
                                </div>
                                
                                <div v-else-if="isApproved && !isOpen" class="space-y-4">
                                    <div>
                                        <p class="text-slate-500 text-xs md:text-sm font-normal mb-2">Ujian akan dimulai dalam:</p>
                                        <div class="text-2xl md:text-3xl font-light text-indigo-600 font-mono tracking-tight animate-pulse">
                                            {{ timeRemaining }}
                                        </div>
                                    </div>
                                    <button disabled class="w-full py-3.5 bg-slate-50 text-slate-400 font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl cursor-not-allowed border border-slate-100">
                                        Ujian Belum Dimulai
                                    </button>
                                </div>

                                <div v-else-if="isApproved && isOpen" class="space-y-4">
                                    <div class="text-emerald-700 bg-emerald-50 p-4 rounded-xl text-xs md:text-sm border border-emerald-100/50 font-normal">
                                        Waktu pengerjaan telah aktif. Silakan masuk ke ruang ujian sekarang.
                                    </div>
                                    
                                    <Link 
                                        v-if="tryout?.id"
                                        :href="route('tryout.exam', tryout.id)"
                                        class="w-full flex items-center justify-center py-3.5 md:py-4 bg-indigo-600 text-white font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95"
                                    >
                                        Masuk Ruang Ujian
                                    </Link>
                                    
                                    <a 
                                        v-else
                                        :href="`/tryouts/${tryout?.id}/exam`"
                                        class="w-full flex items-center justify-center py-3.5 md:py-4 bg-indigo-600 text-white font-normal text-[11px] md:text-xs uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95"
                                    >
                                        Masuk Ruang Ujian
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