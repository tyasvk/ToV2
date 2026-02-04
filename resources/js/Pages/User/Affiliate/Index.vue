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
                title: 'BERHASIL!', 
                text: 'DATA REKENING BERHASIL DIPERBARUI.', 
                icon: 'success', 
                customClass: { popup: 'rounded-[2rem]' } 
            });
        }
    });
};

const confirmWithdraw = () => {
    Swal.fire({
        title: 'CAIRKAN SALDO?',
        text: `SALDO SEBESAR ${formatPrice(props.user.affiliate_balance)} AKAN DIAJUKAN UNTUK PENCAIRAN.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'YA, CAIRKAN',
        confirmButtonColor: '#0F172A',
        cancelButtonText: 'BATAL',
        customClass: { popup: 'rounded-[2.5rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('affiliate.withdraw'), {}, {
                onSuccess: () => {
                    Swal.fire({ 
                        title: 'BERHASIL!', 
                        text: 'PENGAJUAN SEDANG DIPROSES OLEH ADMIN.', 
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
        <div class="max-w-7xl mx-auto py-10 px-4 md:px-6">
            
            <div class="mb-10">
                <h2 class="font-black text-4xl text-slate-900 tracking-tighter uppercase leading-none">🤝 Program Afiliasi</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-3">PUSAT KOMISI DAN HADIAH PARTNER NUSANTARA</p>
            </div>

            <div v-if="!user.affiliate_code" class="bg-white p-20 rounded-[3.5rem] shadow-2xl text-center border border-slate-100">
                <div class="text-7xl mb-8 text-indigo-600">🚀</div>
                <h3 class="font-black text-3xl uppercase mb-4 tracking-tighter">Aktifkan Kode Voucher Anda</h3>
                <p class="text-slate-500 mb-10 max-w-md mx-auto font-bold uppercase text-xs leading-relaxed tracking-tight">
                    Dapatkan komisi dari setiap referal dan hadiah spesial bulanan yang bisa dicairkan langsung ke rekening Anda.
                </p>
                <button @click="router.post(route('affiliate.join'))" class="bg-slate-900 text-white px-12 py-5 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-2xl hover:bg-indigo-600 transition-all">Aktifkan Sekarang</button>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-8 space-y-8">
                    
                    <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white flex items-center gap-6 shadow-xl shadow-indigo-100 relative overflow-hidden group">
                        <div class="absolute right-0 top-0 w-40 h-40 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl transition-transform group-hover:scale-110"></div>
                        <div class="text-3xl relative z-10 animate-bounce">💡</div>
                        <p class="text-[11px] font-black uppercase tracking-tight leading-relaxed relative z-10">
                            Semua hadiah peringkat dan bonus target akan otomatis ditambahkan ke Saldo Afiliasi Anda dan dapat ditarik ke rekening bank.
                        </p>
                    </div>

                    <div class="bg-[#0F172A] p-10 rounded-[3rem] text-white shadow-2xl relative overflow-hidden border border-slate-800">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative z-10">
                            <div>
                                <h3 class="font-black text-2xl uppercase tracking-tighter leading-none">Target Spesial Bulan Ini</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2 leading-none">DAPATKAN BONUS EKSTRA {{ formatPrice(special_bonus) }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-5xl font-black text-emerald-400 leading-none">{{ monthly_count }}</span>
                                <span class="text-slate-500 font-black text-xs uppercase ml-3 tracking-widest">/ {{ target_limit }} REFERAL</span>
                            </div>
                        </div>
                        <div class="mt-10 h-5 bg-slate-800 rounded-full p-1.5 border border-slate-700 relative z-10 shadow-inner">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(52,211,153,0.3)]" 
                                 :style="{ width: Math.min((monthly_count / target_limit) * 100, 100) + '%' }">
                            </div>
                        </div>
                        <p class="text-[9px] font-black mt-6 uppercase tracking-[0.2em] text-center" :class="monthly_count >= target_limit ? 'text-emerald-400 animate-pulse' : 'text-slate-500'">
                            {{ monthly_count >= target_limit ? 'SELAMAT! TARGET TERCAPAI. BONUS AKAN DIVERIFIKASI ADMIN.' : `KURANG ${target_limit - monthly_count} REFERAL LAGI UNTUK KLAIM BONUS.` }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm group hover:border-indigo-100 transition-all">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Saldo Tersedia (Bisa Dicairkan)</p>
                            <h4 class="text-4xl font-black text-slate-900 leading-none mb-8">{{ formatPrice(user.affiliate_balance) }}</h4>
                            <button @click="confirmWithdraw" :disabled="user.affiliate_balance < min_withdrawal || !user.bank_info" class="w-full py-5 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all shadow-lg" :class="user.affiliate_balance >= min_withdrawal && user.bank_info ? 'bg-emerald-500 text-white shadow-emerald-100 hover:bg-emerald-600' : 'bg-slate-50 text-slate-300 cursor-not-allowed border border-slate-100'">Cairkan Dana</button>
                        </div>
                        
                        <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 group-hover:scale-125 transition-transform duration-700"></div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 relative z-10">Kode Voucher Unik Saya</p>
                            <h4 class="text-4xl font-black text-indigo-600 tracking-[0.2em] leading-none uppercase select-all relative z-10">{{ user.affiliate_code }}</h4>
                            <p class="mt-8 text-[9px] font-black text-slate-300 uppercase leading-relaxed relative z-10">BAGIKAN KODE INI DAN DAPATKAN KOMISI Rp 3.000 / TRANSAKSI.</p>
                        </div>
                    </div>

                    <div class="bg-white p-12 rounded-[3.5rem] border border-slate-100 shadow-sm">
                        <h3 class="font-black text-[11px] uppercase tracking-widest mb-10 flex items-center gap-4 text-slate-900 leading-none">
                            <span class="w-2.5 h-6 bg-slate-900 rounded-full"></span> Konfigurasi Rekening Bank
                        </h3>
                        <form @submit.prevent="submitBank" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-3">
                                    <label class="text-[9px] font-black text-slate-400 uppercase ml-2 tracking-widest leading-none">Bank / Nama App (Contoh: BCA / DANA)</label>
                                    <input v-model="bankForm.bank_name" type="text" class="w-full bg-slate-50 border-none rounded-2xl p-5 text-xs font-black focus:ring-2 focus:ring-slate-900 uppercase" required />
                                </div>
                                <div class="space-y-3">
                                    <label class="text-[9px] font-black text-slate-400 uppercase ml-2 tracking-widest leading-none">Nomor Rekening / HP</label>
                                    <input v-model="bankForm.account_number" type="text" class="w-full bg-slate-50 border-none rounded-2xl p-5 text-xs font-black focus:ring-2 focus:ring-slate-900" required />
                                </div>
                            </div>
                            <div class="space-y-3">
                                <label class="text-[9px] font-black text-slate-400 uppercase ml-2 tracking-widest leading-none">Nama Lengkap Pemilik Rekening</label>
                                <input v-model="bankForm.account_name" type="text" class="w-full bg-slate-50 border-none rounded-2xl p-5 text-xs font-black focus:ring-2 focus:ring-slate-900 uppercase" required />
                            </div>
                            <button type="submit" class="bg-slate-900 text-white px-10 py-4 rounded-xl font-black uppercase text-[9px] tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-100">Simpan Data Rekening</button>
                        </form>
                    </div>

                    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-10 py-6 bg-slate-50 border-b border-slate-100">
                            <h3 class="font-black text-[11px] uppercase tracking-widest text-slate-900 leading-none">Riwayat Referal Berhasil</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="ref in referrals.data" :key="ref.id" class="hover:bg-slate-50 transition-all">
                                        <td class="px-10 py-6">
                                            <p class="font-black text-xs text-slate-900 uppercase tracking-tight">{{ ref.user?.name }}</p>
                                        </td>
                                        <td class="px-10 py-6 text-center">
                                            <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100 uppercase leading-none">Komisi Masuk (+3.000)</span>
                                        </td>
                                        <td class="px-10 py-6 text-right">
                                            <p class="text-[10px] font-black text-slate-300 uppercase leading-none">{{ formatDate(ref.created_at) }}</p>
                                        </td>
                                    </tr>
                                    <tr v-if="referrals.data.length === 0">
                                        <td colspan="3" class="px-10 py-20 text-center">
                                            <p class="text-[10px] text-slate-300 font-black uppercase tracking-[0.4em]">Belum Ada Referal Tercatat</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-if="referrals.links.length > 3" class="px-10 py-8 bg-slate-50/20 border-t border-slate-50 flex justify-center gap-2">
                            <template v-for="(link, k) in referrals.links" :key="k">
                                <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase transition-all shadow-sm" :class="link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" />
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-10">
                    
                    <div class="bg-white rounded-[3.5rem] border-2 border-indigo-50 shadow-2xl shadow-indigo-50/50 overflow-hidden">
                        <div class="px-8 py-7 bg-indigo-600">
                            <h3 class="font-black text-[11px] text-white uppercase tracking-[0.3em] leading-none text-center flex items-center justify-center gap-3">
                                <span class="text-lg">🏆</span> PAPAN PEMENANG
                            </h3>
                        </div>
                        <div class="p-3 divide-y divide-slate-50 max-h-[600px] overflow-y-auto custom-scroll">
                            <div v-for="ann in announcements" :key="ann.id" class="p-6 hover:bg-slate-50 transition-all rounded-3xl group">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1 pr-4">
                                        <p class="font-black text-[11px] text-slate-900 uppercase leading-none truncate">{{ ann.user?.name }}</p>
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter mt-1 leading-none">{{ formatDate(ann.created_at) }}</p>
                                    </div>
                                    <span :class="ann.proof_payment.includes('WEEKLY') ? 'bg-blue-50 text-blue-600 border-blue-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'" class="text-[8px] font-black px-2.5 py-1 rounded-md uppercase border shadow-sm leading-none">
                                        {{ ann.proof_payment.includes('WEEKLY') ? 'WEEKLY' : 'MONTHLY' }}
                                    </span>
                                </div>
                                <p class="text-[10px] font-black text-indigo-600 uppercase tracking-tight leading-relaxed group-hover:text-indigo-800 transition-colors">
                                    {{ ann.description }}
                                </p>
                            </div>
                            <div v-if="announcements.length === 0" class="p-16 text-center">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-relaxed">Peringkat sedang dalam verifikasi sistem.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pencairan Saya</p>
                            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.5)]"></span>
                        </div>
                        <div class="divide-y divide-slate-50">
                            <div v-for="wd in withdrawals" :key="wd.id" class="px-8 py-6 flex justify-between items-center group transition-all">
                                <div>
                                    <p class="font-black text-xs text-slate-900 leading-none mb-2">{{ formatPrice(wd.amount) }}</p>
                                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest leading-none">{{ formatDate(wd.created_at) }}</p>
                                </div>
                                <span :class="{
                                    'bg-amber-50 text-amber-600 border-amber-100': wd.status === 'pending', 
                                    'bg-emerald-50 text-emerald-600 border-emerald-100': wd.status === 'approved'
                                }" class="text-[8px] font-black px-3 py-1.5 rounded-lg uppercase border tracking-tighter shadow-sm">
                                    {{ wd.status }}
                                </span>
                            </div>
                            <div v-if="withdrawals.length === 0" class="p-16 text-center">
                                <p class="text-[9px] text-slate-300 font-black uppercase tracking-widest">Belum ada penarikan.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom Scrollbar for Winners Board */
.custom-scroll::-webkit-scrollbar { width: 4px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #CBD5E1; }

/* Table scrollbar */
.overflow-x-auto::-webkit-scrollbar { height: 4px; }
.overflow-x-auto::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>