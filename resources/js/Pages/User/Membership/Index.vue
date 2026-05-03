<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Logika status membership
const isAdidayaActive = computed(() => {
    if (!user.value?.membership_expires_at) return false;
    return new Date(user.value.membership_expires_at) > new Date();
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const buyMembership = () => {
    Swal.fire({
        title: 'Konfirmasi Langganan',
        text: "Apakah Anda ingin melanjutkan ke halaman pembayaran Nusantara Adidaya?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-[2rem] border-none shadow-2xl p-6 md:p-10',
            title: 'font-medium text-slate-900 uppercase tracking-tight text-lg',
            htmlContainer: 'text-slate-500 font-medium text-xs mt-2',
            confirmButton: 'rounded-xl font-medium uppercase tracking-[0.2em] text-[9px] py-4 px-8',
            cancelButton: 'rounded-xl font-medium uppercase tracking-[0.2em] text-[9px] py-4 px-8'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.get(route('checkout.show', { type: 'membership', id: 'premium' }));
        }
    });
};

const features = [
    'Akses Seluruh Katalog Tryout Premium',
    'Simulasi Ranking Nasional & Real-time',
    'Analisis Quantum (Kelemahan & Kekuatan)',
    'Sertifikat Digital Setiap Simulasi',
    'Update Soal Prediksi Terbaru Mingguan',
    'Bebas Iklan & Prioritas Layanan'
];
</script>

<template>
    <Head title="Membership Nusantara" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto px-2 md:px-4 space-y-8 animate-in fade-in duration-700">
            
            <div class="bg-white border border-slate-100 rounded-[2rem] p-6 md:p-10 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6 text-center md:text-left flex-col md:flex-row">
                    <div :class="isAdidayaActive ? 'bg-indigo-600 shadow-indigo-100' : 'bg-slate-100'" 
                         class="w-20 h-20 rounded-[1.5rem] flex items-center justify-center shadow-lg transition-all duration-500 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                             :class="isAdidayaActive ? 'text-white' : 'text-slate-400'" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1.5">Status Keanggotaan</p>
                        <h2 class="text-2xl font-medium text-slate-900 tracking-tight uppercase leading-none">
                            {{ isAdidayaActive ? 'Nusantara Adidaya' : 'Basic Member' }}
                        </h2>
                        <p v-if="isAdidayaActive" class="text-[11px] text-indigo-600 font-medium mt-2 uppercase tracking-widest italic opacity-80">
                            Akses aktif hingga: {{ formatDate(user.membership_expires_at) }}
                        </p>
                    </div>
                </div>
                
                <div v-if="isAdidayaActive" class="px-5 py-2.5 bg-emerald-50 border border-emerald-100 rounded-2xl shrink-0">
                    <span class="text-[10px] font-medium text-emerald-600 uppercase tracking-[0.2em] flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Langganan Aktif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                
                <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 md:p-12 flex flex-col transition-all duration-500 opacity-70 group hover:opacity-100">
                    <div class="mb-10">
                        <h3 class="text-[10px] font-medium text-slate-400 uppercase tracking-[0.3em] mb-3">Paket Dasar</h3>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-medium text-slate-900 tracking-tighter">Rp 0</span>
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">/ Selamanya</span>
                        </div>
                    </div>
                    
                    <ul class="space-y-5 mb-12 flex-1">
                        <li class="flex items-center gap-4 text-slate-500">
                            <div class="shrink-0 w-5 h-5 rounded-lg bg-slate-50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="text-[11px] font-medium uppercase tracking-wider">Akses Tryout Gratis</span>
                        </li>
                        <li class="flex items-center gap-4 text-slate-300 italic line-through">
                            <div class="shrink-0 w-5 h-5 rounded-lg bg-slate-50/50 flex items-center justify-center">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <span class="text-[11px] font-medium uppercase tracking-wider">Materi Premium Adidaya</span>
                        </li>
                    </ul>

                    <button disabled class="w-full py-5 rounded-2xl border border-slate-50 text-[9px] font-medium text-slate-300 uppercase tracking-[0.3em] cursor-not-allowed">
                        Paket Dasar Aktif
                    </button>
                </div>

                <div class="relative group h-full">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 rounded-[2.6rem] blur-xl opacity-50 group-hover:opacity-100 transition duration-1000"></div>
                    <div class="relative bg-white border border-indigo-50 rounded-[2.5rem] p-8 md:p-12 flex flex-col h-full shadow-2xl shadow-indigo-900/5 overflow-hidden">
                        
                        <div class="absolute top-8 right-8">
                            <span class="text-[8px] font-medium text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full uppercase tracking-[0.2em] border border-indigo-100 shadow-sm">
                                Best Choice
                            </span>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-[10px] font-medium text-indigo-600 uppercase tracking-[0.3em] mb-3">Nusantara Adidaya</h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-medium text-slate-900 tracking-tighter">Rp 149.000</span>
                                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">/ 12 Bulan</span>
                            </div>
                        </div>

                        <ul class="space-y-5 mb-12 flex-1">
                            <li v-for="feature in features" :key="feature" class="flex items-center gap-4 text-slate-600">
                                <div class="shrink-0 w-5 h-5 bg-indigo-50 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                                    <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <span class="text-[11px] font-medium uppercase tracking-wider">{{ feature }}</span>
                            </li>
                        </ul>

                        <button 
                            @click="buyMembership"
                            :disabled="isAdidayaActive"
                            :class="isAdidayaActive ? 'bg-slate-50 text-slate-300 border-slate-100 cursor-not-allowed' : 'bg-slate-950 text-white hover:bg-indigo-600 shadow-xl shadow-slate-200'"
                            class="w-full py-5 rounded-2xl text-[10px] font-medium uppercase tracking-[0.3em] transition-all active:scale-95 border border-transparent"
                        >
                            {{ isAdidayaActive ? 'Langganan Aktif' : 'Pilih Adidaya' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-center pt-8 pb-12 max-w-2xl mx-auto px-6">
                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-[0.25em] leading-relaxed">
                    Sistem pembayaran terenkripsi oleh Midtrans. <br class="md:hidden"> Lisensi akses akan terbit secara otomatis setelah transaksi diverifikasi.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 1s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>