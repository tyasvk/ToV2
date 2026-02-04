<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    transactions: Object,
    filters: Object
});

const filterForm = useForm({
    search: props.filters.search || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    month: props.filters.month || '',
    year: props.filters.year || new Date().getFullYear(),
});

const formatPrice = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);

const applyFilter = () => {
    filterForm.get(route('admin.affiliate.transactions'), { preserveState: true });
};

// Debounce search agar tidak spam request
watch(() => filterForm.search, debounce(() => {
    applyFilter();
}, 500));
</script>

<template>
    <Head title="Riwayat Transaksi Affiliate" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-10 px-4">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 mb-10">
                <div>
                    <h2 class="font-black text-4xl text-slate-900 tracking-tighter uppercase leading-none">🧾 Transaksi Affiliate</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-3">LOG KOMISI PARTNER NUSANTARA</p>
                </div>

                <div class="flex flex-wrap items-center gap-4 bg-white p-5 rounded-[3rem] border border-slate-100 shadow-xl w-full xl:w-auto">
                    <input v-model="filterForm.search" type="text" placeholder="CARI PARTNER / KODE..." class="border-none bg-slate-50 rounded-2xl text-[10px] font-black py-3 px-6 focus:ring-2 focus:ring-slate-900 w-full md:w-64 uppercase tracking-widest">
                    <button @click="applyFilter" class="bg-slate-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all ml-auto shadow-lg shadow-slate-200">
                        FILTER
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-10 py-6">Tanggal</th>
                                <th class="px-8 py-6">Pembeli</th>
                                <th class="px-8 py-6">Partner Affiliate</th>
                                <th class="px-8 py-6 text-right">Komisi</th>
                                <th class="px-10 py-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-10 py-8">
                                    <p class="font-black text-xs text-slate-900 uppercase leading-none">{{ new Date(trx.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) }}</p>
                                    <p class="text-[9px] text-slate-300 font-black mt-1 uppercase">{{ trx.order_id }}</p>
                                </td>
                                <td class="px-8 py-8 font-black text-xs uppercase text-slate-600">
                                    {{ trx.user?.name }}
                                </td>
                                <td class="px-8 py-8">
                                    <p class="font-black text-xs uppercase text-slate-900 leading-none">{{ trx.referrer?.name }}</p>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mt-1 block leading-none">{{ trx.referrer?.affiliate_code }}</span>
                                </td>
                                <td class="px-8 py-8 text-right font-black text-emerald-600 text-sm">
                                    {{ formatPrice(trx.affiliate_commission) }}
                                </td>
                                <td class="px-10 py-8 text-center">
                                    <span :class="trx.status === 'paid' || trx.status === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'" class="text-[8px] font-black px-3 py-1.5 rounded-lg border uppercase tracking-widest">
                                        {{ trx.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="5" class="px-10 py-24 text-center">
                                    <p class="text-[10px] text-slate-300 font-black uppercase tracking-[0.4em]">BELUM ADA DATA TRANSAKSI</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="transactions.links.length > 3" class="px-10 py-8 border-t border-slate-50 bg-slate-50/20 flex justify-center gap-2">
                    <template v-for="(link, k) in transactions.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm" :class="link.active ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" />
                        <span v-else v-html="link.label" class="px-5 py-3 text-[10px] font-black text-slate-200 uppercase tracking-widest bg-slate-50/50 rounded-2xl"></span>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>