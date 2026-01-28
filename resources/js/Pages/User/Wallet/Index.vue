<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';

const props = defineProps({
    balance: Number,
    transactions: Array,
    midtrans_client_key: String,
});

const page = usePage();
const showTopUpModal = ref(false);

const form = useForm({
    amount: ''
});

const quickAmounts = [20000, 50000, 100000, 200000];

// --- PERBAIKAN UTAMA DI SINI ---
// 1. Gunakan Computed agar reaktif dan aman (cek apakah flash ada sebelum ambil snapToken)
const snapToken = computed(() => page.props.flash?.snapToken);

onMounted(() => {
    // Inject Script Midtrans Snap
    const script = document.createElement('script');
    script.src = props.midtrans_client_key 
        ? 'https://app.sandbox.midtrans.com/snap/snap.js' // Sandbox URL
        : 'https://app.midtrans.com/snap/snap.js';       // Production URL (jika nanti live)
        
    script.setAttribute('data-client-key', props.midtrans_client_key);
    document.head.appendChild(script);
});

// 2. Watch variable computed 'snapToken'
watch(snapToken, (newToken) => {
    if (newToken && window.snap) {
        window.snap.pay(newToken, {
            onSuccess: function(result){
                alert("Pembayaran Berhasil!");
                // Reload halaman untuk update saldo
                window.location.reload();
            },
            onPending: function(result){
                alert("Menunggu Pembayaran...");
                window.location.reload();
            },
            onError: function(result){
                alert("Pembayaran Gagal!");
                console.error(result);
            },
            onClose: function(){
                console.log('Popup ditutup tanpa pembayaran');
            }
        });
    }
});
// -----------------------

const submitTopUp = () => {
    form.post(route('wallet.topup'), {
        preserveScroll: true,
        onSuccess: () => {
            showTopUpModal.value = false;
            form.reset();
            // Snap Token akan otomatis terdeteksi oleh watcher di atas
        }
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dompet Saya" />

    <AuthenticatedLayout>
        <template #header></template>

        <div class="bg-slate-50/50 min-h-screen pt-0 pb-12">
            <div class="max-w-2xl mx-auto px-0 sm:px-4">
                
                <div class="bg-slate-900 rounded-b-[2.5rem] sm:rounded-[2.5rem] p-8 sm:p-10 shadow-2xl relative overflow-hidden -mt-2 sm:mt-4 text-white">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600 rounded-full blur-[80px] opacity-40 -mr-16 -mt-16"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Total Saldo Aktif</p>
                                <h2 class="text-3xl sm:text-4xl font-black tracking-tight">{{ formatCurrency(balance) }}</h2>
                            </div>
                            <div class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm border border-white/10">
                                👜
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="showTopUpModal = true" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-lg shadow-indigo-900/50 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                                Top Up Saldo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-6 sm:px-0 mt-8">
                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-6 ml-2">Riwayat Transaksi</h3>

                    <div v-if="transactions.length === 0" class="text-center py-12 bg-white rounded-[2rem] border border-dashed border-slate-200">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Belum ada transaksi</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="trx in transactions" :key="trx.id" class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="trx.type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'" 
                                    class="w-12 h-12 rounded-2xl flex items-center justify-center text-lg">
                                    {{ trx.type === 'credit' ? 'Of' : '💸' }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 text-xs uppercase tracking-wide">{{ trx.description }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase mt-0.5">{{ formatDate(trx.created_at) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p :class="trx.type === 'credit' ? 'text-emerald-600' : 'text-slate-900'" class="font-black text-sm">
                                    {{ trx.type === 'credit' ? '+' : '-' }} {{ formatCurrency(trx.amount) }}
                                </p>
                                <span :class="{
                                    'text-amber-500': trx.status === 'pending',
                                    'text-emerald-500': trx.status === 'success',
                                    'text-red-500': trx.status === 'failed'
                                }" class="text-[8px] font-black uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100">
                                    {{ trx.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Teleport to="body">
            <div v-if="showTopUpModal" class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center sm:p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showTopUpModal = false"></div>
                
                <div class="relative bg-white w-full max-w-md rounded-t-[2.5rem] sm:rounded-[2.5rem] p-8 shadow-2xl animate-in slide-in-from-bottom-10 duration-300">
                    <div class="w-12 h-1.5 bg-slate-100 rounded-full mx-auto mb-6 sm:hidden"></div>
                    
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-1">Isi Saldo</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-6">Pilih nominal top up</p>

                    <form @submit.prevent="submitTopUp" class="space-y-6">
                        <div>
                            <input 
                                v-model="form.amount" 
                                type="number" 
                                placeholder="Nominal Lain (Min 10.000)"
                                class="w-full bg-slate-50 border-none rounded-2xl p-4 font-black text-lg text-slate-900 focus:ring-2 focus:ring-indigo-500/20"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="amt in quickAmounts" :key="amt" 
                                type="button"
                                @click="form.amount = amt"
                                class="py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:border-indigo-500 hover:text-indigo-600 transition-all"
                            >
                                {{ formatCurrency(amt) }}
                            </button>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing || form.amount < 10000"
                            class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-xl active:scale-95 disabled:opacity-50"
                        >
                            Bayar Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>