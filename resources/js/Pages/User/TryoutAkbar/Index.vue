<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    tryouts: Array
});

// --- FORMATTERS ---
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// --- LOGIKA STATUS WAKTU ---
const getEventStatus = (start, end) => {
    const now = new Date();
    const startDate = new Date(start);
    const endDate = new Date(end);

    if (now < startDate) return 'upcoming'; // Belum Mulai
    if (now > endDate) return 'ended';     // Sudah Selesai
    return 'ongoing';                      // Sedang Berlangsung
};

// --- HANDLE TOMBOL KLIK ---
const handleAction = (tryout) => {
    const status = getEventStatus(tryout.started_at, tryout.ended_at);
    
    // 1. Jika belum terdaftar -> Arahkan ke halaman Upload Bukti (Register Akbar)
    if (!tryout.is_registered) {
        // PERBAIKAN: Mengarah ke route khusus Tryout Akbar
        router.get(route('tryout-akbar.register', tryout.id));
        return;
    }

    // 2. Jika sudah terdaftar, cek waktunya
    if (status === 'upcoming') {
        alert(`Event belum dimulai. Harap kembali pada ${formatDate(tryout.started_at)} WIB.`);
    } else if (status === 'ended') {
        alert('Maaf, waktu pengerjaan event ini sudah habis.');
    } else {
        // Waktunya pas -> Masuk ke halaman ujian
        router.get(route('tryout.show', tryout.id));
    }
};
</script>

<template>
    <Head title="Event Tryout Akbar" />

    <AuthenticatedLayout>
        
        <div class="bg-gradient-to-r from-amber-600 to-orange-500 py-16 text-center text-white relative overflow-hidden shadow-lg">
            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/20 rounded-full text-xs font-black uppercase tracking-widest mb-4 border border-white/30 backdrop-blur-sm">
                    <span class="text-xl">🏆</span> Official Event
                </div>
                <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight drop-shadow-md">
                    TRYOUT AKBAR NASIONAL
                </h1>
                <p class="text-orange-100 max-w-2xl mx-auto text-lg font-medium">
                    Uji kemampuanmu bersaing secara real-time dengan ribuan peserta lain dalam satu waktu serentak.
                </p>
            </div>
            
            <div class="absolute top-0 left-0 w-full h-full opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
            <div class="absolute -top-10 -left-10 w-64 h-64 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        </div>

        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="tryouts.length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
                    <div class="text-6xl mb-4 grayscale opacity-50">🏆</div>
                    <h3 class="text-xl font-bold text-slate-600">Belum ada Event Aktif</h3>
                    <p class="text-slate-400 mt-2">Nantikan jadwal Tryout Akbar berikutnya!</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="to in tryouts" :key="to.id" class="bg-white rounded-3xl shadow-xl shadow-orange-900/5 border border-orange-100 overflow-hidden hover:-translate-y-2 transition duration-300 group flex flex-col h-full">
                        
                        <div class="bg-gradient-to-r from-amber-500 to-orange-500 text-white text-[10px] font-black uppercase tracking-[0.2em] py-2 text-center shadow-sm">
                            Special Event
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <h3 class="font-black text-xl text-slate-800 mb-2 leading-snug group-hover:text-amber-600 transition min-h-[3.5rem]">
                                {{ to.title }}
                            </h3>
                            
                            <div class="space-y-4 my-6 flex-1">
                                <div class="flex items-start gap-3 text-sm text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center shrink-0 text-lg">📅</div>
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide">Waktu Pelaksanaan</p>
                                        <p class="font-bold text-slate-800 text-xs leading-relaxed mt-0.5">
                                            {{ formatDate(to.started_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Biaya</p>
                                        <p class="font-black text-emerald-600 text-sm mt-0.5">
                                            {{ to.price > 0 ? formatRupiah(to.price) : 'GRATIS' }}
                                        </p>
                                    </div>
                                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Durasi</p>
                                        <p class="font-black text-slate-700 text-sm mt-0.5">{{ to.duration }} Menit</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-100">
                                <button 
                                    @click="handleAction(to)"
                                    class="w-full py-3.5 font-bold rounded-xl transition-all shadow-lg active:scale-95 border-2 flex items-center justify-center gap-2 relative overflow-hidden group/btn"
                                    :class="{
                                        // 1. BELUM DAFTAR -> Hitam/Biru Gelap
                                        'bg-slate-800 text-white border-slate-800 hover:bg-slate-900 hover:shadow-slate-200': !to.is_registered,
                                        
                                        // 2. SUDAH DAFTAR & BELUM MULAI -> Kuning
                                        'bg-amber-50 text-amber-600 border-amber-200 cursor-help hover:bg-amber-100': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'upcoming',

                                        // 3. SUDAH DAFTAR & SEDANG BERLANGSUNG -> Hijau
                                        'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:shadow-emerald-200 animate-pulse-slow': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'ongoing',

                                        // 4. SUDAH SELESAI -> Abu-abu
                                        'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'ended'
                                    }"
                                >
                                    <span v-if="!to.is_registered">👉 Daftar Event</span>
                                    <span v-else-if="getEventStatus(to.started_at, to.ended_at) === 'upcoming'">⏳ Belum Dimulai</span>
                                    <span v-else-if="getEventStatus(to.started_at, to.ended_at) === 'ongoing'">🔥 Kerjakan Sekarang</span>
                                    <span v-else>🏁 Event Selesai</span>
                                </button>

                                <p v-if="to.requirements" class="text-center mt-3 text-[10px] text-slate-400 flex items-center justify-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                    Syarat & Ketentuan Berlaku
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
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
.animate-pulse-slow {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>