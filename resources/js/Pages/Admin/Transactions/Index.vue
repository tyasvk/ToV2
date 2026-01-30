<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

// Search Logic
const search = ref(props.filters.search || '');
watch(search, debounce((value) => {
    router.get(route('admin.transactions.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

// Format Rupiah
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

// Format Tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// Approve Action
const approveTransaction = (id) => {
    if (confirm('Konfirmasi: Setujui pembayaran ini secara manual?')) {
        router.post(route('admin.transactions.approve', id));
    }
};

// Helper warna status
const getStatusClass = (status) => {
    switch (status) {
        case 'paid':
        case 'success':
            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'pending':
            return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'failed':
            return 'bg-red-100 text-red-700 border-red-200';
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <Head title="Manajemen Transaksi" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 py-8 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Transaksi Masuk</h1>
                        <p class="text-sm text-slate-500">Pantau dan kelola pembayaran peserta tryout.</p>
                    </div>

                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            class="block w-full pl-10 pr-4 py-2.5 border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm" 
                            placeholder="Cari Invoice atau Nama..."
                        >
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 font-bold">
                                    <th class="px-6 py-4">Invoice Info</th>
                                    <th class="px-6 py-4">Peserta</th>
                                    <th class="px-6 py-4">Item Tryout</th>
                                    <th class="px-6 py-4 text-right">Total Bayar</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                6</tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/80 transition-colors group">
                                    
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col">
                                            <span class="font-mono font-bold text-slate-700 text-sm group-hover:text-indigo-600 transition-colors">
                                                {{ trx.invoice_code }}
                                            </span>
                                            <span class="text-xs text-slate-400 mt-1">{{ formatDate(trx.created_at) }}</span>
                                            <span v-if="trx.qty > 1" class="inline-flex items-center gap-1 mt-2 w-max px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                KOLEKTIF ({{ trx.qty }})
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                                {{ trx.user?.name.charAt(0).toUpperCase() ?? '?' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-slate-700">{{ trx.user?.name ?? 'User Dihapus' }}</div>
                                                <div class="text-xs text-slate-500">{{ trx.user?.email ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <span class="text-sm font-medium text-slate-600">
                                            {{ trx.tryout?.title ?? 'Item Dihapus' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 align-top text-right">
                                        <span class="text-sm font-black text-slate-700 block">
                                            {{ formatRupiah(trx.amount) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 align-top text-center">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border" :class="getStatusClass(trx.status)">
                                            {{ trx.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 align-top text-center">
                                        <button 
                                            v-if="trx.status === 'pending'" 
                                            @click="approveTransaction(trx.id)"
                                            class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-all shadow-sm active:scale-95"
                                        >
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            Approve
                                        </button>
                                        <span v-else class="text-xs text-slate-300 italic select-none">Selesai</span>
                                    </td>

                                </tr>

                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            <p class="text-sm font-medium">Tidak ada data transaksi ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="transactions.links.length > 3" class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
                        <div class="flex gap-1">
                            <Link 
                                v-for="(link, k) in transactions.links" 
                                :key="k"
                                :href="link.url ?? '#'"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border"
                                :class="{ 
                                    'bg-indigo-600 text-white border-indigo-600 shadow-sm': link.active, 
                                    'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300': !link.active && link.url,
                                    'bg-slate-100 text-slate-400 border-transparent cursor-not-allowed': !link.url 
                                }"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>