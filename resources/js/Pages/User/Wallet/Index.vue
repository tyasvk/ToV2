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
    if (!status) return 'SELESAI'; 
    const s = status.toLowerCase();
    if (['success', 'completed', 'settlement', 'capture'].includes(s)) return 'SELESAI';
    if (['pending'].includes(s)) return 'PENDING';
    if (['failed', 'cancel', 'expire', 'deny'].includes(s)) return 'GAGAL';
    return s.toUpperCase();
};

const getStatusClass = (status) => {
    if (!status) return 'bg-emerald-50 text-emerald-600 border-emerald-200';
    const s = status.toLowerCase();
    if (['success', 'completed', 'settlement', 'capture'].includes(s)) return 'bg-emerald-50 text-emerald-600 border-emerald-200';
    if (['pending'].includes(s)) return 'bg-amber-50 text-amber-600 border-amber-200';
    if (['failed', 'cancel', 'expire', 'deny'].includes(s)) return 'bg-rose-50 text-rose-600 border-rose-200';
    return 'bg-slate-50 text-slate-600 border-slate-200';
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
        <div class="max-w-4xl mx-auto space-y-6 md:space-y-8 animate-in fade-in duration-700 px-4 sm:px-0">
            
            <div class="flex flex-col gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Dompet Saya</h1>
                    <p class="text-[11px] text-slate-500 font-normal tracking-wide mt-1">
                        Kelola saldo dan pantau semua riwayat transaksi akun Anda.
                    </p>
                </div>

                <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-xs font-medium">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 text-xs font-medium">
                    {{ flash.error }}
                </div>

                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[1.5rem] p-6 md:p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden mt-2">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl -ml-5 -mb-5"></div>
                    
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                        <div>
                            <p class="text-[10px] text-slate-300 uppercase tracking-[0.2em] font-medium mb-1.5 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Saldo Aktif
                            </p>
                            <p class="text-3xl md:text-4xl font-medium text-white tracking-tighter">
                                {{ formatCurrency(balance) }}
                            </p>
                        </div>
                        
                        <div class="flex items-center shrink-0">
                            <button @click="openTopUpModal" class="w-full sm:w-auto px-6 py-3 bg-white text-slate-900 hover:bg-slate-50 rounded-xl text-[10px] uppercase font-medium tracking-[0.15em] transition-all active:scale-95 text-center shadow-lg shadow-slate-900/10">
                                Isi Saldo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pb-8">
                <h3 class="text-sm font-medium text-slate-900 uppercase tracking-[0.1em] mb-2 px-1">Riwayat Transaksi</h3>

                <div v-if="transactions.length === 0" class="bg-white border border-slate-100 rounded-[2rem] p-10 text-center shadow-sm">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5M6.75 20.25v.75m0-1.5v-.75m0-1.5v-.75m0-1.5v-.75m0-1.5V15m-1.5-1.5h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-slate-900 uppercase tracking-widest mb-2">Belum Ada Transaksi</h3>
                    <p class="text-xs text-slate-500 font-normal tracking-wide max-w-sm mx-auto">
                        Riwayat penambahan atau pengurangan saldo akun akan muncul di sini.
                    </p>
                </div>

                <div v-else class="flex flex-col gap-3">
                    <div v-for="trx in transactions" :key="trx.id" class="bg-white border border-slate-100 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row justify-between gap-4 transition-all hover:shadow-md hover:border-slate-200 group relative overflow-hidden">
                        
                        <div class="absolute left-0 top-0 bottom-0 w-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300" 
                             :class="isIncome(trx.type) ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                        
                        <div class="flex items-start gap-3 sm:gap-4 min-w-0">
                            <div class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center transition-colors duration-300" 
                                 :class="isIncome(trx.type) ? 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-100' : 'bg-rose-50 text-rose-500 group-hover:bg-rose-100'">
                                <svg v-if="isIncome(trx.type)" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </div>
                            
                            <div class="flex flex-col min-w-0 pt-0.5">
                                <!-- Tanggal dan Badge Status -->
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-[9px] font-medium text-slate-400 uppercase tracking-widest">{{ formatDate(trx.created_at) }}</span>
                                    <span :class="getStatusClass(trx.status)" class="text-[7px] font-medium uppercase tracking-widest px-1.5 py-0.5 rounded border">
                                        {{ getStatusText(trx.status) }}
                                    </span>
                                </div>
                                <h4 class="text-sm md:text-[15px] font-medium text-slate-900 truncate pr-4 tracking-tight leading-tight">
                                    {{ trx.description }}
                                </h4>
                            </div>
                        </div>

                        <div class="flex flex-col md:items-end justify-center border-t md:border-t-0 border-slate-50 pt-3 md:pt-0 pl-14 md:pl-0 gap-2">
                            <div class="text-left md:text-right flex items-center md:block justify-between w-full md:w-auto">
                                <p class="text-[9px] font-medium text-slate-400 uppercase tracking-[0.2em] mb-0.5">Nominal</p>
                                <p class="text-base md:text-lg font-medium tracking-tight" :class="isIncome(trx.type) ? 'text-emerald-500' : 'text-rose-500'">
                                    {{ isIncome(trx.type) ? '+' : '-' }}{{ formatCurrency(trx.amount) }}
                                </p>
                            </div>
                            <!-- Tombol Lanjutkan Pembayaran (Hanya jika pending & topup/credit) -->
                            <div v-if="trx.status === 'pending' && trx.type === 'credit'" class="w-full md:w-auto mt-1 md:mt-0">
                                <button 
                                    @click="handlePayPending(trx.id)" 
                                    :disabled="formPayPending.processing"
                                    class="w-full md:w-auto px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-[9px] uppercase font-medium tracking-wider transition-colors disabled:opacity-50 text-center"
                                >
                                    Lanjutkan Pembayaran
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <Modal :show="showTopUpModal" @close="closeTopUpModal">
            <div class="p-6 md:p-8 bg-white rounded-2xl relative overflow-hidden">
                <header>
                    <h2 class="text-lg font-medium text-slate-900 tracking-tight">Isi Saldo Dompet</h2>
                    <p class="mt-1 text-[11px] text-slate-500 font-normal tracking-wide leading-relaxed">
                        Pilih metode pengisian saldo yang Anda inginkan di bawah ini.
                    </p>
                </header>

                <div class="flex bg-slate-50 p-1 rounded-xl mt-6">
                    <button @click="activeTopUpTab = 'midtrans'" 
                            :class="activeTopUpTab === 'midtrans' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-2 text-[10px] uppercase tracking-widest font-medium rounded-lg transition-all text-center">
                        Transfer / E-Wallet
                    </button>
                    <button @click="activeTopUpTab = 'voucher'" 
                            :class="activeTopUpTab === 'voucher' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'"
                            class="flex-1 py-2 text-[10px] uppercase tracking-widest font-medium rounded-lg transition-all text-center">
                        Kode Voucher
                    </button>
                </div>

                <form v-if="activeTopUpTab === 'midtrans'" @submit.prevent="handleTopUpMidtrans" class="mt-5 space-y-5">
                    <div>
                        <label for="amount" class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Nominal Pengisian (Rp)</label>
                        <input
                            id="amount"
                            type="number"
                            placeholder="Min. 10.000"
                            class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors"
                            v-model="formTopUp.amount"
                            required
                            min="10000"
                            autofocus
                        />
                        <InputError class="mt-2 text-[10px]" :message="formTopUp.errors.amount" />
                        <p class="text-[9px] text-slate-400 font-normal tracking-wide mt-2 leading-relaxed">
                            Mendukung pembayaran via transfer bank (Virtual Account), QRIS, Gopay, OVO, ShopeePay, dsb.
                        </p>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-slate-50 pt-4">
                        <button type="button" @click="closeTopUpModal" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[10px] uppercase font-medium tracking-widest transition-colors">
                            Batal
                        </button>

                        <button type="submit" :disabled="formTopUp.processing" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all hover:bg-indigo-600 active:scale-95 disabled:opacity-50">
                            {{ formTopUp.processing ? 'Memproses...' : 'Lanjutkan Bayar' }}
                        </button>
                    </div>
                </form>

                <form v-if="activeTopUpTab === 'voucher'" @submit.prevent="handleClaimVoucher" class="mt-5 space-y-5">
                    <div>
                        <label for="code" class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Kode Voucher Valid</label>
                        <input
                            id="code"
                            type="text"
                            placeholder="Masukkan kode unik di sini..."
                            class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors uppercase"
                            v-model="formVoucher.code"
                            required
                            autofocus
                        />
                        <InputError class="mt-2 text-[10px]" :message="formVoucher.errors.code" />
                        <p class="text-[9px] text-slate-400 font-normal tracking-wide mt-2 leading-relaxed">
                            Kode voucher biasanya didapatkan dari event tertentu, giveaway, atau pembelian melalui agen resmi kami.
                        </p>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-slate-50 pt-4">
                        <button type="button" @click="closeTopUpModal" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[10px] uppercase font-medium tracking-widest transition-colors">
                            Batal
                        </button>

                        <button type="submit" :disabled="formVoucher.processing" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all hover:bg-indigo-600 active:scale-95 disabled:opacity-50">
                            {{ formVoucher.processing ? 'Memeriksa...' : 'Klaim Saldo' }}
                        </button>
                    </div>
                </form>

            </div>
        </Modal>
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

.space-y-6 > div, .space-y-8 > div {
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.space-y-6 > div:nth-child(1), .space-y-8 > div:nth-child(1) { animation-delay: 0s; }
.space-y-6 > div:nth-child(2), .space-y-8 > div:nth-child(2) { animation-delay: 0.1s; }
</style>