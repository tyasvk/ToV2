<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    balance: {
        type: Number,
        default: 0
    },
    transactions: {
        type: Array,
        default: () => []
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Dompet Saya" />

    <AuthenticatedLayout>
        <div class="space-y-8 md:space-y-12 animate-in fade-in duration-700">
            
            <div class="relative bg-slate-900 rounded-[2.5rem] p-8 md:p-14 overflow-hidden shadow-2xl shadow-indigo-500/10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="space-y-4">
                        <span class="text-[9px] font-medium text-indigo-400 uppercase tracking-[0.3em] border border-indigo-500/20 px-4 py-1.5 rounded-full inline-block">
                            Saldo Aktif
                        </span>
                        <h1 class="text-3xl md:text-5xl font-medium text-white tracking-tighter leading-none">
                            {{ formatCurrency(balance) }}
                        </h1>
                        <p class="text-[11px] md:text-sm text-slate-400 font-medium leading-relaxed max-w-xs">
                            Gunakan saldo anda untuk akses cepat ke berbagai simulasi premium.
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link 
                            :href="route('wallet.topup')"
                            method="post"
                            as="button"
                            type="button"
                            class="bg-white text-slate-900 px-8 py-4 rounded-2xl text-[10px] font-medium uppercase tracking-widest hover:bg-indigo-50 transition-all active:scale-95 shadow-xl"
                        >
                            Tambah Saldo
                        </Link>
                    </div>
                </div>
            </div>

            <div class="space-y-6 pb-20">
                <div class="flex items-center justify-between px-2">
                    <div class="space-y-1">
                        <h2 class="text-lg font-medium text-slate-900 uppercase tracking-widest italic">Riwayat Transaksi</h2>
                        <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Aktivitas penggunaan dompet digital anda</p>
                    </div>
                </div>

                <div v-if="transactions.length > 0" class="grid grid-cols-1 gap-4">
                    <div 
                        v-for="tx in transactions" 
                        :key="tx.id"
                        class="bg-white border border-slate-100 p-6 md:p-8 rounded-[2rem] flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-xl shadow-slate-200/50 transition-all duration-500"
                    >
                        <div class="flex items-center gap-5">
                            <div :class="tx.type === 'credit' ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500'" 
                                 class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0">
                                <svg v-if="tx.type === 'credit'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-medium text-slate-900 uppercase tracking-tight truncate">
                                    {{ tx.description || 'Transaksi Dompet' }}
                                </h3>
                                <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mt-1">
                                    {{ formatDate(tx.created_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-8 border-t md:border-t-0 pt-4 md:pt-0">
                            <div class="text-left md:text-right">
                                <p class="text-[8px] font-medium text-slate-400 uppercase tracking-widest mb-1">Jumlah</p>
                                <p :class="tx.type === 'credit' ? 'text-emerald-500' : 'text-slate-900'" class="text-base font-medium tracking-tight">
                                    {{ tx.type === 'credit' ? '+' : '-' }} {{ formatCurrency(tx.amount) }}
                                </p>
                            </div>
                            <span :class="tx.status === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'" 
                                  class="px-3 py-1.5 rounded-xl text-[8px] font-medium uppercase tracking-widest border shrink-0">
                                {{ tx.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-slate-50 rounded-[3rem] p-16 md:p-24 flex flex-col items-center text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-6 text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em]">Belum Ada Aktivitas Transaksi</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: slideUp 0.6s ease-out forwards;
}
</style>