<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object, 
});

const timeLeft = ref('');
const canStart = ref(false);
let timer = null;

const updateTimer = () => {
    const now = new Date();
    const start = new Date(props.tryout.started_at);
    const end = new Date(props.tryout.ended_at);
    
    if (now >= start && now <= end) {
        canStart.value = true;
        timeLeft.value = "Event Sedang Berlangsung!";
    } else if (now > end) {
        timeLeft.value = "Event Telah Selesai";
    } else {
        const diff = start - now;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        timeLeft.value = `${days}h ${hours}j ${minutes}m ${seconds}d`;
    }
};

onMounted(() => {
    updateTimer();
    timer = setInterval(updateTimer, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});

const startExam = () => {
    router.get(route('tryout.show', props.tryout.id));
};

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Waiting Room" />

    <AuthenticatedLayout>
        <div class="min-h-[calc(100vh-65px)] bg-[#F8FAFC] flex items-center justify-center relative overflow-hidden font-sans p-4">
            
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-5 pointer-events-none"></div>
            <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob pointer-events-none"></div>
            <div class="absolute bottom-[-10%] left-[-5%] w-96 h-96 bg-amber-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000 pointer-events-none"></div>

            <div class="relative z-10 max-w-3xl w-full">
                
                <div class="mb-6 text-center">
                    <Link :href="route('tryout-akbar.index')" class="text-sm font-bold text-slate-400 hover:text-indigo-600 transition">
                        &larr; Kembali ke Katalog
                    </Link>
                </div>

                <div class="bg-white rounded-[2rem] shadow-xl shadow-indigo-100/60 overflow-hidden border border-slate-100 relative">
                    
                    <div class="h-2 w-full bg-gradient-to-r from-indigo-500 via-purple-500 to-amber-500"></div>

                    <div class="p-8 md:p-12 text-center relative z-20">
                        
                        <div v-if="transaction.status === 'pending'">
                            <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6 relative border border-amber-100">
                                <div class="absolute inset-0 border-4 border-amber-100 rounded-full animate-ping"></div>
                                <span class="text-4xl">⏳</span>
                            </div>
                            
                            <h1 class="text-2xl font-black text-slate-800 mb-2">Menunggu Verifikasi Admin</h1>
                            <p class="text-slate-500 max-w-md mx-auto mb-8 font-medium leading-relaxed">
                                Bukti pendaftaran Anda telah kami terima. Admin sedang melakukan pengecekan. Mohon tunggu, proses ini biasanya memakan waktu 1x24 jam.
                            </p>

                            <div class="bg-slate-50 rounded-xl p-4 inline-block border border-slate-200 mb-8">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider mb-1">Kode Pendaftaran</p>
                                <p class="text-lg font-mono font-bold text-slate-700 select-all">{{ transaction.invoice_code }}</p>
                            </div>

                            <button onclick="window.location.reload()" class="block w-full md:w-auto mx-auto px-8 py-3 bg-white border-2 border-slate-200 text-slate-600 font-bold rounded-xl hover:border-indigo-500 hover:text-indigo-600 transition shadow-sm active:scale-95">
                                Refresh Status
                            </button>
                        </div>

                        <div v-else-if="transaction.status === 'paid' || transaction.status === 'success'">
                            
                            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-black uppercase tracking-widest mb-6 border border-green-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Pendaftaran Berhasil
                            </div>

                            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-2">{{ tryout.title }}</h1>
                            <p class="text-slate-500 font-bold mb-8">Selamat! Anda telah terdaftar sebagai peserta.</p>

                            <div class="bg-slate-900 text-white rounded-2xl p-6 mb-8 relative overflow-hidden group shadow-lg shadow-slate-900/20">
                                <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-purple-600 opacity-20 group-hover:opacity-30 transition"></div>
                                
                                <p class="text-xs font-bold text-indigo-200 uppercase tracking-widest mb-2">Menuju Waktu Pelaksanaan</p>
                                <div class="text-3xl md:text-5xl font-mono font-black tracking-tight text-white">
                                    {{ timeLeft }}
                                </div>
                                <p class="text-xs text-slate-300 mt-2 font-medium">{{ formatDate(tryout.started_at) }} WIB</p>
                            </div>

                            <div v-if="canStart">
                                <button @click="startExam" class="w-full md:w-auto px-10 py-4 bg-emerald-500 text-white font-black rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-200/50 transition transform hover:scale-105 animate-pulse">
                                    MULAI UJIAN SEKARANG 🚀
                                </button>
                            </div>
                            <div v-else>
                                <button disabled class="w-full md:w-auto px-10 py-4 bg-slate-100 text-slate-400 font-bold rounded-xl cursor-not-allowed border border-slate-200">
                                    Tombol Ujian Belum Aktif
                                </button>
                                <p class="text-xs text-slate-500 mt-3 font-medium">Tombol akan aktif otomatis saat waktu mulai tiba.</p>
                            </div>
                        </div>

                    </div>

                    <div class="absolute top-1/2 left-0 w-6 h-6 bg-[#F8FAFC] rounded-r-full -translate-y-1/2 z-20 border-r border-slate-100"></div>
                    <div class="absolute top-1/2 right-0 w-6 h-6 bg-[#F8FAFC] rounded-l-full -translate-y-1/2 z-20 border-l border-slate-100"></div>
                    <div class="absolute top-1/2 left-6 right-6 border-t-2 border-dashed border-slate-200 -translate-y-1/2 z-10"></div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
</style>