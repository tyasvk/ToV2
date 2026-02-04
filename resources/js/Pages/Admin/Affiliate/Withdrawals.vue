<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    withdrawals: Object,
    filters: Object
});

const formatPrice = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num || 0);

const approve = (id, amount, name) => {
    Swal.fire({
        title: 'KONFIRMASI TRANSFER',
        text: `PASTIKAN ANDA SUDAH TRANSFER ${formatPrice(amount)} KE REKENING ${name.toUpperCase()}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        confirmButtonText: 'YA, SUDAH TRANSFER',
        cancelButtonText: 'BATAL',
        customClass: { popup: 'rounded-[2.5rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.affiliate.withdrawals.approve', id));
        }
    });
};
</script>

<template>
    <Head title="Manajemen Pencairan Dana" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-10 px-4">
            
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 mb-10">
                <div>
                    <h2 class="font-black text-4xl text-slate-900 tracking-tighter uppercase leading-none">💰 Pencairan Dana</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-3">PROSES PEMBAYARAN KOMISI PARTNER</p>
                </div>

                <div class="flex gap-2 bg-white p-2 rounded-3xl border border-slate-100 shadow-sm">
                    <button v-for="s in [null, 'pending', 'approved']" :key="s" 
                        @click="router.get(route('admin.affiliate.withdrawals'), { status: s })"
                        :class="(filters.status === s || (s === null && !filters.status)) ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400'"
                        class="px-6 py-2.5 rounded-2xl text-[9px] font-black uppercase tracking-widest transition-all">
                        {{ s || 'SEMUA' }}
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-10 py-6">Partner & Rekening</th>
                                <th class="px-8 py-6 text-center">Nominal</th>
                                <th class="px-8 py-6 text-center">Tanggal</th>
                                <th class="px-10 py-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="wd in withdrawals.data" :key="wd.id" class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-10 py-8">
                                    <p class="font-black text-xs text-slate-900 uppercase leading-none mb-2">{{ wd.user?.name }}</p>
                                    <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100 inline-block">
                                        <p class="text-[10px] font-black text-indigo-600 uppercase leading-none">{{ wd.user?.bank_info?.bank_name }}</p>
                                        <p class="text-[12px] font-black text-slate-900 mt-1 leading-none select-all">{{ wd.user?.bank_info?.account_number }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase leading-none">A.N {{ wd.user?.bank_info?.account_name }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-center font-black text-slate-900 text-sm">
                                    {{ formatPrice(wd.amount) }}
                                </td>
                                <td class="px-8 py-8 text-center">
                                    <p class="text-[10px] font-black text-slate-400 uppercase">{{ new Date(wd.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) }}</p>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <div v-if="wd.status === 'pending'">
                                        <button @click="approve(wd.id, wd.amount, wd.user.name)" class="bg-emerald-500 text-white px-6 py-3 rounded-2xl text-[9px] font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:bg-emerald-600">
                                            APPROVE & BAYAR
                                        </button>
                                    </div>
                                    <div v-else class="flex items-center justify-end gap-2 text-emerald-600 font-black text-[10px] uppercase">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> SUDAH DIBAYAR
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="withdrawals.data.length === 0">
                                <td colspan="4" class="px-10 py-24 text-center">
                                    <p class="text-[10px] text-slate-300 font-black uppercase tracking-[0.4em]">TIDAK ADA PENGAJUAN PENCAIRAN</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="withdrawals.links.length > 3" class="px-10 py-8 border-t border-slate-50 flex justify-center gap-2">
                    <template v-for="(link, k) in withdrawals.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm" :class="link.active ? 'bg-slate-900 text-white shadow-lg' : 'bg-white text-slate-400 border border-slate-100'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>