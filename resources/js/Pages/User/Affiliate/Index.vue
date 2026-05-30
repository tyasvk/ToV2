<script setup>
import { ref, computed, watch } from 'vue';
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
    competitionSettings: Object, // Menampung judul & deskripsi dinamis dari admin
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
    <Head title="Program Afiliasi & Kemitraan" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Program Afiliasi & Kemitraan</h1>
                <p class="text-xs text-slate-500 font-medium tracking-wide mt-1">
                    Bagikan tautan/token Anda, bantu teman mendapatkan diskon & saldo gratis, dan kumpulkan komisi tanpa batas!
                </p>
            </div>

            <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-xs font-medium">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="p-4 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 text-xs font-medium">
                {{ flash.error }}
            </div>

            <div v-if="!affiliate_code" class="bg-white border border-slate-100 rounded-[1.5rem] p-6 sm:p-8 md:p-12 shadow-sm space-y-6">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-2 md:mb-4 border border-indigo-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-10 md:h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-center">
                    <h2 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Gabung Program Kemitraan</h2>
                    <p class="text-slate-500 text-xs md:text-sm max-w-xl mx-auto leading-relaxed mt-2 font-medium">
                        Aktifkan akun afiliasi Anda sekarang secara gratis dan nikmati keuntungan ganda dari setiap transaksi!
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto pt-2 md:pt-4">
                    <div class="bg-slate-50 rounded-xl p-4 md:p-5 border border-slate-100 flex flex-col gap-2">
                        <div class="text-indigo-600 font-medium text-base md:text-lg">1. Tautan Afiliasi (Link)</div>
                        <ul class="text-xs md:text-sm text-slate-600 space-y-1.5 list-disc pl-4 font-medium">
                            <li>Setiap pendaftar via link, Anda dapat komisi <span class="text-emerald-600 font-medium">{{ formatCurrency(commission_per_referral) }}</span>.</li>
                            <li>Pendaftar langsung mendapat Saldo Dompet <span class="text-indigo-600 font-medium">{{ formatCurrency(wallet_bonus_for_referral) }}</span>.</li>
                        </ul>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 md:p-5 border border-slate-100 flex flex-col gap-2">
                        <div class="text-indigo-600 font-medium text-base md:text-lg">2. Token & Diskon Grup</div>
                        <ul class="text-xs md:text-sm text-slate-600 space-y-1.5 list-disc pl-4 font-medium">
                            <li>Gunakan token saat checkout, pembeli dipotong <span class="text-indigo-600 font-medium">{{ formatCurrency(token_discount) }}</span>, Anda komisi <span class="text-emerald-600 font-medium">{{ formatCurrency(token_commission) }}</span>.</li>
                            <li>Pembeli berkelompok (2-5 org) dapat ekstra diskon otomatis hingga Rp 25.000.</li>
                        </ul>
                    </div>
                </div>

                <div class="text-center pt-2 md:pt-4">
                    <button @click="registerAffiliate" :disabled="joinForm.processing" class="w-full sm:w-auto px-6 md:px-8 py-3.5 md:py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[11px] uppercase font-medium tracking-[0.15em] transition-all active:scale-95 shadow-md shadow-indigo-100 disabled:opacity-50">
                        {{ joinForm.processing ? 'Memproses...' : 'Daftar Afiliasi Sekarang (Gratis)' }}
                    </button>
                </div>
            </div>

            <div v-else class="space-y-6 md:space-y-8">
                
                <div class="flex border-b border-slate-200/60 p-0.5 gap-2 overflow-x-auto custom-scrollbar">
                    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[10px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                        Ikhtisar
                    </button>
                    <button @click="activeTab = 'withdraw'" :class="activeTab === 'withdraw' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[10px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                        Tarik Saldo
                    </button>
                    <button @click="activeTab = 'earnings'" :class="activeTab === 'earnings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[10px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                        Riwayat Pendapatan
                    </button>
                    <button @click="activeTab = 'competition'" :class="activeTab === 'competition' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[10px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                        Kompetisi Reward
                    </button>
                </div>

                <div v-if="activeTab === 'overview'" class="space-y-4 md:space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                            <div class="mb-4">
                                <p class="text-[10px] text-slate-400 uppercase tracking-[0.2em] font-medium mb-1">Tautan Pendaftaran (Saldo Dompet)</p>
                                <p class="text-xs text-slate-500 font-normal leading-relaxed">
                                    Pendaftar via link ini langsung mendapat Saldo Dompet <span class="text-indigo-600 font-medium">{{ formatCurrency(wallet_bonus_for_referral) }}</span>. Anda mendapat komisi <span class="text-emerald-600 font-medium">{{ formatCurrency(commission_per_referral) }}</span> ketika pendaftar melakukan pembelian tryout pertama.
                                </p>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2.5 text-xs text-slate-600 font-medium select-all truncate">
                                    {{ affiliate_url }}
                                </div>
                                <button @click="copyLink" :class="copiedLink ? 'bg-emerald-500 text-white' : 'bg-slate-900 text-white hover:bg-indigo-600'" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-[10px] uppercase font-medium tracking-wider transition-all shrink-0">
                                    {{ copiedLink ? 'Tersalin' : 'Salin Link' }}
                                </button>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-100 rounded-[1.5rem] p-4 sm:p-5 shadow-sm flex flex-col justify-between">
                            <div class="mb-4">
                                <p class="text-[10px] text-indigo-400 uppercase tracking-[0.2em] font-medium mb-1">Token Diskon & Group Buy</p>
                                <p class="text-xs text-slate-600 font-normal leading-relaxed">
                                    Dimasukkan saat checkout: Pembeli dipotong harga <span class="text-indigo-600 font-medium">{{ formatCurrency(token_discount) }}</span>, Anda komisi <span class="text-emerald-600 font-medium">{{ formatCurrency(token_commission) }}</span>. Ekstra diskon hingga Rp 25.000 untuk kelompok!
                                </p>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="flex-1 bg-white border border-indigo-200 rounded-xl px-4 py-2.5 text-sm text-indigo-700 font-medium tracking-widest text-center select-all">
                                    {{ affiliate_code }}
                                </div>
                                <button @click="copyToken" :class="copiedToken ? 'bg-emerald-500 text-white' : 'bg-indigo-600 text-white hover:bg-indigo-700'" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-[10px] uppercase font-medium tracking-wider transition-all shrink-0">
                                    {{ copiedToken ? 'Tersalin' : 'Salin Token' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-3.5 md:p-4 shadow-sm flex flex-col justify-center">
                            <p class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-wider font-medium mb-1">Pemakai Token Bulan Ini</p>
                            <p class="text-lg md:text-xl font-medium text-slate-800 tracking-tighter">{{ monthly_count }} <span class="text-xs text-slate-400">/ {{ target_limit }}</span></p>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-3.5 md:p-4 shadow-sm flex flex-col justify-center">
                            <p class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-wider font-medium mb-1">Total Pemakai Token</p>
                            <p class="text-lg md:text-xl font-medium text-blue-500 tracking-tighter">{{ stats.token_usages || 0 }}</p>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-3.5 md:p-4 shadow-sm flex flex-col justify-center">
                            <p class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-wider font-medium mb-1">Bonus Diperoleh</p>
                            <p class="text-lg md:text-xl font-medium text-pink-500 tracking-tighter">{{ formatCurrency(special_bonus) }}</p>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-3.5 md:p-4 shadow-sm flex flex-col justify-center">
                            <p class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-wider font-medium mb-1">Total Pendapatan</p>
                            <p class="text-lg md:text-xl font-medium text-emerald-500 tracking-tighter">{{ formatCurrency(stats.total_earnings || 0) }}</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[1.5rem] p-5 md:p-6 shadow-md text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <p class="text-[9px] md:text-[10px] text-slate-300 uppercase tracking-[0.2em] font-medium mb-1">Saldo Komisi Saat Ini</p>
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-1 sm:gap-2">
                                <p class="text-2xl md:text-3xl font-medium tracking-tight text-emerald-400">{{ formatCurrency(user?.affiliate_balance || 0) }}</p>
                                <p class="text-[10px] text-slate-400 font-normal">(Min. penarikan {{ formatCurrency(min_withdrawal) }})</p>
                            </div>
                        </div>
                        <button @click="activeTab = 'withdraw'" class="w-full sm:w-auto px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.15em] transition-colors border border-white/10">
                            Pencairan Komisi
                        </button>
                    </div>
                </div>

                <div v-if="activeTab === 'withdraw'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm space-y-4">
                            <div class="flex justify-between items-center">
                                <h3 class="text-xs md:text-sm font-medium text-slate-800 uppercase tracking-widest">Rekening Pencairan</h3>
                                <div class="flex gap-2">
                                    <button v-if="user?.bank_info && !isEditingBank" @click="isEditingBank = true" type="button" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-medium uppercase tracking-wider px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">Edit Rekening</button>
                                    <button v-if="user?.bank_info && isEditingBank" @click="isEditingBank = false" type="button" class="text-[10px] text-slate-500 hover:text-slate-700 font-medium uppercase tracking-wider px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal Edit</button>
                                </div>
                            </div>
                            <div v-if="user?.bank_info && !isEditingBank" class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col gap-1.5 transition-all">
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest font-medium mb-1">Informasi Tersimpan</div>
                                <div class="text-sm font-medium text-slate-800">{{ user.bank_info.bank_name }}</div>
                                <div class="text-xs font-mono text-slate-600">{{ user.bank_info.account_number }}</div>
                                <div class="text-xs text-slate-600 font-normal">a.n {{ user.bank_info.account_name }}</div>
                            </div>
                            <form v-else @submit.prevent="updateBankInfo" class="space-y-4 mt-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Nama Bank / Dompet Digital</label>
                                        <input v-model="bankForm.bank_name" type="text" placeholder="BCA / Mandiri / Dana / Gopay" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl px-4 py-3 text-xs md:text-sm font-normal text-slate-800" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Nomor Rekening / No. HP</label>
                                        <input v-model="bankForm.account_number" type="text" placeholder="Masukkan Nomor" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl px-4 py-3 text-xs md:text-sm font-normal text-slate-800" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Atas Nama Pemilik</label>
                                    <input v-model="bankForm.account_name" type="text" placeholder="Sesuai aplikasi/buku tabungan" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl px-4 py-3 text-xs md:text-sm font-normal text-slate-800" />
                                </div>
                                <button type="submit" :disabled="bankForm.processing" class="w-full py-3.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[11px] uppercase font-medium tracking-[0.2em] transition-all disabled:opacity-50">
                                    {{ bankForm.processing ? 'Menyimpan Data...' : 'Simpan Data Bank' }}
                                </button>
                            </form>
                        </div>
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm space-y-4">
                            <h3 class="text-xs md:text-sm font-medium text-slate-800 uppercase tracking-widest">Pengajuan Tarik Saldo</h3>
                            <div class="bg-slate-50 rounded-xl p-4 text-xs md:text-sm space-y-2 border border-slate-100">
                                <div class="flex justify-between items-center"><span class="text-slate-500 font-normal">Saldo Tersedia:</span><span class="font-medium text-slate-800">{{ formatCurrency(user?.affiliate_balance || 0) }}</span></div>
                                <div class="flex justify-between items-center"><span class="text-slate-500 font-normal">Batas Minimal Tarik:</span><span class="font-medium text-indigo-600">{{ formatCurrency(min_withdrawal) }}</span></div>
                            </div>
                            <form @submit.prevent="submitWithdrawal" class="space-y-4 mt-2">
                                <div>
                                    <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Jumlah Nominal (Rp)</label>
                                    <input v-model="withdrawForm.amount" type="number" placeholder="Contoh: 30000" class="w-full bg-slate-50 border border-slate-200 focus:border-emerald-500 focus:ring-0 rounded-xl px-4 py-3 text-sm font-normal text-emerald-600" />
                                    <p v-if="withdrawForm.errors.amount" class="text-[10px] text-rose-500 mt-1 font-medium">{{ withdrawForm.errors.amount }}</p>
                                </div>
                                <button type="submit" :disabled="withdrawForm.processing || !user?.bank_info || (user?.affiliate_balance < min_withdrawal)" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] uppercase font-medium tracking-[0.2em] transition-all disabled:opacity-50">
                                    {{ withdrawForm.processing ? 'Meneruskan Pengajuan...' : 'Tarik Saldo Sekarang' }}
                                </button>
                                <p v-if="!user?.bank_info" class="text-[10px] text-rose-500 text-center font-normal mt-2">Harap simpan rekening pencairan di atas terlebih dahulu.</p>
                            </form>
                        </div>
                    </div>
                    <div class="space-y-3 md:col-span-1">
                        <h4 class="text-[10px] font-medium text-slate-400 uppercase tracking-[0.15em] px-1">Riwayat Status Pencairan</h4>
                        <div v-if="withdrawals.length === 0" class="bg-white border border-slate-100 rounded-[1.5rem] p-6 text-center text-xs text-slate-400 font-normal shadow-sm">
                            Belum ada riwayat penarikan.
                        </div>
                        <div v-else class="flex flex-col gap-2.5">
                            <div v-for="wd in withdrawals" :key="wd.id" class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm flex flex-col gap-2 transition-all">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <span class="text-[9px] text-slate-400 font-medium">{{ formatDate(wd.created_at) }}</span>
                                        <div class="text-xs text-slate-700 font-medium mt-0.5 leading-snug">{{ wd.bank_name }}</div>
                                    </div>
                                    <span :class="wd.status === 'approved' ? 'text-emerald-600 bg-emerald-50 border border-emerald-100' : wd.status === 'pending' ? 'text-amber-600 bg-amber-50 border border-amber-100' : 'text-rose-600 bg-rose-50 border border-rose-100'" 
                                          class="text-[9px] font-medium uppercase tracking-wider px-2 py-1 rounded-md text-center shrink-0">
                                        {{ wd.status === 'approved' ? 'Telah Dikirim' : wd.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-end mt-1 pt-2 border-t border-slate-50">
                                    <span class="text-[10px] text-slate-500 font-normal">Nominal:</span>
                                    <span class="text-sm font-medium text-slate-900">{{ formatCurrency(wd.amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'earnings'" class="space-y-3">
                    <h3 class="text-xs md:text-sm font-medium text-slate-900 uppercase tracking-wider px-1">Riwayat Pendapatan Afiliasi</h3>
                    <div v-if="earning_history.length === 0" class="bg-white border border-slate-100 rounded-[1.5rem] p-8 md:p-10 text-center text-xs md:text-sm text-slate-400 font-normal shadow-sm">
                        Belum ada yang menggunakan kode atau tautan Anda. Bagikan sekarang ke media sosial!
                    </div>
                    <div v-else class="flex flex-col gap-2.5">
                        <div v-for="earn in earning_history" :key="earn.id" class="bg-white border border-slate-100 rounded-xl p-4 flex items-center justify-between gap-4 transition-all shadow-sm">
                            <div class="min-w-0 flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center border" :class="earn.type.includes('Token') ? 'bg-indigo-50 border-indigo-100 text-indigo-500' : 'bg-emerald-50 border-emerald-100 text-emerald-500'">
                                    <span class="text-xs">{{ earn.type.includes('Token') ? '🎫' : '👤' }}</span>
                                </div>
                                <div>
                                    <h4 class="text-xs md:text-sm font-medium text-slate-900 truncate tracking-tight">{{ earn.name }}</h4>
                                    <p class="text-[9px] md:text-[10px] text-slate-400 font-medium mt-0.5">
                                        {{ earn.type }} &bull; {{ formatDate(earn.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs md:text-sm font-medium text-emerald-600 block">+{{ formatCurrency(earn.amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'competition'" class="space-y-6">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-[1.5rem] p-5 md:p-8 shadow-md text-white">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg md:text-xl font-medium tracking-tight">
                                {{ competitionSettings?.title || 'Kompetisi Penggunaan Token' }}
                            </h3>
                        </div>
                        <p class="text-xs md:text-sm text-indigo-100 font-normal leading-relaxed max-w-3xl whitespace-pre-line">
                            {{ competitionSettings?.description || 'Raih target penggunaan token terbanyak setiap minggu dan bulan untuk mendapatkan hadiah saldo khusus secara langsung! Bagikan tautan kompetisi atau token unik Anda di bawah ini ke seluruh media sosial.' }}
                        </p>
                        <div class="mt-4 inline-block bg-white/20 border border-white/30 rounded-lg px-3 py-1.5 text-[10px] uppercase tracking-widest font-medium">
                            Periode Berjalan: {{ currentMonthName }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 md:p-5 shadow-sm">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium mb-2">Tautan Referensi Anda</p>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-xs text-slate-600 font-normal select-all truncate flex items-center">
                                    {{ affiliate_url }}
                                </div>
                                <button @click="copyLink" :class="copiedLink ? 'bg-emerald-500 text-white' : 'bg-slate-900 text-white hover:bg-indigo-600'" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-[10px] uppercase font-medium tracking-wider transition-all shrink-0">
                                    {{ copiedLink ? 'Tersalin' : 'Salin Link' }}
                                </button>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 md:p-5 shadow-sm">
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium mb-2">Token Kompetisi Anda</p>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="flex-1 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2 text-sm text-indigo-600 font-medium select-all truncate text-center flex items-center justify-center tracking-widest">
                                    {{ affiliate_code }}
                                </div>
                                <button @click="copyToken" :class="copiedToken ? 'bg-emerald-500 text-white' : 'bg-indigo-600 text-white hover:bg-indigo-700'" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-[10px] uppercase font-medium tracking-wider transition-all shrink-0">
                                    {{ copiedToken ? 'Tersalin' : 'Salin Token' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 mb-3 gap-2">
                                <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest">Top 5 Peringkat Mingguan</h3>
                                <select v-model="filterWeek" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[9px] md:text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1.5 focus:border-indigo-500 focus:ring-0 transition-colors cursor-pointer w-full sm:w-auto">     <option v-for="week in archiveWeeks" :key="week.value" :value="week.value">{{ week.label }}</option>
                                </select>
                            </div>

                            <div v-if="weekly_leaderboard.length > 0" class="flex flex-col gap-2">
                                <div v-for="(leader, index) in weekly_leaderboard" :key="'w'+index" class="flex items-center justify-between p-3 rounded-xl transition-colors" :class="index === 0 ? 'bg-amber-50 border border-amber-100' : index === 1 ? 'bg-slate-50 border border-slate-200' : index === 2 ? 'bg-orange-50 border border-orange-100' : 'hover:bg-slate-50'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-medium" :class="index === 0 ? 'bg-amber-500 text-white' : index === 1 ? 'bg-slate-400 text-white' : index === 2 ? 'bg-orange-400 text-white' : 'bg-slate-100 text-slate-500'">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-xs font-medium text-slate-700 truncate max-w-[120px] sm:max-w-[150px]">
                                            {{ leader.name }}
                                            <span v-if="leader.name === user.name" class="ml-1 text-[9px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded uppercase">(Anda)</span>
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider shrink-0">{{ leader.total }} Token</span>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-xs text-slate-400 font-normal">
                                Belum ada data di minggu ini.
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 mb-3 gap-2">
                                <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest">Top 5 Peringkat Bulanan</h3>
                                <select v-model="filterMonth" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[9px] md:text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1.5 focus:border-indigo-500 focus:ring-0 transition-colors cursor-pointer w-full sm:w-auto">    <option v-for="month in archiveMonths" :key="month.value" :value="month.value">{{ month.label }}</option>
                                </select>
                            </div>

                            <div v-if="monthly_leaderboard.length > 0" class="flex flex-col gap-2">
                                <div v-for="(leader, index) in monthly_leaderboard" :key="'m'+index" class="flex items-center justify-between p-3 rounded-xl transition-colors" :class="index === 0 ? 'bg-indigo-50 border border-indigo-100' : index === 1 ? 'bg-slate-50 border border-slate-200' : index === 2 ? 'bg-orange-50 border border-orange-100' : 'hover:bg-slate-50'">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 flex items-center justify-center rounded-full text-[10px] font-medium" :class="index === 0 ? 'bg-indigo-500 text-white' : index === 1 ? 'bg-slate-400 text-white' : index === 2 ? 'bg-orange-400 text-white' : 'bg-slate-100 text-slate-500'">
                                            {{ index + 1 }}
                                        </div>
                                        <span class="text-xs font-medium text-slate-700 truncate max-w-[120px] sm:max-w-[150px]">
                                            {{ leader.name }}
                                            <span v-if="leader.name === user.name" class="ml-1 text-[9px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded uppercase">(Anda)</span>
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider shrink-0">{{ leader.total }} Token</span>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-xs text-slate-400 font-normal">
                                Belum ada data di bulan ini.
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-6 shadow-sm space-y-4 mt-2">
                        <h3 class="text-xs font-medium text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-2.5">Riwayat Pemenang Kompetisi</h3>
                        <div v-if="announcements && announcements.length > 0" class="divide-y divide-slate-100">
                            <div v-for="ann in announcements" :key="ann.id" class="py-3 flex justify-between items-center text-xs transition-colors hover:bg-slate-50/50 rounded-lg px-2">
                                <div>
                                    <span class="font-medium text-slate-700 truncate pr-2 block">{{ ann.user?.name }}</span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">{{ formatDate(ann.created_at) }}</span>
                                </div>
                                <span class="font-medium text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded text-[9px] uppercase shrink-0 border border-indigo-100">
                                    {{ ann.proof_payment === 'REWARD-WEEKLY' ? 'Juara Mingguan' : 'Juara Bulanan' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-xs text-slate-400 font-normal">
                            Belum ada pemenang kompetisi di periode ini. Jadilah yang pertama mencapai target!
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.animate-in { animation-duration: 0.6s; animation-fill-mode: both; }
</style>