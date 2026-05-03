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
        paid: 'text-emerald-500 bg-emerald-50 border-emerald-100',
        success: 'text-emerald-500 bg-emerald-50 border-emerald-100',
        pending: 'text-amber-500 bg-amber-50 border-amber-100',
        failed: 'text-rose-500 bg-rose-50 border-rose-100'
    };
    return classes[status] || 'text-slate-500 bg-slate-50 border-slate-100';
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-8 md:space-y-12 animate-in fade-in duration-700">
            
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 px-1">
                <div class="space-y-2">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight uppercase italic leading-none">Command Center</h1>
                    <p class="text-[10px] md:text-[11px] text-slate-400 font-medium uppercase tracking-[0.3em]">
                        Halo, <span class="text-indigo-600">{{ user.name }}</span> — {{ currentDate }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('admin.tryouts.index')" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-slate-900 text-white px-6 py-3.5 rounded-2xl text-[10px] font-medium uppercase tracking-widest transition-all active:scale-95 shadow-lg shadow-slate-200">
                        Kelola Tryout
                    </Link>
                    <Link :href="route('admin.users.index')" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 bg-white border border-slate-100 text-slate-600 px-6 py-3.5 rounded-2xl text-[10px] font-medium uppercase tracking-widest transition-all active:scale-95">
                        Kelola User
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-100 relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-[9px] font-medium uppercase tracking-[0.2em] opacity-60 mb-2">Total Pendapatan</p>
                        <h3 class="text-2xl font-medium tracking-tighter">{{ formatRupiah(stats.total_revenue) }}</h3>
                        <div class="mt-4 inline-block px-2 py-1 bg-white/10 rounded-lg text-[8px] font-medium uppercase tracking-widest">Real-time Data</div>
                    </div>
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                </div>

                <div v-for="(val, label, index) in { 
                    'User Terdaftar': stats.total_users, 
                    'Tryout Aktif': stats.active_tryouts, 
                    'Perlu Approval': stats.pending_transactions 
                }" :key="index" class="bg-white border border-slate-100 p-8 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-500">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-[0.2em] mb-2">{{ label }}</p>
                    <h3 :class="label === 'Perlu Approval' && val > 0 ? 'text-amber-500 animate-pulse' : 'text-slate-900'" class="text-2xl font-medium tracking-tighter leading-none">
                        {{ val }}
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pb-20">
                
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-sm">
                        <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-[10px] font-medium text-slate-900 uppercase tracking-widest italic">Transaksi Terakhir</h3>
                            <Link :href="route('admin.transactions.index')" class="text-[9px] font-medium text-indigo-600 uppercase tracking-widest">Lihat Semua</Link>
                        </div>
                        
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-slate-50">
                                        <th class="px-8 py-5 text-[9px] font-medium text-slate-400 uppercase tracking-widest">Pembeli</th>
                                        <th class="px-8 py-5 text-[9px] font-medium text-slate-400 uppercase tracking-widest">Item</th>
                                        <th class="px-8 py-5 text-[9px] font-medium text-slate-400 uppercase tracking-widest text-right">Nominal</th>
                                        <th class="px-8 py-5 text-[9px] font-medium text-slate-400 uppercase tracking-widest text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="trx in recentTransactions" :key="trx.id" class="hover:bg-slate-50 transition-colors">
                                        <td class="px-8 py-5">
                                            <p class="text-xs font-medium text-slate-900 uppercase tracking-tight truncate max-w-[120px]">{{ trx.user?.name || 'User Terhapus' }}</p>
                                            <p class="text-[8px] text-slate-400 font-medium mt-1 uppercase">{{ formatDate(trx.created_at) }}</p>
                                        </td>
                                        <td class="px-8 py-5 text-[10px] font-medium text-slate-500 uppercase italic truncate max-w-[150px]">
                                            {{ trx.tryout?.title || 'Produk' }}
                                        </td>
                                        <td class="px-8 py-5 text-right text-xs font-medium text-slate-900">
                                            {{ formatRupiah(trx.amount) }}
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <span :class="getStatusClass(trx.status)" class="text-[8px] font-medium uppercase tracking-widest px-2.5 py-1 rounded-md border">
                                                {{ trx.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!recentTransactions.length">
                                        <td colspan="4" class="px-8 py-16 text-center text-[9px] text-slate-300 font-medium uppercase tracking-[0.3em] italic">Belum ada transaksi</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="px-8 py-6 bg-slate-50/50 border-b border-slate-100">
                            <h3 class="text-[10px] font-medium text-slate-900 uppercase tracking-widest italic">Member Baru</h3>
                        </div>
                        <div class="p-2 space-y-1 flex-1">
                            <div v-for="user in newUsers" :key="user.id" class="flex items-center gap-4 p-4 hover:bg-slate-50 rounded-3xl transition-all group">
                                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xs font-medium shadow-lg shrink-0 group-hover:bg-indigo-600 transition-colors">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-medium text-slate-900 uppercase tracking-tight truncate">{{ user.name }}</p>
                                    <p class="text-[9px] text-slate-400 font-medium truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <div v-if="!newUsers.length" class="p-12 text-center text-[9px] text-slate-300 font-medium uppercase tracking-widest italic leading-relaxed">
                                Belum ada user bergabung.
                            </div>
                        </div>
                        <div class="p-6 bg-slate-50/50 border-t border-slate-50 text-center">
                            <Link :href="route('admin.users.index')" class="text-[9px] font-medium text-indigo-600 uppercase tracking-widest border-b border-indigo-100 pb-0.5">Kelola Semua User</Link>
                        </div>
                    </div>
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

.custom-scrollbar::-webkit-scrollbar {
    height: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #F1F5F9;
    border-radius: 10px;
}
</style>