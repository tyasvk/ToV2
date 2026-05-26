<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

// --- FILTER & SEARCH ---
const handleSearch = debounce((value) => {
    router.get(route('admin.transactions.index'), 
        { search: value, status: props.filters.status }, 
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 500);

watch(search, (value) => handleSearch(value));

const filterStatus = (status) => {
    router.get(route('admin.transactions.index'), 
        { search: search.value, status: status }, 
        { preserveState: true, preserveScroll: true }
    );
};

// --- FORMATTER ---
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// --- ACTION ---
const approveTransaction = (id) => {
    if (confirm('Setujui pembayaran ini?')) {
        router.post(route('admin.transactions.approve', id), {}, { preserveScroll: true });
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'paid':
        case 'success': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'failed':  return 'bg-rose-50 text-rose-700 border-rose-200';
        default: return 'bg-slate-100 text-slate-600 border-slate-200';
    }
};
</script>

<template>
    <Head title="Manajemen Transaksi" />

    <AuthenticatedLayout>
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Manajemen Transaksi</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Daftar riwayat pembayaran peserta tryout.</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input v-model="search" type="text" placeholder="Cari invoice atau nama..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all" />
                </div>

                <div class="flex gap-2 bg-slate-100 p-1 rounded-xl">
                    <button @click="filterStatus('')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', !filters.status ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Semua</button>
                    <button @click="filterStatus('pending')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters.status === 'pending' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Pending</button>
                    <button @click="filterStatus('paid')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters.status === 'paid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Paid</button>
                    <button @click="filterStatus('failed')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters.status === 'failed' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Failed</button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Invoice</th>
                                <th class="px-6 py-4">Peserta</th>
                                <th class="px-6 py-4">Item</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-slate-700 text-xs">{{ trx.invoice_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-[10px]">{{ trx.user?.name.charAt(0) }}</div>
                                        <div>
                                            <p class="font-semibold text-slate-900 text-sm">{{ trx.user?.name || '-' }}</p>
                                            <p class="text-xs text-slate-500">{{ trx.user?.email || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-xs truncate max-w-[150px]">{{ trx.tryout?.title || '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900 text-xs">{{ formatRupiah(trx.amount) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="getStatusClass(trx.status)" class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border">
                                        {{ trx.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="trx.status === 'pending'" @click="approveTransaction(trx.id)" 
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 shadow-sm transition active:scale-95">
                                        Approve
                                    </button>
                                    <span v-else class="text-xs text-slate-300 font-medium">Tuntas</span>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">Belum ada transaksi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-center mt-6" v-if="transactions.links.length > 3">
                <div class="flex gap-1 bg-white p-1 rounded-xl shadow-sm border border-slate-200">
                    <template v-for="(link, key) in transactions.links" :key="key">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'text-slate-600 border-transparent hover:bg-slate-50'"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>