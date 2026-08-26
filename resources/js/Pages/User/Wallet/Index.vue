<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    balance: {
        type: Number,
        default: 0
    },
    transactions: {
        type: Array,
        default: () => []
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    midtrans_client_key: String, 
    snapToken: String 
});

const page = usePage();
const showTopUpModal = ref(false);
const activeTopUpTab = ref('midtrans'); 

onMounted(() => {
    if (props.midtrans_client_key && !document.getElementById('midtrans-script')) {
        const isSandbox = props.midtrans_client_key.includes('SB-');
        const scriptUrl = isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/snap.js' 
            : 'https://app.midtrans.com/snap/snap.js';
            
        const script = document.createElement('script');
        script.id = 'midtrans-script';
        script.src = scriptUrl;
        script.setAttribute('data-client-key', props.midtrans_client_key);
        document.head.appendChild(script);
    }
});

const formTopUp = useForm({
    amount: ''
});

const formVoucher = useForm({
    code: ''
});

// Form khusus untuk memproses ulang pembayaran pending
const formPayPending = useForm({});

const openTopUpModal = () => {
    showTopUpModal.value = true;
};

const closeTopUpModal = () => {
    showTopUpModal.value = false;
    formTopUp.reset();
    formVoucher.reset();
};

const handleTopUpMidtrans = () => {
    formTopUp.post(route('wallet.topup'), {
        preserveScroll: true,
        onSuccess: (pageContext) => {
            closeTopUpModal();
            triggerMidtransPopup(pageContext);
        }
    });
};

const handlePayPending = (transactionId) => {
    // Sesuaikan nama route ini dengan yang ada di web.php Anda (misal: 'wallet.payPending' atau 'wallet.pay-pending')
    formPayPending.post(route('wallet.payPending', transactionId), {
        preserveScroll: true,
        onSuccess: (pageContext) => {
            triggerMidtransPopup(pageContext);
        }
    });
};

// Fungsi reusable untuk memanggil popup Midtrans
const triggerMidtransPopup = (pageContext) => {
    const token = pageContext.props.snapToken || pageContext.props.flash?.snapToken;
    
    if (token && window.snap) {
        window.snap.pay(token, {
            onSuccess: function(result) {
                window.location.reload(); 
            },
            onPending: function(result) {
                console.log('Menunggu Pembayaran');
            },
            onError: function(result) {
                alert('Pembayaran gagal atau dibatalkan.');
            },
            onClose: function() {
                console.log('Popup ditutup sebelum pembayaran diselesaikan');
            }
        });
    } else if (!pageContext.props.flash?.error) {
        alert('Gagal mendapatkan token pembayaran dari server.');
    }
};

const handleClaimVoucher = () => {
    formVoucher.post(route('wallet.claim_voucher'), {
        preserveScroll: true,
        onSuccess: () => closeTopUpModal()
    });
};

const isIncome = (type) => {
    return ['in', 'credit', 'deposit', 'topup', 'commission'].includes(type?.toLowerCase());
};

const getStatusText = (status) => {
    if (!status) return 'Selesai'; 
    const s = status.toLowerCase();
    if (['success', 'completed', 'settlement', 'capture'].includes(s)) return 'Selesai';
    if (['pending'].includes(s)) return 'Menunggu';
    if (['failed', 'cancel', 'expire', 'deny'].includes(s)) return 'Gagal';
    return s.charAt(0).toUpperCase() + s.slice(1);
};

// Warna Status ala Apple
const getStatusClass = (status) => {
    if (!status) return 'bg-[#E5F5EA] text-[#34C759] border-[#34C759]/20';
    const s = status.toLowerCase();
    if (['success', 'completed', 'settlement', 'capture'].includes(s)) return 'bg-[#E5F5EA] text-[#34C759] border-[#34C759]/20';
    if (['pending'].includes(s)) return 'bg-[#FFF9E6] text-[#FF9500] border-[#FF9500]/20';
    if (['failed', 'cancel', 'expire', 'deny'].includes(s)) return 'bg-[#FFF0F0] text-[#FF3B30] border-[#FF3B30]/20';
    return 'bg-[#F5F5F7] text-[#86868B] border-black/5';
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).replace('.', ':');
};
</script>

<template>
    <Head title="Dompet Saya" />

    <AuthenticatedLayout>
        <!-- Background khas Apple (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-8 md:space-y-10">
                
                <!-- Header -->
                <div class="space-y-1.5 md:space-y-2">
                    <h1 class="text-[28px] sm:text-[36px] font-bold text-[#1D1D1F] tracking-tight leading-tight">Dompet Saya</h1>
                    <p class="text-[14px] sm:text-[16px] text-[#86868B] font-medium leading-relaxed">
                        Kelola saldo dan pantau semua riwayat transaksi akun Anda di satu tempat.
                    </p>
                </div>

                <!-- Flash Messages iCloud Style -->
                <div v-if="flash?.success" class="flex items-center gap-3 p-4 bg-[#E5F5EA] border border-[#34C759]/20 rounded-[16px] text-[#34C759] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <span class="text-[13px] font-semibold">{{ flash.success }}</span>
                </div>
                <div v-if="flash?.error" class="flex items-center gap-3 p-4 bg-[#FFF0F0] border border-[#FF3B30]/20 rounded-[16px] text-[#FF3B30] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-[13px] font-semibold">{{ flash.error }}</span>
                </div>

                <!-- Apple Wallet Style Card -->
                <div class="bg-[#1D1D1F] rounded-[32px] p-8 md:p-10 shadow-[0_12px_40px_rgba(0,0,0,0.15)] relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-48 h-48 bg-[#007AFF]/20 rounded-full blur-[60px] -mr-10 -mt-10 pointer-events-none"></div>
                    <div class="absolute left-0 bottom-0 w-40 h-40 bg-[#34C759]/20 rounded-full blur-[50px] -ml-10 -mb-10 pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            <p class="text-[12px] sm:text-[13px] text-[#86868B] uppercase tracking-widest font-semibold mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#34C759]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Saldo Tersedia
                            </p>
                            <p class="text-[40px] sm:text-[52px] font-bold text-white tracking-tight leading-none">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                        
                        <div class="shrink-0">
                            <button @click="openTopUpModal" class="w-full md:w-auto px-8 py-3.5 bg-white text-[#1D1D1F] hover:bg-[#F5F5F7] rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] shadow-sm flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Isi Saldo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Transaksi -->
                <div class="space-y-4">
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] px-2">Riwayat Transaksi</h3>

                    <!-- Empty State -->
                    <div v-if="transactions.length === 0" class="bg-white border border-black/5 rounded-[32px] p-12 text-center shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <div class="w-20 h-20 bg-[#F5F5F7] rounded-full flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-[#86868B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5M6.75 20.25v.75m0-1.5v-.75m0-1.5v-.75m0-1.5v-.75m0-1.5V15m-1.5-1.5h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75" />
                            </svg>
                        </div>
                        <h3 class="text-[18px] font-bold text-[#1D1D1F] mb-2">Belum Ada Transaksi</h3>
                        <p class="text-[14px] text-[#86868B] font-medium max-w-sm mx-auto">
                            Riwayat penambahan atau pengurangan saldo akun Anda akan muncul di sini.
                        </p>
                    </div>

                    <!-- List Transaksi (Grouped List iOS Style) -->
                    <div v-else class="bg-white border border-black/5 rounded-[28px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden">
                        <div v-for="(trx, index) in transactions" :key="trx.id" 
                             class="p-5 sm:p-6 flex flex-col md:flex-row justify-between gap-4 md:items-center hover:bg-[#F5F5F7]/50 transition-colors"
                             :class="{ 'border-b border-black/5': index !== transactions.length - 1 }">
                            
                            <div class="flex items-start gap-4 min-w-0">
                                <!-- Ikon -->
                                <div class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center transition-colors shadow-sm border border-black/5" 
                                     :class="isIncome(trx.type) ? 'bg-[#E5F5EA] text-[#34C759]' : 'bg-[#FFF0F0] text-[#FF3B30]'">
                                    <svg v-if="isIncome(trx.type)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                    </svg>
                                </div>
                                
                                <div class="flex flex-col min-w-0 pt-0.5">
                                    <h4 class="text-[15px] sm:text-[16px] font-semibold text-[#1D1D1F] truncate tracking-tight mb-1">
                                        {{ trx.description }}
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[12px] font-medium text-[#86868B]">{{ formatDate(trx.created_at) }}</span>
                                        <span class="w-1 h-1 rounded-full bg-[#D1D1D6]"></span>
                                        <!-- Badge Status -->
                                        <span :class="getStatusClass(trx.status)" class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-md border">
                                            {{ getStatusText(trx.status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col md:items-end justify-center md:pl-4 pl-16">
                                <p class="text-[16px] sm:text-[18px] font-bold tracking-tight" :class="isIncome(trx.type) ? 'text-[#34C759]' : 'text-[#1D1D1F]'">
                                    {{ isIncome(trx.type) ? '+' : '-' }}{{ formatCurrency(trx.amount) }}
                                </p>
                                
                                <!-- Tombol Lanjutkan Pembayaran (Hanya jika pending & topup/credit) -->
                                <div v-if="trx.status === 'pending' && trx.type === 'credit'" class="mt-2.5">
                                    <button 
                                        @click="handlePayPending(trx.id)" 
                                        :disabled="formPayPending.processing"
                                        class="px-4 py-2 bg-[#F0F4FF] hover:bg-[#007AFF] text-[#007AFF] hover:text-white rounded-full text-[11px] sm:text-[12px] font-semibold transition-all active:scale-[0.98] disabled:opacity-50 text-center border border-[#007AFF]/10 hover:border-transparent"
                                    >
                                        Lanjutkan Bayar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL ISI SALDO (iCloud Sheet Style) -->
        <Modal :show="showTopUpModal" @close="closeTopUpModal" maxWidth="md">
            <div class="bg-white rounded-[24px] relative overflow-hidden font-sans">
                <!-- Header Modal -->
                <div class="p-6 sm:p-8 border-b border-black/5 flex justify-between items-start">
                    <div>
                        <h2 class="text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-1">Isi Saldo Dompet</h2>
                        <p class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                            Pilih metode pengisian saldo yang Anda inginkan.
                        </p>
                    </div>
                    <button @click="closeTopUpModal" class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-[#E3E3E8] flex items-center justify-center text-[#86868B] transition-colors shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <!-- Segmented Control (iOS Style) -->
                    <div class="flex bg-[#E3E3E8]/60 p-1 rounded-[12px] mb-6">
                        <button @click="activeTopUpTab = 'midtrans'" 
                                :class="activeTopUpTab === 'midtrans' ? 'bg-white shadow-sm text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'"
                                class="flex-1 py-2 text-[13px] font-semibold rounded-[10px] transition-all text-center">
                            Transfer / E-Wallet
                        </button>
                        <button @click="activeTopUpTab = 'voucher'" 
                                :class="activeTopUpTab === 'voucher' ? 'bg-white shadow-sm text-[#1D1D1F]' : 'text-[#86868B] hover:text-[#1D1D1F]'"
                                class="flex-1 py-2 text-[13px] font-semibold rounded-[10px] transition-all text-center">
                            Kode Voucher
                        </button>
                    </div>

                    <!-- TAB 1: MIDTRANS -->
                    <form v-if="activeTopUpTab === 'midtrans'" @submit.prevent="handleTopUpMidtrans" class="space-y-6">
                        <div>
                            <label for="amount" class="block text-[12px] font-semibold text-[#86868B] mb-2">Nominal Pengisian (Rp)</label>
                            <input
                                id="amount"
                                type="number"
                                placeholder="Min. 10.000"
                                class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] px-4 py-3.5 text-[15px] font-bold text-[#1D1D1F] transition-all outline-none"
                                v-model="formTopUp.amount"
                                required
                                min="10000"
                                autofocus
                            />
                            <InputError class="mt-2 text-[12px] text-[#FF3B30]" :message="formTopUp.errors.amount" />
                            <p class="text-[12px] text-[#86868B] font-medium mt-2.5 leading-relaxed">
                                Mendukung pembayaran via Transfer Bank (VA), QRIS, Gopay, OVO, ShopeePay, dll.
                            </p>
                        </div>

                        <button type="submit" :disabled="formTopUp.processing" class="w-full py-3.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] disabled:opacity-50 shadow-[0_4px_14px_rgba(0,122,255,0.3)]">
                            {{ formTopUp.processing ? 'Memproses...' : 'Lanjutkan Bayar' }}
                        </button>
                    </form>

                    <!-- TAB 2: VOUCHER -->
                    <form v-if="activeTopUpTab === 'voucher'" @submit.prevent="handleClaimVoucher" class="space-y-6">
                        <div>
                            <label for="code" class="block text-[12px] font-semibold text-[#86868B] mb-2">Kode Voucher</label>
                            <input
                                id="code"
                                type="text"
                                placeholder="Masukkan kode unik di sini..."
                                class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[14px] px-4 py-3.5 text-[15px] font-bold text-[#1D1D1F] transition-all outline-none uppercase"
                                v-model="formVoucher.code"
                                required
                                autofocus
                            />
                            <InputError class="mt-2 text-[12px] text-[#FF3B30]" :message="formVoucher.errors.code" />
                            <p class="text-[12px] text-[#86868B] font-medium mt-2.5 leading-relaxed">
                                Kode voucher biasanya didapatkan dari event tertentu, giveaway, atau pembelian melalui agen resmi kami.
                            </p>
                        </div>

                        <button type="submit" :disabled="formVoucher.processing" class="w-full py-3.5 bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] disabled:opacity-50 shadow-md">
                            {{ formVoucher.processing ? 'Memeriksa...' : 'Klaim Saldo' }}
                        </button>
                    </form>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
/* Menghilangkan panah atas/bawah di input number browser */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>