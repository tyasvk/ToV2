<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Object, // Data transaksi hasil paginate()
});

// Helper untuk format Rupiah
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

// Helper format Tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Helper Warna Status
const getStatusBadge = (status) => {
    const s = status.toLowerCase();
    if (s === 'success' || s === 'settlement' || s === 'capture') 
        return 'bg-emerald-50 text-emerald-600 border-emerald-100';
    if (s === 'pending') 
        return 'bg-amber-50 text-amber-600 border-amber-100';
    return 'bg-rose-50 text-rose-600 border-rose-100';
};
</script>

<template>
    <Head title="Riwayat Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Audit Log Transaksi</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Laporan Penjualan & Aktivitas Pembayaran</p>
                </div>
                <div class="flex gap-2">
                    <button @click="window.print()" class="px-4 py-2 bg-white border border-slate-100 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all shadow-sm">
                        Export PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail Pelanggan</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Metode</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu Transaksi</th>
                                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Nominal</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-slate-50/30 transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ trx.user_name }}</div>
                                        <div class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter mt-1">{{ trx.tryout_title }}</div>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="inline-flex items-center gap-2 px-2 py-1 bg-slate-100 rounded-lg">
                                            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">{{ trx.payment_method || 'SNAP' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-[10px] font-bold text-slate-500 uppercase">
                                        {{ formatDate(trx.created_at) }}
                                    </td>
                                    <td class="px-6 py-6 text-center font-black text-slate-900 text-sm italic tracking-tighter">
                                        {{ formatPrice(trx.amount) }}
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span :class="getStatusBadge(trx.status)" class="px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest shadow-sm">
                                            {{ trx.status }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="5" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="text-4xl mb-4 opacity-20">💰</div>
                                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.5em]">Belum Ada Data Penjualan</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-slate-50/30 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            Menampilkan {{ transactions.from ?? 0 }} - {{ transactions.to ?? 0 }} Dari {{ transactions.total }} Data
                        </span>
                        <div class="flex gap-2">
                            <Link 
                                v-for="(link, k) in transactions.links" 
                                :key="k"
                                :href="link.url || '#'"
                                v-html="link.label"
                                :class="[
                                    'px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all',
                                    link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 border border-slate-100 hover:border-slate-900'
                                ]"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
* { font-style: normal !important; }
.transition-all { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }

/* Sembunyikan Pagination Saat Print */
@media print {
    .bg-slate-50\/30, button, header { display: none !important; }
    .max-w-7xl { max-width: 100% !important; margin: 0 !important; }
    .rounded-[2.5rem] { border-radius: 0 !important; }
}
</style>