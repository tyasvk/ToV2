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

// --- 1. DETEKSI TIPE TRANSAKSI (Membership vs Tryout) ---
const isMembership = computed(() => {
    // Jika tryout_id kosong, berarti ini Membership
    return !props.transaction?.tryout_id;
});

// --- 2. AUTO-SELECT METODE ---
const setAutoMethod = () => {
    if (props.transaction) {
        // Jika bukan membership (beli Tryout eceran), otomatis pilih Midtrans
        if (!isMembership.value) {
            selectedMethod.value = 'midtrans';
        }
    }
};

onMounted(() => {
    setAutoMethod();
    // Cek apakah Snap JS sudah dimuat
    if (typeof window.snap === 'undefined') {
        console.warn('Midtrans Snap JS belum dimuat. Pastikan Anda memasang script Snap di app.blade.php');
    }
});

watch(() => props.transaction, setAutoMethod, { immediate: true });

// --- 3. HELPER FORMATTER ---
const formatRupiah = (num) => {
    const value = Number(num) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(value);
};

const isBalanceEnough = computed(() => {
    return Number(props.user_balance) >= (Number(props.transaction?.amount) || 0);
});

// --- 4. LOGIKA PEMBAYARAN (FIXED) ---
const processPayment = () => {
    console.log("Tombol Konfirmasi Ditekan. Metode:", selectedMethod.value);

    // Cek apakah metode sudah dipilih
    if (!selectedMethod.value) {
        Swal.fire({
            title: 'Pilih Metode Dulu',
            text: 'Silakan klik salah satu kotak metode pembayaran di atas (Dompet atau Transfer).',
            icon: 'warning',
            confirmButtonText: 'Oke, Paham',
            confirmButtonColor: '#1e293b',
            customClass: { popup: 'rounded-2xl' }
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
        title: 'Bayar pakai Dompet?',
        text: `Saldo akan terpotong ${formatRupiah(props.transaction?.amount)}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Bayar Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#1e293b',
        reverseButtons: true,
        customClass: { popup: 'rounded-2xl' }
    }).then((result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            router.post(route('checkout.process', props.transaction.id), {
                payment_method: 'wallet'
            }, {
                onFinish: () => isProcessing.value = false,
                onSuccess: () => {
                    // Redirect otomatis ditangani oleh Controller
                },
                onError: (errors) => {
                    Swal.fire({ title: 'Gagal', text: errors.message || 'Terjadi kesalahan.', icon: 'error' });
                }
            });
        }
    });
};

const handleMidtransPayment = () => {
    // Cek Ketersediaan Snap Midtrans
    if (typeof window.snap === 'undefined') {
        Swal.fire({
            title: 'Sistem Belum Siap',
            text: 'Gagal memuat sistem pembayaran Midtrans. Coba refresh halaman.',
            icon: 'error',
            confirmButtonColor: '#1e293b'
        });
        return;
    }

    isProcessing.value = true;

    window.snap.pay(props.transaction.snap_token, {
        onSuccess: function(result) {
            isProcessing.value = false;
            // Arahkan ke dashboard atau halaman riwayat
            router.visit(route('dashboard'));
            Swal.fire('Berhasil!', 'Pembayaran Anda sedang diproses.', 'success');
        },
        onPending: function(result) {
            isProcessing.value = false;
            Swal.fire('Menunggu Pembayaran', 'Silakan selesaikan pembayaran sesuai instruksi.', 'info');
        },
        onError: function(result) {
            isProcessing.value = false;
            Swal.fire('Gagal', 'Pembayaran gagal atau dibatalkan.', 'error');
        },
        onClose: function() {
            isProcessing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Checkout - Nusantara Adidaya" />

    <AuthenticatedLayout>
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16 text-center">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
                <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/20 rounded-full blur-[100px]"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative max-w-5xl mx-auto px-6 z-10">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-indigo-500/20 border border-indigo-400/20 text-indigo-200 text-[10px] font-black tracking-[0.2em] uppercase mb-6 backdrop-blur-sm shadow-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span> Gerbang Pembayaran Aman
                </span>
                
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tighter mb-4 leading-tight uppercase">
                    SELESAIKAN <span class="font-['Instrument_Serif'] italic text-indigo-200 font-normal">INVESTASI ANDA.</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-sm text-slate-400 font-medium leading-relaxed opacity-80">
                    Satu langkah terakhir menuju persiapan ASN terbaik bersama Nusantara Adidaya.
                </p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-0 -mt-16 relative z-10 pb-20 font-sans">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-sm border border-slate-100 relative z-20">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-[0.3em] mb-8 flex items-center gap-3">
                            <span class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center italic text-[10px] shadow-sm font-black">01</span>
                            Metode Pembayaran
                        </h3>

                        <div class="space-y-3">
                            <div v-if="isMembership"
                                @click="isBalanceEnough && !isProcessing ? selectedMethod = 'wallet' : null"
                                :class="[
                                    'p-4 md:p-5 rounded-2xl border-2 transition-all duration-300 flex items-center justify-between group select-none',
                                    selectedMethod === 'wallet' ? 'border-indigo-600 bg-indigo-50/30 shadow-md shadow-indigo-100/20' : 'border-slate-50 bg-white hover:border-indigo-200',
                                    !isBalanceEnough ? 'opacity-50 grayscale cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-2xl transition-all shadow-sm', selectedMethod === 'wallet' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-600 border border-indigo-50']">
                                        💳
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Dompet Nusantara</p>
                                        <p v-if="isBalanceEnough" class="text-[10px] font-bold text-slate-500 mt-0.5">Saldo: {{ formatRupiah(user_balance) }}</p>
                                        <p v-else class="text-[10px] font-bold text-rose-500 mt-0.5">Saldo Tidak Cukup ({{ formatRupiah(user_balance) }})</p>
                                    </div>
                                </div>
                                
                                <div v-if="selectedMethod === 'wallet'" class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shadow-md animate-in zoom-in">
                                    <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7" /></svg>
                                </div>
                            </div>

                            <div @click="!isProcessing ? selectedMethod = 'midtrans' : null"
                                class="p-4 md:p-5 rounded-2xl border-2 transition-all duration-300 flex items-center justify-between cursor-pointer group shadow-sm select-none"
                                :class="selectedMethod === 'midtrans' ? 'border-indigo-600 bg-indigo-50/30 shadow-md shadow-indigo-100/20' : 'border-slate-50 bg-white hover:border-indigo-200'"
                            >
                                <div class="flex items-center gap-4">
                                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center text-2xl transition-all shadow-sm', selectedMethod === 'midtrans' ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-600 border border-indigo-50']">
                                        📱
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Transfer & QRIS</p>
                                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Otomatis • Terpercaya • Instan</p>
                                    </div>
                                </div>
                                <div v-if="selectedMethod === 'midtrans'" class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shadow-md animate-in zoom-in">
                                    <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 sticky top-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-100 overflow-hidden relative z-20">
                         <div class="absolute inset-0 opacity-[0.02] bg-[radial-gradient(#4f46e5_1.5px,transparent_1.5px)] [background-size:24px_24px] pointer-events-none"></div>

                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-10 text-center relative z-10">Rincian Pembayaran</h3>
                        
                        <div class="space-y-6 mb-12 relative z-10">
                            <div class="flex flex-col gap-1">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Produk Layanan</span>
                                <span class="text-[11px] font-black text-slate-900 uppercase italic leading-tight">
                                    {{ props.transaction?.description || 'Memuat...' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">No. Invoice</span>
                                <span class="text-[9px] font-black text-indigo-600 tracking-wider">
                                    #{{ props.transaction?.invoice_code }}
                                </span>
                            </div>
                            <div class="pt-8 border-t-2 border-dashed border-slate-100 flex justify-between items-end">
                                <span class="text-[10px] font-black text-slate-900 uppercase">Total Bayar</span>
                                <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">
                                    {{ formatRupiah(props.transaction?.amount) }}
                                </span>
                            </div>
                        </div>

                        <button 
                            @click="processPayment"
                            :disabled="isProcessing"
                            type="button"
                            class="w-full py-6 rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] transition-all duration-300 flex items-center justify-center gap-3 relative z-50 shadow-xl cursor-pointer"
                            :class="[
                                isProcessing 
                                    ? 'bg-slate-800 text-slate-400 cursor-wait' 
                                    : 'bg-slate-900 text-white hover:bg-indigo-600 active:scale-95'
                            ]"
                        >
                            <template v-if="isProcessing">Menghubungkan...</template>
                            <template v-else>Konfirmasi Pembayaran</template>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>