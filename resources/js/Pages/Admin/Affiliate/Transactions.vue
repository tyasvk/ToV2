<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
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

const formatPrice = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(num || 0);

const applyFilter = () => {
    filterForm.get(route('admin.affiliate.transactions'), { 
        preserveState: true,
        preserveScroll: true 
    });
};

watch(() => filterForm.search, debounce(() => {
    applyFilter();
}, 500));
</script>

<template>
    <Head title="Riwayat Transaksi Affiliate" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-8 px-4 md:py-10">
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10 px-1">
                <div class="space-y-1">
                    <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tight uppercase leading-none">Transaksi Affiliate</h2>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Log komisi partner nusantara</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs">🔍</span>
                        <input 
                            v-model="filterForm.search" 
                            type="text" 
                            placeholder="CARI PARTNER / KODE..." 
                            class="w-full border-none bg-white shadow-sm ring-1 ring-slate-100 rounded-xl text-[10px] font-medium py-3 pl-10 pr-4 focus:ring-2 focus:ring-slate-900 uppercase tracking-widest"
                        >
                    </div>
                    <button @click="applyFilter" class="w-full sm:w-auto bg-slate-900 text-white px-8 py-3 rounded-xl text-[10px] font-medium uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md active:scale-95">
                        FILTER
                    </button>
                </div>
            </div>

            <div class="space-y-4 pb-20">
                
                <div class="hidden md:block bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[9px] font-medium text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5">Tanggal & Order</th>
                                <th class="px-8 py-5">Pembeli</th>
                                <th class="px-8 py-5">Partner Affiliate</th>
                                <th class="px-8 py-5 text-right">Komisi</th>
                                <th class="px-8 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="font-medium text-xs text-slate-900 uppercase leading-none">{{ new Date(trx.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) }}</p>
                                    <p class="text-[9px] text-slate-400 font-medium mt-1.5 uppercase">{{ trx.order_id }}</p>
                                </td>
                                <td class="px-8 py-6 text-xs uppercase text-slate-600 font-medium">
                                    {{ trx.user?.name }}
                                </td>
                                <td class="px-8 py-6">
                                    <p class="font-medium text-xs uppercase text-slate-900 leading-none">{{ trx.referrer?.name }}</p>
                                    <span class="text-[9px] font-medium text-indigo-500 uppercase tracking-widest mt-1.5 block leading-none">{{ trx.referrer?.affiliate_code }}</span>
                                </td>
                                <td class="px-8 py-6 text-right font-medium text-emerald-600 text-sm tracking-tight">
                                    {{ formatPrice(trx.affiliate_commission) }}
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span :class="trx.status === 'paid' || trx.status === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'" class="text-[8px] font-medium px-3 py-1.5 rounded-lg border uppercase tracking-widest">
                                        {{ trx.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-4">
                    <div v-for="trx in transactions.data" :key="trx.id" class="bg-white p-6 rounded-[1.5rem] border border-slate-100 shadow-sm space-y-4">
                        <div class="flex justify-between items-start">
                            <div class="min-w-0">
                                <p class="text-[8px] text-slate-400 font-medium uppercase tracking-widest mb-1">Tanggal & Order</p>
                                <p class="text-[11px] font-medium text-slate-900 uppercase truncate">{{ new Date(trx.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) }}</p>
                                <p class="text-[9px] text-slate-400 font-medium mt-1 uppercase">{{ trx.order_id }}</p>
                            </div>
                            <span :class="trx.status === 'paid' || trx.status === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100'" class="text-[8px] font-medium px-2 py-1 rounded-md border uppercase tracking-widest">
                                {{ trx.status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <p class="text-[8px] text-slate-400 font-medium uppercase tracking-widest mb-1">Pembeli</p>
                                <p class="text-[10px] font-medium text-slate-600 uppercase truncate">{{ trx.user?.name }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] text-slate-400 font-medium uppercase tracking-widest mb-1">Komisi</p>
                                <p class="text-[11px] font-medium text-emerald-600 uppercase">{{ formatPrice(trx.affiliate_commission) }}</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-50">
                            <p class="text-[8px] text-slate-400 font-medium uppercase tracking-widest mb-1.5">Partner Affiliate</p>
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-medium text-slate-900 uppercase">{{ trx.referrer?.name }}</p>
                                <span class="text-[9px] font-medium text-indigo-500 uppercase tracking-widest">{{ trx.referrer?.affiliate_code }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="transactions.data.length === 0" class="bg-white py-20 rounded-[2rem] border border-slate-100 text-center">
                    <p class="text-[10px] text-slate-300 font-medium uppercase tracking-[0.4em]">Belum ada data transaksi</p>
                </div>

                <div v-if="transactions.links.length > 3" class="flex flex-wrap justify-center gap-2 pt-6">
                    <template v-for="(link, k) in transactions.links" :key="k">
                        <Link 
                            v-if="link.url" 
                            :href="link.url" 
                            v-html="link.label" 
                            class="px-4 py-2 rounded-xl text-[10px] font-medium uppercase tracking-widest transition-all shadow-sm" 
                            :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" 
                        />
                        <span v-else v-html="link.label" class="px-4 py-2 text-[10px] font-medium text-slate-200 uppercase tracking-widest bg-slate-50/50 rounded-xl"></span>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>