<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

// MENERIMA PROPS DARI CHECKOUTCONTROLLER
const props = defineProps({
    transaction: Object, 
    item_name: String,    // Nama produk (Tryout atau Membership)
    snap_token: String,   // Token dari Midtrans
    midtransClientKey: String // Opsional: jika ingin inject script via Vue
});

// COMPUTED: Normalisasi Data Transaksi
const trx = computed(() => {
    const base = props.transaction || {};
    
    return {
        id: base.id || 0,
        tryout_id: base.tryout_id || null, // Null jika Membership
        reference: base.invoice_code || base.reference || `TRX-${base.id}`,
        title: props.item_name || base.description || 'Produk CPNS Nusantara',
        
        // Harga Final (diambil langsung dari database amount)
        total_pay: Number(base.amount || 0),
        
        status: base.status || 'pending',
        payment_method: base.payment_method || 'Otomatis (Midtrans)',
        created_at: base.created_at || new Date(),
        snap_token: props.snap_token || base.snap_token
    };
});

// INJECT SCRIPT MIDTRANS (Jika belum ada di app.blade.php)
onMounted(() => {
    const snapScriptUrl = "https://app.sandbox.midtrans.com/snap/snap.js"; // Ganti ke app.midtrans.com untuk Production
    const clientKey = props.midtransClientKey || 'YOUR_CLIENT_KEY_HERE';

    if (!document.getElementById('midtrans-script')) {
        const script = document.createElement('script');
        script.src = snapScriptUrl;
        script.setAttribute('data-client-key', clientKey);
        script.id = 'midtrans-script';
        document.head.appendChild(script);
    }
});

// FUNGSI BAYAR MIDTRANS
const payNow = () => {
    const token = trx.value.snap_token; 
    if (!token) {
        alert("Token pembayaran tidak ditemukan. Silakan refresh halaman.");
        return;
    }

    window.snap.pay(token, {
        onSuccess: (result) => { 
            // Redirect berdasarkan jenis produk
            if (trx.value.tryout_id) {
                router.visit(route('tryout.my'), { method: 'get' });
            } else {
                router.visit(route('dashboard'), { method: 'get' });
            }
        },
        onPending: (result) => { 
            alert("Menunggu pembayaran Anda!"); 
        },
        onError: (result) => { 
            alert("Pembayaran gagal! Silakan coba lagi."); 
        },
        onClose: () => { 
            console.log('Customer closed the popup without finishing the payment'); 
        }
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
    if (['pending', 'challenge'].includes(s)) return 'Menunggu';
    return 'Gagal/Expired';
};
</script>

<template>
    <Head title="Pembayaran" />

    <AuthenticatedLayout>
        <div class="relative bg-[#0F172A] text-white py-12 px-4 border-b border-gray-800">
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px]"></div>
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                <div>
                    <h1 class="text-3xl font-black mb-2">Selesaikan <span class="text-indigo-400">Pembayaran</span></h1>
                    <p class="text-slate-400 text-sm">Hanya selangkah lagi untuk menikmati fitur premium.</p>
                </div>
                <Link :href="trx.tryout_id ? route('tryout.index') : route('membership.index')" 
                    class="px-6 py-2.5 bg-white/5 border border-white/10 rounded-lg text-xs font-bold uppercase tracking-widest hover:bg-white/10 transition-all"
                >
                    Kembali
                </Link>
            </div>
        </div>

        <div class="py-10 bg-[#F8F9FA] min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">ID Invoice</p>
                                    <h3 class="text-xl font-mono font-bold text-gray-900">{{ trx.reference }}</h3>
                                </div>
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter border-2" :class="getStatusColor(trx.status)">
                                    {{ getStatusLabel(trx.status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-6 pb-8 border-b border-gray-100">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanggal</p>
                                    <p class="text-sm font-bold text-gray-800">{{ formatDateTime(trx.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Metode</p>
                                    <p class="text-sm font-bold text-gray-800">{{ trx.payment_method }}</p>
                                </div>
                            </div>

                            <div class="pt-8">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Rincian Produk</h4>
                                <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                    <div class="w-12 h-12 bg-indigo-600 text-white flex items-center justify-center rounded-lg text-xl shadow-lg">
                                        {{ trx.tryout_id ? '📝' : '💎' }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-bold text-gray-900 leading-tight">{{ trx.title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Akses Premium CPNS Nusantara</p>
                                    </div>
                                    <p class="font-bold text-gray-900">{{ formatRupiah(trx.total_pay) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 border border-indigo-100 p-6 rounded-2xl flex gap-4">
                            <div class="shrink-0 text-indigo-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-xs text-indigo-700 leading-relaxed font-medium">
                                Pembayaran diproses secara otomatis. Jangan menutup halaman ini sampai Anda diarahkan kembali ke aplikasi setelah membayar di jendela Midtrans.
                            </p>
                        </div>
                    </div>

                    <div class="lg:col-span-5 lg:sticky lg:top-8">
                        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200 border border-gray-100 overflow-hidden">
                            <div class="bg-gray-900 p-8 text-white text-center relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-transparent"></div>
                                <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400 mb-2 relative z-10">Total Bayar</h3>
                                <p class="text-4xl font-black text-white relative z-10">{{ formatRupiah(trx.total_pay) }}</p>
                            </div>

                            <div class="p-8">
                                <div v-if="['pending', 'challenge'].includes(trx.status)" class="space-y-4">
                                    <button @click="payNow" 
                                        class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all active:scale-95"
                                    >
                                        Bayar Sekarang
                                    </button>
                                    <p class="text-center text-[10px] text-gray-400 font-medium">
                                        Mendukung QRIS, Bank Transfer, & E-Wallet
                                    </p>
                                </div>

                                <div v-else-if="['paid', 'settlement', 'success', 'capture'].includes(trx.status)" class="text-center">
                                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <p class="font-bold text-gray-900 mb-4">Pembayaran Berhasil!</p>
                                    <Link :href="trx.tryout_id ? route('tryout.my') : route('dashboard')" 
                                        class="inline-block w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold uppercase tracking-widest text-xs"
                                    >
                                        Mulai Gunakan Fitur
                                    </Link>
                                </div>

                                <div v-else class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                                    <p class="text-xs font-bold text-red-700">Transaksi Kadaluarsa atau Dibatalkan</p>
                                    <Link :href="route('tryout.index')" class="text-[10px] text-red-500 underline mt-2 block font-bold uppercase">Coba Lagi</Link>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-center gap-3 opacity-40">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_Midtrans.png" class="h-4" alt="Midtrans">
                            <div class="h-3 w-[1px] bg-gray-300"></div>
                            <p class="text-[9px] font-bold text-gray-500 uppercase tracking-widest">Secure Payment</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>