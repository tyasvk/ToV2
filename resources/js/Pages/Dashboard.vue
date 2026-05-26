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
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=2563EB&background=EFF6FF`;
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
</script>

<template>
    <Head title="Dashboard Peserta - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="mb-6 md:mb-10 animate-in fade-in slide-in-from-top-4 duration-1000">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
                
                <div class="absolute top-0 right-0 w-[300px] h-[300px] bg-blue-50 rounded-full blur-[80px] pointer-events-none -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-[200px] h-[200px] bg-slate-100 rounded-full blur-[60px] pointer-events-none -ml-20 -mb-20"></div>

                <div class="p-6 md:p-10 flex flex-col md:flex-row items-center gap-6 md:gap-8 relative z-10">
                    
                    <div class="relative shrink-0 group">
                        <div class="absolute -inset-1 bg-gradient-to-tr from-blue-600/20 to-blue-400/20 rounded-2xl blur opacity-40"></div>
                        <div class="relative w-24 h-24 md:w-32 md:h-32 bg-white rounded-2xl overflow-hidden border border-slate-200 p-1 shadow-sm">
                            <img 
                                :src="userAvatar" 
                                :alt="user.name"
                                class="w-full h-full object-cover rounded-xl"
                            />
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-blue-600 p-1 rounded-full shadow-md border-2 border-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 text-center md:text-left z-10">
                        <div class="space-y-2">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3 justify-center md:justify-start">
                                <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight uppercase">
                                    {{ user.name }}
                                </h2>
                                <span class="inline-block px-2.5 py-0.5 bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-semibold uppercase tracking-wider rounded-md self-center">
                                    Premium Account
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-1">
                                <p class="text-[10px] font-mono font-semibold text-slate-600 bg-slate-100 border border-slate-200/60 px-2 py-0.5 rounded">
                                    #{{ user.participant_number || 'PENDING' }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider self-center">
                                    Aktif sejak: {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 bg-slate-50/80 border border-slate-200/60 p-3.5 rounded-xl md:inline-block max-w-xl">
                            <p class="text-xs font-light text-slate-600 leading-relaxed font-serif italic">
                                "{{ motivation }}"
                            </p>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-4 bg-slate-50/60 border border-slate-200/60 p-4 rounded-2xl">
                        <div class="relative w-14 h-14 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3.5" fill="transparent" class="text-slate-200/60" />
                                <circle cx="28" cy="28" r="24" stroke="currentColor" stroke-width="3.5" fill="transparent" stroke-dasharray="150.7" :stroke-dashoffset="150.7 - (Math.min(stats?.average_score || 0, 100) / 100) * 150.7" class="text-blue-600 transition-all duration-1000" />
                            </svg>
                            <span class="absolute text-xs font-bold text-slate-800 tracking-tighter">{{ stats?.average_score || 0 }}%</span>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-tight">Siap Ujian</p>
                            <p class="text-xs font-bold text-slate-800 uppercase tracking-wide">Target Skor</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-center text-sm">📝</div>
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded uppercase tracking-wider">Progress</span>
                </div>
                <p class="text-3xl font-bold text-slate-900 tracking-tight mb-0.5">{{ stats?.completed_count || 0 }}</p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Simulasi Selesai</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-green-50 border border-green-100 rounded-lg flex items-center justify-center text-sm">📈</div>
                    <span class="text-[10px] font-bold text-green-700 bg-green-50 border border-green-100 px-2 py-0.5 rounded uppercase tracking-wider">Akumulasi</span>
                </div>
                <p class="text-3xl font-bold text-slate-900 tracking-tight mb-0.5">{{ stats?.average_score || 0 }}</p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Skor Rata-Rata</p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm sm:col-span-2 md:col-span-1">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 bg-amber-50 border border-amber-100 rounded-lg flex items-center justify-center text-sm">🕒</div>
                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded uppercase tracking-wider">Aktivitas</span>
                </div>
                <p class="text-sm font-bold text-slate-800 tracking-tight mb-1 uppercase truncate">{{ stats?.last_activity || 'Belum ada' }}</p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir Belajar</p>
            </div>
        </div>

        <div class="mb-5 flex items-center justify-between px-1">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-[0.18em]">Rekomendasi Paket</h3>
            <Link :href="route('tryout.index')" class="text-[10px] font-bold text-blue-600 uppercase tracking-wider hover:text-blue-700 transition">Lihat Semua →</Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 pb-10">
            <div v-for="tryout in availableTryouts" :key="tryout.id" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between group">
                <div class="p-5 bg-slate-50 border-b border-slate-200/60 flex items-center justify-between">
                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center text-white font-bold text-[11px] shadow-sm">TO</div>
                    <span class="text-[9px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-0.5 rounded-md uppercase tracking-wider">Premium Preparation</span>
                </div>
                <div class="p-6 flex flex-col justify-between flex-1">
                    <h4 class="font-bold text-base text-slate-900 leading-snug uppercase mb-6 tracking-tight h-12 overflow-hidden group-hover:text-blue-600 transition-colors">
                        {{ tryout.title }}
                    </h4>
                    
                    <Link :href="route('tryout.show', tryout.id)" class="block w-full text-center bg-blue-600 text-white py-3 rounded-xl font-semibold text-xs uppercase tracking-wider hover:bg-blue-700 transition-colors shadow-sm active:scale-95">
                        Lihat Detail & Mulai
                    </Link>
                    
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

.font-serif {
    font-family: Georgia, 'Times New Roman', Times, serif;
}

.animate-in {
    animation-duration: 0.6s;
    animation-fill-mode: both;
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>