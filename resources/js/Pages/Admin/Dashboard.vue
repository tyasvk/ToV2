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
        paid: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        success: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        pending: 'bg-amber-100 text-amber-700 border-amber-200',
        failed: 'bg-red-100 text-red-700 border-red-200'
    };
    return classes[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50/50 py-8 font-sans text-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">Dashboard Overview</h1>
                        <p class="text-slate-500 text-sm mt-1">
                            Halo, <span class="font-bold text-[#004a87]">{{ user.name }}</span>! 👋 Berikut ringkasan hari ini, <span class="font-medium text-slate-700">{{ currentDate }}</span>.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <Link :href="route('admin.tryouts.index')" class="inline-flex items-center gap-2 bg-[#004a87] hover:bg-blue-800 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-200 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Kelola Tryout
                        </Link>

                        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            Kelola User
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-[#004a87] to-blue-600 rounded-2xl p-6 text-white shadow-xl shadow-blue-200 overflow-hidden relative group flex flex-col items-center justify-center text-center">
                        <div class="relative z-10 flex flex-col items-center w-full">
                            <div class="bg-white/20 p-1.5 rounded-lg mb-3 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-blue-100 text-xs font-bold uppercase tracking-wider mb-1">Total Pendapatan</p>
                                <h3 class="text-2xl font-black">{{ formatRupiah(stats.total_revenue) }}</h3>
                            </div>
                            <div class="mt-3 text-[10px] font-bold text-blue-100 bg-white/10 px-2 py-0.5 rounded-full">Update Real-time</div>
                        </div>
                        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition duration-500"></div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col items-center justify-center text-center">
                        <div class="bg-indigo-50 p-1.5 rounded-lg text-indigo-600 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">User Terdaftar</p>
                            <h3 class="text-2xl font-black text-slate-800">{{ stats.total_users }}</h3>
                        </div>
                        <Link :href="route('admin.users.index')" class="text-xs font-bold text-indigo-600 mt-3 hover:underline">Lihat Semua User &rarr;</Link>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col items-center justify-center text-center">
                        <div class="bg-purple-50 p-1.5 rounded-lg text-purple-600 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Tryout Aktif</p>
                            <h3 class="text-2xl font-black text-slate-800">{{ stats.active_tryouts }}</h3>
                        </div>
                        <Link :href="route('admin.tryouts.index')" class="text-xs font-bold text-purple-600 mt-3 hover:underline">Kelola Soal &rarr;</Link>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition relative overflow-hidden flex flex-col items-center justify-center text-center">
                        <div class="relative z-10 flex flex-col items-center w-full">
                            <div class="bg-amber-50 p-1.5 rounded-lg text-amber-500 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Perlu Approval</p>
                                <h3 class="text-2xl font-black text-amber-500">{{ stats.pending_transactions }}</h3>
                            </div>
                            <Link :href="route('admin.transactions.index')" class="text-xs font-bold text-amber-600 mt-3 hover:underline">Cek Transaksi &rarr;</Link>
                        </div>
                        <div v-if="stats.pending_transactions > 0" class="absolute top-3 right-3 w-3 h-3 bg-red-500 rounded-full animate-ping"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                                Transaksi Terakhir
                            </h3>
                            <Link :href="route('admin.transactions.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded-lg transition">Lihat Semua</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase text-slate-400 font-bold">
                                    <tr>
                                        <th class="px-6 py-3">User</th>
                                        <th class="px-6 py-3">Item</th>
                                        <th class="px-6 py-3">Total</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="trx in recentTransactions" :key="trx.id" class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-700">{{ trx.user?.name || 'Deleted User' }}</div>
                                            <div class="text-xs text-slate-400">{{ formatDate(trx.created_at) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">{{ trx.tryout?.title || 'Unknown Item' }}</td>
                                        <td class="px-6 py-4 font-bold text-slate-700">{{ formatRupiah(trx.amount) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border" :class="getStatusClass(trx.status)">{{ trx.status }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="recentTransactions.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">Belum ada data transaksi.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden h-fit">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="font-bold text-slate-800">Member Baru</h3>
                        </div>
                        <div class="divide-y divide-slate-100">
                            <div v-for="user in newUsers" :key="user.id" class="px-6 py-4 flex items-center gap-4 hover:bg-slate-50 transition">
                                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm border border-slate-200 shrink-0">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-700 truncate">{{ user.name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ user.email }}</p>
                                </div>
                                <div class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">USER</div>
                            </div>
                            <div v-if="newUsers.length === 0" class="p-8 text-center text-slate-400 italic text-xs">Belum ada user baru.</div>
                        </div>
                        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 text-center">
                            <Link :href="route('admin.users.index')" class="text-xs font-bold text-indigo-600 hover:underline">Kelola Semua User</Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>