<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object, // { users, tryouts, revenue, transactions }
    recent_purchases: Array
});

// Helper format Rupiah
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Command Center</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Overview & Analytics System v2.0</p>
                </div>
                <div class="flex gap-2">
                    <button class="p-3 bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-slate-900 transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl shadow-slate-200">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl"></div>
                        <span class="block text-[8px] font-black uppercase tracking-[0.3em] text-indigo-400 mb-4">Total Revenue</span>
                        <h3 class="text-2xl font-black italic tracking-tighter">{{ formatPrice(stats?.revenue ?? 0) }}</h3>
                        <div class="mt-6 flex items-center gap-2">
                            <span class="text-[9px] px-2 py-0.5 bg-emerald-500 rounded-full font-black text-white">▲ 12%</span>
                            <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest">vs Last Month</span>
                        </div>
                    </div>

                    <div v-for="(stat, index) in [
                        { label: 'Total Users', value: stats?.users ?? 0, color: 'bg-white', iconColor: 'text-indigo-600', iconBg: 'bg-indigo-50' },
                        { label: 'Active Tryouts', value: stats?.tryouts ?? 0, color: 'bg-white', iconColor: 'text-emerald-600', iconBg: 'bg-emerald-50' },
                        { label: 'Transactions', value: stats?.transactions ?? 0, color: 'bg-white', iconColor: 'text-amber-600', iconBg: 'bg-amber-50' },
                    ]" :key="index" class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="block text-[8px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">{{ stat.label }}</span>
                                <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ stat.value }}</h3>
                            </div>
                            <div :class="[stat.iconBg, stat.iconColor]" class="w-12 h-12 rounded-2xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div class="lg:col-span-8 bg-white border border-slate-100 rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col">
                        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-black text-slate-900 uppercase tracking-tight">Recent Transactions</h3>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Real-time payment data</p>
                            </div>
                            <Link :href="route('admin.transactions.index')" class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:text-slate-900 transition-colors">View All</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-slate-50/50">
                                        <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Customer</th>
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Amount</th>
                                        <th class="px-8 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="purchase in recent_purchases" :key="purchase.id" class="group hover:bg-slate-50/50 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ purchase.user?.name }}</div>
                                            <div class="text-[8px] text-slate-400 font-bold uppercase tracking-tighter">{{ purchase.tryout?.title }}</div>
                                        </td>
                                        <td class="px-6 py-5 text-center font-black text-xs text-slate-700">
                                            {{ formatPrice(purchase.amount) }}
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[8px] font-black uppercase tracking-widest">Success</span>
                                        </td>
                                    </tr>
                                    <tr v-if="recent_purchases?.length === 0">
                                        <td colspan="3" class="px-8 py-20 text-center">
                                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">No recent activity</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-8">
                        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm relative overflow-hidden">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6">System_Status</h3>
                            <div class="space-y-6">
                                <div v-for="(status, i) in [
                                    { label: 'Laravel Octane', value: 'Running', color: 'text-emerald-500' },
                                    { label: 'Swoole Worker', value: 'Active', color: 'text-emerald-500' },
                                    { label: 'Midtrans API', value: 'Connected', color: 'text-indigo-500' },
                                ]" :key="i" class="flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-600 uppercase">{{ status.label }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span :class="status.color" class="text-[9px] font-black uppercase tracking-widest">{{ status.value }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100">
                            <h3 class="text-lg font-black uppercase tracking-tight mb-2 leading-none">Management</h3>
                            <p class="text-[9px] font-bold text-indigo-200 uppercase tracking-widest mb-6">Quick shortlink</p>
                            <Link :href="route('admin.tryouts.index')" class="flex items-center justify-between group bg-white/10 p-4 rounded-2xl hover:bg-white/20 transition-all">
                                <span class="text-[10px] font-black uppercase tracking-widest">Go to Tryout Manager</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Paksa Font Tegak */
* { font-style: normal !important; }
.transition-all { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
</style>