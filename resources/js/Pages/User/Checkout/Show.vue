<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

// MENERIMA SEMUA KEMUNGKINAN PROPS
const props = defineProps({
    transaction: Object, 
    checkout: Object,    
    order: Object,
    payment: Object,       
    tryout: Object, // Seringkali data transaksi salah dikirim ke variabel ini
    snapToken: String,
    midtransClientKey: String 
});

// COMPUTED: Normalisasi Data Transaksi (Analisa Mendalam)
const trx = computed(() => {
    // 1. Cek semua prop yang mungkin berisi data transaksi
    // Kita cek apakah 'props.tryout' sebenarnya berisi data transaksi (punya field total_amount/gross_amount?)
    let potentialTrx = props.transaction || props.checkout || props.order || props.payment;
    
    // DETEKSI ISU BACKEND: Jika props.tryout memiliki field 'total_amount' atau 'gross_amount', 
    // berarti backend salah memberi nama variabel (dikira tryout padahal transaksi). Kita gunakan itu.
    if (!potentialTrx && props.tryout && (props.tryout.total_amount || props.tryout.gross_amount || props.tryout.snap_token)) {
        console.warn('Backend Warning: Data Transaksi dikirim dalam variabel "tryout".');
        potentialTrx = props.tryout;
    }

    const base = potentialTrx || {};
    
    // Ambil data produk (jika ada relasi tryout di dalam transaksi, gunakan itu)
    const product = base.tryout || props.tryout || {};

    // 2. LOGIKA HARGA TOTAL
    // Prioritas: Ambil Total dari Transaksi dulu. Jangan ambil Product Price kecuali terpaksa.
    const totalPayment = 
        base.gross_amount || 
        base.total_amount || 
        base.total_price || 
        base.amount || 
        base.final_price ||
        product.price || // Fallback terakhir (Harga Satuan)
        0;

    return {
        id: base.id || 0,
        tryout_id: base.tryout_id || product.id || 0,
        
        reference: base.reference || base.order_id || base.invoice_number || `TRX-${base.id || 'NEW'}`,
        title: base.title || base.product_name || product.title || 'Paket Tryout Premium',
        
        // TOTAL BAYAR FINAL
        total_pay: Number(totalPayment),
        
        status: base.status || base.transaction_status || 'pending',
        payment_method: base.payment_method || base.payment_type || 'Otomatis',
        created_at: base.created_at || new Date(),
        
        snap_token: base.snap_token || props.snapToken
    };
});

// INJECT SCRIPT MIDTRANS
onMounted(() => {
    if (!props.midtransClientKey) return;
    const scriptUrl = "https://app.sandbox.midtrans.com/snap/snap.js"; 
    if (!document.getElementById('midtrans-script')) {
        const script = document.createElement('script');
        script.src = scriptUrl;
        script.setAttribute('data-client-key', props.midtransClientKey);
        script.id = 'midtrans-script';
        document.head.appendChild(script);
    }
});

// FUNGSI BAYAR MIDTRANS
const payNow = () => {
    const token = trx.value.snap_token; 
    if (!token) {
        alert("Token pembayaran tidak ditemukan. Mohon pastikan Controller mengirim 'transaction' dengan benar.");
        return;
    }
    window.snap.pay(token, {
        onSuccess: (result) => { router.post(route('checkout.callback.internal'), result); },
        onPending: (result) => { alert("Menunggu pembayaran Anda!"); router.reload(); },
        onError: (result) => { alert("Pembayaran gagal!"); router.reload(); },
        onClose: () => { alert('Anda menutup popup tanpa menyelesaikan pembayaran'); }
    });
};

// FORMATTER
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// STATUS HELPER
const getStatusColor = (status) => {
    const s = status ? status.toLowerCase() : '';
    if (['paid', 'settlement', 'success', 'capture'].includes(s)) return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    if (['pending', 'challenge'].includes(s)) return 'bg-amber-50 text-amber-700 border-amber-100';
    return 'bg-red-50 text-red-700 border-red-100';
};
const getStatusLabel = (status) => {
    const s = status ? status.toLowerCase() : '';
    if (['paid', 'settlement', 'success', 'capture'].includes(s)) return 'Lunas';
    if (['pending', 'challenge'].includes(s)) return 'Menunggu Pembayaran';
    if (['expire', 'failure'].includes(s)) return 'Kadaluarsa';
    if (['cancel', 'deny'].includes(s)) return 'Dibatalkan';
    return 'Gagal';
};
</script>

<template>
    <Head title="Detail Transaksi" />

    <AuthenticatedLayout>
        
        <div class="relative bg-[#0F172A] text-white overflow-hidden py-10 px-4 sm:px-6 lg:px-8 border-b border-gray-800 font-sans">
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-amber-500/5 rounded-full blur-[80px]"></div>
            
            <div class="relative z-10 max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight mb-1 tracking-tight">
                        Detail <span class="text-amber-400">Transaksi</span>
                    </h1>
                    <p class="text-slate-400 text-xs md:text-sm max-w-lg font-normal">
                        Selesaikan pembayaran untuk mulai mengerjakan ujian.
                    </p>
                </div>

                <Link :href="trx.tryout_id ? route('tryout.register', trx.tryout_id) : route('tryout.index')" 
                    class="group flex items-center gap-2 px-5 py-2.5 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all text-[10px] font-bold uppercase tracking-widest text-slate-300 hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Pendaftaran
                </Link>
            </div>
        </div>

        <div class="min-h-screen bg-[#F8F9FA] relative z-20 py-8 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                            <div class="h-0.5 w-full bg-gradient-to-r from-[#0F172A] via-amber-500 to-[#0F172A] absolute top-0 left-0"></div>
                            
                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">No. Referensi</h3>
                                    <p class="text-lg font-bold text-[#0F172A] tracking-tight">{{ trx.reference }}</p>
                                </div>
                                <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest border"
                                      :class="getStatusColor(trx.status)">
                                    {{ getStatusLabel(trx.status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 bg-[#F8F9FA] p-4 rounded-xl border border-gray-100">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Waktu Transaksi</p>
                                    <p class="text-xs font-bold text-gray-800 leading-relaxed capitalize">{{ formatDateTime(trx.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Metode</p>
                                    <p class="text-xs font-bold text-gray-800 capitalize">{{ trx.payment_method }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide flex items-center gap-2 mb-4">
                                <span class="w-1.5 h-4 bg-slate-300 rounded-full"></span>
                                Rincian Item
                            </h3>

                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-[#0F172A] text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-indigo-900/20 shrink-0">
                                    📑
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-base mb-1 tracking-tight">
                                        {{ trx.title }}
                                    </h4>
                                    <p class="text-xs text-slate-500 mb-2">Akses penuh simulasi CAT BKN dengan pembahasan lengkap.</p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded uppercase">Digital Product</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-[#0F172A]">{{ formatRupiah(trx.total_pay) }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="['pending', 'challenge'].includes(trx.status)" class="bg-amber-50 rounded-xl border border-amber-100 p-6">
                             <h4 class="text-sm font-bold text-amber-800 uppercase tracking-wide mb-2 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Instruksi
                             </h4>
                             <p class="text-xs text-amber-700 leading-relaxed font-medium">
                                Klik tombol "Bayar Sekarang" di sebelah kanan. Popup Midtrans akan muncul untuk menyelesaikan pembayaran.
                             </p>
                        </div>
                    </div>

                    <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-6">
                        
                        <div class="bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden relative">
                            <div class="bg-[#0F172A] p-6 relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-20 h-20 bg-white/5 rounded-bl-full"></div>
                                <div class="relative z-10">
                                    <h2 class="text-sm font-bold text-white uppercase tracking-widest">Tagihan</h2>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mt-1 opacity-80">Total Pembayaran</p>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="text-center mb-6">
                                    <p class="text-3xl font-bold text-[#0F172A] tracking-tight">{{ formatRupiah(trx.total_pay) }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Sudah termasuk pajak & biaya admin</p>
                                </div>

                                <div class="space-y-3">
                                    
                                    <button v-if="['pending', 'challenge'].includes(trx.status)" 
                                        @click="payNow"
                                        class="group relative w-full overflow-hidden bg-[#0F172A] text-white py-4 rounded-xl text-xs font-bold uppercase tracking-[0.2em] transition-all shadow-lg hover:shadow-xl hover:shadow-indigo-900/20 active:scale-[0.98]"
                                    >
                                        <div class="relative z-10 flex items-center justify-center gap-2">
                                            <span>Bayar Sekarang</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </div>
                                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-900 to-[#0F172A] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    </button>

                                    <Link v-else-if="['paid', 'settlement', 'success', 'capture'].includes(trx.status)"
                                        :href="route('tryout.my')"
                                        class="w-full py-4 bg-emerald-600 text-white rounded-xl text-xs font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2 shadow-lg shadow-emerald-200 hover:bg-emerald-700 transition-all"
                                    >
                                        <span>Mulai Ujian</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </Link>
                                    
                                    <button v-else
                                        disabled
                                        class="w-full py-4 bg-gray-100 text-gray-400 rounded-xl text-xs font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2 cursor-not-allowed"
                                    >
                                        <span>Transaksi Selesai/Gagal</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                         <div class="text-center">
                            <p class="text-[10px] text-slate-400 flex items-center justify-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                Transaksi Terenkripsi & Aman
                            </p>
                        </div>
                    </div>

                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>