<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tryouts: Array
});

// --- 1. LOGIKA TIMER REAKTIF ---
const now = ref(new Date());
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});

// Fungsi Hitung Mundur
const getCountdown = (startTime) => {
    const start = new Date(startTime);
    const diff = start - now.value;

    if (diff <= 0) return null;

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

// --- 2. LOGIKA STATUS PENDAFTARAN ---
const getStatus = (tryout) => {
    // Pastikan relasi transactions sudah di-load di Controller
    const hasPaid = tryout.transactions && tryout.transactions.some(t => 
        t.status === 'paid' || t.status === 'success'
    );
    
    if (!hasPaid) return 'unregistered';

    const startTime = new Date(tryout.started_at);
    return now.value >= startTime ? 'started' : 'waiting';
};

// --- 3. FORMATTER ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50/50 pb-20">
            
            <div class="bg-slate-900 pt-12 pb-20 md:pt-24 md:pb-32 px-6 rounded-b-[3.5rem] md:rounded-b-[5rem] -mt-2 relative overflow-hidden text-white">
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-600 rounded-full blur-[120px] opacity-20 -mr-20 -mt-20"></div>
                
                <div class="max-w-7xl mx-auto relative z-10 text-center md:text-left">
                    <span class="text-[10px] md:text-xs font-black uppercase tracking-[0.4em] text-indigo-400">Official Exam Center</span>
                    <h2 class="text-4xl md:text-6xl font-black tracking-tighter uppercase italic leading-none mt-2">
                        Katalog <span class="text-indigo-500 underline decoration-indigo-400/30">Tryout</span>
                    </h2>
                    <p class="text-[10px] md:text-sm text-slate-400 font-bold uppercase tracking-widest mt-4 max-w-xl mx-auto md:mx-0">
                        Persiapkan diri dengan simulasi ujian standar nasional.
                    </p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 md:-mt-16 relative z-20">
                
                <div v-if="tryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    <div v-for="tryout in tryouts" :key="tryout.id" 
                        class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden group hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 flex flex-col">
                        
                        <div class="p-8 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-6">
                                <span :class="tryout.is_paid ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'" 
                                    class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border">
                                    {{ tryout.is_paid ? 'Premium' : 'Gratis' }}
                                </span>
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-xl border border-slate-100 group-hover:scale-110 transition-transform">
                                    {{ getStatus(tryout) === 'started' ? '🚀' : '📝' }}
                                </div>
                            </div>

                            <h3 class="font-black text-xl md:text-2xl text-slate-900 uppercase tracking-tight mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ tryout.title }}
                            </h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-6">
                                ⏱️ {{ tryout.duration_minutes }} Menit Pengerjaan
                            </p>

                            <div class="space-y-3 mb-8">
                                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100/50">
                                    <span class="text-lg">📅</span>
                                    <div>
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Jadwal Mulai</p>
                                        <p class="text-[11px] font-bold text-slate-700 uppercase">{{ formatDate(tryout.started_at) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100/50">
                                    <span class="text-lg">💰</span>
                                    <div>
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Harga Paket</p>
                                        <p class="text-sm font-black text-slate-900 italic">{{ formatCurrency(tryout.price) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto space-y-3">
                                <Link v-if="getStatus(tryout) === 'unregistered'"
                                    :href="route('tryout.register', tryout.id)"
                                    class="flex items-center justify-center w-full py-5 bg-slate-900 text-white rounded-[1.8rem] font-black text-[10px] md:text-[11px] uppercase tracking-[0.2em] shadow-lg hover:bg-indigo-600 transition-all active:scale-95"
                                >
                                    Daftar Sekarang
                                </Link>

                                <Link v-else-if="getStatus(tryout) === 'started'"
                                    :href="route('tryout.wait', tryout.id)"
                                    class="flex items-center justify-center w-full py-5 bg-emerald-600 text-white rounded-[1.8rem] font-black text-[10px] md:text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all gap-2"
                                >
                                    Mulai Ujian
                                </Link>

                                <div v-else class="w-full py-4 bg-indigo-50/50 text-indigo-600 text-center rounded-[1.8rem] font-black text-[10px] uppercase tracking-[0.1em] border-2 border-dashed border-indigo-100 flex flex-col gap-1">
                                    <span class="text-[8px] text-slate-400 uppercase">Terdaftar - Mulai Dalam:</span>
                                    <span class="text-sm font-black tracking-[0.2em] tabular-nums">
                                        {{ getCountdown(tryout.started_at) }}
                                    </span>
                                </div>

                                <div v-if="getStatus(tryout) !== 'unregistered'" class="flex justify-center">
                                    <Link :href="route('tryout.leaderboard', tryout.id)" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-all py-2">
                                        🏆 Lihat Peringkat
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-24 bg-white rounded-[4rem] border-2 border-dashed border-slate-100">
                    <div class="text-5xl mb-4 opacity-20">📭</div>
                    <h3 class="font-black text-xl text-slate-900 uppercase tracking-tight">Belum ada paket tersedia</h3>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Nantikan pembaruan soal terbaru!</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.transition-all { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
</style>