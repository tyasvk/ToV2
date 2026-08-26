<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: Object,
    affiliate_code: String,
    affiliate_url: String,
    stats: Object,
    earning_history: Array, 
    withdrawals: Array,
    announcements: Array,
    weekly_leaderboard: { type: Array, default: () => [] },
    monthly_leaderboard: { type: Array, default: () => [] },
    competitionSettings: Object, 
    monthly_count: Number,
    target_limit: Number,
    special_bonus: Number,
    min_withdrawal: Number,
    commission_per_referral: Number,
    wallet_bonus_for_referral: Number,
    token_discount: Number,
    token_commission: Number,
    archiveMonths: Array,
    archiveWeeks: Array,
    selectedFilters: Object,
    flash: Object,
    errors: Object
});

const activeTab = ref('overview');
const copiedLink = ref(false);
const copiedToken = ref(false);
const isEditingBank = ref(!props.user?.bank_info);

// Filter Arsip Peringkat
const filterWeek = ref(props.selectedFilters?.week || '');
const filterMonth = ref(props.selectedFilters?.month || '');

const updateLeaderboardFilter = () => {
    router.get(route('affiliate.index'), {
        week: filterWeek.value,
        month: filterMonth.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const currentMonthName = computed(() => {
    return new Date().toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
});

const joinForm = useForm({});
const bankForm = useForm({
    bank_name: props.user?.bank_info?.bank_name || '',
    account_number: props.user?.bank_info?.account_number || '',
    account_name: props.user?.bank_info?.account_name || ''
});
const withdrawForm = useForm({ amount: '' });

const registerAffiliate = () => {
    joinForm.post(route('affiliate.register'), { preserveScroll: true });
};

const updateBankInfo = () => {
    bankForm.put(route('affiliate.bank.update'), { 
        preserveScroll: true,
        onSuccess: () => {
            isEditingBank.value = false;
        }
    });
};

const submitWithdrawal = () => {
    withdrawForm.post(route('affiliate.withdraw'), { 
        preserveScroll: true,
        onSuccess: () => {
            withdrawForm.reset('amount');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};

const copyLink = () => {
    if (!props.affiliate_url) return;
    navigator.clipboard.writeText(props.affiliate_url);
    copiedLink.value = true;
    setTimeout(() => { copiedLink.value = false; }, 2000);
};

const copyToken = () => {
    if (!props.affiliate_code) return;
    navigator.clipboard.writeText(props.affiliate_code);
    copiedToken.value = true;
    setTimeout(() => { copiedToken.value = false; }, 2000);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' }).replace('.', ':') + ' WIB';
};
</script>

<template>
    <Head title="Afiliasi & Kemitraan" />

    <AuthenticatedLayout>
        <!-- Background Default Apple (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 md:pt-10 space-y-6 md:space-y-8">
                
                <!-- Header Page -->
                <div class="space-y-2 max-w-3xl">
                    <h1 class="text-[28px] sm:text-[36px] font-bold text-[#1D1D1F] tracking-tight leading-tight">Afiliasi & Kemitraan</h1>
                    <p class="text-[14px] sm:text-[16px] text-[#86868B] font-medium leading-relaxed">
                        Bagikan tautan referensi Anda, bantu teman mendapatkan diskon, dan kumpulkan komisi tanpa batas waktu.
                    </p>
                </div>

                <!-- Flash Messages iCloud Style -->
                <div v-if="flash?.success" class="flex items-center gap-3 p-4 bg-[#E5F5EA] border border-[#34C759]/20 rounded-[16px] text-[#34C759] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <span class="text-[13px] font-semibold">{{ flash.success }}</span>
                </div>
                <div v-if="flash?.error" class="flex items-center gap-3 p-4 bg-[#FFF0F0] border border-[#FF3B30]/20 rounded-[16px] text-[#FF3B30] shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-[13px] font-semibold">{{ flash.error }}</span>
                </div>

                <!-- STATE 1: BELUM TERDAFTAR AFILIASI -->
                <div v-if="!affiliate_code" class="bg-white rounded-[32px] p-8 md:p-14 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-[#F0F4FF] text-[#007AFF] rounded-full flex items-center justify-center mb-6 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-[24px] sm:text-[30px] font-bold text-[#1D1D1F] tracking-tight mb-3">Gabung Program Kemitraan</h2>
                    <p class="text-[14px] sm:text-[15px] text-[#86868B] font-medium max-w-xl mx-auto leading-relaxed mb-8">
                        Aktifkan akun afiliasi Anda sekarang secara gratis dan nikmati keuntungan ganda dari setiap transaksi referral Anda.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 w-full max-w-3xl text-left mb-10">
                        <div class="bg-[#F5F5F7] rounded-[24px] p-6">
                            <h3 class="text-[16px] font-bold text-[#1D1D1F] mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-white text-[#007AFF] flex items-center justify-center text-[12px] shadow-sm">1</span>
                                Tautan Afiliasi
                            </h3>
                            <ul class="text-[13px] text-[#86868B] space-y-2 font-medium leading-relaxed list-inside list-disc">
                                <li>Komisi <span class="text-[#34C759] font-bold">{{ formatCurrency(commission_per_referral) }}</span> per transaksi.</li>
                                <li>Pendaftar dapat <span class="text-[#007AFF] font-bold">{{ formatCurrency(wallet_bonus_for_referral) }}</span>.</li>
                            </ul>
                        </div>
                        <div class="bg-[#F5F5F7] rounded-[24px] p-6">
                            <h3 class="text-[16px] font-bold text-[#1D1D1F] mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-white text-[#007AFF] flex items-center justify-center text-[12px] shadow-sm">2</span>
                                Token & Diskon
                            </h3>
                            <ul class="text-[13px] text-[#86868B] space-y-2 font-medium leading-relaxed list-inside list-disc">
                                <li>Diskon pembeli <span class="text-[#007AFF] font-bold">{{ formatCurrency(token_discount) }}</span>.</li>
                                <li>Komisi tambahan <span class="text-[#34C759] font-bold">{{ formatCurrency(token_commission) }}</span>.</li>
                            </ul>
                        </div>
                    </div>

                    <button @click="registerAffiliate" :disabled="joinForm.processing" class="px-8 py-3.5 bg-[#007AFF] text-white rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] shadow-[0_4px_14px_rgba(0,122,255,0.3)] disabled:opacity-50 min-w-[200px]">
                        {{ joinForm.processing ? 'Memproses...' : 'Daftar Afiliasi' }}
                    </button>
                </div>

                <!-- STATE 2: SUDAH TERDAFTAR AFILIASI -->
                <div v-else class="space-y-6 md:space-y-8">
                    
                    <!-- Segmented Control (Tabs) iOS Style -->
                    <div class="bg-[#E3E3E8]/60 p-1 rounded-[12px] flex overflow-x-auto no-scrollbar max-w-fit mx-auto sm:mx-0">
                        <button @click="activeTab = 'overview'" class="px-5 py-2 text-[13px] font-semibold rounded-[10px] transition-all whitespace-nowrap" :class="activeTab === 'overview' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                            Ikhtisar
                        </button>
                        <button @click="activeTab = 'withdraw'" class="px-5 py-2 text-[13px] font-semibold rounded-[10px] transition-all whitespace-nowrap" :class="activeTab === 'withdraw' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                            Tarik Saldo
                        </button>
                        <button @click="activeTab = 'competition'" class="px-5 py-2 text-[13px] font-semibold rounded-[10px] transition-all whitespace-nowrap" :class="activeTab === 'competition' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                            Kompetisi Reward
                        </button>
                        <button @click="activeTab = 'earnings'" class="px-5 py-2 text-[13px] font-semibold rounded-[10px] transition-all whitespace-nowrap" :class="activeTab === 'earnings' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                            Riwayat
                        </button>
                    </div>

                    <!-- TAB: OVERVIEW -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        
                        <!-- Top Banner Kompetisi (Apple Wallet Card Style) -->
                        <div v-if="competitionSettings" class="bg-gradient-to-br from-[#007AFF] to-[#5E5CE6] rounded-[24px] p-6 sm:p-8 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-5 relative overflow-hidden shadow-[0_8px_30px_rgba(0,122,255,0.2)]">
                            <div class="absolute right-0 top-0 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none -mr-10 -mt-10"></div>
                            
                            <div class="relative z-10 max-w-xl">
                                <h3 class="text-[20px] font-bold tracking-tight mb-1.5 drop-shadow-sm flex items-center gap-2">
                                    🏆 {{ competitionSettings.title || 'Kompetisi Afiliasi Sedang Berlangsung!' }}
                                </h3>
                                <p class="text-[13px] text-white/80 font-medium leading-relaxed whitespace-pre-line">
                                    {{ competitionSettings.description || 'Kejar target penggunaan token bulan ini dan menangkan reward spesial. Cek peringkat Anda sekarang!' }}
                                </p>
                            </div>
                            <button @click="activeTab = 'competition'" class="relative z-10 w-full sm:w-auto px-6 py-3 bg-white text-[#007AFF] rounded-full text-[13px] font-bold tracking-wide transition-all active:scale-[0.98] shadow-sm shrink-0">
                                Papan Peringkat
                            </button>
                        </div>

                        <!-- Link & Token Widget Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            
                            <!-- Link Widget -->
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 p-6 flex flex-col justify-between">
                                <div class="mb-5">
                                    <h4 class="text-[15px] font-bold text-[#1D1D1F] mb-1">Tautan Pendaftaran (Saldo Dompet)</h4>
                                    <p class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                                        Pendaftar via link ini mendapat Saldo Dompet <strong class="text-[#007AFF]">{{ formatCurrency(wallet_bonus_for_referral) }}</strong>. Anda komisi <strong class="text-[#34C759]">{{ formatCurrency(commission_per_referral) }}</strong>.
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1 bg-[#F5F5F7] rounded-[14px] px-4 py-3 text-[13px] text-[#1D1D1F] font-medium truncate select-all">
                                        {{ affiliate_url }}
                                    </div>
                                    <button @click="copyLink" :class="copiedLink ? 'bg-[#34C759] text-white' : 'bg-[#1D1D1F] text-white hover:bg-[#333336]'" class="px-5 py-3 rounded-full text-[13px] font-semibold transition-all active:scale-[0.98] shrink-0">
                                        {{ copiedLink ? 'Tersalin' : 'Salin Link' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Token Widget -->
                            <div class="bg-gradient-to-br from-[#F0F4FF] to-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-[#007AFF]/10 p-6 flex flex-col justify-between">
                                <div class="mb-5">
                                    <h4 class="text-[15px] font-bold text-[#007AFF] mb-1">Token Diskon & Group Buy</h4>
                                    <p class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                                        Saat checkout: Pembeli dipotong <strong class="text-[#007AFF]">{{ formatCurrency(token_discount) }}</strong>, Anda komisi <strong class="text-[#34C759]">{{ formatCurrency(token_commission) }}</strong>. Ekstra diskon untuk pembelian kelompok!
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1 bg-white border border-[#007AFF]/20 rounded-[14px] px-4 py-3 text-[14px] text-[#007AFF] font-bold tracking-widest text-center truncate select-all">
                                        {{ affiliate_code }}
                                    </div>
                                    <button @click="copyToken" :class="copiedToken ? 'bg-[#34C759] text-white' : 'bg-[#007AFF] text-white hover:bg-[#0062CC]'" class="px-5 py-3 rounded-full text-[13px] font-semibold transition-all active:scale-[0.98] shrink-0 shadow-[0_4px_10px_rgba(0,122,255,0.2)]">
                                        {{ copiedToken ? 'Tersalin' : 'Salin Token' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-5">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-semibold mb-1">Pemakai Bulan Ini</p>
                                <p class="text-[24px] font-bold text-[#1D1D1F] tracking-tight">{{ monthly_count }}</p>
                            </div>
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-5">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-semibold mb-1">Total Pemakai</p>
                                <p class="text-[24px] font-bold text-[#007AFF] tracking-tight">{{ stats.token_usages || 0 }}</p>
                            </div>
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-5">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-semibold mb-1">Bonus Reward</p>
                                <p class="text-[20px] font-bold text-[#AF52DE] tracking-tight">{{ formatCurrency(special_bonus) }}</p>
                            </div>
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-5">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-semibold mb-1">Total Pendapatan</p>
                                <p class="text-[20px] font-bold text-[#34C759] tracking-tight">{{ formatCurrency(stats.total_earnings || 0) }}</p>
                            </div>
                        </div>

                        <!-- Saldo Banner -->
                        <div class="bg-[#1D1D1F] rounded-[24px] p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-5 shadow-[0_8px_30px_rgba(0,0,0,0.1)]">
                            <div class="text-center sm:text-left">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-semibold mb-1">Saldo Komisi Tersedia</p>
                                <div class="flex flex-col sm:flex-row items-baseline gap-2">
                                    <p class="text-[32px] sm:text-[36px] font-bold text-[#34C759] tracking-tight leading-none">{{ formatCurrency(user?.affiliate_balance || 0) }}</p>
                                    <p class="text-[11px] text-[#86868B] font-medium">(Min. penarikan {{ formatCurrency(min_withdrawal) }})</p>
                                </div>
                            </div>
                            <button @click="activeTab = 'withdraw'" class="w-full sm:w-auto px-6 py-3.5 bg-white/10 hover:bg-white/20 text-white rounded-full text-[13px] font-semibold transition-all backdrop-blur-md active:scale-[0.98]">
                                Tarik Saldo
                            </button>
                        </div>
                    </div>

                    <!-- TAB: WITHDRAW -->
                    <div v-if="activeTab === 'withdraw'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Form Bank -->
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 p-6 sm:p-8">
                                <div class="flex justify-between items-center mb-5">
                                    <h3 class="text-[16px] font-bold text-[#1D1D1F]">Rekening Pencairan</h3>
                                    <button v-if="user?.bank_info" @click="isEditingBank = !isEditingBank" type="button" class="text-[12px] font-semibold text-[#007AFF] bg-[#F0F4FF] hover:bg-[#E0EBF5] px-3 py-1.5 rounded-full transition-colors">
                                        {{ isEditingBank ? 'Batal Edit' : 'Edit Rekening' }}
                                    </button>
                                </div>
                                
                                <div v-if="user?.bank_info && !isEditingBank" class="bg-[#F5F5F7] rounded-[16px] p-5">
                                    <p class="text-[10px] text-[#86868B] uppercase tracking-wider font-bold mb-1">Terhubung Ke</p>
                                    <div class="text-[16px] font-bold text-[#1D1D1F]">{{ user.bank_info.bank_name }}</div>
                                    <div class="text-[14px] font-mono text-[#86868B] mt-0.5">{{ user.bank_info.account_number }}</div>
                                    <div class="text-[13px] text-[#1D1D1F] font-medium mt-1">A.n. {{ user.bank_info.account_name }}</div>
                                </div>
                                
                                <form v-else @submit.prevent="updateBankInfo" class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[12px] font-semibold text-[#86868B] mb-1.5">Nama Bank / Dompet Digital</label>
                                            <input v-model="bankForm.bank_name" type="text" placeholder="BCA / Dana / Gopay" required class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[12px] px-4 py-3 text-[14px] font-medium text-[#1D1D1F] transition-all outline-none" />
                                        </div>
                                        <div>
                                            <label class="block text-[12px] font-semibold text-[#86868B] mb-1.5">Nomor Rekening / No. HP</label>
                                            <input v-model="bankForm.account_number" type="text" placeholder="Masukkan Nomor" required class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[12px] px-4 py-3 text-[14px] font-medium text-[#1D1D1F] transition-all outline-none" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[12px] font-semibold text-[#86868B] mb-1.5">Atas Nama Pemilik</label>
                                        <input v-model="bankForm.account_name" type="text" placeholder="Sesuai buku tabungan" required class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 rounded-[12px] px-4 py-3 text-[14px] font-medium text-[#1D1D1F] transition-all outline-none" />
                                    </div>
                                    <button type="submit" :disabled="bankForm.processing" class="w-full py-3.5 bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] disabled:opacity-50 mt-2">
                                        {{ bankForm.processing ? 'Menyimpan...' : 'Simpan Rekening' }}
                                    </button>
                                </form>
                            </div>

                            <!-- Form Penarikan -->
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 p-6 sm:p-8">
                                <h3 class="text-[16px] font-bold text-[#1D1D1F] mb-5">Pengajuan Tarik Saldo</h3>
                                <div class="bg-[#F5F5F7] rounded-[16px] p-4 text-[13px] space-y-2 mb-5">
                                    <div class="flex justify-between items-center"><span class="text-[#86868B] font-medium">Saldo Tersedia</span><span class="font-bold text-[#1D1D1F]">{{ formatCurrency(user?.affiliate_balance || 0) }}</span></div>
                                    <div class="flex justify-between items-center"><span class="text-[#86868B] font-medium">Batas Minimal Tarik</span><span class="font-bold text-[#007AFF]">{{ formatCurrency(min_withdrawal) }}</span></div>
                                </div>
                                <form @submit.prevent="submitWithdrawal" class="space-y-4">
                                    <div>
                                        <label class="block text-[12px] font-semibold text-[#86868B] mb-1.5">Jumlah Nominal (Rp)</label>
                                        <input v-model="withdrawForm.amount" type="number" placeholder="Contoh: 30000" class="w-full bg-[#F5F5F7] border-transparent focus:bg-white focus:border-[#34C759] focus:ring-2 focus:ring-[#34C759]/20 rounded-[12px] px-4 py-3 text-[14px] font-bold text-[#34C759] transition-all outline-none" />
                                        <p v-if="withdrawForm.errors.amount" class="text-[11px] text-[#FF3B30] mt-1.5 font-medium">{{ withdrawForm.errors.amount }}</p>
                                    </div>
                                    <button type="submit" :disabled="withdrawForm.processing || !user?.bank_info || (user?.affiliate_balance < min_withdrawal)" class="w-full py-3.5 bg-[#34C759] hover:bg-[#2EAF4E] text-white rounded-full text-[14px] font-semibold transition-all active:scale-[0.98] disabled:opacity-50 mt-2 shadow-[0_4px_14px_rgba(52,199,89,0.3)]">
                                        {{ withdrawForm.processing ? 'Memproses...' : 'Tarik Saldo Sekarang' }}
                                    </button>
                                    <p v-if="!user?.bank_info" class="text-[11px] text-[#FF3B30] text-center font-medium mt-3">Harap simpan rekening pencairan di atas terlebih dahulu.</p>
                                </form>
                            </div>
                        </div>

                        <!-- Riwayat Penarikan -->
                        <div class="lg:col-span-1">
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5 p-6">
                                <h4 class="text-[13px] font-bold text-[#86868B] uppercase tracking-wider mb-4">Status Pencairan</h4>
                                <div v-if="withdrawals.length === 0" class="text-center py-10 text-[13px] text-[#86868B] font-medium">
                                    Belum ada riwayat penarikan.
                                </div>
                                <div v-else class="flex flex-col gap-4">
                                    <div v-for="wd in withdrawals" :key="wd.id" class="border-b border-black/5 pb-4 last:border-0 last:pb-0">
                                        <div class="flex items-start justify-between gap-2 mb-1.5">
                                            <span class="text-[10px] text-[#86868B] font-semibold">{{ formatDate(wd.created_at) }}</span>
                                            <span :class="wd.status === 'approved' ? 'text-[#34C759] bg-[#E5F5EA]' : wd.status === 'pending' ? 'text-[#FF9500] bg-[#FFF9E6]' : 'text-[#FF3B30] bg-[#FFF0F0]'" class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">
                                                {{ wd.status === 'approved' ? 'Berhasil' : wd.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
                                            </span>
                                        </div>
                                        <div class="text-[13px] text-[#1D1D1F] font-bold truncate">{{ wd.bank_name }}</div>
                                        <div class="text-[14px] font-bold text-[#1D1D1F] mt-1">{{ formatCurrency(wd.amount) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: COMPETITION -->
                    <div v-if="activeTab === 'competition'" class="space-y-6">
                        
                        <!-- Header Banner -->
                        <div class="bg-gradient-to-br from-[#007AFF] to-[#5E5CE6] rounded-[24px] p-6 sm:p-10 shadow-[0_8px_30px_rgba(0,122,255,0.2)] text-white relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none -mr-20 -mt-20"></div>
                            <div class="relative z-10">
                                <h3 class="text-[22px] sm:text-[28px] font-bold tracking-tight mb-2">
                                    {{ competitionSettings?.title || 'Kompetisi Penggunaan Token' }}
                                </h3>
                                <p class="text-[14px] sm:text-[15px] text-white/80 font-medium leading-relaxed max-w-2xl whitespace-pre-line mb-4">
                                    {{ competitionSettings?.description || 'Raih target penggunaan token terbanyak setiap minggu dan bulan untuk mendapatkan hadiah saldo khusus secara langsung!' }}
                                </p>
                                <span class="inline-block bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-[8px] text-[11px] font-bold tracking-wider uppercase">
                                    Periode: {{ currentMonthName }}
                                </span>
                            </div>
                        </div>

                        <!-- Links & Token Split -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="bg-white border border-black/5 rounded-[24px] p-6 shadow-sm">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-bold mb-3">Tautan Referensi</p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1 bg-[#F5F5F7] rounded-[14px] px-4 py-3 text-[13px] text-[#1D1D1F] font-medium select-all truncate">
                                        {{ affiliate_url }}
                                    </div>
                                    <button @click="copyLink" :class="copiedLink ? 'bg-[#34C759] text-white' : 'bg-[#1D1D1F] text-white hover:bg-[#333336]'" class="px-5 py-3 rounded-full text-[13px] font-semibold transition-all active:scale-[0.98] shrink-0">
                                        {{ copiedLink ? 'Tersalin' : 'Salin' }}
                                    </button>
                                </div>
                            </div>
                            <div class="bg-white border border-black/5 rounded-[24px] p-6 shadow-sm">
                                <p class="text-[11px] text-[#86868B] uppercase tracking-wider font-bold mb-3">Token Promo</p>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <div class="flex-1 bg-[#F0F4FF] text-[#007AFF] border border-[#007AFF]/20 rounded-[14px] px-4 py-3 text-[14px] font-bold text-center tracking-widest select-all truncate">
                                        {{ affiliate_code }}
                                    </div>
                                    <button @click="copyToken" :class="copiedToken ? 'bg-[#34C759] text-white' : 'bg-[#007AFF] text-white hover:bg-[#0062CC]'" class="px-5 py-3 rounded-full text-[13px] font-semibold transition-all active:scale-[0.98] shrink-0">
                                        {{ copiedToken ? 'Tersalin' : 'Salin' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Papan Peringkat -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            <!-- Mingguan -->
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-6">
                                <div class="flex items-center justify-between mb-5 pb-3 border-b border-black/5">
                                    <h3 class="text-[14px] font-bold text-[#1D1D1F]">Top 5 Mingguan</h3>
                                    <select v-model="filterWeek" @change="updateLeaderboardFilter" class="bg-[#F5F5F7] border-none text-[#1D1D1F] text-[12px] font-semibold rounded-[10px] px-3 py-1.5 focus:ring-0 cursor-pointer outline-none">
                                        <option v-for="week in archiveWeeks" :key="week.value" :value="week.value">{{ week.label }}</option>
                                    </select>
                                </div>
                                <div v-if="weekly_leaderboard.length > 0" class="space-y-3">
                                    <div v-for="(leader, index) in weekly_leaderboard" :key="'w'+index" class="flex items-center justify-between p-3 rounded-[16px] bg-[#F5F5F7]">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 flex items-center justify-center rounded-full text-[12px] font-bold" :class="index === 0 ? 'bg-[#FF9500] text-white' : index === 1 ? 'bg-[#8E8E93] text-white' : index === 2 ? 'bg-[#A2845E] text-white' : 'bg-white text-[#1D1D1F] shadow-sm'">
                                                {{ index + 1 }}
                                            </div>
                                            <span class="text-[14px] font-bold text-[#1D1D1F] truncate max-w-[150px]">
                                                {{ leader.name }}
                                                <span v-if="leader.name === user.name" class="text-[10px] text-[#007AFF] bg-[#F0F4FF] px-1.5 py-0.5 rounded font-semibold ml-1">(Anda)</span>
                                            </span>
                                        </div>
                                        <span class="text-[12px] font-bold text-[#86868B]">{{ leader.total }} Token</span>
                                    </div>
                                </div>
                                <div v-else class="text-center py-10 text-[13px] text-[#86868B] font-medium">Belum ada data minggu ini.</div>
                            </div>

                            <!-- Bulanan -->
                            <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-6">
                                <div class="flex items-center justify-between mb-5 pb-3 border-b border-black/5">
                                    <h3 class="text-[14px] font-bold text-[#1D1D1F]">Top 5 Bulanan</h3>
                                    <select v-model="filterMonth" @change="updateLeaderboardFilter" class="bg-[#F5F5F7] border-none text-[#1D1D1F] text-[12px] font-semibold rounded-[10px] px-3 py-1.5 focus:ring-0 cursor-pointer outline-none">
                                        <option v-for="month in archiveMonths" :key="month.value" :value="month.value">{{ month.label }}</option>
                                    </select>
                                </div>
                                <div v-if="monthly_leaderboard.length > 0" class="space-y-3">
                                    <div v-for="(leader, index) in monthly_leaderboard" :key="'m'+index" class="flex items-center justify-between p-3 rounded-[16px] bg-[#F5F5F7]">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 flex items-center justify-center rounded-full text-[12px] font-bold" :class="index === 0 ? 'bg-[#007AFF] text-white' : index === 1 ? 'bg-[#8E8E93] text-white' : index === 2 ? 'bg-[#A2845E] text-white' : 'bg-white text-[#1D1D1F] shadow-sm'">
                                                {{ index + 1 }}
                                            </div>
                                            <span class="text-[14px] font-bold text-[#1D1D1F] truncate max-w-[150px]">
                                                {{ leader.name }}
                                                <span v-if="leader.name === user.name" class="text-[10px] text-[#007AFF] bg-[#F0F4FF] px-1.5 py-0.5 rounded font-semibold ml-1">(Anda)</span>
                                            </span>
                                        </div>
                                        <span class="text-[12px] font-bold text-[#86868B]">{{ leader.total }} Token</span>
                                    </div>
                                </div>
                                <div v-else class="text-center py-10 text-[13px] text-[#86868B] font-medium">Belum ada data bulan ini.</div>
                            </div>

                        </div>

                        <!-- Tabel Riwayat Pemenang -->
                        <div class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-6">
                            <h3 class="text-[14px] font-bold text-[#1D1D1F] mb-4">Riwayat Pemenang Kompetisi</h3>
                            <div v-if="announcements && announcements.length > 0" class="overflow-x-auto no-scrollbar">
                                <table class="w-full text-left border-collapse text-[13px]">
                                    <thead>
                                        <tr class="text-[11px] text-[#86868B] uppercase tracking-wider border-b border-black/5">
                                            <th class="py-3 px-2 font-semibold text-center w-16">Peringkat</th>
                                            <th class="py-3 px-2 font-semibold">Nama Mitra</th>
                                            <th class="py-3 px-2 font-semibold">Periode</th>
                                            <th class="py-3 px-2 font-semibold text-center">Token</th>
                                            <th class="py-3 px-2 font-semibold text-right">Kategori</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-black/5 text-[#1D1D1F]">
                                        <tr v-for="ann in announcements" :key="ann.id" class="hover:bg-[#F5F5F7] transition-colors">
                                            <td class="py-3 px-2 text-center font-bold">
                                                <span v-if="ann.rank === 1" class="text-[#FF9500]">🥇 1</span>
                                                <span v-else-if="ann.rank === 2" class="text-[#8E8E93]">🥈 2</span>
                                                <span v-else-if="ann.rank === 3" class="text-[#A2845E]">🥉 3</span>
                                                <span v-else>{{ ann.rank }}</span>
                                            </td>
                                            <td class="py-3 px-2">
                                                <div class="font-bold">{{ ann.user?.name || 'Mitra Afiliasi' }}</div>
                                                <div class="text-[11px] text-[#86868B] mt-0.5">{{ formatDate(ann.created_at) }}</div>
                                            </td>
                                            <td class="py-3 px-2 font-medium text-[#86868B]">{{ ann.period }}</td>
                                            <td class="py-3 px-2 text-center font-bold">{{ ann.tokens }}</td>
                                            <td class="py-3 px-2 text-right">
                                                <span :class="ann.proof_payment === 'REWARD-WEEKLY' ? 'text-[#007AFF] bg-[#F0F4FF]' : 'text-[#34C759] bg-[#E5F5EA]'" class="font-bold px-2.5 py-1 rounded-[6px] text-[10px] uppercase">
                                                    {{ ann.proof_payment === 'REWARD-WEEKLY' ? 'Mingguan' : 'Bulanan' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-8 text-[13px] text-[#86868B] font-medium">Belum ada pemenang di periode ini.</div>
                        </div>

                    </div>

                    <!-- TAB: EARNINGS (RIWAYAT PENDAPATAN) -->
                    <div v-if="activeTab === 'earnings'" class="space-y-4">
                        <h3 class="text-[16px] font-bold text-[#1D1D1F] px-1">Riwayat Pendapatan Afiliasi</h3>
                        <div v-if="earning_history.length === 0" class="bg-white rounded-[24px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-black/5 p-12 text-center text-[14px] text-[#86868B] font-medium">
                            Belum ada komisi yang masuk. Bagikan link atau token Anda sekarang!
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="earn in earning_history" :key="earn.id" class="bg-white rounded-[20px] p-5 flex items-center justify-between gap-4 shadow-sm border border-black/5">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center text-[16px]" :class="earn.type.includes('Token') ? 'bg-[#F0F4FF]' : 'bg-[#E5F5EA]'">
                                        {{ earn.type.includes('Token') ? '🎫' : '👤' }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-[14px] font-bold text-[#1D1D1F] truncate">{{ earn.name }}</h4>
                                        <p class="text-[12px] text-[#86868B] font-medium mt-0.5 truncate">{{ earn.type }} &bull; {{ formatDate(earn.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[15px] font-bold text-[#34C759]">+{{ formatCurrency(earn.amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.animate-in { animation-duration: 0.6s; animation-fill-mode: both; }
</style>