<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    transaction: Object,
    user_balance: [Number, String],
});

const selectedMethod = ref(null);
const isProcessing = ref(false);

const isMembership = computed(() => {
    return !props.transaction?.tryout_id;
});

const setAutoMethod = () => {
    if (props.transaction && !isMembership.value) {
        selectedMethod.value = 'midtrans';
    }
};

// --- PERBAIKAN: Paksa load script Production Midtrans ---
const loadMidtransScript = () => {
    if (document.getElementById('midtrans-script')) return; 

    const script = document.createElement('script');
    // MENGGUNAKAN URL PRODUCTION (Tanpa kata sandbox)
    script.src = 'https://app.midtrans.com/snap/snap.js';
    // MENGGUNAKAN CLIENT KEY PRODUCTION ANDA
    script.setAttribute('data-client-key', 'Mid-client-sYattwxjbV1gCt1Q');
    script.id = 'midtrans-script';
    document.head.appendChild(script);
};

onMounted(() => {
    setAutoMethod();
    loadMidtransScript(); 
});

watch(() => props.transaction, setAutoMethod, { immediate: true });

const formatRupiah = (num) => {
    const value = Number(num) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};

const isBalanceEnough = computed(() => {
    return Number(props.user_balance) >= (Number(props.transaction?.amount) || 0);
});

// Gaya kustom SweetAlert2 agar menyerupai iOS Alert
const swalCustomClass = {
    popup: 'rounded-[24px] border border-black/5 shadow-[0_10px_40px_rgba(0,0,0,0.1)] p-6 font-sans',
    title: 'font-bold text-[#1D1D1F] tracking-tight text-[20px]',
    htmlContainer: 'text-[#86868B] font-medium text-[14px] mt-1',
    confirmButton: 'rounded-full font-semibold text-[14px] py-3 px-8 transition-all bg-[#007AFF] text-white hover:bg-[#0062CC]',
    cancelButton: 'rounded-full font-semibold text-[14px] py-3 px-8 transition-all bg-[#F5F5F7] text-[#1D1D1F] hover:bg-[#E3E3E8]'
};

const processPayment = () => {
    if (!selectedMethod.value) {
        Swal.fire({
            title: 'Metode Belum Dipilih',
            text: 'Silakan pilih metode pembayaran Dompet atau Transfer untuk melanjutkan.',
            icon: 'warning',
            confirmButtonText: 'Mengerti',
            buttonsStyling: false,
            customClass: swalCustomClass
        });
        return;
    }

    if (selectedMethod.value === 'wallet') {
        handleWalletPayment();
    } else {
        handleMidtransPayment();
    }
};

const handleWalletPayment = () => {
    Swal.fire({
        title: 'Konfirmasi Pembayaran',
        text: `Saldo Dompet Anda akan terpotong sebesar ${formatRupiah(props.transaction?.amount)}.`,
        showCancelButton: true,
        confirmButtonText: 'Bayar Sekarang',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        buttonsStyling: false,
        customClass: swalCustomClass
    }).then((result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            router.post(route('checkout.process', props.transaction.id), {
                payment_method: 'wallet'
            }, {
                onFinish: () => isProcessing.value = false,
                onError: (errors) => {
                    Swal.fire({ 
                        title: 'Pembayaran Gagal', 
                        text: errors.message || 'Terjadi kesalahan pada sistem.', 
                        icon: 'error',
                        confirmButtonText: 'Tutup',
                        buttonsStyling: false,
                        customClass: swalCustomClass
                    });
                }
            });
        }
    });
};

const handleMidtransPayment = () => {
    if (!props.transaction?.snap_token) {
        Swal.fire({ 
            title: 'Sistem Error', 
            text: 'Gagal mendapatkan token Midtrans. Pastikan Konfigurasi Server Key Anda benar.', 
            icon: 'error',
            confirmButtonText: 'Tutup',
            buttonsStyling: false,
            customClass: swalCustomClass
        });
        return;
    }

    if (typeof window.snap === 'undefined') {
        Swal.fire({ 
            title: 'Menghubungkan...', 
            text: 'Sistem sedang menyiapkan jalur pembayaran aman, silakan klik tombol bayar sekali lagi.', 
            icon: 'info',
            confirmButtonText: 'Mengerti',
            buttonsStyling: false,
            customClass: swalCustomClass
        });
        return;
    }

    isProcessing.value = true;
    window.snap.pay(props.transaction.snap_token, {
        onSuccess: function(result) {
            isProcessing.value = false;
            router.visit(route('dashboard'));
        },
        onPending: function(result) {
            isProcessing.value = false;
            Swal.fire({
                title: 'Menunggu Pembayaran',
                text: 'Silakan selesaikan transaksi Anda di halaman pembayaran.',
                icon: 'info',
                confirmButtonText: 'Mengerti',
                buttonsStyling: false,
                customClass: swalCustomClass
            });
        },
        onError: function(result) {
            isProcessing.value = false;
            Swal.fire({
                title: 'Transaksi Gagal',
                text: 'Pembayaran Anda gagal diproses. Silakan coba metode lain.',
                icon: 'error',
                confirmButtonText: 'Tutup',
                buttonsStyling: false,
                customClass: swalCustomClass
            });
        },
        onClose: function() {
            isProcessing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Checkout Pembayaran - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background khas Apple (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 md:space-y-8">
                
                <!-- HEADER -->
                <div class="flex flex-col gap-1.5 border-b border-black/5 pb-5">
                    <h1 class="text-[26px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                        Selesaikan Transaksi
                    </h1>
                    <p class="text-[14px] sm:text-[15px] text-[#86868B] font-medium">
                        Silakan pilih metode pembayaran untuk melanjutkan.
                    </p>
                </div>

                <!-- GRID KONTEN -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
                    
                    <!-- KIRI: Pilihan Metode Pembayaran -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-[24px] sm:rounded-[32px] shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 p-6 sm:p-8">
                            <h3 class="text-[16px] sm:text-[18px] font-bold text-[#1D1D1F] mb-5">
                                Pilih Metode
                            </h3>

                            <div class="space-y-3.5">
                                <!-- OPSI: SALDO DOMPET -->
                                <div v-if="isMembership"
                                    @click="isBalanceEnough && !isProcessing ? selectedMethod = 'wallet' : null"
                                    class="p-4 sm:p-5 rounded-[16px] border-2 transition-all flex items-center justify-between cursor-pointer group"
                                    :class="[
                                        selectedMethod === 'wallet' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-black/5 hover:border-black/10 bg-white', 
                                        !isBalanceEnough ? 'opacity-50 grayscale cursor-not-allowed' : ''
                                    ]"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                             :class="selectedMethod === 'wallet' ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.3)]' : 'bg-[#F5F5F7] text-[#86868B] group-hover:bg-[#E3E3E8]'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H4.5A2.25 2.25 0 002.25 12v6.75A2.25 2.25 0 004.5 21h15a2.25 2.25 0 002.25-2.25V12z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[15px] sm:text-[16px] font-bold text-[#1D1D1F] leading-tight mb-0.5">
                                                Dompet Nusantara
                                            </span>
                                            <span class="text-[12px] font-medium" :class="isBalanceEnough ? 'text-[#86868B]' : 'text-[#FF3B30]'">
                                                Saldo: {{ formatRupiah(user_balance) }}
                                                <span v-if="!isBalanceEnough" class="ml-1">(Tidak cukup)</span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Indikator Centang -->
                                    <div class="shrink-0 ml-3">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                             :class="selectedMethod === 'wallet' ? 'bg-[#007AFF] border-[#007AFF]' : 'border-[#D1D1D6]'">
                                            <svg v-if="selectedMethod === 'wallet'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- OPSI: TRANSFER / QRIS -->
                                <div @click="!isProcessing ? selectedMethod = 'midtrans' : null"
                                    class="p-4 sm:p-5 rounded-[16px] border-2 transition-all flex items-center justify-between cursor-pointer group"
                                    :class="selectedMethod === 'midtrans' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-black/5 hover:border-black/10 bg-white'"
                                >
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 transition-colors"
                                             :class="selectedMethod === 'midtrans' ? 'bg-[#007AFF] text-white shadow-[0_2px_8px_rgba(0,122,255,0.3)]' : 'bg-[#F5F5F7] text-[#86868B] group-hover:bg-[#E3E3E8]'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[15px] sm:text-[16px] font-bold text-[#1D1D1F] leading-tight mb-0.5">
                                                Transfer Bank & QRIS
                                            </span>
                                            <span class="text-[12px] font-medium text-[#86868B]">
                                                Verifikasi instan, diproses otomatis
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Indikator Centang -->
                                    <div class="shrink-0 ml-3">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
                                             :class="selectedMethod === 'midtrans' ? 'bg-[#007AFF] border-[#007AFF]' : 'border-[#D1D1D6]'">
                                            <svg v-if="selectedMethod === 'midtrans'" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KANAN: Ringkasan Pesanan (Sticky) -->
                    <div class="lg:col-span-5 relative">
                        <div class="bg-white rounded-[24px] sm:rounded-[32px] shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 p-6 sm:p-8 sticky top-8">
                            <h3 class="text-[16px] sm:text-[18px] font-bold text-[#1D1D1F] mb-5">
                                Ringkasan Transaksi
                            </h3>
                            
                            <!-- Box Detail -->
                            <div class="bg-[#F5F5F7] rounded-[16px] p-5 mb-6 space-y-4">
                                <div class="flex flex-col">
                                    <span class="text-[11px] text-[#86868B] font-semibold uppercase tracking-wider mb-1">
                                        Layanan / Produk
                                    </span>
                                    <span class="text-[14px] sm:text-[15px] font-bold text-[#1D1D1F] leading-snug">
                                        {{ props.transaction?.description || 'Pembelian Layanan' }}
                                    </span>
                                </div>

                                <div class="w-full h-px bg-black/5"></div> <!-- Garis Pemisah -->

                                <div class="flex justify-between items-center">
                                    <span class="text-[11px] text-[#86868B] font-semibold uppercase tracking-wider">
                                        Total Pembayaran
                                    </span>
                                    <span class="text-[20px] sm:text-[24px] font-black text-[#1D1D1F] tracking-tight leading-none">
                                        {{ selectedMethod === 'midtrans' ? formatRupiah(props.transaction?.total_amount) : formatRupiah(props.transaction?.amount) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Tombol Bayar -->
                            <button 
                                @click="processPayment" 
                                :disabled="isProcessing"
                                class="w-full py-4 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[15px] font-semibold transition-all active:scale-[0.98] flex items-center justify-center gap-2 shadow-[0_4px_14px_rgba(0,122,255,0.3)] disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                            >
                                <svg v-if="!isProcessing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                <span>{{ isProcessing ? 'Memproses Transaksi...' : 'Bayar Sekarang' }}</span>
                            </button>
                            
                            <p class="text-center text-[12px] text-[#86868B] font-medium mt-4">
                                Transaksi aman dan terenkripsi.
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