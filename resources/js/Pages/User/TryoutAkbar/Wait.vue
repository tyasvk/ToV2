<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object,
});

// Helper untuk memastikan waktu dibaca persis seperti yang di-set admin (Waktu Lokal)
const normalizeDate = (dateString) => {
    if (!dateString) return null;
    
    // Ganti spasi dengan 'T' agar format string menjadi standar ISO (YYYY-MM-DDTHH:MM:SS)
    let cleanString = String(dateString).replace(' ', 'T');
    
    // Buang pecahan detik desimal (misal: .000000Z)
    if (cleanString.includes('.')) {
        cleanString = cleanString.split('.')[0];
    }
    
    // Hapus huruf Z (Z = UTC). Jika huruf Z ada, browser akan menggeser jamnya!
    if (cleanString.endsWith('Z')) {
        cleanString = cleanString.slice(0, -1);
    }

    const parsedDate = new Date(cleanString);
    return isNaN(parsedDate.getTime()) ? null : parsedDate;
};

// Fungsi format tanggal & waktu pengerjaan serentak
const formatEventDateTime = (event) => {
    const start = normalizeDate(event.started_at || event.start_date);
    const end = normalizeDate(event.end_date || event.ended_at);

    if (!start) return 'Jadwal Belum Ditentukan';

    const optionsDate = { day: '2-digit', month: 'short', year: 'numeric' };
    const optionsTime = { hour: '2-digit', minute: '2-digit' };
    
    const startDateStr = start.toLocaleDateString('id-ID', optionsDate);
    const startTimeStr = start.toLocaleTimeString('id-ID', optionsTime).replace(/\./g, ':');

    if (!end) {
        return `${startDateStr} • ${startTimeStr} WIB`;
    }

    const endDateStr = end.toLocaleDateString('id-ID', optionsDate);
    const endTimeStr = end.toLocaleTimeString('id-ID', optionsTime).replace(/\./g, ':');

    if (startDateStr === endDateStr) {
        return `${startDateStr} • ${startTimeStr} - ${endTimeStr} WIB`;
    } else {
        return `${startDateStr} ${startTimeStr} - ${endDateStr} ${endTimeStr} WIB`;
    }
};

// --- LOGIKA AKSES UJIAN ---
const isOpen = ref(false);
let intervalId = null;

const checkSchedules = () => {
    const startTime = normalizeDate(props.tryout.started_at || props.tryout.start_date);
    
    if (!startTime) {
        isOpen.value = false;
        return;
    }
    
    // Bandingkan waktu mulai (startTime) dengan jam lokal PC peserta saat ini
    const now = new Date();
    isOpen.value = now >= startTime;
};

// Cek apakah pendaftaran sudah disetujui (status paid atau success)
const isApproved = computed(() => {
    return props.transaction?.status === 'paid' || props.transaction?.status === 'success';
});

// Syarat mutlak masuk ujian: waktu sudah mulai DAN sudah disetujui admin
const canEnterExam = computed(() => {
    return isOpen.value && isApproved.value;
});

onMounted(() => {
    checkSchedules();
    intervalId = setInterval(checkSchedules, 1000); // Sinkronisasi otomatis tiap 1 detik (agar lebih responsif)
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
        <div class="min-h-screen bg-slate-50 font-sans selection:bg-indigo-100 selection:text-indigo-700 py-6 md:py-12 flex flex-col justify-center">
            
            <div class="max-w-xl mx-auto w-full px-4 sm:px-6 relative z-10">
                
                <div class="mb-5 text-center">
                    <Link :href="route('tryout-akbar.index')" class="inline-flex items-center gap-2 text-[11px] md:text-xs text-slate-400 hover:text-indigo-600 transition-colors uppercase tracking-widest font-normal bg-white px-4 py-2 rounded-full border border-slate-200/60 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Keluar Ruang Tunggu
                    </Link>
                </div>

                <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden">
                    
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                            </span>
                            <span class="text-[10px] text-slate-500 uppercase tracking-widest font-normal">Lobby Ruang Tunggu</span>
                        </div>
                        
                        <span class="text-[9px] px-2.5 py-1 rounded-md border uppercase tracking-wider font-normal"
                            :class="isApproved ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'"
                        >
                            {{ isApproved ? 'Disetujui Admin' : 'Menunggu Persetujuan' }}
                        </span>
                    </div>

                    <div class="p-6 md:p-10 text-center space-y-6 md:space-y-8">
                        
                        <div class="space-y-2">
                            <span class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-[0.2em] block font-normal">
                                Simulasi Akbar
                            </span>
                            <h1 class="text-xl md:text-2xl text-slate-800 leading-snug uppercase tracking-wide font-normal max-w-md mx-auto">
                                {{ tryout.title }}
                            </h1>
                        </div>

                        <div class="bg-indigo-50/40 border border-indigo-100/50 rounded-2xl p-5 max-w-sm mx-auto space-y-3">
                            <div class="w-9 h-9 bg-white text-indigo-500 rounded-full border border-indigo-100/80 shadow-sm flex items-center justify-center mx-auto">
                                <svg xmlns="http://www.w3.org/2000/xl" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-[9px] text-indigo-400 uppercase tracking-widest block font-normal">Waktu Pelaksanaan</span>
                                <p class="text-xs md:text-sm text-indigo-900 font-normal">
                                    {{ formatEventDateTime(tryout) }}
                                </p>
                            </div>
                        </div>

                        <div class="max-w-sm mx-auto text-xs md:text-sm text-slate-500 font-normal leading-relaxed">
                            <p v-if="!isApproved" class="text-amber-600/90 bg-amber-50/30 border border-amber-100/50 p-3 rounded-xl italic">
                                Akun Anda belum disetujui oleh admin. Anda tidak dapat memulai ujian meskipun waktu pelaksanaan sudah dimulai. Silakan segarkan halaman berkala.
                            </p>
                            <p v-else-if="isApproved && !isOpen" class="text-slate-500">
                                Pendaftaran Anda sudah diverifikasi & disetujui! Pengerjaan belum dimulai, silakan tunggu sampai gerbang ujian dibuka otomatis.
                            </p>
                            <p v-else-if="canEnterExam" class="text-emerald-600 font-normal">
                                Verifikasi sukses dan waktu pengerjaan telah aktif! Silakan klik tombol di bawah untuk langsung menuju ruang pengerjaan soal.
                            </p>
                        </div>

                        <div class="border-t border-slate-100 max-w-xs mx-auto"></div>

                        <div class="max-w-sm mx-auto">
                            
                            <div class="space-y-3" v-if="!isApproved">
                                <div class="w-full py-4 bg-slate-100 text-slate-400 text-xs uppercase tracking-widest rounded-2xl flex items-center justify-center gap-2 cursor-not-allowed font-normal border border-slate-200/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    Menunggu Persetujuan Admin
                                </div>
                                <button @click="refreshLobby" class="w-full py-3.5 bg-white border border-slate-200 hover:border-indigo-200 text-slate-600 hover:text-indigo-600 transition-all active:scale-[0.98] text-[11px] md:text-xs uppercase tracking-widest rounded-2xl font-normal shadow-sm">
                                    Segarkan Ruangan
                                </button>
                            </div>

                            <div class="space-y-3" v-else-if="isApproved && !isOpen">
                                <div class="w-full py-4 bg-slate-100 text-slate-400 text-xs uppercase tracking-widest rounded-2xl flex items-center justify-center gap-2 cursor-not-allowed font-normal border border-slate-200/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Ujian Belum Dimulai
                                </div>
                                <button @click="refreshLobby" class="w-full py-3.5 bg-white border border-slate-200 hover:border-indigo-200 text-slate-600 hover:text-indigo-600 transition-all active:scale-[0.98] text-[11px] md:text-xs uppercase tracking-widest rounded-2xl font-normal shadow-sm">
                                    Segarkan Ruangan
                                </button>
                            </div>

                            <div v-else-if="canEnterExam">
                                <Link 
                                    :href="route('tryout-akbar.exam', tryout.id)"
                                    class="w-full py-4 bg-slate-900 text-white text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-800 transition-all active:scale-[0.98] flex items-center justify-center gap-2 font-normal shadow-xl shadow-slate-900/10"
                                >
                                    Masuk Ruang Ujian
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </Link>
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
</style>