<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps({
    balance: [Number, String], 
    transactions: Array,
    midtrans_client_key: String,
});

const page = usePage();
const showTopUpModal = ref(false);
const form = useForm({ amount: '' });
const quickAmounts = [20000, 50000, 100000, 200000];

// --- 1. UTILS FORMATTER (DIPERBARUI DENGAN MAPPING NAMA) ---
const formatCurrency = (amount) => {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Fungsi Baru: Mengubah "Sprint Flash" menjadi "Prawira" di tampilan
const formatDescription = (desc) => {
    if (!desc) return '-';
    
    // Kamus Penerjemah (Case Insensitive)
    const map = {
        'sprint flash': 'Prawira',
        'mastery plan': 'Wiranata',
        'ultimate pass': 'Mahapatih',
        'standard pro': 'Satria'
    };

    let formatted = desc;
    Object.keys(map).forEach(key => {
        // Regex untuk mengganti teks tanpa peduli huruf besar/kecil
        const regex = new RegExp(key, 'gi'); 
        formatted = formatted.replace(regex, map[key]);
    });

    return formatted;
};

// --- 2. LOGIKA MIDTRANS ---
const snapToken = computed(() => usePage().props.flash?.snapToken);

onMounted(() => {
    if (!document.querySelector('script[src*="snap.js"]')) {
        const script = document.createElement('script');
        const isSandbox = props.midtrans_client_key?.includes('SB-');
        script.src = isSandbox 
            ? 'https://app.sandbox.midtrans.com/snap/snap.js' 
            : 'https://app.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', props.midtrans_client_key);
        document.head.appendChild(script);
    }
});

const payWithSnap = (token) => {
    if (window.snap && token) {
        window.snap.pay(token, {
            onSuccess: () => { alert("Sukses!"); router.reload(); },
            onPending: () => { alert("Menunggu Pembayaran..."); router.reload(); },
            onError: () => { alert("Gagal!"); },
            onClose: () => { console.log('Popup ditutup'); }
        });
    }
};

watch(snapToken, (newToken) => {
    if (newToken) payWithSnap(newToken);
});

// --- 3. SUBMIT FORM ---
const submitTopUp = () => {
    form.post(route('wallet.topup'), {
        preserveScroll: true,
        onSuccess: (page) => {
            showTopUpModal.value = false;
            form.reset();
            const token = page.props.flash?.snapToken;
            if (token) payWithSnap(token);
        },
    });
};

const payPendingTransaction = (transactionId) => {
    router.post(route('wallet.pay', transactionId), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
             const token = page.props.flash?.snapToken;
             if(token) payWithSnap(token);
        },
    });
};
</script>

<template>
    <Head title="Dompet Saya" />

    <AuthenticatedLayout>
        <div class="bg-slate-50/50 min-h-screen pt-0 pb-12">
            <div class="max-w-2xl mx-auto px-0 sm:px-4">
                
                <div class="bg-slate-900 rounded-b-[2.5rem] sm:rounded-[2.5rem] p-8 sm:p-10 shadow-2xl relative overflow-hidden -mt-2 sm:mt-4 text-white">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 rounded-full blur-[80px] opacity-40 -mr-16 -mt-16"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Total Saldo Aktif</p>
                                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">
                                    {{ formatCurrency(balance) }}
                                </h2>
                            </div>
                            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                                👜
                            </div>
                        </div>

                        <button @click="showTopUpModal = true" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg transition-all active:scale-95">
                            Top Up Saldo
                        </button>
                    </div>
                </div>

                <div class="px-6 sm:px-0 mt-8">
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-6 ml-2">Riwayat Transaksi</h3>
                    
                    <div v-if="transactions.length === 0" class="text-center py-10 opacity-50">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada transaksi</p>
                    </div>

                    <div v-for="trx in transactions" :key="trx.id" class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div :class="trx.type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'" class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg">
                                {{ trx.type === 'credit' ? '📥' : '📤' }}
                            </div>
                            <div>
                                <p class="font-black text-slate-900 text-xs uppercase tracking-wide">
                                    {{ formatDescription(trx.description) }}
                                </p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ formatDate(trx.created_at) }}</p>
                            </div>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <p :class="trx.type === 'credit' ? 'text-emerald-600' : 'text-slate-900'" class="font-black text-sm">
                                {{ trx.type === 'credit' ? '+' : '-' }} {{ formatCurrency(trx.amount) }}
                            </p>
                            <button v-if="trx.status === 'pending' && trx.type === 'credit'" @click="payPendingTransaction(trx.id)" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest mt-1">
                                Bayar
                            </button>
                            <span v-else class="text-[8px] font-black uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100 mt-1">
                                {{ trx.status }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Teleport to="body">
            <div v-if="showTopUpModal" class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center sm:p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showTopUpModal = false"></div>
                <div class="relative bg-white w-full max-w-md rounded-t-[2.5rem] sm:rounded-[2.5rem] p-8 shadow-2xl animate-in slide-in-from-bottom-10 duration-300">
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-6">Isi Saldo</h3>
                    <form @submit.prevent="submitTopUp" class="space-y-6">
                        <input v-model="form.amount" type="number" placeholder="Nominal (Min 10.000)" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-black text-lg focus:ring-2 focus:ring-indigo-500/20" />
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="amt in quickAmounts" :key="amt" type="button" @click="form.amount = amt" class="py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold hover:border-indigo-500 transition-all">
                                {{ formatCurrency(amt) }}
                            </button>
                        </div>
                        <button type="submit" :disabled="form.processing || form.amount < 10000" class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg active:scale-95">
                            Bayar Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>