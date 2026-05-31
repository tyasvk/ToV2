<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    announcement: String,
    unpurchased_tryouts: {
        type: Array,
        default: () => []
    },
    balance: {
        type: Number,
        default: 0
    },
    stats: Object,
    activeExam: { 
        type: Object,
        default: null
    }
});

const page = usePage();

// --- SAFE USER ACCESS ---
const user = computed(() => page.props.auth?.user || {});

// --- LOGIC FOTO PROFIL ---
const userAvatar = computed(() => {
    const u = user.value;
    if (u.profile_photo_url && !u.profile_photo_url.includes('ui-avatars.com')) return u.profile_photo_url;
    const rawPath = u.profile_photo_path || u.avatar;
    if (rawPath) return `/storage/${rawPath.replace(/^\//, '')}`;
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=2563EB&background=EFF6FF`;
});

// --- PENGUMUMAN LINK FORMATTER ---
const formattedAnnouncement = computed(() => {
    if (!props.announcement) return '';
    const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return props.announcement.replace(urlPattern, (url) => 
        `<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline transition-colors break-all font-medium">${url}</a>`
    );
});

// --- FORMAT FORMATTER ---
const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);

const formatDate = (dateString) => {
    if (!dateString) return 'Memuat...'; 
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Format salah';
        return date.toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':') + ' WIB';
    } catch (e) {
        return '-';
    }
};

const motivation = "Masa depan adalah milik mereka yang menyiapkan diri hari ini. Konsistensi adalah kunci kemenangan.";

// --- LOGIKA TIMER BERGERAK UNTUK DASHBOARD ---
const activeTimeLeft = ref(props.activeExam?.time_left_seconds || 0);
let countdownTimer = null;

const formattedActiveTimeLeft = computed(() => {
    const safeSeconds = Math.max(0, Math.floor(activeTimeLeft.value));
    const h = Math.floor(safeSeconds / 3600);
    const m = Math.floor((safeSeconds % 3600) / 60);
    const s = safeSeconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
});

onMounted(() => {
    if (props.activeExam && activeTimeLeft.value > 0) {
        countdownTimer = setInterval(() => {
            if (activeTimeLeft.value > 0) {
                activeTimeLeft.value--;
            } else {
                clearInterval(countdownTimer);
                // Waktu habis, refresh dashboard agar banner ujian aktif hilang dan data sinkron
                router.reload({ only: ['activeExam', 'stats'] }); 
            }
        }, 1000);
    }
});

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});
</script>

<template>
    <Head title="Dashboard - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto animate-in fade-in slide-in-from-top-4 duration-700">

            <div class="block md:hidden px-4 py-6 space-y-5">
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-[150px] h-[150px] bg-blue-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>
                    <div class="absolute bottom-0 left-0 w-[100px] h-[100px] bg-slate-50 rounded-full blur-[30px] pointer-events-none -ml-10 -mb-10"></div>

                    <div class="p-5 flex flex-col items-center gap-4 relative z-10 text-center">
                        <div class="relative shrink-0">
                            <div class="relative w-20 h-20 bg-white rounded-2xl overflow-hidden border border-slate-200 p-1 shadow-sm">
                                <img :src="userAvatar" :alt="user.name" class="w-full h-full object-cover rounded-xl" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 bg-blue-600 p-1 rounded-full shadow-sm border-2 border-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-1.5 w-full">
                            <div class="flex flex-col items-center gap-1.5">
                                <h2 class="text-lg text-slate-900 tracking-tight uppercase font-bold">
                                    Halo, <span class="text-blue-600">{{ user.name }}</span>
                                </h2>
                                <span class="inline-block px-3 py-1 bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">
                                    Peserta Aktif
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap justify-center gap-2 items-center mt-1">
                                <p class="text-[11px] font-mono font-bold text-slate-600 bg-slate-100 border border-slate-200/60 px-2 py-1 rounded-md">
                                    #{{ user.participant_number || 'PENDING' }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                    Aktif: {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-2 w-full bg-slate-50/80 border border-slate-200/60 p-3 rounded-xl">
                            <p class="text-xs font-normal text-slate-500 leading-relaxed font-serif italic">
                                "{{ motivation }}"
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="activeExam && activeTimeLeft > 0" class="bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-0.5 rounded-2xl shadow-sm animate-pulse-slow">
                    <div class="bg-white p-4 rounded-[14px] flex flex-col gap-4">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-amber-50 rounded-lg text-amber-500 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest block">Ujian Berjalan!</span>
                                <h4 class="text-sm font-bold text-slate-800 line-clamp-1 uppercase tracking-tight">
                                    {{ activeExam.title }}
                                </h4>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                            <div class="font-mono text-sm font-bold text-slate-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 flex flex-col items-center">
                                <span class="text-[9px] text-slate-400 font-sans uppercase tracking-widest leading-none mb-1">Sisa Waktu</span>
                                <span class="tabular-nums" :class="{ 'text-rose-500 animate-pulse': activeTimeLeft <= 300 }">⏱️ {{ formattedActiveTimeLeft }}</span>
                            </div>
                            <Link :href="route('tryout.exam', activeExam.id)" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm active:scale-95">
                                Lanjutkan
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="announcement" class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 shadow-sm flex items-start gap-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-400"></div>
                    <div class="shrink-0 text-amber-500 bg-amber-100 p-2 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a2.25 2.25 0 0 1-3.12-.94l-2.13-3.704a15.3 15.3 0 0 1-1.4-2.684M10.34 15.84c1.208.13 2.43.19 3.66.19 1.23 0 2.452-.06 3.66-.19m0 9.18a21.53 21.53 0 0 0 2.95-.53c.64-.13 1.12-.66 1.16-1.31l.05-.75c.045-.66-.35-1.26-.96-1.47a21.53 21.53 0 0 0-2.95-.53m-7.22 3.1c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a2.25 2.25 0 0 1-3.12-.94l-2.13-3.704a15.3 15.3 0 0 1-1.4-2.684" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[10px] text-amber-800 font-bold uppercase tracking-widest">Pengumuman Pusat</h3>
                        <p class="text-xs text-amber-700 font-medium leading-relaxed mt-1" v-html="formattedAnnouncement"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <Link :href="route('wallet.index')" class="col-span-2 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between group active:scale-95 transition-transform">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-xl shrink-0">💳</div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Saldo Aktif</p>
                                <p class="text-lg font-bold text-slate-900 tracking-tight">{{ formatCurrency(balance) }}</p>
                            </div>
                        </div>
                        <div class="text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </div>
                    </Link>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center gap-2">
                        <div class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-lg">📝</div>
                        <div>
                            <p class="text-xl font-bold text-slate-900 leading-none">{{ stats?.completed_count || 0 }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total Ujian</p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center gap-2 relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 w-20 h-20 opacity-5">
                             <svg class="w-full h-full transform -rotate-90"><circle cx="40" cy="40" r="30" stroke="currentColor" stroke-width="10" fill="transparent" stroke-dasharray="188.4" :stroke-dashoffset="188.4 - (Math.min(stats?.average_score || 0, 100) / 100) * 188.4" class="text-blue-600" /></svg>
                        </div>
                        <div class="w-10 h-10 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-lg z-10">📈</div>
                        <div class="z-10">
                            <p class="text-xl font-bold text-slate-900 leading-none">{{ stats?.average_score || 0 }}</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Rata Rata</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Rekomendasi Paket</h3>
                        <Link :href="route('tryout.index')" class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Lihat Semua →</Link>
                    </div>

                    <div v-if="unpurchased_tryouts && unpurchased_tryouts.length > 0" class="flex flex-col gap-4 pb-8">
                        <div v-for="tryout in unpurchased_tryouts" :key="tryout.id" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="p-3 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between">
                                <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center text-white font-bold text-xs">TO</div>
                                <span class="text-[9px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-1 rounded-md uppercase tracking-wider">Tersedia</span>
                            </div>
                            <div class="p-4 flex flex-col gap-3">
                                <div>
                                    <h4 class="font-bold text-sm text-slate-900 leading-tight uppercase mb-1 line-clamp-2">{{ tryout.title }}</h4>
                                    <p class="text-[11px] text-slate-500 font-normal leading-relaxed line-clamp-2">{{ tryout.description || 'Paket simulasi terbaru untuk mematangkan persiapan tes Anda.' }}</p>
                                </div>
                                <Link :href="route('tryout.show', tryout.id)" class="block w-full text-center bg-blue-50 text-blue-700 border border-blue-200 py-3 rounded-xl font-bold text-[11px] uppercase tracking-wider active:scale-95 mt-1">
                                    Lihat Detail & Beli
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center shadow-sm">
                        <div class="text-3xl mb-2">📭</div>
                        <p class="text-xs text-slate-500 font-medium leading-relaxed">Anda sudah memiliki semua tryout yang tersedia.</p>
                    </div>
                </div>

            </div>


            <div class="hidden md:block px-2.5 py-3 space-y-4">
                
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 w-[200px] h-[200px] bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-16 -mt-16"></div>
                    <div class="absolute bottom-0 left-0 w-[150px] h-[150px] bg-slate-50 rounded-full blur-[40px] pointer-events-none -ml-16 -mb-16"></div>

                    <div class="p-4 md:p-5 flex flex-col md:flex-row items-center gap-4 md:gap-6 relative z-10">
                        <div class="relative shrink-0">
                            <div class="relative w-16 h-16 md:w-20 md:h-20 bg-white rounded-xl overflow-hidden border border-slate-200 p-0.5 shadow-sm">
                                <img :src="userAvatar" :alt="user.name" class="w-full h-full object-cover rounded-lg" />
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 bg-blue-600 p-0.5 rounded-full shadow-sm border border-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 text-center md:text-left z-10">
                            <div class="space-y-1">
                                <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 justify-center md:justify-start">
                                    <h2 class="text-base md:text-lg text-slate-900 tracking-tight uppercase font-medium">
                                        Halo, <span class="font-bold text-blue-600">{{ user.name }}</span>
                                    </h2>
                                    <span class="inline-block px-2 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 text-[9px] font-medium uppercase tracking-wider rounded-md self-center">
                                        Peserta Aktif
                                    </span>
                                </div>
                                <div class="flex flex-wrap justify-center md:justify-start gap-2 items-center">
                                    <p class="text-[9px] font-mono font-medium text-slate-600 bg-slate-100 border border-slate-200/60 px-1.5 py-0.5 rounded">
                                        #{{ user.participant_number || 'PENDING' }}
                                    </p>
                                    <p class="text-[9px] font-normal text-slate-400 uppercase tracking-wider">
                                        Aktif: {{ formatDate(user.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2 bg-slate-50/80 border border-slate-200/60 px-3 py-1.5 rounded-lg md:inline-block max-w-xl">
                                <p class="text-[11px] font-normal text-slate-500 leading-relaxed font-serif italic">
                                    "{{ motivation }}"
                                </p>
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-3 bg-slate-50/60 border border-slate-200/60 p-2.5 rounded-xl">
                            <div class="relative w-10 h-10 flex items-center justify-center">
                                <svg class="w-full h-full transform -rotate-90">
                                    <circle cx="20" cy="20" r="17" stroke="currentColor" stroke-width="2.5" fill="transparent" class="text-slate-200/60" />
                                    <circle cx="20" cy="20" r="17" stroke="currentColor" stroke-width="2.5" fill="transparent" stroke-dasharray="106.8" :stroke-dashoffset="106.8 - (Math.min(stats?.average_score || 0, 100) / 100) * 106.8" class="text-blue-500 transition-all duration-1000" />
                                </svg>
                                <span class="absolute text-[10px] font-medium text-slate-800 tracking-tighter">{{ stats?.average_score || 0 }}%</span>
                            </div>
                            <div class="flex flex-col">
                                <p class="text-[8px] font-medium text-slate-400 uppercase tracking-widest leading-none">Target</p>
                                <p class="text-[11px] font-medium text-slate-800 uppercase tracking-wide mt-0.5">Skor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeExam && activeTimeLeft > 0" class="bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 p-0.5 rounded-xl shadow-sm animate-pulse-slow">
                    <div class="bg-white p-3.5 rounded-[10px] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-left">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-amber-50 rounded-lg text-amber-500 shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="space-y-0.5 overflow-hidden">
                                <span class="text-[9px] font-bold text-amber-600 uppercase tracking-wider block">Ujian Sedang Berjalan!</span>
                                <h4 class="text-xs sm:text-sm font-bold text-slate-800 truncate uppercase tracking-tight">
                                    {{ activeExam.title }}
                                </h4>
                                <p class="text-[10px] text-slate-400 font-normal leading-normal">
                                    Sesi ujian Anda masih aktif. Sisa waktu pengerjaan terus berkurang.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-3 border-t sm:border-t-0 border-slate-100 pt-2.5 sm:pt-0 shrink-0">
                            <div class="font-mono text-xs font-semibold text-slate-700 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 flex flex-col min-w-[70px]">
                                <span class="text-[8px] text-slate-400 font-sans uppercase font-medium tracking-wide leading-none mb-0.5">Sisa Waktu</span>
                                <span class="tabular-nums" :class="{ 'text-rose-500': activeTimeLeft <= 300 }">⏱️ {{ formattedActiveTimeLeft }}</span>
                            </div>
                            <Link :href="route('tryout.exam', activeExam.id)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm transition active:scale-95">
                                Lanjutkan
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="announcement" class="bg-amber-50/80 border border-amber-200 rounded-xl p-3.5 shadow-sm flex items-start gap-3 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
                    <div class="shrink-0 text-amber-500 bg-amber-100 p-1.5 rounded-lg mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a2.25 2.25 0 0 1-3.12-.94l-2.13-3.704a15.3 15.3 0 0 1-1.4-2.684M10.34 15.84c1.208.13 2.43.19 3.66.19 1.23 0 2.452-.06 3.66-.19m0 9.18a21.53 21.53 0 0 0 2.95-.53c.64-.13 1.12-.66 1.16-1.31l.05-.75c.045-.66-.35-1.26-.96-1.47a21.53 21.53 0 0 0-2.95-.53m-7.22 3.1c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38a2.25 2.25 0 0 1-3.12-.94l-2.13-3.704a15.3 15.3 0 0 1-1.4-2.684" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[9px] text-amber-800 font-medium uppercase tracking-widest">Pengumuman Pusat</h3>
                        <p class="text-xs text-amber-700 font-normal leading-relaxed mt-0.5" v-html="formattedAnnouncement"></p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                    <Link :href="route('wallet.index')" class="bg-white p-2.5 sm:p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 sm:gap-3 hover:border-blue-300 transition-colors group">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-50 border border-blue-100 group-hover:bg-blue-100 group-hover:border-blue-200 rounded-lg flex items-center justify-center text-xs shrink-0 transition-colors">💳</div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-slate-900 tracking-tight leading-none truncate">{{ formatCurrency(balance) }}</p>
                            <p class="text-[8px] sm:text-[9px] font-medium text-slate-400 uppercase tracking-wider mt-1 truncate">Saldo Aktif</p>
                        </div>
                    </Link>

                    <div class="bg-white p-2.5 sm:p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 sm:gap-3">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-xs shrink-0">📝</div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-slate-900 tracking-tight leading-none truncate">{{ stats?.completed_count || 0 }}</p>
                            <p class="text-[8px] sm:text-[9px] font-medium text-slate-400 uppercase tracking-wider mt-1 truncate">Total Ujian</p>
                        </div>
                    </div>

                    <div class="bg-white p-2.5 sm:p-3.5 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 sm:gap-3">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center text-xs shrink-0">📈</div>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-medium text-slate-900 tracking-tight leading-none truncate">{{ stats?.average_score || 0 }}</p>
                            <p class="text-[8px] sm:text-[9px] font-medium text-slate-400 uppercase tracking-wider mt-1 truncate">Rata Rata</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="font-medium text-[10px] sm:text-xs text-slate-900 uppercase tracking-[0.15em]">Rekomendasi Paket Tryout</h3>
                        <Link :href="route('tryout.index')" class="text-[10px] font-medium text-blue-600 uppercase tracking-wider hover:text-blue-700 transition">Lihat Semua →</Link>
                    </div>

                    <div v-if="unpurchased_tryouts && unpurchased_tryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pb-6">
                        <div v-for="tryout in unpurchased_tryouts" :key="tryout.id" class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
                            <div class="p-3 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between">
                                <div class="w-6 h-6 bg-slate-900 rounded-md flex items-center justify-center text-white font-medium text-[9px] shadow-sm">TO</div>
                                <span class="text-[8px] font-medium text-blue-600 bg-blue-50 border border-blue-100 px-1.5 py-0.5 rounded uppercase tracking-wider">Tersedia</span>
                            </div>
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <h4 class="font-medium text-xs sm:text-sm text-slate-900 leading-tight uppercase mb-1.5 tracking-tight group-hover:text-blue-600 transition-colors line-clamp-1">
                                    {{ tryout.title }}
                                </h4>
                                <p class="text-[11px] text-slate-400 font-normal leading-relaxed line-clamp-2 mb-4">
                                    {{ tryout.description || 'Paket simulasi terbaru untuk mematangkan persiapan tes Anda.' }}
                                </p>
                                
                                <Link :href="route('tryout.show', tryout.id)" class="block w-full text-center bg-blue-50 text-blue-600 border border-blue-100 py-2 rounded-lg font-medium text-[10px] sm:text-xs uppercase tracking-wider hover:bg-blue-600 hover:text-white transition-colors shadow-sm active:scale-95">
                                    Lihat Detail & Beli
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-white border border-slate-200 rounded-xl p-6 text-center shadow-sm flex flex-col items-center">
                        <div class="w-9 h-9 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mb-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <p class="text-xs text-slate-400 font-normal">Anda sudah memiliki semua tryout yang tersedia saat ini.</p>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.font-serif {
    font-family: Georgia, 'Times New Roman', Times, serif;
}

.animate-in {
    animation-duration: 0.5s;
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes pulseSlow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.95; }
}
.animate-pulse-slow {
    animation: pulseSlow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.tabular-nums { font-variant-numeric: tabular-nums; }
</style>