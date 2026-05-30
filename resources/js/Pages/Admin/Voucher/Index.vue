<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';

defineProps({
    vouchers: Object
});

const page = usePage();

// Mengambil parameter aktif dari URL (Default sekarang adalah 'available' / Tersedia)
const urlParams = new URLSearchParams(window.location.search);
const activeTab = ref(urlParams.get('status') || 'available');
const perPage = ref(urlParams.get('per_page') || '10');

const form = useForm({
    amount: '',
    quantity: 1,
});

// Mengirim form pembuatan voucher baru
const submit = () => {
    form.post(route('admin.vouchers.store'), {
        onSuccess: () => {
            form.reset('amount', 'quantity');
            // Opsional: Otomatis pindah ke tab 'Tersedia' setelah generate sukses
            if (activeTab.value !== 'available') activeTab.value = 'available';
        },
    });
};

// Menghapus voucher kosong yang belum terpakai
const deleteVoucher = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus voucher ini?')) {
        router.delete(route('admin.vouchers.destroy', id), {
            preserveScroll: true
        });
    }
};

// Fungsi pemicu pembaruan filter tab dan limit per_page ke backend
const updateFilter = () => {
    router.get(route('admin.vouchers.index'), {
        status: activeTab.value,
        per_page: perPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Mengawasi perubahan pilihan tab atau baris per halaman
watch([activeTab, perPage], () => {
    updateFilter();
});

// Helper format Rupiah
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// Helper format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).replace('.', ':');
};
</script>

<template>
    <Head title="Kelola Voucher Saldo Dompet" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Kelola Voucher Saldo</h1>
                <p class="text-[11px] text-slate-500 font-normal tracking-wide mt-1">
                    Generate, pantau, dan hapus kode voucher sekali pakai untuk pengisian saldo dompet pengguna.
                </p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8 items-start">
                
                <div class="bg-white border border-slate-100 rounded-[1.5rem] p-6 shadow-xl shadow-slate-200/40 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                    
                    <header class="relative z-10">
                        <h3 class="text-sm font-medium text-slate-900 uppercase tracking-[0.1em]">Generate Voucher</h3>
                        <p class="mt-1 text-[10px] text-slate-400 font-normal leading-relaxed">
                            Buat serentak kode unik baru dengan nominal saldo tertentu.
                        </p>
                    </header>

                    <form @submit.prevent="submit" class="mt-6 space-y-5 relative z-10">
                        <div>
                            <label for="amount" class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Nominal Saldo (Rp)</label>
                            <input
                                id="amount"
                                type="number"
                                placeholder="Contoh: 50000"
                                class="w-full bg-slate-50 border border-slate-100 focus:border-rose-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors"
                                v-model="form.amount"
                                required
                                min="1000"
                            />
                            <InputError class="mt-2 text-[10px]" :message="form.errors.amount" />
                        </div>

                        <div>
                            <label for="quantity" class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Jumlah Voucher Pembuatan</label>
                            <input
                                id="quantity"
                                type="number"
                                class="w-full bg-slate-50 border border-slate-100 focus:border-rose-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors"
                                v-model="form.quantity"
                                min="1"
                                max="50"
                                required
                            />
                            <InputError class="mt-2 text-[10px]" :message="form.errors.quantity" />
                        </div>

                        <div class="pt-2">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="w-full px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all hover:bg-rose-600 active:scale-95 disabled:opacity-50 text-center shadow-lg shadow-slate-900/10"
                            >
                                {{ form.processing ? 'Memproses...' : 'Generate Kode' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white border border-slate-100 rounded-[1.5rem] p-6 shadow-xl shadow-slate-200/40 xl:col-span-2 space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-50 pb-4">
                        
                        <div class="flex bg-slate-50 p-1 rounded-xl w-full sm:w-auto">
                            <button 
                                @click="activeTab = 'available'"
                                :class="activeTab === 'available' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                class="flex-1 sm:flex-none px-4 py-2 text-[10px] uppercase tracking-widest rounded-lg transition-all text-center"
                            >
                                Voucher Tersedia
                            </button>
                            <button 
                                @click="activeTab = 'used'"
                                :class="activeTab === 'used' ? 'bg-white shadow-sm text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-700 font-medium'"
                                class="flex-1 sm:flex-none px-4 py-2 text-[10px] uppercase tracking-widest rounded-lg transition-all text-center"
                            >
                                Voucher Terpakai
                            </button>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-auto">
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Tampilkan:</span>
                            <select 
                                v-model="perPage" 
                                class="bg-slate-50 border border-slate-100 text-slate-700 text-xs font-medium rounded-xl px-3 py-1.5 focus:border-rose-500 focus:bg-white focus:ring-0 transition-colors cursor-pointer"
                            >
                                <option value="10">10 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="100">100 Baris</option>
                            </select>
                        </div>

                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-medium uppercase tracking-[0.1em] text-[9px]">
                                    <th class="px-4 py-3.5 rounded-l-xl">Kode Voucher</th>
                                    <th class="px-4 py-3.5">Nominal</th>
                                    <th class="px-4 py-3.5 text-center">Status</th>
                                    <th class="px-4 py-3.5">Klaim Oleh</th>
                                    <th class="px-4 py-3.5">Waktu Klaim</th>
                                    <th class="px-4 py-3.5 text-right rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50/50">
                                <tr v-for="voucher in vouchers.data" :key="voucher.id" class="group hover:bg-slate-50/40 transition-colors duration-150 text-xs text-slate-600">
                                    <td class="px-4 py-4 font-mono font-bold text-slate-900 select-all tracking-wide">
                                        {{ voucher.code }}
                                    </td>
                                    <td class="px-4 py-4 font-medium text-slate-700">
                                        {{ formatCurrency(voucher.amount) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span 
                                            v-if="voucher.is_used" 
                                            class="inline-block px-2.5 py-0.5 text-[8px] font-medium uppercase tracking-widest rounded bg-rose-50 text-rose-600 border border-rose-100"
                                        >
                                            Terpakai
                                        </span>
                                        <span 
                                            v-else 
                                            class="inline-block px-2.5 py-0.5 text-[8px] font-medium uppercase tracking-widest rounded bg-emerald-50 text-emerald-600 border border-emerald-100"
                                        >
                                            Tersedia
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-slate-700 truncate max-w-[120px]">
                                        {{ voucher.user ? voucher.user.name : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-slate-400 font-normal">
                                        {{ voucher.used_at ? formatDate(voucher.used_at) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <button 
                                            v-if="!voucher.is_used" 
                                            @click="deleteVoucher(voucher.id)"
                                            class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg text-[9px] uppercase font-medium tracking-wider transition-colors"
                                        >
                                            Hapus
                                        </button>
                                        <span v-else class="text-[10px] text-slate-300 italic">No Action</span>
                                    </td>
                                </tr>
                                <tr v-if="vouchers.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-normal tracking-wide">
                                        Tidak ada data voucher saldo yang cocok dengan kriteria filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 flex justify-center gap-1.5 border-t border-slate-50" v-if="vouchers.links && vouchers.links.length > 3">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, index) in vouchers.links"
                            :key="index"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3 py-1.5 text-[10px] uppercase font-medium rounded-lg border transition-all"
                            :class="{
                                'bg-rose-50 text-rose-600 border-rose-100 shadow-sm': link.active,
                                'text-slate-500 border-slate-100 hover:bg-slate-50': !link.active && link.url,
                                'text-slate-300 border-transparent cursor-not-allowed opacity-40': !link.url
                            }"
                            :data="{ status: activeTab, per_page: perPage }"
                        />
                    </div>

                </div>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.animate-in {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>