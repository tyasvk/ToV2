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
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&color=4F46E5&background=EEF2FF&bold=true`;
});

// --- FIX TANGGAL & JAM REGISTRASI ---
const formatDate = (dateString) => {
    // Jika data tidak ada, tampilkan indikator loading/placeholder sementara
    if (!dateString) return 'Memuat data...'; 
    
    try {
        const date = new Date(dateString);
        
        // Cek apakah date valid
        if (isNaN(date.getTime())) return 'Format tanggal salah';

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

// Kalkulasi Lingkaran Progres
const strokeDashoffset = computed(() => {
    const score = props.stats?.average_score || 0;
    return 213.6 - (Math.min(score, 100) / 100) * 213.6;
});
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AuthenticatedLayout>
        <div class="mb-10 animate-in fade-in slide-in-from-top-4 duration-1000">
            <div class="bg-gradient-to-br from-slate-900 via-gray-900 to-indigo-950 rounded-[3.5rem] shadow-2xl shadow-indigo-200/20 overflow-hidden relative border border-white/5">
                
                <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 -skew-x-12 translate-x-20"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>

                <div class="p-10 md:p-14 flex flex-col md:flex-row items-center gap-10 relative z-10">
                    
                    <div class="relative shrink-0 group">
                        <div class="absolute -inset-2 bg-gradient-to-tr from-indigo-500 to-blue-400 rounded-[2.5rem] blur opacity-30 group-hover:opacity-60 transition duration-1000"></div>
                        <div class="relative w-32 h-32 md:w-36 md:h-36 bg-white rounded-[2.5rem] overflow-hidden border-4 border-white/20 shadow-2xl">
                            <img 
                                :src="userAvatar" 
                                :alt="user.name"
                                class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                                @error="(e) => e.target.src = `https://ui-avatars.com/api/?name=${user.name}&background=EEF2FF&color=4F46E5&bold=true`"
                            />
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-emerald-500 border-4 border-slate-900 p-1.5 rounded-2xl shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 text-center md:text-left space-y-4">
                        <div class="space-y-1">
                            <div class="flex flex-col md:flex-row md:items-center gap-4 justify-center md:justify-start">
                                <h2 class="text-3xl md:text-4xl font-black text-white tracking-tighter uppercase leading-none">
                                    {{ user.name }}
                                </h2>
                                <span class="px-4 py-1.5 bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-[9px] font-black uppercase tracking-[0.2em] rounded-full self-center">
                                    Member Premium
                                </span>
                            </div>
                            
                            <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-3">
                                <p class="text-[11px] font-mono font-bold text-indigo-300/80 uppercase tracking-[0.15em] bg-white/5 px-3 py-1 rounded-lg">
                                    ID: {{ user.participant_number || 'REG-PENDING' }}
                                </p>
                                <p class="text-[10px] font-bold text-indigo-300/50 uppercase tracking-[0.2em] self-center">
                                    Aktif Sejak {{ formatDate(user.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl inline-block max-w-2xl">
                            <p class="text-[11px] font-bold text-white uppercase tracking-widest leading-relaxed opacity-80 italic">
                                "{{ motivation }}"
                            </p>
                        </div>
                    </div>

                    <div class="hidden lg:flex items-center gap-6 bg-white/5 p-6 pr-8 rounded-[3rem] border border-white/10 backdrop-blur-md">
                        <div class="relative w-20 h-20 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="6" fill="transparent" class="text-white/5" />
                                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="6" fill="transparent" stroke-dasharray="213.6" :stroke-dashoffset="strokeDashoffset" class="text-indigo-500 transition-all duration-1000 ease-out" />
                            </svg>
                            <span class="absolute text-sm font-black text-white tracking-tighter">{{ stats?.average_score || 0 }}%</span>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Target</p>
                            <p class="text-xs font-black text-white uppercase tracking-widest leading-none">Siap Ujian</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">📝</div>
                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Progress</span>
                </div>
                <p class="text-4xl font-black text-gray-900 tracking-tighter mb-1">{{ stats?.completed_count || 0 }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Simulasi Selesai</p>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-xl group-hover:bg-green-600 group-hover:text-white transition-colors">📈</div>
                    <span class="text-[9px] font-black text-green-400 uppercase tracking-widest">Akumulasi</span>
                </div>
                <p class="text-4xl font-black text-gray-900 tracking-tighter mb-1">{{ stats?.average_score || 0 }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Skor Rata-Rata</p>
            </div>
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition-colors">🕒</div>
                    <span class="text-[9px] font-black text-amber-400 uppercase tracking-widest">Aktivitas</span>
                </div>
                <p class="text-lg font-black text-gray-900 tracking-tight mb-1 uppercase truncate">{{ stats?.last_activity || '-' }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Update Terakhir</p>
            </div>
        </div>

        <div class="mb-8 flex items-center justify-between px-4">
            <h3 class="font-black text-sm text-gray-900 uppercase tracking-[0.2em]">Rekomendasi Ujian</h3>
            <Link :href="route('tryout.index')" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-gray-900 transition">Lihat Semua Paket →</Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="tryout in availableTryouts" :key="tryout.id" class="bg-white rounded-[3rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group">
                <div class="h-24 bg-gray-50 p-8 flex items-center justify-between border-b border-gray-100">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xs group-hover:rotate-12 transition-transform">TO</div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Premium Package</span>
                </div>
                <div class="p-8">
                    <h4 class="font-black text-lg text-gray-900 leading-tight uppercase mb-8 tracking-tighter h-12 overflow-hidden">{{ tryout.title }}</h4>
                    <Link :href="route('tryout.start', tryout.id)" class="block w-full text-center bg-gray-900 text-white py-5 rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all shadow-lg active:scale-95">Mulai Simulasi</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.font-serif { font-family: Georgia, serif; }
</style>