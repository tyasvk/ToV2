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

onMounted(() => {
    setAutoMethod();
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

const processPayment = () => {
    if (!selectedMethod.value) {
        Swal.fire({
            title: 'Metode belum dipilih',
            text: 'Silakan pilih metode pembayaran Dompet atau Transfer.',
            icon: 'warning',
            confirmButtonColor: '#334155'
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
        text: `Saldo akan terpotong sebesar ${formatRupiah(props.transaction?.amount)}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Bayar Sekarang',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#334155',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            router.post(route('checkout.process', props.transaction.id), {
                payment_method: 'wallet'
            }, {
                onFinish: () => isProcessing.value = false,
                onError: (errors) => {
                    Swal.fire({ title: 'Gagal', text: errors.message || 'Terjadi kesalahan.', icon: 'error' });
                }
            });
        }
    });
};

const handleMidtransPayment = () => {
    if (typeof window.snap === 'undefined') {
        Swal.fire({ title: 'Sistem', text: 'Gagal memuat metode pembayaran.', icon: 'error' });
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
            Swal.fire('Menunggu Pembayaran', 'Selesaikan transaksi Anda.', 'info');
        },
        onError: function(result) {
            isProcessing.value = false;
            Swal.fire('Gagal', 'Pembayaran gagal diproses.', 'error');
        },
        onClose: function() {
            isProcessing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Checkout Pembayaran" />

    <AuthenticatedLayout>
        <div class="bg-white border-b border-slate-100 py-8 px-6 text-center">
            <h1 class="text-xl md:text-2xl text-slate-900 tracking-tight font-medium">Selesaikan Transaksi</h1>
            <p class="text-xs text-slate-500 mt-2 uppercase tracking-widest">Silakan pilih metode untuk melanjutkan</p>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-6">Pilih Metode</h3>

                        <div class="space-y-3">
                            <div v-if="isMembership"
                                @click="isBalanceEnough && !isProcessing ? selectedMethod = 'wallet' : null"
                                :class="['p-4 rounded-xl border transition-all flex items-center justify-between cursor-pointer', selectedMethod === 'wallet' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200', !isBalanceEnough ? 'opacity-50 grayscale cursor-not-allowed' : '']"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="text-lg">💳</div>
                                    <div>
                                        <p class="text-sm text-slate-900 font-medium">Dompet Nusantara</p>
                                        <p class="text-[10px]" :class="isBalanceEnough ? 'text-slate-500' : 'text-red-500'">Saldo: {{ formatRupiah(user_balance) }}</p>
                                    </div>
                                </div>
                                <div v-if="selectedMethod === 'wallet'" class="text-blue-600">✓</div>
                            </div>

                            <div @click="!isProcessing ? selectedMethod = 'midtrans' : null"
                                class="p-4 rounded-xl border transition-all flex items-center justify-between cursor-pointer"
                                :class="selectedMethod === 'midtrans' ? 'border-blue-600 bg-blue-50/50' : 'border-slate-200'"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="text-lg">📸</div>
                                    <div>
                                        <p class="text-sm text-slate-900 font-medium">Transfer & QRIS</p>
                                        <p class="text-[10px] text-slate-500 italic">Otomatis • Instan</p>
                                    </div>
                                </div>
                                <div v-if="selectedMethod === 'midtrans'" class="text-blue-600">✓</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 sticky top-6 h-fit">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-6">Ringkasan</h3>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-slate-400 uppercase">Layanan</span>
                                <span class="text-sm text-slate-900 font-medium mt-1">{{ props.transaction?.description }}</span>
                            </div>

                            <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                                <span class="text-xs text-slate-500 uppercase">Total Bayar</span>
                                <span class="text-xl text-slate-900 font-medium tracking-tight">
                                    {{ selectedMethod === 'midtrans' ? formatRupiah(props.transaction?.total_amount) : formatRupiah(props.transaction?.amount) }}
                                </span>
                            </div>
                        </div>

                        <button @click="processPayment" :disabled="isProcessing"
                            class="w-full py-3.5 rounded-xl text-xs font-medium uppercase tracking-widest transition-all bg-slate-900 text-white hover:bg-blue-600 disabled:bg-slate-300"
                        >
                            {{ isProcessing ? 'Memproses...' : 'Bayar Sekarang' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>