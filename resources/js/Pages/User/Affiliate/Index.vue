<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    affiliate_code: {
        type: String,
        default: ''
    },
    affiliate_url: {
        type: String,
        default: ''
    },
    stats: {
        type: Object,
        default: () => ({
            clicks: 0,
            registrations: 0,
            conversions: 0,
            total_earnings: 0,
            current_balance: 0
        })
    },
    referred_users: {
        type: Array,
        default: () => []
    },
    withdrawals: {
        type: Array,
        default: () => []
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

const activeTab = ref('overview');
const copied = ref(false);

const form = useForm({
    amount: '',
    bank_name: '',
    account_number: '',
    account_name: ''
});

const copyLink = () => {
    navigator.clipboard.writeText(props.affiliate_url);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const submitWithdrawal = () => {
    form.post(route('affiliate.withdraw'), {
        preserveScroll: true,
        onSuccess: () => form.reset()
    });
};

// Format mata uang Rupiah
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

// Format tanggal
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Program Afiliasi" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Program Afiliasi</h1>
                <p class="text-[11px] text-slate-500 font-medium tracking-wide mt-1">
                    Bagikan tautan Anda, undang teman, dan dapatkan komisi dari setiap transaksi.
                </p>
            </div>

            <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-xs font-medium">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 text-xs font-medium">
                {{ flash.error }}
            </div>

            <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm space-y-4">
                <div>
                    <p class="text-[9px] text-slate-400 uppercase tracking-[0.25em] font-medium mb-1">Tautan Afiliasi Anda</p>
                    <p class="text-xs text-slate-500 font-medium tracking-wide">Salin tautan di bawah ini untuk disebarkan ke media sosial atau teman.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs text-slate-600 font-medium select-all truncate tracking-tight flex items-center">
                        {{ affiliate_url }}
                    </div>
                    <button @click="copyLink" 
                            :class="copied ? 'bg-emerald-500 text-white' : 'bg-slate-900 text-white hover:bg-indigo-600'"
                            class="sm:px-6 py-3 rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all active:scale-95 text-center shrink-0">
                        {{ copied ? 'Tersalin' : 'Salin Tautan' }}
                    </button>
                </div>
            </div>

            <div class="flex border-b border-slate-200/60 p-0.5 gap-2 overflow-x-auto custom-scrollbar">
                <button @click="activeTab = 'overview'" 
                        :class="activeTab = 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="px-4 py-2 text-[10px] uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                    Ikhtisar
                </button>
                <button @click="activeTab = 'withdraw'" 
                        :class="activeTab = 'withdraw' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="px-4 py-2 text-[10px] uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                    Tarik Komisi
                </button>
                <button @click="activeTab = 'referrals'" 
                        :class="activeTab = 'referrals' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'"
                        class="px-4 py-2 text-[10px] uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                    Daftar Rujukan
                </button>
            </div>

            <div v-if="activeTab = 'overview'" class="space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                        <p class="text-[8px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1">Total Klik</p>
                        <p class="text-xl md:text-2xl font-medium text-slate-800 tracking-tighter">{{ stats.clicks || 0 }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                        <p class="text-[8px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1">Pendaftaran</p>
                        <p class="text-xl md:text-2xl font-medium text-blue-500 tracking-tighter">{{ stats.registrations || 0 }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                        <p class="text-[8px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1">Konversi Pembelian</p>
                        <p class="text-xl md:text-2xl font-medium text-pink-500 tracking-tighter">{{ stats.conversions || 0 }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                        <p class="text-[8px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1">Total Pendapatan</p>
                        <p class="text-xl md:text-2xl font-medium text-emerald-500 tracking-tighter">{{ formatCurrency(stats.total_earnings || 0) }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[1.5rem] p-5 md:p-6 shadow-md text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-[9px] text-slate-300 uppercase tracking-[0.2em] font-medium mb-1">Saldo Komisi Saat Ini</p>
                        <p class="text-2xl md:text-3xl font-medium tracking-tight text-emerald-400">{{ formatCurrency(stats.current_balance || 0) }}</p>
                    </div>
                    <button @click="activeTab = 'withdraw'" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.15em] transition-colors border border-white/10 text-center">
                        Tarik Komisi Sekarang
                    </button>
                </div>
            </div>

            <div v-if="activeTab = 'withdraw'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2 bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-medium text-slate-900 uppercase tracking-wider mb-2">Formulir Tarik Dana</h3>
                    
                    <form @submit.prevent="submitWithdrawal" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Jumlah Penarikan (Rp)</label>
                            <input v-model="form.amount" type="number" placeholder="Contoh: 50000" class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors" />
                            <p v-if="errors.amount" class="text-[10px] text-rose-500 mt-1 font-medium">{{ errors.amount }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Nama Bank / E-Wallet</label>
                                <input v-model="form.bank_name" type="text" placeholder="Contoh: BCA, Mandiri, Dana" class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors" />
                                <p v-if="errors.bank_name" class="text-[10px] text-rose-500 mt-1 font-medium">{{ errors.bank_name }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Nomor Rekening / HP</label>
                                <input v-model="form.account_number" type="text" placeholder="Masukkan nomor rekening" class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors" />
                                <p v-if="errors.account_number" class="text-[10px] text-rose-500 mt-1 font-medium">{{ errors.account_number }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Nama Pemilik Rekening</label>
                            <input v-model="form.account_name" type="text" placeholder="Nama sesuai buku tabungan" class="w-full bg-slate-50 border border-slate-100 focus:border-indigo-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs font-medium text-slate-800 transition-colors" />
                            <p v-if="errors.account_name" class="text-[10px] text-rose-500 mt-1 font-medium">{{ errors.account_name }}</p>
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full py-3 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all active:scale-95 text-center disabled:opacity-50">
                            {{ form.processing ? 'Memproses...' : 'Kirim Pengajuan' }}
                        </button>
                    </form>
                </div>

                <div class="space-y-3">
                    <h4 class="text-[10px] font-medium text-slate-400 uppercase tracking-[0.15em] px-1">Status Penarikan</h4>
                    
                    <div v-if="withdrawals.length === 0" class="bg-slate-50 border border-slate-100 rounded-xl p-6 text-center text-xs text-slate-400 font-medium">
                        Belum ada pengajuan.
                    </div>

                    <div v-else class="flex flex-col gap-2">
                        <div v-for="wd in withdrawals" :key="wd.id" class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-medium">{{ formatDate(wd.created_at) }}</span>
                                <span :class="wd.status === 'approved' ? 'text-emerald-600 bg-emerald-50' : wd.status === 'pending' ? 'text-amber-600 bg-amber-50' : 'text-rose-600 bg-rose-50'"
                                      class="text-[9px] font-medium uppercase tracking-wider px-2 py-0.5 rounded-md">
                                    {{ wd.status }}
                                </span>
                            </div>
                            <div class="flex items-baseline justify-between mt-1">
                                <span class="text-xs text-slate-500 font-medium tracking-tight">{{ wd.bank_name }}</span>
                                <span class="text-sm font-medium text-slate-900 tracking-tight">{{ formatCurrency(wd.amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab = 'referrals'" class="space-y-3">
                <h3 class="text-sm font-medium text-slate-900 uppercase tracking-wider px-1">Pengguna Terdaftar</h3>
                
                <div v-if="referred_users.length === 0" class="bg-white border border-slate-100 rounded-[1.5rem] p-10 text-center text-xs text-slate-400 font-medium">
                    Belum ada teman yang bergabung melalui tautan Anda.
                </div>

                <div v-else class="flex flex-col gap-2.5">
                    <div v-for="refUser in referred_users" :key="refUser.id" class="bg-white border border-slate-100 rounded-xl p-4 flex items-center justify-between gap-4 transition-all hover:shadow-sm">
                        <div class="min-w-0">
                            <h4 class="text-sm font-medium text-slate-900 truncate tracking-tight pr-2">{{ refUser.name }}</h4>
                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">Bergabung: {{ formatDate(refUser.created_at) }}</p>
                        </div>
                        
                        <div class="text-right shrink-0">
                            <span :class="refUser.has_purchased ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-slate-400 bg-slate-50 border-slate-100'"
                                  class="text-[9px] font-medium uppercase tracking-wider px-2.5 py-1 rounded-lg border">
                                {{ refUser.has_purchased ? 'Komisi Aktif' : 'Terdaftar' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.animate-in {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}
</style>