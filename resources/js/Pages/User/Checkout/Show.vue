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
            title: 'Pilih Metode Dulu',
            text: 'Silakan pilih metode pembayaran Dompet atau Transfer.',
            icon: 'warning',
            confirmButtonColor: '#1e293b'
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
        Swal.fire({ title: 'Sistem Belum Siap', text: 'Gagal memuat Midtrans. Refresh halaman.', icon: 'error' });
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
            Swal.fire('Menunggu Pembayaran', 'Selesaikan sesuai instruksi.', 'info');
        },
        onError: function(result) {
            isProcessing.value = false;
            Swal.fire('Gagal', 'Pembayaran gagal.', 'error');
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
        <div class="relative bg-slate-900 overflow-hidden shadow-md -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16 text-center">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
            <div class="relative max-w-5xl mx-auto px-6 z-10 text-white font-black uppercase">
                <h1 class="text-3xl md:text-5xl tracking-tighter">Selesaikan <span class="italic text-indigo-300">Investasi Anda.</span></h1>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 -mt-16 relative z-10 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                        <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest mb-8">Metode Pembayaran</h3>

                        <div class="space-y-3">
                            <div v-if="isMembership"
                                @click="isBalanceEnough && !isProcessing ? selectedMethod = 'wallet' : null"
                                :class="['p-5 rounded-2xl border-2 transition-all flex items-center justify-between cursor-pointer', selectedMethod === 'wallet' ? 'border-indigo-600 bg-indigo-50/30' : 'border-slate-50', !isBalanceEnough ? 'opacity-50 grayscale cursor-not-allowed' : '']"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="text-2xl">💳</div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-900 uppercase">Dompet Nusantara</p>
                                        <p class="text-[10px] font-bold" :class="isBalanceEnough ? 'text-slate-500' : 'text-rose-500'">Saldo: {{ formatRupiah(user_balance) }}</p>
                                    </div>
                                </div>
                                <div v-if="selectedMethod === 'wallet'">✓</div>
                            </div>

                            <div @click="!isProcessing ? selectedMethod = 'midtrans' : null"
                                class="p-5 rounded-2xl border-2 transition-all flex items-center justify-between cursor-pointer"
                                :class="selectedMethod === 'midtrans' ? 'border-indigo-600 bg-indigo-50/30' : 'border-slate-50'"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="text-2xl">📸</div>
                                    <div>
                                        <p class="text-[11px] font-black text-slate-900 uppercase">Transfer & QRIS</p>
                                        <p class="text-[10px] font-bold text-slate-500 italic">Otomatis • Instan • Aman</p>
                                    </div>
                                </div>
                                <div v-if="selectedMethod === 'midtrans'">✓</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 sticky top-8">
                    <div class="bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-10 text-center">Rincian Pembayaran</h3>
                        
                        <div class="space-y-6 mb-12">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Produk Layanan</span>
                                <span class="text-[11px] font-black text-slate-900 uppercase italic">{{ props.transaction?.description }}</span>
                            </div>

                            <div class="pt-8 border-t-2 border-dashed border-slate-100 flex justify-between items-end">
                                <span class="text-[10px] font-black text-slate-900 uppercase">Total Bayar</span>
                                <span class="text-3xl font-black text-slate-900 tracking-tighter leading-none">
                                    {{ selectedMethod === 'midtrans' ? formatRupiah(props.transaction?.total_amount) : formatRupiah(props.transaction?.amount) }}
                                </span>
                            </div>
                            <p v-if="selectedMethod === 'midtrans'" class="text-[9px] text-center text-slate-400 font-bold italic">
                                *Sudah termasuk biaya layanan & PPN
                            </p>
                        </div>

                        <button @click="processPayment" :disabled="isProcessing"
                            class="w-full py-6 rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all bg-slate-900 text-white hover:bg-indigo-600 disabled:bg-slate-300 shadow-xl"
                        >
                            {{ isProcessing ? 'Menghubungkan...' : 'Konfirmasi Pembayaran' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>