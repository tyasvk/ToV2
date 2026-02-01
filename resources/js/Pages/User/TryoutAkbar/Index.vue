<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tryouts: Array
});

// --- STATE MODAL ---
const showUpcomingModal = ref(false);
const selectedTryout = ref(null);

// --- FORMATTERS ---
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    }).replace('.', ':') + ' WIB';
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

// --- HANDLE TOMBOL KLIK UTAMA ---
const handleAction = (tryout) => {
    const status = getEventStatus(tryout.started_at, tryout.ended_at);
    
    // 1. Jika belum terdaftar -> Register
    if (!tryout.is_registered) {
        router.get(route('tryout-akbar.register', tryout.id));
        return;
    }

    // 2. CEK: Jika sudah mengerjakan (Limit 1x)
    if (tryout.latest_attempt_id) {
        return; // Tombol jadi statis, tidak ada aksi
    }

    // 3. Logika Waktu
    if (status === 'upcoming') {
        selectedTryout.value = tryout;
        showUpcomingModal.value = true;
    } else if (status === 'ended') {
        router.get(route('tryout.history.detail', tryout.id));
    } else {
        router.get(route('tryout.show', tryout.id)); 
    }
};

const closeModal = () => {
    showUpcomingModal.value = false;
    selectedTryout.value = null;
};
</script>

<template>
    <Head title="Event Tryout Akbar" />

    <AuthenticatedLayout>
        
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-center">
                <span class="inline-flex items-center gap-1.5 py-0.5 px-2.5 rounded-full bg-indigo-500/20 border border-indigo-400/20 text-indigo-200 text-[10px] font-bold tracking-widest uppercase mb-4 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span> Kompetisi Nasional
                </span>
                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2 leading-tight">
                    Tryout Akbar <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">Nasional</span>
                </h1>
                <p class="mt-2 max-w-2xl mx-auto text-sm md:text-base text-slate-400 font-medium">
                    Uji kemampuanmu bersaing dengan ribuan peserta lain di seluruh Indonesia.
                </p>
            </div>
        </div>

        <div class="py-8 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="tryouts.length === 0" class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-dashed border-slate-200 text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-3xl mb-3 grayscale opacity-50">🏆</div>
                    <h3 class="text-lg font-bold text-slate-800">Belum Ada Event</h3>
                    <p class="text-slate-500 text-sm mt-1">Nantikan jadwal Tryout Akbar berikutnya!</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="to in tryouts" :key="to.id" class="group bg-white rounded-2xl shadow-sm hover:shadow-lg border border-slate-200 overflow-hidden transition-all duration-300 hover:-translate-y-1 flex flex-col h-full relative">
                        
                        <div class="absolute top-3 right-3 z-10">
                            <span v-if="to.is_registered" class="px-2 py-0.5 bg-emerald-500/90 backdrop-blur-sm text-white text-[9px] font-bold uppercase tracking-wider rounded-md shadow-sm flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Terdaftar
                            </span>
                        </div>

                        <div class="h-16 bg-gradient-to-r from-slate-50 to-white relative overflow-hidden border-b border-slate-50">
                            <div class="absolute -left-2 -top-2 w-16 h-16 bg-indigo-500/5 rounded-full blur-xl group-hover:bg-indigo-500/10 transition"></div>
                            <div class="absolute right-4 top-1 text-4xl opacity-[0.03] font-black text-slate-900 select-none">TO</div>
                        </div>

                        <div class="px-5 pb-5 pt-0 flex-1 flex flex-col -mt-8 relative z-10">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-md border border-slate-100 flex items-center justify-center text-2xl mb-3 group-hover:scale-105 transition duration-300">
                                🎖️
                            </div>

                            <h3 class="text-base font-bold text-slate-800 leading-snug mb-0.5 group-hover:text-indigo-600 transition truncate" :title="to.title">
                                {{ to.title }}
                            </h3>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-4">Official CPNS Nusantara Event</p>

                            <div class="mb-4 bg-slate-50/50 p-1 rounded-xl border border-slate-100">
                                <div class="grid grid-cols-2 gap-px bg-slate-100/50 rounded-lg overflow-hidden">
                                    <div class="bg-white p-2.5">
                                        <p class="text-[9px] font-bold text-emerald-600 uppercase mb-0.5">Mulai</p>
                                        <p class="text-[10px] font-bold text-slate-700 leading-tight">{{ formatDate(to.started_at) }}</p>
                                        <p class="text-[9px] text-slate-400">{{ formatTime(to.started_at) }}</p>
                                    </div>
                                    <div class="bg-white p-2.5 border-l border-slate-50">
                                        <p class="text-[9px] font-bold text-red-600 uppercase mb-0.5">Selesai</p>
                                        <p class="text-[10px] font-bold text-slate-700 leading-tight">{{ formatDate(to.ended_at) }}</p>
                                        <p class="text-[9px] text-slate-400">{{ formatTime(to.ended_at) }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between px-3 py-2 mt-px bg-white rounded-b-lg">
                                    <span class="text-[10px] font-bold text-slate-500">{{ to.duration }} Menit</span>
                                    <span :class="to.price > 0 ? 'text-indigo-600' : 'text-emerald-600'" class="text-[10px] font-black">
                                        {{ to.price > 0 ? formatRupiah(to.price) : 'GRATIS' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-auto flex flex-col gap-2">
                                <div 
                                    v-if="to.latest_attempt_id"
                                    class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-widest text-center bg-emerald-50 text-emerald-600 border border-emerald-100 cursor-default select-none"
                                >
                                    ✅ Sudah Dikerjakan
                                </div>

                                <button 
                                    v-else
                                    @click="handleAction(to)"
                                    class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-widest transition-all shadow-sm active:scale-95 flex items-center justify-center gap-1.5"
                                    :class="{
                                        'bg-slate-900 text-white hover:bg-indigo-600 hover:shadow-indigo-200': !to.is_registered,
                                        'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'upcoming',
                                        'bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-200 animate-pulse': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'ongoing',
                                        'bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200': to.is_registered && getEventStatus(to.started_at, to.ended_at) === 'ended'
                                    }"
                                >
                                    <template v-if="!to.is_registered">
                                        Daftar <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                    </template>
                                    <template v-else-if="getEventStatus(to.started_at, to.ended_at) === 'upcoming'">
                                        ⏳ Menunggu Jadwal
                                    </template>
                                    <template v-else-if="getEventStatus(to.started_at, to.ended_at) === 'ongoing'">
                                        🚀 Masuk Ujian
                                    </template>
                                    <template v-else>
                                        🏁 Event Selesai
                                    </template>
                                </button>

                                <template v-if="to.is_registered">
                                    <button 
                                        v-if="!to.latest_attempt_id && getEventStatus(to.started_at, to.ended_at) === 'upcoming'"
                                        @click="handleAction(to)"
                                        class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-widest text-center text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-amber-600 hover:border-amber-200 transition active:scale-95 flex items-center justify-center gap-1.5"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Tunggu Dimulai
                                    </button>

                                    <Link 
                                        v-else-if="to.latest_attempt_id" 
                                        :href="route('tryout.result', to.latest_attempt_id)"
                                        class="w-full py-2.5 rounded-lg font-bold text-[10px] uppercase tracking-widest text-center text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition active:scale-95 flex items-center justify-center gap-1.5"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                        Hasil Tryout Akbar
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Modal :show="showUpcomingModal" @close="closeModal">
            <div class="p-6 text-center">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3 text-amber-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <h2 class="text-lg font-black text-slate-900 mb-2">
                    Event Belum Dimulai
                </h2>
                
                <p class="text-slate-600 text-xs mb-5 leading-relaxed px-4" v-if="selectedTryout">
                    Harap kembali pada <br>
                    <span class="font-bold text-slate-900">{{ formatDate(selectedTryout.started_at) }}</span> 
                    pukul 
                    <span class="font-bold text-slate-900">{{ formatTime(selectedTryout.started_at) }}</span>.
                </p>

                <div class="flex justify-center px-4">
                    <SecondaryButton @click="closeModal" class="w-full justify-center !py-2 !text-xs">
                        Baik, Saya Mengerti
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>