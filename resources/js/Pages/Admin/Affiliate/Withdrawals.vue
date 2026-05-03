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
        customClass: { 
            popup: 'rounded-[2.5rem] p-6',
            title: 'font-medium uppercase tracking-tight text-xl',
            htmlContainer: 'text-slate-500 font-medium text-xs mt-2',
            confirmButton: 'rounded-xl font-medium uppercase tracking-widest text-[10px] py-4 px-8',
            cancelButton: 'rounded-xl font-medium uppercase tracking-widest text-[10px] py-4 px-8'
        }
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
        <div class="max-w-6xl mx-auto py-8 px-4 md:px-6 space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 px-1">
                <div class="space-y-1">
                    <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tighter uppercase leading-none">💰 Pencairan Dana</h2>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">PROSES PEMBAYARAN KOMISI PARTNER</p>
                </div>

                <div class="w-full lg:w-auto overflow-x-auto no-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
                    <div class="flex gap-2 bg-white p-1.5 rounded-2xl border border-slate-100 shadow-sm w-max lg:w-auto">
                        <button v-for="s in [null, 'pending', 'approved']" :key="s" 
                            @click="router.get(route('admin.affiliate.withdrawals'), { status: s })"
                            :class="(filters.status === s || (s === null && !filters.status)) ? 'bg-slate-900 text-white shadow-md' : 'text-slate-400'"
                            class="px-5 py-2.5 rounded-xl text-[9px] font-medium uppercase tracking-widest transition-all whitespace-nowrap">
                            {{ s || 'SEMUA' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="pb-20 space-y-6">
                
                <div class="hidden md:block bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                    <table class="w-full text-left table-auto">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[9px] font-medium text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5 w-1/3">Partner & Rekening</th>
                                <th class="px-8 py-5 text-center">Nominal</th>
                                <th class="px-8 py-5 text-center">Tanggal</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="wd in withdrawals.data" :key="wd.id" class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-8 py-8">
                                    <div class="space-y-3">
                                        <p class="font-medium text-xs text-slate-900 uppercase leading-none">{{ wd.user?.name }}</p>
                                        <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50 inline-block min-w-[220px]">
                                            <p class="text-[9px] font-medium text-indigo-600 uppercase tracking-widest leading-none mb-2">{{ wd.user?.bank_info?.bank_name }}</p>
                                            <p class="text-sm font-medium text-slate-900 leading-none tracking-tight select-all">{{ wd.user?.bank_info?.account_number }}</p>
                                            <p class="text-[9px] font-medium text-slate-400 mt-2 uppercase tracking-wide leading-none">A.N {{ wd.user?.bank_info?.account_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-center font-medium text-slate-900 text-sm tracking-tight whitespace-nowrap">
                                    {{ formatPrice(wd.amount) }}
                                </td>
                                <td class="px-8 py-8 text-center">
                                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">
                                        {{ new Date(wd.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) }}
                                    </p>
                                </td>
                                <td class="px-8 py-8 text-right">
                                    <button v-if="wd.status === 'pending'" @click="approve(wd.id, wd.amount, wd.user.name)" class="bg-emerald-500 text-white px-5 py-3 rounded-xl text-[9px] font-medium uppercase tracking-widest shadow-lg shadow-emerald-50 hover:bg-emerald-600 transition-all active:scale-95">
                                        Approve
                                    </button>
                                    <span v-else class="text-[9px] text-emerald-500 font-medium uppercase tracking-[0.2em] flex items-center justify-end gap-2">
                                        <span class="w-1 h-1 bg-emerald-500 rounded-full"></span> Selesai
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden space-y-4">
                    <div v-for="wd in withdrawals.data" :key="wd.id" class="bg-white border border-slate-100 rounded-[2rem] p-6 space-y-5 shadow-sm overflow-hidden">
                        
                        <div class="flex justify-between items-start gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-900 uppercase truncate leading-none">{{ wd.user?.name }}</p>
                                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-widest mt-2">{{ new Date(wd.created_at).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) }}</p>
                            </div>
                            <span :class="wd.status === 'pending' ? 'text-amber-500 border-amber-100 bg-amber-50' : 'text-emerald-500 border-emerald-100 bg-emerald-50'" class="px-3 py-1 rounded-xl text-[8px] font-medium uppercase tracking-widest border shrink-0">
                                {{ wd.status }}
                            </span>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[8px] text-indigo-500 font-medium uppercase tracking-widest">{{ wd.user?.bank_info?.bank_name }}</span>
                            </div>
                            <p class="text-lg font-medium text-slate-900 tracking-tight leading-none break-all">{{ wd.user?.bank_info?.account_number }}</p>
                            <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wide leading-none">A.N {{ wd.user?.bank_info?.account_name }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-5 border-t border-slate-50 gap-4">
                            <div class="flex flex-col shrink-0">
                                <span class="text-[8px] text-slate-400 uppercase tracking-widest mb-1.5">Nominal Cair</span>
                                <span class="text-sm font-medium text-slate-900 tracking-tight leading-none">{{ formatPrice(wd.amount) }}</span>
                            </div>
                            
                            <button 
                                v-if="wd.status === 'pending'" 
                                @click="approve(wd.id, wd.amount, wd.user.name)" 
                                class="bg-emerald-500 text-white text-[9px] font-medium uppercase tracking-widest px-6 py-4 rounded-2xl shadow-lg shadow-emerald-100 active:scale-95 flex-1 max-w-[140px]"
                            >
                                Approve
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="withdrawals.data.length === 0" class="py-20 text-center">
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.4em]">Tidak ada pengajuan pencairan</p>
                </div>

                <div v-if="withdrawals.links.length > 3" class="flex flex-wrap justify-center gap-2 pt-6">
                    <template v-for="(link, k) in withdrawals.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" 
                              class="px-4 py-2 rounded-xl text-[10px] font-medium uppercase tracking-widest transition-all" 
                              :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }

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