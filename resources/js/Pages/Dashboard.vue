<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    availableTryouts: Array,
    stats: Object
});

const page = usePage();

// --- SAFE USER ACCESS ---
const user = computed(() => {
    return page.props.auth?.user || {};
});

// --- LOGIC FOTO PROFIL ---
const userAvatar = computed(() => {
    const u = user.value;
    if (u.profile_photo_url && !u.profile_photo_url.includes('ui-avatars.com')) {
        return u.profile_photo_url;
    }
    const rawPath = u.profile_photo_path || u.avatar;
    if (rawPath) {
        const cleanPath = rawPath.replace(/^\//, '');
        return `/storage/${cleanPath}`;
    }
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=4F46E5&background=EEF2FF`;
});

// --- FIX TANGGAL & JAM REGISTRASI ---
const formatDate = (dateString) => {
    if (!dateString) return 'Memuat...'; 
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Format salah';
        return date.toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).replace('.', ':') + ' WIB';
    } catch (e) {
        return '-';
    }
};

const motivation = "Masa depan adalah milik mereka yang menyiapkan diri hari ini. Konsistensi adalah kunci kemenangan.";

// Kalkulasi Lingkaran Progres (Disederhanakan untuk efisiensi)
const strokeDashoffset = computed(() => {
    const score = props.stats?.average_score || 0;
    return 213.6 - (Math.min(score, 100) / 100) * 213.6;
});
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AuthenticatedLayout>
        <div class="mb-6 md:mb-10 animate-in fade-in slide-in-from-top-4 duration-1000">
            <div class="bg-gradient-to-br from-slate-900 via-gray-900 to-indigo-950 rounded-[2rem] md:rounded-[3.5rem] shadow-2xl shadow-indigo-200/10 overflow-hidden relative border border-white/5">
                
                <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 -skew-x-12 translate-x-20 hidden md:block"></div>

                <div class="p-6 md:p-14 flex flex-col md:flex-row items-center gap-6 md:gap-10 relative z-10">
                    
                    <div class="relative shrink-0 group">
                        <div class="absolute -inset-1.5 bg-gradient-to-tr from-indigo-500/30 to-blue-400/30 rounded-3xl md:rounded-[2.5rem] blur opacity-50"></div>
                        <div class="relative w-24 h-24 md:w-36 md:h-36 bg-white rounded-3xl md:rounded-[2.5rem] overflow-hidden border-2 md:border-4 border-white/20">
                            <img 
                                :src="userAvatar" 
                                :alt="user.name"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-emerald-500 border-2 md:border-4 border-slate-900 p-1 rounded-full shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 text-center md:text-left">
                        <div class="space-y-2">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                                <h2 class="text-xl md:text-3xl font-medium text-white tracking-tight uppercase">
                                    {{ user.name }}
                                </h2>
                                <span class="px-3 py-1 bg-white/10 border border-white/10 text-indigo-200 text-[9px] font-medium uppercase tracking-widest rounded-full self-center">
                                    Premium Account
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-1">
                                <p class="text-[10px] font-mono font-medium text-indigo-300/70 uppercase tracking-wider bg-white/5 px-2 py-0.5 rounded-md">
                                    #{{ user.participant_number || 'PENDING' }}
                                </p>
                                <p class="text-[10px] font-medium text-indigo-300/40 uppercase tracking-widest self-center">
                                    Aktif: {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 md:mt-6 bg-white/5 backdrop-blur-sm border border-white/5 p-4 rounded-xl md:inline-block max-w-xl">
                            <p class="text-[10px] md:text-[11px] font-medium text-white/70 uppercase tracking-widest leading-relaxed italic">
                                "{{ motivation }}"
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-5 bg-white/5 p-5 rounded-[2rem] border border-white/5">
                        <div class="relative w-16 h-16 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="transparent" class="text-white/5" />
                                <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="transparent" stroke-dasharray="175.8" :stroke-dashoffset="175.8 - (Math.min(stats?.average_score || 0, 100) / 100) * 175.8" class="text-indigo-500 transition-all duration-1000" />
                            </svg>
                            <span class="absolute text-xs font-medium text-white tracking-tighter">{{ stats?.average_score || 0 }}%</span>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-[8px] font-medium text-indigo-300/60 uppercase tracking-widest">Siap Ujian</p>
                            <p class="text-[10px] font-medium text-white uppercase tracking-wider">Target Skor</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 mb-10">
            <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-lg">📝</div>
                    <span class="text-[9px] font-medium text-indigo-400 uppercase tracking-widest">Progress</span>
                </div>
                <p class="text-3xl md:text-4xl font-medium text-slate-900 tracking-tighter mb-1">{{ stats?.completed_count || 0 }}</p>
                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Simulasi Selesai</p>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-lg">📈</div>
                    <span class="text-[9px] font-medium text-emerald-500 uppercase tracking-widest">Akumulasi</span>
                </div>
                <p class="text-3xl md:text-4xl font-medium text-slate-900 tracking-tighter mb-1">{{ stats?.average_score || 0 }}</p>
                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Skor Rata-Rata</p>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm sm:col-span-2 md:col-span-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center text-lg">🕒</div>
                    <span class="text-[9px] font-medium text-amber-500 uppercase tracking-widest">Aktivitas</span>
                </div>
                <p class="text-sm md:text-base font-medium text-slate-900 tracking-tight mb-1 uppercase truncate">{{ stats?.last_activity || 'Belum ada' }}</p>
                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">Terakhir Belajar</p>
            </div>
        </div>

        <div class="mb-6 flex items-center justify-between px-2 md:px-4">
            <h3 class="font-medium text-[11px] md:text-xs text-slate-900 uppercase tracking-[0.2em]">Rekomendasi Paket</h3>
            <Link :href="route('tryout.index')" class="text-[9px] md:text-[10px] font-medium text-indigo-600 uppercase tracking-widest hover:text-slate-900 transition">Lihat Semua →</Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 pb-10">
            <div v-for="tryout in availableTryouts" :key="tryout.id" class="bg-white rounded-[2rem] border border-slate-100 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-500 group">
                <div class="h-20 bg-slate-50 p-6 flex items-center justify-between border-b border-slate-100">
                    <div class="w-9 h-9 bg-slate-900 rounded-xl flex items-center justify-center text-white font-medium text-[10px]">TO</div>
                    <span class="text-[8px] font-medium text-slate-400 uppercase tracking-widest tracking-[0.1em]">Premium Preparation</span>
                </div>
                <div class="p-6 md:p-8">
                    <h4 class="font-medium text-base text-slate-900 leading-snug uppercase mb-6 md:mb-8 tracking-tight h-10 overflow-hidden">{{ tryout.title }}</h4>
                    <Link :href="route('tryout.start', tryout.id)" class="block w-full text-center bg-slate-900 text-white py-4 rounded-xl font-medium text-[9px] uppercase tracking-[0.25em] hover:bg-indigo-600 transition-all shadow-lg active:scale-95">Mulai Ujian</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollbar Stylist */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.font-serif { font-family: Georgia, serif; }

/* Custom smooth animation */
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>