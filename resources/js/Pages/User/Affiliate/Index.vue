<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    user: Object,
    referrals: Object,
    withdrawals: Array,
    announcements: Array,
    monthly_count: Number,
    target_limit: Number,
    special_bonus: Number,
    min_withdrawal: Number
});

const bankForm = useForm({
    bank_name: props.user.bank_info?.bank_name || '',
    account_number: props.user.bank_info?.account_number || '',
    account_name: props.user.bank_info?.account_name || '',
});

const formatPrice = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0 
}).format(num || 0);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short'
    });
};

const submitBank = () => {
    bankForm.post(route('affiliate.bank-info'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({ 
                title: 'BERHASIL', 
                text: 'Data rekening berhasil diperbarui.', 
                icon: 'success', 
                customClass: { 
                    popup: 'rounded-[2rem]',
                    title: 'font-medium uppercase tracking-tight',
                    confirmButton: 'rounded-xl font-medium uppercase tracking-widest text-xs py-3 px-6'
                } 
            });
        }
    });
};

const confirmWithdraw = () => {
    Swal.fire({
        title: 'CAIRKAN SALDO?',
        text: `Saldo sebesar ${formatPrice(props.user.affiliate_balance)} akan diajukan untuk pencairan.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'YA, CAIRKAN',
        confirmButtonColor: '#0F172A',
        cancelButtonText: 'BATAL',
        customClass: { 
            popup: 'rounded-[2.5rem]',
            title: 'font-medium uppercase tracking-tight',
            confirmButton: 'rounded-xl font-medium uppercase tracking-widest text-xs py-3 px-6',
            cancelButton: 'rounded-xl font-medium uppercase tracking-widest text-xs py-3 px-6'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('affiliate.withdraw'), {}, {
                onSuccess: () => {
                    Swal.fire({ 
                        title: 'BERHASIL', 
                        text: 'Pengajuan sedang diproses oleh admin.', 
                        icon: 'success', 
                        customClass: { popup: 'rounded-[2rem]' } 
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Program Afiliasi" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-6 md:py-10 px-4 md:px-6">
            
            <div class="mb-8 md:mb-12 px-1">
                <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tight uppercase leading-none italic">🤝 Program Afiliasi</h2>
                <p class="text-[9px] md:text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-3">Pusat Komisi dan Hadiah Partner Nusantara</p>
            </div>

            <div v-if="!user.affiliate_code" class="bg-white p-10 md:p-20 rounded-[2.5rem] md:rounded-[3.5rem] shadow-sm text-center border border-slate-100">
                <div class="text-5xl md:text-7xl mb-6 md:mb-8 text-indigo-500 opacity-80">🚀</div>
                <h3 class="font-medium text-xl md:text-2xl uppercase mb-4 tracking-tight">Aktifkan Kode Voucher</h3>
                <p class="text-slate-500 mb-8 md:mb-10 max-w-md mx-auto font-medium uppercase text-[10px] md:text-xs leading-relaxed tracking-wide opacity-70">
                    Dapatkan komisi dari setiap referal dan hadiah spesial bulanan yang bisa dicairkan langsung ke rekening Anda.
                </p>
                <button @click="router.post(route('affiliate.join'))" class="bg-slate-950 text-white px-10 py-4 md:px-12 md:py-5 rounded-2xl font-medium uppercase text-[10px] tracking-widest shadow-xl hover:bg-indigo-600 transition-all active:scale-95">Aktifkan Sekarang</button>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-10">
                
                <div class="lg:col-span-8 space-y-6 md:space-y-8">
                    
                    <div class="bg-indigo-600 p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] text-white flex items-center gap-5 shadow-lg shadow-indigo-100/50 relative overflow-hidden group">
                        <div class="text-2xl relative z-10">💡</div>
                        <p class="text-[10px] md:text-[11px] font-medium uppercase tracking-tight leading-relaxed relative z-10 opacity-90">
                            Semua hadiah peringkat dan bonus target akan otomatis ditambahkan ke Saldo Afiliasi Anda dan dapat ditarik ke rekening bank.
                        </p>
                    </div>

                    <div class="bg-slate-900 p-8 md:p-10 rounded-[2.5rem] md:rounded-[3rem] text-white shadow-xl relative overflow-hidden border border-slate-800">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                            <div>
                                <h3 class="font-medium text-xl md:text-2xl uppercase tracking-tight leading-none">Target Spesial Bulan Ini</h3>
                                <p class="text-[9px] md:text-[10px] font-medium text-slate-400 uppercase tracking-widest mt-2 leading-none">Bonus Ekstra {{ formatPrice(special_bonus) }}</p>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl md:text-5xl font-medium text-emerald-400 leading-none">{{ monthly_count }}</span>
                                <span class="text-slate-500 font-medium text-[10px] uppercase tracking-widest">/ {{ target_limit }} Referal</span>
                            </div>
                        </div>
                        <div class="mt-8 md:mt-10 h-3 bg-slate-800 rounded-full overflow-hidden border border-slate-700 p-0.5">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.3)]" 
                                 :style="{ width: Math.min((monthly_count / target_limit) * 100, 100) + '%' }">
                            </div>
                        </div>
                        <p class="text-[8px] md:text-[9px] font-medium mt-6 uppercase tracking-[0.2em] text-center" :class="monthly_count >= target_limit ? 'text-emerald-400' : 'text-slate-500'">
                            {{ monthly_count >= target_limit ? 'Target tercapai! Bonus akan diverifikasi admin.' : `Kurang ${target_limit - monthly_count} referal lagi untuk klaim bonus.` }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col justify-between">
                            <div>
                                <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mb-2">Saldo Tersedia</p>
                                <h4 class="text-3xl font-medium text-slate-900 tracking-tighter">{{ formatPrice(user.affiliate_balance) }}</h4>
                            </div>
                            <button @click="confirmWithdraw" :disabled="user.affiliate_balance < min_withdrawal || !user.bank_info" class="w-full mt-8 py-4 rounded-xl font-medium uppercase text-[9px] tracking-widest transition-all shadow-md" :class="user.affiliate_balance >= min_withdrawal && user.bank_info ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-50 text-slate-300 cursor-not-allowed'">Cairkan Dana</button>
                        </div>
                        
                        <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                            <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mb-2 relative z-10">Kode Voucher Unik</p>
                            <h4 class="text-3xl font-medium text-indigo-600 tracking-[0.2em] leading-none uppercase select-all relative z-10">{{ user.affiliate_code }}</h4>
                            <p class="mt-8 text-[9px] font-medium text-slate-400 uppercase leading-relaxed relative z-10 italic">Bagikan kode ini dan dapatkan komisi Rp 3.000 / transaksi.</p>
                        </div>
                    </div>

                    <div class="bg-white p-8 md:p-12 rounded-[2.5rem] md:rounded-[3rem] border border-slate-100 shadow-sm">
                        <h3 class="font-medium text-[11px] uppercase tracking-widest mb-8 flex items-center gap-3 text-slate-900 leading-none italic">
                            <span class="w-1.5 h-4 bg-slate-900 rounded-full"></span> Konfigurasi Rekening Bank
                        </h3>
                        <form @submit.prevent="submitBank" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[8px] font-medium text-slate-400 uppercase ml-1 tracking-widest">Bank / Nama App (Contoh: BCA / DANA)</label>
                                    <input v-model="bankForm.bank_name" type="text" class="w-full bg-slate-50 border-transparent rounded-xl p-4 text-xs font-medium focus:ring-1 focus:ring-slate-900 uppercase" required />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[8px] font-medium text-slate-400 uppercase ml-1 tracking-widest">Nomor Rekening / HP</label>
                                    <input v-model="bankForm.account_number" type="text" class="w-full bg-slate-50 border-transparent rounded-xl p-4 text-xs font-medium focus:ring-1 focus:ring-slate-900" required />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[8px] font-medium text-slate-400 uppercase ml-1 tracking-widest">Nama Lengkap Pemilik Rekening</label>
                                <input v-model="bankForm.account_name" type="text" class="w-full bg-slate-50 border-transparent rounded-xl p-4 text-xs font-medium focus:ring-1 focus:ring-slate-900 uppercase" required />
                            </div>
                            <button type="submit" class="bg-slate-950 text-white px-8 py-3.5 rounded-xl font-medium uppercase text-[9px] tracking-widest hover:bg-indigo-600 transition-all shadow-lg active:scale-95">Simpan Data Rekening</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100">
                            <h3 class="font-medium text-[10px] uppercase tracking-widest text-slate-900 leading-none italic">Riwayat Referal Berhasil</h3>
                        </div>
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="ref in referrals.data" :key="ref.id" class="hover:bg-slate-50 transition-all">
                                        <td class="px-8 py-5">
                                            <p class="font-medium text-xs text-slate-900 uppercase tracking-tight truncate max-w-[150px]">{{ ref.user?.name }}</p>
                                        </td>
                                        <td class="px-8 py-5 text-center">
                                            <span class="text-[8px] font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100 uppercase tracking-tight">Komisi Masuk</span>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <p class="text-[9px] font-medium text-slate-400 uppercase leading-none">{{ formatDate(ref.created_at) }}</p>
                                        </td>
                                    </tr>
                                    <tr v-if="referrals.data.length === 0">
                                        <td colspan="3" class="px-8 py-16 text-center">
                                            <p class="text-[9px] text-slate-300 font-medium uppercase tracking-[0.3em]">Belum ada referal tercatat</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="referrals.links.length > 3" class="px-8 py-6 bg-slate-50/20 border-t border-slate-50 flex justify-center gap-2">
                            <template v-for="(link, k) in referrals.links" :key="k">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-4 py-2 rounded-xl text-[9px] font-medium uppercase transition-all" :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" />
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6 md:space-y-10">
                    
                    <div class="bg-white rounded-[2.5rem] md:rounded-[3rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
                        <div class="px-8 py-6 bg-indigo-600 text-center">
                            <h3 class="font-medium text-[10px] text-white uppercase tracking-[0.25em] leading-none">🏆 Papan Pemenang</h3>
                        </div>
                        <div class="p-2 divide-y divide-slate-50 max-h-[500px] overflow-y-auto custom-scroll">
                            <div v-for="ann in announcements" :key="ann.id" class="p-5 hover:bg-slate-50 transition-all rounded-2xl group">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1 min-w-0 pr-2">
                                        <p class="font-medium text-[11px] text-slate-900 uppercase leading-none truncate">{{ ann.user?.name }}</p>
                                        <p class="text-[8px] font-medium text-slate-400 uppercase tracking-tight mt-1">{{ formatDate(ann.created_at) }}</p>
                                    </div>
                                    <span :class="ann.proof_payment.includes('WEEKLY') ? 'text-blue-600 bg-blue-50 border-blue-100' : 'text-emerald-600 bg-emerald-50 border-emerald-100'" class="text-[7px] font-medium px-2 py-0.5 rounded-md uppercase border shrink-0">
                                        {{ ann.proof_payment.includes('WEEKLY') ? 'Weekly' : 'Monthly' }}
                                    </span>
                                </div>
                                <p class="text-[10px] font-medium text-slate-500 uppercase tracking-tight leading-relaxed group-hover:text-indigo-600 transition-colors">
                                    {{ ann.description }}
                                </p>
                            </div>
                            <div v-if="announcements.length === 0" class="p-12 text-center text-slate-300">
                                <p class="text-[9px] font-medium uppercase tracking-widest italic leading-relaxed">Peringkat sedang dalam verifikasi sistem.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-5 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                            <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest leading-none">Pencairan Saya</p>
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                        </div>
                        <div class="divide-y divide-slate-50">
                            <div v-for="wd in withdrawals" :key="wd.id" class="px-8 py-5 flex justify-between items-center group">
                                <div>
                                    <p class="font-medium text-[11px] text-slate-900 leading-none mb-1.5">{{ formatPrice(wd.amount) }}</p>
                                    <p class="text-[8px] text-slate-400 font-medium uppercase tracking-widest leading-none">{{ formatDate(wd.created_at) }}</p>
                                </div>
                                <span :class="{
                                    'bg-amber-50 text-amber-600 border-amber-100': wd.status === 'pending', 
                                    'bg-emerald-50 text-emerald-600 border-emerald-100': wd.status === 'approved'
                                }" class="text-[7px] font-medium px-2 py-1 rounded-md uppercase border tracking-tight">
                                    {{ wd.status }}
                                </span>
                            </div>
                            <div v-if="withdrawals.length === 0" class="p-12 text-center text-slate-300">
                                <p class="text-[9px] font-medium uppercase tracking-widest leading-none italic">Belum ada penarikan.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 3px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #F1F5F9; border-radius: 10px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #E2E8F0; }

.custom-scrollbar::-webkit-scrollbar { height: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #F1F5F9; border-radius: 10px; }
</style>