<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Pilihan Paket Membership
const membershipPlans = [
    { id: 'premium_7', duration: 7, label: '7 Hari', price: 19000, priceFormatted: 'Rp 19.000' },
    { id: 'premium_30', duration: 30, label: '30 Hari', price: 49000, priceFormatted: 'Rp 49.000' },
    { id: 'premium_90', duration: 90, label: '90 Hari', price: 99000, priceFormatted: 'Rp 99.000' },
    { id: 'premium_365', duration: 365, label: '1 Tahun', price: 149000, priceFormatted: 'Rp 149.000' }
];

// Default pilihan jatuh pada 1 Tahun
const selectedPlan = ref(membershipPlans[3]);

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
        text: `Anda memilih paket Adidaya ${selectedPlan.value.label}. Lanjutkan ke pembayaran?`,
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
            confirmButton: 'rounded-xl font-medium uppercase tracking-[0.2em] text-[9px] py-4 px-8 transition-all',
            cancelButton: 'rounded-xl font-medium uppercase tracking-[0.2em] text-[9px] py-4 px-8 transition-all'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.get(route('checkout.show', { type: 'membership', id: selectedPlan.value.id }));
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
        <div class="max-w-4xl mx-auto px-4 py-8 md:py-10 space-y-6 animate-in fade-in duration-700">
            
            <div class="relative overflow-hidden rounded-[2rem] bg-white border border-slate-200 shadow-sm p-6 md:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 transition-all hover:shadow-md">
                
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left w-full">
                    <div class="w-20 h-20 rounded-[1.5rem] flex items-center justify-center shrink-0 transition-colors"
                         :class="isAdidayaActive ? 'bg-indigo-50 border border-indigo-100' : 'bg-slate-50 border border-slate-100'">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                             class="w-10 h-10" :class="isAdidayaActive ? 'text-indigo-600' : 'text-slate-400'">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                    </div>
                    
                    <div class="flex-1">
                        <p class="text-[10px] text-slate-400 uppercase tracking-[0.25em] font-medium mb-1">Status Lisensi</p>
                        <h2 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight leading-none mb-3">
                            {{ isAdidayaActive ? 'Nusantara Adidaya' : 'Basic Member' }}
                        </h2>
                        
                        <div v-if="isAdidayaActive" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="text-[10px] font-medium text-emerald-600 uppercase tracking-widest">
                                Aktif hingga: {{ formatDate(user.membership_expires_at) }}
                            </span>
                        </div>
                        <p v-else class="text-[11px] text-slate-500 font-medium tracking-wide">
                            Upgrade lisensi Anda untuk mendapatkan akses ke seluruh tryout premium.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-[2rem] p-6 md:p-8 shadow-sm transition-all hover:shadow-md flex flex-col md:flex-row gap-8 items-center md:items-start justify-between">
                
                <div class="flex-1 w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[10px] font-medium text-slate-400 uppercase tracking-[0.3em]">Paket Basic</h3>
                    </div>
                    
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="text-4xl font-medium text-slate-800 tracking-tighter">Rp 0</span>
                        <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">/ Selamanya</span>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="shrink-0 w-5 h-5 rounded-full bg-slate-50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Akses Terbatas Tryout Gratis</span>
                        </div>
                        <div class="flex items-center gap-4 opacity-40">
                            <div class="shrink-0 w-5 h-5 rounded-full bg-slate-50 flex items-center justify-center">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wide line-through">Materi Premium & Analisis Lengkap</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-64 shrink-0">
                    <button disabled class="w-full py-4 rounded-xl bg-slate-50 text-[9px] font-medium text-slate-400 uppercase tracking-[0.2em] cursor-not-allowed border border-slate-100">
                        Status Saat Ini
                    </button>
                </div>
            </div>

            <div class="relative bg-white border border-indigo-100 rounded-[2rem] p-6 md:p-8 shadow-lg shadow-indigo-900/5 transition-all overflow-hidden flex flex-col md:flex-row gap-8 items-start justify-between group">
                
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-50 rounded-full blur-[60px] opacity-60 pointer-events-none group-hover:bg-indigo-100 transition-colors duration-700"></div>
                
                <div class="flex-1 w-full relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <h3 class="text-[10px] font-medium text-indigo-600 uppercase tracking-[0.3em]">Nusantara Adidaya</h3>
                        
                        <span v-if="selectedPlan.duration === 365" class="ml-2 px-2 py-1 bg-amber-50 border border-amber-100 rounded-md text-[8px] font-medium text-amber-600 uppercase tracking-widest hidden sm:block">
                            Paling Hemat
                        </span>
                    </div>
                    
                    <div class="flex items-baseline gap-2 mb-6 transition-all duration-300">
                        <span class="text-4xl sm:text-5xl font-medium text-slate-900 tracking-tighter">{{ selectedPlan.priceFormatted }}</span>
                        <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">/ {{ selectedPlan.label }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4 mb-2">
                        <div v-for="(feature, index) in features" :key="index" class="flex items-start gap-4">
                            <div class="shrink-0 w-5 h-5 rounded-full bg-indigo-50 flex items-center justify-center mt-0.5 transition-colors">
                                <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span class="text-xs font-medium text-slate-600 uppercase tracking-wide leading-relaxed">{{ feature }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-72 shrink-0 flex flex-col gap-4 relative z-10">
                    
                    <div v-if="!isAdidayaActive" class="w-full">
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-[0.2em] mb-2 text-center md:text-left">Pilih Durasi Paket</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                v-for="plan in membershipPlans" 
                                :key="plan.id"
                                @click="selectedPlan = plan"
                                :class="[
                                    selectedPlan.id === plan.id 
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 border-indigo-600' 
                                        : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50',
                                    'border py-3 px-2 rounded-xl text-center transition-all duration-300 active:scale-95'
                                ]"
                            >
                                <div class="text-[9px] uppercase tracking-widest font-medium mb-1">{{ plan.label }}</div>
                                <div class="text-[10px] font-medium tracking-tight" :class="selectedPlan.id === plan.id ? 'text-indigo-100' : 'text-slate-700'">
                                    {{ plan.priceFormatted }}
                                </div>
                            </button>
                        </div>
                    </div>

                    <button 
                        @click="buyMembership"
                        :disabled="isAdidayaActive"
                        :class="isAdidayaActive 
                            ? 'bg-slate-50 text-slate-400 border border-slate-100 cursor-not-allowed' 
                            : 'bg-slate-900 text-white hover:bg-indigo-600 hover:shadow-lg hover:shadow-indigo-500/20 active:scale-[0.98]'"
                        class="w-full py-4 rounded-xl text-[10px] font-medium uppercase tracking-[0.25em] transition-all duration-300 flex items-center justify-center gap-3 mt-auto border border-transparent"
                    >
                        <span>{{ isAdidayaActive ? 'Lisensi Telah Aktif' : 'Ambil Paket Adidaya' }}</span>
                        <svg v-if="!isAdidayaActive" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="text-center pt-2 pb-8">
                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-[0.2em] leading-relaxed">
                    Sistem pembayaran terenkripsi dan diverifikasi otomatis oleh Midtrans.
                </p>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.space-y-6 > div {
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.space-y-6 > div:nth-child(1) { animation-delay: 0s; }
.space-y-6 > div:nth-child(2) { animation-delay: 0.1s; }
.space-y-6 > div:nth-child(3) { animation-delay: 0.2s; }
</style>