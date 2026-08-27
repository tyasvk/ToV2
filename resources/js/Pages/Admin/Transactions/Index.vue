<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    transactions: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

// --- FILTER & SEARCH ---
const handleSearch = debounce((value) => {
    router.get(route('admin.transactions.index'), 
        { search: value, status: props.filters.status }, 
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 500);

watch(search, (value) => handleSearch(value));

const filterStatus = (status) => {
    router.get(route('admin.transactions.index'), 
        { search: search.value, status: status }, 
        { preserveState: true, preserveScroll: true }
    );
};

// --- FORMATTER ---
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// --- ACTION ---
const approveTransaction = (id) => {
    if (confirm('Setujui pembayaran ini?')) {
        router.post(route('admin.transactions.approve', id), {}, { preserveScroll: true });
    }
};

const rejectTransaction = (id) => {
    if (confirm('Tolak pembayaran ini? Transaksi akan dibatalkan.')) {
        router.post(route('admin.transactions.reject', id), {}, { preserveScroll: true });
    }
};

// Warna Status ala Apple
const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'paid':
        case 'success': return 'bg-[#E5F5EA] text-[#34C759] border-[#34C759]/20';
        case 'pending': return 'bg-[#FFF9E6] text-[#FF9500] border-[#FF9500]/20';
        case 'failed':  
        case 'deny':
        case 'cancel':
        case 'rejected': return 'bg-[#FFF0F0] text-[#FF3B30] border-[#FF3B30]/20';
        default: return 'bg-[#F5F5F7] text-[#86868B] border-black/5';
    }
};
</script>

<template>
    <Head title="Manajemen Transaksi" />

    <AuthenticatedLayout>
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-20 md:pb-28 font-sans animate-in fade-in duration-500 overflow-x-hidden">
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-4 sm:space-y-6">
                
                <!-- HEADER CARD -->
                <div class="bg-white p-5 sm:p-6 md:p-8 rounded-[24px] md:rounded-[32px] border border-black/5 shadow-sm relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6 w-full">
                    <div class="relative z-10 space-y-1">
                        <h1 class="text-[22px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight">Manajemen Transaksi</h1>
                        <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium">Daftar riwayat pembayaran peserta.</p>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-80 z-10 shrink-0">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#86868B]">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari invoice atau nama..." 
                            class="w-full bg-[#F5F5F7] border border-transparent rounded-[16px] pl-11 pr-4 py-3 sm:py-3.5 text-[13px] sm:text-[14px] font-medium text-[#1D1D1F] placeholder:text-[#86868B] focus:bg-white focus:ring-4 focus:ring-[#007AFF]/10 focus:border-[#007AFF]/40 outline-none transition-all shadow-inner" 
                        />
                    </div>
                </div>

                <!-- FILTER SEGMENTED CONTROL -->
                <div class="w-full max-w-[calc(100vw-2rem)] sm:max-w-full overflow-x-auto custom-scrollbar pb-2">
                    <div class="inline-flex bg-white p-1 sm:p-1.5 rounded-[16px] sm:rounded-[20px] border border-black/5 shadow-sm min-w-max">
                        <div class="flex gap-1 bg-[#F5F5F7] p-1 rounded-[12px] sm:rounded-[16px]">
                            <button @click="filterStatus('')" 
                                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-[10px] sm:rounded-[12px] text-[12px] sm:text-[13px] font-bold transition-all whitespace-nowrap', !filters.status ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                                Semua
                            </button>
                            <button @click="filterStatus('pending')" 
                                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-[10px] sm:rounded-[12px] text-[12px] sm:text-[13px] font-bold transition-all whitespace-nowrap', filters.status === 'pending' ? 'bg-white text-[#FF9500] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                                Pending
                            </button>
                            <button @click="filterStatus('paid')" 
                                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-[10px] sm:rounded-[12px] text-[12px] sm:text-[13px] font-bold transition-all whitespace-nowrap', filters.status === 'paid' ? 'bg-white text-[#34C759] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                                Berhasil
                            </button>
                            <button @click="filterStatus('failed')" 
                                :class="['px-4 sm:px-5 py-2 sm:py-2.5 rounded-[10px] sm:rounded-[12px] text-[12px] sm:text-[13px] font-bold transition-all whitespace-nowrap', filters.status === 'failed' ? 'bg-white text-[#FF3B30] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                                Ditolak
                            </button>
                        </div>
                    </div>
                </div>

                <!-- MAIN TABLE CARD -->
                <div class="bg-white rounded-[20px] sm:rounded-[24px] border border-black/5 shadow-[0_4px_24px_rgba(0,0,0,0.02)] overflow-hidden w-full max-w-[calc(100vw-2rem)] sm:max-w-full">
                    <div class="overflow-x-auto custom-scrollbar w-full">
                        <!-- Menggunakan min-w-[800px] agar di HP bisa di-scroll, tapi di Desktop pas dan tidak memicu scroll -->
                        <table class="w-full text-left min-w-[800px] table-fixed">
                            <thead class="bg-[#F5F5F7]/50 border-b border-black/5 text-[10px] sm:text-[11px] font-bold text-[#86868B] uppercase tracking-widest">
                                <tr>
                                    <th class="px-4 py-4 w-[15%]">Invoice</th>
                                    <th class="px-4 py-4 w-[25%]">Peserta</th>
                                    <th class="px-4 py-4 w-[25%]">Item Pembelian</th>
                                    <th class="px-4 py-4 w-[12%] text-right">Nominal</th>
                                    <th class="px-4 py-4 w-[8%] text-center">Status</th>
                                    <th class="px-4 py-4 w-[15%] text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5">
                                <tr v-for="trx in transactions.data" :key="trx.id" class="hover:bg-[#F0F4FF]/50 transition-colors group">
                                    
                                    <!-- Invoice -->
                                    <td class="px-4 py-4 align-top overflow-hidden">
                                        <p class="font-mono font-bold text-[#1D1D1F] text-[11px] sm:text-[12px] truncate">{{ trx.invoice_code }}</p>
                                        <p class="text-[10px] sm:text-[11px] text-[#86868B] font-medium mt-0.5">{{ formatDate(trx.created_at) }}</p>
                                    </td>
                                    
                                    <!-- Peserta -->
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex items-start gap-2.5 sm:gap-3">
                                            <div class="w-8 h-8 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center font-bold text-[11px] sm:text-[13px] border border-[#007AFF]/10 shrink-0 mt-0.5">
                                                {{ trx.user?.name ? trx.user.name.charAt(0).toUpperCase() : '?' }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-bold text-[#1D1D1F] text-[12px] sm:text-[13px] truncate">{{ trx.user?.name || 'User Terhapus' }}</p>
                                                <p class="text-[11px] text-[#86868B] truncate">{{ trx.user?.email || '-' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Item -->
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex flex-col">
                                            <p class="text-[11px] sm:text-[12px] font-semibold text-[#1D1D1F] break-words line-clamp-2">
                                                {{ trx.tryout?.title || (trx.metadata?.plan_name ? 'Membership: ' + trx.metadata.plan_name : 'Paket Belajar') }}
                                            </p>
                                            <p class="text-[9px] sm:text-[10px] font-medium text-[#86868B] uppercase tracking-wider mt-1">
                                                Mtd: {{ trx.payment_method || '-' }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Nominal -->
                                    <td class="px-4 py-4 text-right align-top">
                                        <p class="font-bold text-[#1D1D1F] text-[12px] sm:text-[13px]">{{ formatRupiah(trx.amount) }}</p>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-4 text-center align-top">
                                        <span :class="getStatusClass(trx.status)" class="px-2.5 py-1.5 rounded-full text-[9px] font-bold uppercase tracking-widest border shadow-sm inline-flex items-center justify-center">
                                            {{ trx.status }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-4 text-right align-top">
                                        <div v-if="trx.status === 'pending'" class="flex items-center justify-end gap-1.5 sm:gap-2">
                                            
                                            <!-- Tolak (Responsive: Ikon saja di Tablet, Teks + Ikon di Layar Besar) -->
                                            <button @click="rejectTransaction(trx.id)" 
                                                class="px-2.5 py-2 lg:px-3 lg:py-2 bg-[#FFF0F0] text-[#FF3B30] hover:bg-[#FF3B30] hover:text-white border border-[#FF3B30]/20 rounded-full text-[11px] font-bold shadow-sm transition-all active:scale-95 flex items-center justify-center gap-1.5 shrink-0"
                                                title="Tolak Pembayaran"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                <span class="hidden xl:inline">Tolak</span>
                                            </button>
                                            
                                            <!-- Terima -->
                                            <button @click="approveTransaction(trx.id)" 
                                                class="px-2.5 py-2 lg:px-3 lg:py-2 bg-[#34C759] text-white hover:bg-[#2EAD4E] rounded-full text-[11px] font-bold shadow-[0_4px_14px_rgba(52,199,89,0.3)] transition-all active:scale-95 flex items-center justify-center gap-1.5 shrink-0"
                                                title="Setujui Pembayaran"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                                <span class="hidden xl:inline">Terima</span>
                                            </button>
                                        </div>

                                        <span v-else class="text-[11px] text-[#C7C7CC] font-bold flex items-center justify-end gap-1.5 pt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="hidden lg:inline">Selesai</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="6" class="px-4 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-[#86868B]">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mb-2 opacity-50"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                            <p class="text-[13px] font-bold">Tidak ada transaksi ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div class="flex justify-center mt-6 w-full" v-if="transactions.links.length > 3">
                    <div class="flex flex-wrap justify-center gap-1.5 bg-white p-1.5 rounded-[24px] shadow-sm border border-black/5 max-w-[calc(100vw-2rem)] sm:max-w-full">
                        <template v-for="(link, key) in transactions.links" :key="key">
                            <Link v-if="link.url" :href="link.url" v-html="link.label"
                                class="min-w-[28px] h-[28px] sm:min-w-[32px] sm:h-[32px] px-2.5 sm:px-3 flex items-center justify-center rounded-full text-[11px] sm:text-[12px] font-bold transition-all shrink-0"
                                :class="link.active ? 'bg-[#1D1D1F] text-white shadow-md' : 'text-[#86868B] hover:bg-[#F5F5F7] hover:text-[#1D1D1F]'"
                            />
                        </template>
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
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #D1D1D6;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>