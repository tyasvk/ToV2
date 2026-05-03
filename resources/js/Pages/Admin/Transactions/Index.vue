<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
watch(search, debounce((value) => {
    router.get(route('admin.transactions.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const approveTransaction = (id) => {
    if (confirm('Konfirmasi: Setujui pembayaran ini?')) {
        router.post(route('admin.transactions.approve', id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'paid':
        case 'success':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'pending':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'failed':
            return 'bg-rose-50 text-rose-600 border-rose-100';
        default:
            return 'bg-slate-50 text-slate-500 border-slate-100';
    }
};
</script>

<template>
    <Head title="Manajemen Transaksi" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto py-8 px-4 md:px-6 space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-1">
                <div class="space-y-2">
                    <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tight uppercase leading-none">Transaksi Masuk</h2>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Daftar pembayaran peserta tryout</p>
                </div>

                <div class="relative w-full md:w-80 group">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300">🔍</span>
                    <input 
                        v-model="search" 
                        type="text" 
                        class="w-full pl-12 pr-6 py-4 bg-white border border-slate-100 rounded-2xl text-xs font-medium focus:ring-1 focus:ring-indigo-500 shadow-sm uppercase tracking-wider placeholder:normal-case" 
                        placeholder="Cari Invoice atau Nama..."
                    >
                </div>
            </div>

            <div class="pb-20 space-y-6">
                
                <div class="hidden md:block bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="bg-slate-50/30 border-b border-slate-100 text-[9px] md:text-[10px] font-medium text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5">Invoice</th>
                                <th class="px-8 py-5">Peserta</th>
                                <th class="px-8 py-5">Item</th>
                                <th class="px-8 py-5 text-right">Total</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/50 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <span class="font-medium text-slate-900 text-[11px] uppercase tracking-wider leading-none block">{{ trx.invoice_code }}</span>
                                        <p class="text-[9px] text-slate-400 font-medium uppercase">{{ formatDate(trx.created_at) }}</p>
                                    </div>
                                </td>

                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-900 text-white rounded-xl flex items-center justify-center text-[9px] font-medium shrink-0">
                                            {{ trx.user?.name.charAt(0) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-medium text-slate-900 uppercase truncate leading-none">{{ trx.user?.name ?? 'User Terhapus' }}</p>
                                            <p class="text-[9px] text-slate-400 font-medium truncate mt-1 leading-none">{{ trx.user?.email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-8 py-6">
                                    <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wide leading-tight line-clamp-2">
                                        {{ trx.tryout?.title ?? 'Item Terhapus' }}
                                    </span>
                                </td>

                                <td class="px-8 py-6 text-right">
                                    <span class="text-xs font-medium text-slate-900 tracking-tight whitespace-nowrap">
                                        {{ formatRupiah(trx.amount) }}
                                    </span>
                                </td>

                                <td class="px-8 py-6 text-center">
                                    <span :class="getStatusClass(trx.status)" class="px-3 py-1 rounded-lg text-[8px] font-medium uppercase tracking-widest border">
                                        {{ trx.status }}
                                    </span>
                                </td>

                                <td class="px-8 py-6 text-right">
                                    <button 
                                        v-if="trx.status === 'pending'" 
                                        @click="approveTransaction(trx.id)"
                                        class="bg-indigo-600 text-white text-[9px] font-medium uppercase tracking-widest px-4 py-2.5 rounded-xl hover:bg-slate-900 transition-all shadow-md active:scale-95"
                                    >
                                        Approve
                                    </button>
                                    <span v-else class="text-[8px] text-slate-300 font-medium uppercase tracking-widest">Tuntas</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-4">
                    <div v-for="trx in transactions.data" :key="trx.id" class="bg-white border border-slate-100 rounded-[2rem] p-6 space-y-5 shadow-sm overflow-hidden">
                        
                        <div class="flex justify-between items-start gap-4">
                            <div class="space-y-1 min-w-0">
                                <span class="text-[11px] font-medium text-slate-900 uppercase tracking-wider block leading-none truncate">{{ trx.invoice_code }}</span>
                                <span class="text-[9px] text-slate-400 font-medium uppercase tracking-widest">{{ formatDate(trx.created_at) }}</span>
                            </div>
                            <span :class="getStatusClass(trx.status)" class="px-3 py-1 rounded-xl text-[8px] font-medium uppercase tracking-widest border shrink-0">
                                {{ trx.status }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-slate-900 text-white rounded-xl flex items-center justify-center text-[10px] font-medium shrink-0">{{ trx.user?.name.charAt(0).toUpperCase() }}</div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-slate-900 uppercase truncate leading-none">{{ trx.user?.name }}</p>
                                    <p class="text-[9px] text-slate-400 font-medium truncate mt-1 leading-none">{{ trx.user?.email }}</p>
                                </div>
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <p class="text-[8px] text-slate-400 uppercase tracking-widest mb-1.5 leading-none">Paket Tryout</p>
                                <p class="text-[10px] font-medium text-slate-600 uppercase leading-snug break-words">
                                    {{ trx.tryout?.title }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between pt-5 border-t border-slate-50 gap-4">
                            <div class="flex flex-col items-center sm:items-start w-full sm:w-auto">
                                <span class="text-[8px] text-slate-400 uppercase tracking-widest leading-none mb-1.5">Total Bayar</span>
                                <span class="text-sm font-medium text-slate-900 tracking-tight leading-none">{{ formatRupiah(trx.amount) }}</span>
                            </div>
                            
                            <button 
                                v-if="trx.status === 'pending'" 
                                @click="approveTransaction(trx.id)" 
                                class="w-full sm:w-auto bg-indigo-600 text-white text-[9px] font-medium uppercase tracking-widest px-8 py-4 rounded-2xl shadow-lg shadow-indigo-100 active:scale-95 transition-all"
                            >
                                Approve
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="transactions.data.length === 0" class="py-20 text-center">
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.4em]">Tidak ada data transaksi</p>
                </div>

                <div v-if="transactions.links.length > 3" class="flex flex-wrap justify-center gap-2 pt-6">
                    <template v-for="(link, k) in transactions.links" :key="k">
                        <Link 
                            v-if="link.url" 
                            :href="link.url" 
                            v-html="link.label" 
                            class="px-4 py-2 rounded-xl text-[10px] font-medium uppercase tracking-widest transition-all" 
                            :class="link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 border border-slate-100'" 
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Menghilangkan scrollbar secara paksa */
.overflow-x-auto {
    overflow-x: hidden !important;
}

.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.bg-white, .md\:hidden > div {
    animation: slideUp 0.6s ease-out forwards;
}
</style>