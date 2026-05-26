<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentTransactions: Array,
    newUsers: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
const currentDate = new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

const getStatusClass = (status) => {
    const classes = {
        paid: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        success: 'text-emerald-600 bg-emerald-50 border-emerald-200',
        pending: 'text-amber-600 bg-amber-50 border-amber-200',
        failed: 'text-rose-600 bg-rose-50 border-rose-200'
    };
    return classes[status] || 'text-slate-600 bg-slate-50 border-slate-200';
};
</script>

<template>
    <Head title="Admin Dashboard - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-1.5">
                        <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Command Center</h1>
                        <p class="text-xs md:text-sm text-slate-500 font-medium">
                            Halo, <span class="text-blue-600 font-semibold">{{ user.name }}</span> — {{ currentDate }}
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 mt-2 md:mt-0">
                        <Link :href="route('admin.tryouts.index')" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center">
                            Kelola Tryout
                        </Link>
                        <Link :href="route('admin.users.index')" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-600 px-6 py-2.5 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all active:scale-95 text-center shadow-sm">
                            Kelola User
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[10px] font-medium uppercase tracking-widest opacity-80">Total Pendapatan</p>
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-semibold tracking-tight">{{ formatRupiah(stats.total_revenue) }}</h3>
                    </div>
                </div>

                <div v-for="(val, label, index) in { 
                    'User Terdaftar': stats.total_users, 
                    'Tryout Aktif': stats.active_tryouts, 
                    'Perlu Approval': stats.pending_transactions 
                }" :key="index" class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-3">{{ label }}</p>
                    <h3 :class="label === 'Perlu Approval' && val > 0 ? 'text-amber-500' : 'text-slate-900'" class="text-2xl font-semibold tracking-tight leading-none">
                        {{ val }}
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-10">
                
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col h-full">
                        <div class="px-6 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Transaksi Terakhir</h3>
                            <Link :href="route('admin.transactions.index')" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors uppercase tracking-wider">Lihat Semua</Link>
                        </div>
                        
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left whitespace-nowrap">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50/30">
                                        <th class="px-6 py-4 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Pembeli</th>
                                        <th class="px-6 py-4 text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Item</th>
                                        <th class="px-6 py-4 text-[10px] font-semibold text-slate-500 uppercase tracking-widest text-right">Nominal</th>
                                        <th class="px-6 py-4 text-[10px] font-semibold text-slate-500 uppercase tracking-widest text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="trx in recentTransactions" :key="trx.id" class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-slate-900 truncate max-w-[150px]">{{ trx.user?.name || 'User Terhapus' }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ formatDate(trx.created_at) }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-slate-600 truncate max-w-[180px]">
                                            {{ trx.tryout?.title || 'Produk' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-slate-900">
                                            {{ formatRupiah(trx.amount) }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStatusClass(trx.status)" class="text-[10px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-md border">
                                                {{ trx.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!recentTransactions.length">
                                        <td colspan="4" class="px-6 py-12 text-center text-sm text-slate-400 font-medium">Belum ada transaksi saat ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-4">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="px-6 py-5 bg-slate-50/50 border-b border-slate-100">
                            <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">Member Baru</h3>
                        </div>
                        <div class="p-2 space-y-1 flex-1">
                            <div v-for="user in newUsers" :key="user.id" class="flex items-center gap-4 p-4 hover:bg-slate-50 rounded-xl transition-all group">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-sm font-semibold shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-slate-500 truncate mt-0.5">{{ user.email }}</p>
                                </div>
                            </div>
                            <div v-if="!newUsers.length" class="p-10 text-center text-sm text-slate-400 font-medium">
                                Belum ada user yang bergabung.
                            </div>
                        </div>
                        <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-center">
                            <Link :href="route('admin.users.index')" class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors uppercase tracking-wider">Kelola Semua User</Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>