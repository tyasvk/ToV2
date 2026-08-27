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

// Palet warna status disesuaikan dengan standar iOS (Green, Orange, Red, Gray)
const getStatusClass = (status) => {
    const s = String(status).toLowerCase();
    if (['paid', 'success', 'settlement', 'lunas'].includes(s)) {
        return 'text-[#34C759] bg-[#E5F5EA] border-[#34C759]/20';
    }
    if (['pending'].includes(s)) {
        return 'text-[#FF9500] bg-[#FFF9E6] border-[#FF9500]/20';
    }
    if (['failed', 'expire', 'cancel', 'deny'].includes(s)) {
        return 'text-[#FF3B30] bg-[#FFF0F0] border-[#FF3B30]/20';
    }
    return 'text-[#86868B] bg-[#F5F5F7] border-black/5';
};
</script>

<template>
    <Head title="Admin Dashboard - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background Abu-abu Sistem iCloud (#F5F5F7) -->
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-20 md:pb-28 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 md:space-y-8">
                
                <!-- 1. HEADER (Welcome Banner - Apple ID Style) -->
                <div class="bg-white p-6 sm:p-8 md:p-10 rounded-[24px] sm:rounded-[32px] border border-black/5 shadow-[0_8px_30px_rgba(0,0,0,0.03)] flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
                    
                    <div class="relative z-10 space-y-1.5">
                        <h1 class="text-[28px] sm:text-[34px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                            Command Center
                        </h1>
                        <p class="text-[14px] sm:text-[15px] text-[#86868B] font-medium">
                            Halo, <span class="text-[#007AFF] font-bold">{{ user.name }}</span> — {{ currentDate }}
                        </p>
                    </div>

                    <div class="relative z-10 flex flex-col sm:flex-row gap-3 sm:gap-4 shrink-0">
                        <Link :href="route('admin.tryouts.index')" class="inline-flex items-center justify-center gap-2 bg-[#007AFF] hover:bg-[#0062CC] text-white px-6 py-3.5 rounded-full text-[13px] font-bold transition-all shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98]">
                            Kelola Tryout
                        </Link>
                        <Link :href="route('admin.users.index')" class="inline-flex items-center justify-center gap-2 bg-[#F5F5F7] hover:bg-[#E3E3E8] text-[#1D1D1F] px-6 py-3.5 rounded-full text-[13px] font-bold transition-all active:scale-[0.98]">
                            Kelola User
                        </Link>
                    </div>
                </div>

                <!-- 2. STATS WIDGETS -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
                    
                    <!-- Total Pendapatan (Premium Black Card) -->
                    <div class="bg-[#1D1D1F] rounded-[24px] p-6 shadow-[0_12px_40px_rgba(0,0,0,0.12)] relative overflow-hidden flex flex-col justify-between min-h-[140px] group">
                        <div class="relative z-10 flex items-center justify-between mb-4">
                            <p class="text-[11px] font-bold text-[#86868B] uppercase tracking-widest">Total Pendapatan</p>
                            <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                        <h3 class="relative z-10 text-[28px] sm:text-[32px] font-bold text-white tracking-tight leading-none truncate">
                            {{ formatRupiah(stats.total_revenue) }}
                        </h3>
                    </div>

                    <!-- Statistik Lainnya (White iOS Cards) -->
                    <div v-for="(val, label, index) in { 
                        'User Terdaftar': stats.total_users, 
                        'Tryout Aktif': stats.active_tryouts, 
                        'Perlu Approval': stats.pending_transactions 
                    }" :key="index" class="bg-white border border-black/5 p-6 rounded-[24px] shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[140px]">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[11px] font-bold text-[#86868B] uppercase tracking-widest">{{ label }}</p>
                            <div class="w-8 h-8 rounded-full bg-[#F5F5F7] flex items-center justify-center text-[#86868B]">
                                <!-- Ikon Dinamis Sederhana -->
                                <svg v-if="label === 'User Terdaftar'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                <svg v-else-if="label === 'Tryout Aktif'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <svg v-else class="w-4 h-4 text-[#FF9500]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                        </div>
                        <h3 :class="label === 'Perlu Approval' && val > 0 ? 'text-[#FF9500]' : 'text-[#1D1D1F]'" class="text-[28px] sm:text-[32px] font-bold tracking-tight leading-none">
                            {{ val }}
                        </h3>
                    </div>
                </div>

                <!-- 3. DATA TABLES SECTIONS -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-10">
                    
                    <!-- Transaksi Terakhir -->
                    <div class="lg:col-span-8 space-y-4">
                        <div class="bg-white border border-black/5 rounded-[24px] overflow-hidden shadow-[0_4px_24px_rgba(0,0,0,0.02)] flex flex-col h-full">
                            <div class="px-6 py-5 bg-white border-b border-black/5 flex items-center justify-between">
                                <h3 class="text-[15px] font-bold text-[#1D1D1F] tracking-tight">Transaksi Terakhir</h3>
                                <Link :href="route('admin.transactions.index')" class="text-[13px] font-semibold text-[#007AFF] hover:text-[#0062CC] transition-colors flex items-center gap-1">
                                    Lihat Semua
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </Link>
                            </div>
                            
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left whitespace-nowrap">
                                    <thead>
                                        <tr class="border-b border-black/5 bg-[#F5F5F7]/50">
                                            <th class="px-6 py-3.5 text-[10px] font-bold text-[#86868B] uppercase tracking-widest">Pembeli</th>
                                            <th class="px-6 py-3.5 text-[10px] font-bold text-[#86868B] uppercase tracking-widest">Item</th>
                                            <th class="px-6 py-3.5 text-[10px] font-bold text-[#86868B] uppercase tracking-widest text-right">Nominal</th>
                                            <th class="px-6 py-3.5 text-[10px] font-bold text-[#86868B] uppercase tracking-widest text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-black/5">
                                        <tr v-for="trx in recentTransactions" :key="trx.id" class="hover:bg-[#F5F5F7]/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <p class="text-[14px] font-bold text-[#1D1D1F] truncate max-w-[150px]">{{ trx.user?.name || 'User Terhapus' }}</p>
                                                <p class="text-[12px] text-[#86868B] font-medium mt-0.5">{{ formatDate(trx.created_at) }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-[13px] font-semibold text-[#1D1D1F] truncate max-w-[180px]">
                                                {{ trx.tryout?.title || 'Paket Belajar' }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-[14px] font-bold text-[#1D1D1F]">
                                                {{ formatRupiah(trx.amount) }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span :class="getStatusClass(trx.status)" class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border shadow-sm">
                                                    {{ trx.status }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="!recentTransactions.length">
                                            <td colspan="4" class="px-6 py-12 text-center text-[13px] text-[#86868B] font-medium">Belum ada transaksi saat ini.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Member Baru -->
                    <div class="lg:col-span-4 space-y-4">
                        <div class="bg-white border border-black/5 rounded-[24px] shadow-[0_4px_24px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col h-full">
                            <div class="px-6 py-5 bg-white border-b border-black/5">
                                <h3 class="text-[15px] font-bold text-[#1D1D1F] tracking-tight">Member Baru</h3>
                            </div>
                            
                            <div class="p-3 space-y-1 flex-1">
                                <div v-for="user in newUsers" :key="user.id" class="flex items-center gap-3.5 p-3 hover:bg-[#F5F5F7] rounded-[16px] transition-all group">
                                    <!-- Avatar iCloud Style -->
                                    <div class="w-10 h-10 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center text-[14px] font-bold shrink-0 border border-[#007AFF]/10">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[14px] font-bold text-[#1D1D1F] truncate leading-tight">{{ user.name }}</p>
                                        <p class="text-[12px] text-[#86868B] font-medium truncate mt-0.5">{{ user.email }}</p>
                                    </div>
                                </div>
                                <div v-if="!newUsers.length" class="p-10 text-center text-[13px] text-[#86868B] font-medium">
                                    Belum ada user yang bergabung.
                                </div>
                            </div>
                            
                            <div class="p-4 bg-white border-t border-black/5 text-center">
                                <Link :href="route('admin.users.index')" class="text-[12px] font-bold text-[#007AFF] hover:text-[#0062CC] transition-colors uppercase tracking-widest flex items-center justify-center gap-1">
                                    Kelola Semua User
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </Link>
                            </div>
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
    background: #D1D1D6;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>