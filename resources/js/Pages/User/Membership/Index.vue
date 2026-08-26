<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();
const user = computed(() => page.props.auth.user);

// 1. Ambil data packages secara dinamis dari database (Inertia Props)
const props = defineProps({
    packages: Array
});

// 2. Format data packages agar sesuai dengan kebutuhan tampilan UI
const membershipPlans = computed(() => {
    if (!props.packages) return [];
    return props.packages.map(pkg => ({
        id: pkg.id, // Menggunakan ID asli dari database (integer)
        duration: pkg.duration_days,
        label: pkg.duration_days >= 365 ? '1 Tahun' : `${pkg.duration_days} Hari`,
        name: pkg.name,
        price: pkg.price,
        priceFormatted: new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(pkg.price)
    }));
});

// 3. Pasang default pilihan pada paket dengan durasi terlama (atau indeks terakhir)
const selectedPlan = ref(null);
watch(membershipPlans, (newPlans) => {
    if (newPlans.length > 0 && !selectedPlan.value) {
        selectedPlan.value = newPlans[newPlans.length - 1]; // Mengambil paket terlama (misal 1 tahun)
    }
}, { immediate: true });

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
    if (!selectedPlan.value) return;

    Swal.fire({
        title: 'Konfirmasi Lisensi',
        text: `Anda memilih paket Adidaya ${selectedPlan.value.label}. Lanjutkan ke pembayaran?`,
        showCancelButton: true,
        confirmButtonColor: '#007AFF',
        cancelButtonColor: '#F5F5F7',
        confirmButtonText: 'Lanjutkan',
        cancelButtonText: 'Batal', 
        reverseButtons: true,
        customClass: {
            popup: 'rounded-[24px] border border-black/5 shadow-[0_10px_40px_rgba(0,0,0,0.1)] p-6 font-sans',
            title: 'font-bold text-[#1D1D1F] tracking-tight text-[20px]',
            htmlContainer: 'text-[#86868B] font-medium text-[14px] mt-1',
            confirmButton: 'rounded-full font-semibold text-[14px] py-3 px-8 transition-all',
            cancelButton: 'rounded-full font-semibold text-[14px] py-3 px-8 text-[#1D1D1F] transition-all hover:bg-[#E3E3E8]'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('membership.buy'), { 
                plan_id: selectedPlan.value.id,
                payment_method: 'gateway' 
            });
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
        <!-- Background transparan, padding bawah dikurangi -->
        <div class="w-full bg-transparent pb-16 md:pb-24 font-sans animate-in fade-in duration-500">
            
            <!-- Padding top dan gap diperkecil (space-y-4 md:space-y-6) -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-4 md:pt-6 space-y-4 md:space-y-5">
                
                <!-- STATUS KARTU (Lebih Pendek & Padat) -->
                <div class="bg-white rounded-[24px] p-5 sm:p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 flex flex-col sm:flex-row items-center sm:items-center justify-between gap-4 sm:gap-5 transition-all">
                    
                    <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-5 text-center sm:text-left w-full">
                        <!-- Ikon Profil Diperkecil (w-14 h-14) -->
                        <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 transition-colors shadow-sm border border-black/5"
                             :class="isAdidayaActive ? 'bg-[#F0F4FF]' : 'bg-[#F5F5F7]'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" 
                                 class="w-7 h-7" :class="isAdidayaActive ? 'text-[#007AFF]' : 'text-[#86868B]'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </div>
                        
                        <!-- Teks Diperkecil & Margin Dirapatkan -->
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 mb-1.5 sm:mb-1">
                                <h2 class="text-[20px] sm:text-[22px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                                    {{ isAdidayaActive ? 'Nusantara Adidaya' : 'Basic Member' }}
                                </h2>
                                <!-- Badge Status -->
                                <div v-if="isAdidayaActive" class="inline-flex items-center justify-center sm:justify-start gap-1.5 mt-1 sm:mt-0">
                                    <span class="px-2.5 py-1 bg-[#E5F5EA] text-[#34C759] border border-[#34C759]/20 rounded-full text-[10px] font-bold tracking-wide shadow-sm flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-60"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-[#34C759]"></span>
                                        </span>
                                        Aktif hingga {{ formatDate(user.membership_expires_at) }}
                                    </span>
                                </div>
                            </div>
                            
                            <p v-if="!isAdidayaActive" class="text-[13px] sm:text-[14px] text-[#86868B] font-medium leading-relaxed m-0">
                                Dapatkan akses tanpa batas ke seluruh tryout premium dengan melakukan upgrade lisensi Anda hari ini.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- PILIHAN PAKET & FITUR (Lebih Padat) -->
                <div v-if="selectedPlan" class="bg-white rounded-[24px] sm:rounded-[28px] p-5 sm:p-8 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 flex flex-col lg:flex-row gap-6 lg:gap-10">
                    
                    <!-- Fitur & Harga -->
                    <div class="flex-1 flex flex-col">
                        <div class="mb-6 border-b border-black/5 pb-6">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-7 h-7 rounded-full bg-[#F0F4FF] flex items-center justify-center text-[#007AFF] shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                                <h3 class="text-[16px] sm:text-[18px] font-bold text-[#1D1D1F]">Nusantara Adidaya</h3>
                            </div>
                            
                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-[32px] sm:text-[38px] font-bold text-[#1D1D1F] tracking-tight leading-none">
                                    {{ selectedPlan.priceFormatted }}
                                </span>
                                <span class="text-[14px] sm:text-[15px] text-[#86868B] font-semibold">
                                    / {{ selectedPlan.label }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <p class="text-[13px] font-bold text-[#1D1D1F] mb-4">Yang Anda dapatkan:</p>
                            <!-- Space antar fitur dirapatkan (space-y-3) -->
                            <div class="space-y-3">
                                <div v-for="(feature, index) in features" :key="index" class="flex items-start gap-3">
                                    <div class="shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-[13px] sm:text-[14px] font-medium text-[#1D1D1F] leading-snug break-words">
                                        {{ feature }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Paket (Box Abu-abu Kanan, padding & ukuran diperkecil) -->
                    <div class="w-full lg:w-[320px] shrink-0 bg-[#F5F5F7] rounded-[20px] sm:rounded-[24px] p-5 flex flex-col gap-5">
                        
                        <div v-if="!isAdidayaActive" class="w-full flex-1">
                            <p class="text-[11px] font-bold text-[#86868B] uppercase tracking-wider mb-3 px-1">
                                Pilih Durasi Langganan
                            </p>
                            
                            <!-- List Pilihan Paket -->
                            <div class="space-y-2.5">
                                <button 
                                    v-for="plan in membershipPlans" 
                                    :key="plan.id"
                                    @click="selectedPlan = plan"
                                    class="w-full text-left p-3.5 rounded-[16px] border-2 transition-all duration-300 flex items-center justify-between group bg-white shadow-sm"
                                    :class="selectedPlan.id === plan.id ? 'border-[#007AFF] ring-4 ring-[#007AFF]/10' : 'border-transparent hover:border-black/5'"
                                >
                                    <div class="flex flex-col min-w-0 pr-2">
                                        <div class="text-[14px] font-bold mb-0.5 break-words" :class="selectedPlan.id === plan.id ? 'text-[#007AFF]' : 'text-[#1D1D1F]'">
                                            {{ plan.label }}
                                        </div>
                                        <div v-if="plan.duration >= 365" class="text-[10px] font-semibold bg-[#F0F4FF] text-[#007AFF] px-2 py-0.5 rounded border border-[#007AFF]/20 w-fit">
                                            Paling Hemat
                                        </div>
                                    </div>
                                    <div class="text-[14px] font-semibold shrink-0" :class="selectedPlan.id === plan.id ? 'text-[#007AFF]' : 'text-[#86868B]'">
                                        {{ plan.priceFormatted }}
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Lanjutkan -->
                        <div class="mt-auto pt-1">
                            <button 
                                @click="buyMembership"
                                :disabled="isAdidayaActive || !selectedPlan"
                                :class="isAdidayaActive 
                                    ? 'bg-[#E3E3E8] text-[#86868B] border border-black/5 cursor-not-allowed' 
                                    : 'bg-[#007AFF] text-white hover:bg-[#0062CC] shadow-[0_4px_12px_rgba(0,122,255,0.3)] active:scale-[0.98]'"
                                class="w-full py-3.5 rounded-full text-[14px] font-semibold transition-all duration-300 flex items-center justify-center gap-2"
                            >
                                <span>{{ isAdidayaActive ? 'Lisensi Telah Aktif' : 'Lanjutkan Pembayaran' }}</span>
                                <svg v-if="!isAdidayaActive" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            
                            <p class="text-center text-[11px] text-[#86868B] font-medium mt-3 px-2">
                                Transaksi aman dan terenkripsi. Diproses secara otomatis.
                            </p>
                        </div>
                        
                    </div>

                </div>

            </div>
            
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>