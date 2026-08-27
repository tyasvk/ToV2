<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isRefreshing = ref(false);

// 1. PERBAIKAN: Fungsi Hard Refresh untuk membersihkan cache Inertia
// Ini memaksa browser memuat data auth.user paling baru langsung dari database
const refreshStatus = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['auth'],
        onFinish: () => {
            isRefreshing.value = false;
            // Fallback: Jika Inertia masih nge-cache, kita paksa browser reload penuh
            if (!isAdidayaActive.value) {
                window.location.reload();
            }
        }
    });
};

// Auto-refresh sekali saat halaman dimuat (berguna saat user baru saja di-redirect dari Payment Gateway)
onMounted(() => {
    setTimeout(() => {
        router.reload({ only: ['auth'] });
    }, 1500); // Jeda 1.5 detik memberi waktu bagi webhook backend untuk selesai memproses DB
});

// 2. LOGIKA TANGGAL ANTI-GAGAL
const isAdidayaActive = computed(() => {
    if (!user.value?.membership_expires_at) return false;
    
    // Konversi string tanggal dari database dengan aman
    const expiryDate = new Date(user.value.membership_expires_at.replace(/-/g, '/').replace('T', ' '));
    const now = new Date();
    
    return expiryDate.getTime() > now.getTime();
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    const d = new Date(dateString.replace(/-/g, '/').replace('T', ' '));
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const props = defineProps({
    packages: Array
});

const membershipPlans = computed(() => {
    if (!props.packages) return [];
    return props.packages.map(pkg => ({
        id: pkg.id,
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

const selectedPlan = ref(null);
watch(membershipPlans, (newPlans) => {
    if (newPlans.length > 0 && !selectedPlan.value) {
        selectedPlan.value = newPlans[newPlans.length - 1]; 
    }
}, { immediate: true });

const buyMembership = () => {
    if (!selectedPlan.value) return;

    Swal.fire({
        title: 'Konfirmasi Lisensi',
        text: `Anda memilih paket Adidaya ${selectedPlan.value.label}. Lanjutkan ke pembayaran?`,
        showCancelButton: true,
        confirmButtonColor: '#1D1D1F',
        cancelButtonColor: '#F8FAFC',
        confirmButtonText: 'Lanjutkan',
        cancelButtonText: 'Batal', 
        reverseButtons: true,
        customClass: {
            popup: 'rounded-[24px] border border-slate-200 shadow-xl p-6 font-sans',
            title: 'font-bold text-slate-900 tracking-tight text-[20px]',
            htmlContainer: 'text-slate-500 font-medium text-[14px] mt-1',
            confirmButton: 'rounded-full font-bold text-[14px] py-3 px-8 transition-all',
            cancelButton: 'rounded-full font-bold text-[14px] py-3 px-8 text-slate-700 transition-all hover:bg-slate-100 border border-slate-200'
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
        <!-- Background Clean Premium -->
        <div class="w-full bg-[#F8FAFC] min-h-screen pb-16 md:pb-24 font-sans animate-in fade-in duration-500 relative overflow-hidden">

            <!-- Pendaran Latar Belakang Sangat Halus -->
            <div class="fixed top-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
            <div class="fixed bottom-[-10%] left-[-10%] w-[450px] h-[450px] bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-4 md:pt-8 space-y-4 md:space-y-6 relative z-10">

                <!-- STATUS KARTU -->
                <div class="bg-white rounded-[24px] p-5 sm:p-6 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center sm:items-center justify-between gap-4 sm:gap-5 transition-all">

                    <div class="flex flex-col sm:flex-row items-center sm:items-center gap-4 sm:gap-5 text-center sm:text-left w-full">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 transition-colors shadow-sm border"
                             :class="isAdidayaActive ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-100 border-slate-200'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" 
                                 class="w-7 h-7" :class="isAdidayaActive ? 'text-emerald-600' : 'text-slate-400'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </div>

                        <div class="flex-1 flex flex-col justify-center">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-3 mb-1.5 sm:mb-1">
                                <h2 class="text-[20px] sm:text-[22px] font-bold tracking-tight leading-tight" :class="isAdidayaActive ? 'text-slate-900' : 'text-slate-700'">
                                    {{ isAdidayaActive ? 'Nusantara Adidaya' : 'Basic Member' }}
                                </h2>
                                
                                <!-- Badge Status -->
                                <div v-if="isAdidayaActive" class="inline-flex items-center justify-center sm:justify-start gap-1.5 mt-1 sm:mt-0">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full text-[10px] font-bold tracking-wide shadow-sm flex items-center gap-1.5">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-60"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                        </span>
                                        Aktif hingga {{ formatDate(user.membership_expires_at) }}
                                    </span>
                                </div>
                            </div>

                            <p v-if="!isAdidayaActive" class="text-[13px] sm:text-[14px] text-slate-500 font-medium leading-relaxed m-0">
                                Dapatkan akses ke seluruh tryout premium dengan mengaktifkan lisensi Anda.
                            </p>
                        </div>
                    </div>

                    <!-- TOMBOL REFRESH MANUAL (Hanya muncul jika belum aktif) -->
                    <div v-if="!isAdidayaActive" class="shrink-0 w-full sm:w-auto mt-2 sm:mt-0 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                        <button 
                            @click="refreshStatus" 
                            :disabled="isRefreshing"
                            class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[12px] rounded-full transition-all flex items-center justify-center gap-2 border border-slate-200"
                        >
                            <svg v-if="isRefreshing" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            {{ isRefreshing ? 'Mengecek...' : 'Cek Status Pembayaran' }}
                        </button>
                    </div>

                </div>

                <!-- PILIHAN PAKET & FITUR -->
                <div v-if="selectedPlan" class="bg-white rounded-[24px] sm:rounded-[28px] p-5 sm:p-8 shadow-sm border border-slate-200 flex flex-col lg:flex-row gap-6 lg:gap-10">

                    <!-- Fitur & Harga -->
                    <div class="flex-1 flex flex-col">
                        <div class="mb-6 border-b border-slate-100 pb-6">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </div>
                                <h3 class="text-[16px] sm:text-[18px] font-bold text-slate-900">Nusantara Adidaya</h3>
                            </div>

                            <div class="flex flex-wrap items-baseline gap-2">
                                <span class="text-[32px] sm:text-[40px] font-bold text-slate-900 tracking-tight leading-none">
                                    {{ selectedPlan.priceFormatted }}
                                </span>
                                <span class="text-[14px] sm:text-[15px] text-slate-500 font-semibold">
                                    / {{ selectedPlan.label }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <p class="text-[13px] font-bold text-slate-900 mb-4 uppercase tracking-wider">Yang Anda Dapatkan:</p>
                            <div class="space-y-3.5">
                                <div v-for="(feature, index) in features" :key="index" class="flex items-start gap-3">
                                    <div class="shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-[13px] sm:text-[14px] font-medium text-slate-700 leading-snug break-words">
                                        {{ feature }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Paket -->
                    <div class="w-full lg:w-[320px] shrink-0 bg-slate-50 rounded-[20px] sm:rounded-[24px] p-5 flex flex-col gap-5 border border-slate-100">

                        <div v-if="!isAdidayaActive" class="w-full flex-1">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">
                                Pilih Durasi Langganan
                            </p>

                            <div class="space-y-2.5">
                                <button 
                                    v-for="plan in membershipPlans" 
                                    :key="plan.id"
                                    @click="selectedPlan = plan"
                                    class="w-full text-left p-4 rounded-[16px] border-2 transition-all duration-300 flex items-center justify-between group bg-white shadow-sm"
                                    :class="selectedPlan.id === plan.id ? 'border-slate-900 ring-4 ring-slate-900/5' : 'border-transparent hover:border-slate-200'"
                                >
                                    <div class="flex flex-col min-w-0 pr-2">
                                        <div class="text-[14px] font-bold mb-1.5 break-words" :class="selectedPlan.id === plan.id ? 'text-slate-900' : 'text-slate-700'">
                                            {{ plan.label }}
                                        </div>
                                        <div v-if="plan.duration >= 365" class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md w-fit tracking-wider uppercase">
                                            Paling Hemat
                                        </div>
                                    </div>
                                    <div class="text-[14px] font-bold shrink-0" :class="selectedPlan.id === plan.id ? 'text-slate-900' : 'text-slate-500'">
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
                                    ? 'bg-emerald-50 text-emerald-600 border border-emerald-200 cursor-not-allowed' 
                                    : 'bg-slate-900 text-white hover:bg-black shadow-md shadow-slate-900/20 active:scale-[0.98]'"
                                class="w-full py-4 rounded-full text-[14px] font-bold transition-all duration-300 flex items-center justify-center gap-2"
                            >
                                <svg v-if="isAdidayaActive" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ isAdidayaActive ? 'Lisensi Telah Aktif' : 'Lanjutkan Pembayaran' }}</span>
                                <svg v-if="!isAdidayaActive" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <p class="text-center text-[11px] text-slate-400 font-medium mt-3 px-2">
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