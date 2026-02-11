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

const isMembership = computed(() => !props.transaction?.tryout_id);

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
        Swal.fire({ title: 'Pilih Metode', text: 'Silakan pilih metode pembayaran.', icon: 'warning' });
        return;
    }
    selectedMethod.value === 'wallet' ? handleWalletPayment() : handleMidtransPayment();
};

const handleWalletPayment = () => {
    Swal.fire({
        title: 'Konfirmasi Dompet',
        text: `Potong saldo ${formatRupiah(props.transaction?.amount)}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Bayar',
        confirmButtonColor: '#1e293b'
    }).then((result) => {
        if (result.isConfirmed) {
            isProcessing.value = true;
            router.post(route('checkout.process', props.transaction.id), {
                payment_method: 'wallet'
            }, {
                onFinish: () => isProcessing.value = false,
                onError: (err) => Swal.fire('Gagal', err.message || 'Terjadi kesalahan', 'error')
            });
        }
    });
};

const handleMidtransPayment = () => {
    // PROTEKSI: Cek apakah token tersedia dari props
    if (!props.transaction.snap_token) {
        Swal.fire({
            title: 'Token Error',
            text: 'Gagal mendapatkan token pembayaran. Refresh halaman atau cek koneksi internet.',
            icon: 'error'
        });
        return;
    }

    if (typeof window.snap === 'undefined') {
        Swal.fire('Sistem Belum Siap', 'Script Midtrans gagal dimuat.', 'error');
        return;
    }

    isProcessing.value = true;
    window.snap.pay(props.transaction.snap_token, {
        onSuccess: (res) => {
            isProcessing.value = false;
            router.visit(route('dashboard'));
            Swal.fire('Berhasil!', 'Pembayaran diterima.', 'success');
        },
        onPending: (res) => {
            isProcessing.value = false;
            Swal.fire('Menunggu', 'Silakan selesaikan scan QRIS Anda.', 'info');
        },
        onError: () => {
            isProcessing.value = false;
            Swal.fire('Gagal', 'Pembayaran dibatalkan atau error.', 'error');
        },
        onClose: () => isProcessing.value = false
    });
};
</script>

<template>
    <Head title="Checkout" />
    <AuthenticatedLayout>
        <div class="bg-slate-900 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 py-16 text-center text-white">
            <h1 class="text-3xl font-black uppercase tracking-tighter">Selesaikan <span class="italic text-indigo-300">Investasi Anda.</span></h1>
        </div>

        <div class="max-w-6xl mx-auto -mt-16 relative z-10 pb-20 px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-7 bg-white p-8 rounded-[2rem] shadow-sm border">
                    <h3 class="text-[10px] font-black uppercase tracking-widest mb-8">Pilih Metode Pembayaran</h3>
                    <div class="space-y-3">
                        <div v-if="isMembership" @click="isBalanceEnough && !isProcessing ? selectedMethod = 'wallet' : null"
                            class="p-5 border-2 rounded-2xl cursor-pointer flex justify-between items-center"
                            :class="[selectedMethod === 'wallet' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-50', !isBalanceEnough ? 'opacity-50 grayscale' : '']">
                            <div>
                                <p class="text-[11px] font-black uppercase">Dompet Nusantara</p>
                                <p class="text-[10px] font-bold" :class="isBalanceEnough ? 'text-slate-500' : 'text-red-500'">Saldo: {{ formatRupiah(user_balance) }}</p>
                            </div>
                            <span v-if="selectedMethod === 'wallet'">✓</span>
                        </div>

                        <div @click="!isProcessing ? selectedMethod = 'midtrans' : null"
                            class="p-5 border-2 rounded-2xl cursor-pointer flex justify-between items-center"
                            :class="selectedMethod === 'midtrans' ? 'border-indigo-600 bg-indigo-50' : 'border-slate-50'">
                            <div>
                                <p class="text-[11px] font-black uppercase">Bayar via QRIS</p>
                                <p class="text-[10px] font-bold text-slate-500">Scan QR • Otomatis • Aman</p>
                            </div>
                            <span v-if="selectedMethod === 'midtrans'">✓</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-white p-10 rounded-[2.5rem] shadow-xl border">
                    <h3 class="text-[10px] font-black text-center text-slate-400 uppercase tracking-widest mb-10">Ringkasan Pembayaran</h3>
                    <div class="space-y-6 mb-12">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase text-slate-400">Produk</span>
                            <span class="text-[11px] font-black uppercase italic">{{ props.transaction?.description }}</span>
                        </div>
                        <div class="pt-8 border-t-2 border-dashed border-slate-100 flex justify-between items-end">
                            <span class="text-[10px] font-black uppercase">Total Bayar</span>
                            <span class="text-3xl font-black">
                                {{ selectedMethod === 'midtrans' ? formatRupiah(props.transaction?.total_amount) : formatRupiah(props.transaction?.amount) }}
                            </span>
                        </div>
                        <p v-if="selectedMethod === 'midtrans'" class="text-[9px] text-center text-slate-400 italic font-bold">*Sudah termasuk biaya QRIS & PPN</p>
                    </div>
                    <button @click="processPayment" :disabled="isProcessing"
                        class="w-full py-6 rounded-2xl bg-slate-900 text-white font-black text-[11px] uppercase tracking-widest hover:bg-indigo-600 transition-all">
                        {{ isProcessing ? 'Menghubungkan...' : 'Bayar Sekarang' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>