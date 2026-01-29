<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3'; // Tambahkan usePage
import { computed } from 'vue';

const props = defineProps({
    availableTryouts: Array,
    stats: Object
});

const page = usePage();

// --- PERBAIKAN: SAFE USER ACCESS ---
// Kita buat computed property agar aman jika user undefined
const user = computed(() => {
    return page.props.auth?.user || {
        name: 'Peserta',
        id: 0,
        created_at: new Date(),
        email: ''
    };
});

const formatDate = (dateString) => {
    const date = dateString ? new Date(dateString) : new Date();
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const getInitials = (name) => {
    // Tambahkan pengecekan keamanan agar tidak error jika name kosong
    if (!name) return 'TO';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const motivation = "Masa depan adalah milik mereka yang menyiapkan diri hari ini. Konsistensi adalah kunci kemenangan.";

// Kalkulasi Lingkaran Progres (Keliling lingkaran r=34 adalah 213.6)
const strokeDashoffset = computed(() => {
    // Tambahkan optional chaining pada stats juga
    const score = props.stats?.average_score || 0;
    return 213.6 - (score / 100) * 213.6;
});
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AuthenticatedLayout>
        <div class="mb-10 animate-in fade-in slide-in-from-top-4 duration-700">
            <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-indigo-950 rounded-[3.5rem] shadow-2xl shadow-indigo-200/20 overflow-hidden relative border border-white/5">
                
                <div class="absolute top-0 right-0 w-1/2 h-full bg-white/5 -skew-x-12 translate-x-20"></div>
                <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl"></div>

                <div class="p-10 md:p-14 flex flex-col md:flex-row items-center gap-10 relative z-10">
                    <div class="w-28 h-28 bg-white rounded-[2.5rem] flex items-center justify-center text-indigo-900 text-4xl font-black shadow-2xl shrink-0 border-4 border-white/10">
                        {{ getInitials(user.name) }}
                    </div>

                    <div class="flex-1 text-center md:text-left space-y-4">
                        <div class="space-y-1">
                            <div class="flex flex-col md:flex-row md:items-center gap-4 justify-center md:justify-start">
                                <h2 class="text-4xl font-black text-white tracking-tighter uppercase leading-none">
                                    {{ user.name }}
                                </h2>
                                <span class="px-4 py-1.5 bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-[9px] font-black uppercase tracking-[0.2em] rounded-full self-center">
                                    Akun Terverifikasi
                                </span>
                            </div>
                            <p class="text-[10px] font-bold text-indigo-300/60 uppercase tracking-[0.3em]">
                                ID Peserta: #{{ String(user.id).padStart(5, '0') }} — Bergabung {{ formatDate(user.created_at) }}
                            </p>
                        </div>

                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 p-5 rounded-2xl inline-block max-w-2xl">
                            <p class="text-[11px] font-bold text-white uppercase tracking-widest leading-relaxed">
                                <span class="text-indigo-400 mr-2">"</span>
                                {{ motivation }}
                                <span class="text-indigo-400 ml-1">"</span>
                            </p>
                        </div>
                    </div>

                    <div class="hidden lg:flex items-center gap-6 bg-white/5 p-6 pr-8 rounded-[3rem] border border-white/10 backdrop-blur-md group hover:bg-white/10 transition-all duration-500">
                        <div class="relative w-20 h-20 flex items-center justify-center">
                            <svg class="w-full h-full transform -rotate-90 transition-all duration-1000">
                                <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="6" fill="transparent" class="text-white/5" />
                                <circle 
                                    cx="40" cy="40" r="34" 
                                    stroke="currentColor" 
                                    stroke-width="6" 
                                    fill="transparent" 
                                    stroke-dasharray="213.6" 
                                    :stroke-dashoffset="strokeDashoffset" 
                                    class="text-indigo-500 transition-all duration-1000 ease-out" 
                                />
                            </svg>
                            <span class="absolute text-sm font-black text-white uppercase tracking-tighter">
                                {{ stats?.average_score || 0 }}%
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-[9px] font-black text-indigo-300 uppercase tracking-[0.2em] mb-1">Status Kesiapan</p>
                            <p class="text-xs font-black text-white uppercase tracking-widest leading-none">Siap Ujian</p>
                            <div class="mt-2 flex gap-1">
                                <div class="w-1 h-1 rounded-full bg-green-500 animate-pulse"></div>
                                <div class="w-1 h-1 rounded-full bg-green-500 animate-pulse delay-75"></div>
                                <div class="w-1 h-1 rounded-full bg-green-500 animate-pulse delay-150"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl">📝</div>
                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest">Total Ujian</span>
                </div>
                <p class="text-4xl font-black text-gray-900 tracking-tighter mb-1">{{ stats?.completed_count || 0 }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pengerjaan Selesai</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-50 rounded-2xl flex items-center justify-center text-xl">📈</div>
                    <span class="text-[9px] font-black text-green-400 uppercase tracking-widest">Skor Rata-Rata</span>
                </div>
                <p class="text-4xl font-black text-gray-900 tracking-tighter mb-1">{{ stats?.average_score || 0 }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nilai Akumulasi</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:translate-y-[-4px] transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-xl">🕒</div>
                    <span class="text-[9px] font-black text-amber-400 uppercase tracking-widest">Aktivitas</span>
                </div>
                <p class="text-lg font-black text-gray-900 tracking-tight mb-1 uppercase truncate">{{ stats?.last_activity || '-' }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sesi Terakhir</p>
            </div>
        </div>

        <div class="mb-8 flex items-center justify-between px-4">
            <h3 class="font-black text-sm text-gray-900 uppercase tracking-[0.2em]">Rekomendasi Paket Ujian</h3>
            <Link :href="route('tryout.index')" class="text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-gray-900 transition">
                Lihat Semua →
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="tryout in availableTryouts" :key="tryout.id" 
                class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group">
                <div class="h-24 bg-gray-50 p-8 flex items-center justify-between border-b border-gray-100">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black text-xs">P</div>
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Premium Tryout</span>
                </div>
                <div class="p-8">
                    <h4 class="font-black text-lg text-gray-900 leading-tight uppercase mb-8 tracking-tighter h-12 overflow-hidden">
                        {{ tryout.title }}
                    </h4>
                    <Link :href="route('tryout.start', tryout.id)" 
                        class="block w-full text-center bg-gray-900 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-lg shadow-gray-200">
                        Mulai Ujian Sekarang
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>