<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({ 
    tryout: Object, 
    snapToken: String,
    midtransClientKey: String 
});

onMounted(() => {
    // Load Midtrans Snap Script secara dinamis
    const script = document.createElement('script');
    script.src = "https://app.sandbox.midtrans.com/snap/snap.js"; // Ganti ke app.midtrans.com jika sudah Production
    script.setAttribute('data-client-key', props.midtransClientKey);
    document.head.appendChild(script);
});

const payNow = () => {
    window.snap.pay(props.snapToken, {
        onSuccess: function(result) {
            // Arahkan ke halaman sukses atau panggil fungsi simpan ke DB
            router.post(route('checkout.callback.internal'), result);
        },
        onPending: function(result) {
            alert("Menunggu pembayaran Anda!");
        },
        onError: function(result) {
            alert("Pembayaran gagal!");
        },
        onClose: function() {
            alert('Anda menutup popup tanpa menyelesaikan pembayaran');
        }
    });
};
</script>

<template>
    <Head title="Checkout Pembayaran" />
    <AuthenticatedLayout>
        <div class="py-10 bg-slate-50/50 min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-6">Lanjutkan Pembayaran</h2>

                    <div class="bg-slate-900 rounded-3xl p-6 mb-8 text-left text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <span class="text-[8px] font-black opacity-50 uppercase tracking-widest">Akses Paket</span>
                            <h3 class="text-base font-black mb-4 truncate">{{ tryout.title }}</h3>
                            <div class="flex justify-between items-end">
                                <span class="text-2xl font-black text-indigo-400">Rp {{ Number(tryout.price).toLocaleString('id-ID') }}</span>
                                <span class="text-[8px] font-bold opacity-30 italic">Secure Payment</span>
                            </div>
                        </div>
                    </div>

                    <button @click="payNow" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all active:scale-95">
                        Bayar Sekarang
                    </button>
                    
                    <Link :href="route('tryout.index')" class="mt-4 block text-[9px] font-bold text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors">
                        Kembali Ke Dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>