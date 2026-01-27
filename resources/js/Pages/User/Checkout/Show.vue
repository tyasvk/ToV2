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
    const script = document.createElement('script');
    script.src = "https://app.sandbox.midtrans.com/snap/snap.js";
    script.setAttribute('data-client-key', props.midtransClientKey);
    document.head.appendChild(script);
});

const payNow = () => {
    window.snap.pay(props.snapToken, {
        onSuccess: (result) => router.post(route('checkout.callback.internal'), result),
        onPending: () => alert("Menunggu pembayaran Anda!"),
        onError: () => alert("Pembayaran gagal!"),
        onClose: () => alert('Anda menutup popup tanpa menyelesaikan pembayaran')
    });
};
</script>

<template>
    <Head title="Checkout Pembayaran" />
    <AuthenticatedLayout>
        <div class="pt-0 pb-12 bg-slate-50/50 min-h-screen flex items-start justify-center px-4">
            
            <div class="w-full max-w-md bg-white rounded-b-[2.5rem] rounded-t-none shadow-2xl shadow-slate-200/50 border-x border-b border-slate-100 overflow-hidden">
                <div class="p-8 text-center">
                    
                    <div class="w-12 h-12 bg-slate-900 text-white rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    
                    <h2 class="text-lg font-black text-slate-900 uppercase tracking-[0.2em] mb-6">Payment Confirmation</h2>

                    <div class="bg-slate-900 rounded-[2rem] p-6 mb-8 text-left text-white relative overflow-hidden shadow-xl shadow-indigo-100/10">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
                        
                        <div class="relative z-10">
                            <span class="text-[8px] font-black text-indigo-400 uppercase tracking-widest block mb-1">Product_Title</span>
                            <h3 class="text-sm font-black uppercase tracking-tight mb-6 line-clamp-1">{{ tryout.title }}</h3>
                            
                            <div class="flex justify-between items-end pt-4 border-t border-white/5">
                                <div>
                                    <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest block mb-0.5">Amount to pay</span>
                                    <span class="text-2xl font-black text-white italic tracking-tighter">
                                        Rp{{ Number(tryout.price).toLocaleString('id-ID') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[7px] font-bold text-slate-500 uppercase tracking-widest">Order ID</span>
                                    <span class="text-[9px] font-black text-indigo-400">#{{ String(tryout.id).padStart(4, '0') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button 
                            @click="payNow" 
                            class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all active:scale-95"
                        >
                            Pay Securely Now
                        </button>
                        
                        <Link :href="route('tryout.index')" class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-rose-500 transition-colors">
                            Cancel Transaction
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
* { font-style: normal !important; }

/* Animasi Masuk dari Atas */
.rounded-t-none {
    animation: slideDown 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>