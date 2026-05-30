<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array,
    withdrawals: Array,
    summary: Object,
    flash: Object
});

const currentTab = ref('users_list');
const statusForm = useForm({ status: '' });
const rewardForm = useForm({ amount: 0, type: '' });

// Fungsi Mengubah Status Penarikan Dana
const changeWithdrawStatus = (id, newStatus) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status penarikan ini menjadi ${newStatus}?`)) {
        statusForm.status = newStatus;
        statusForm.post(route('admin.affiliate.withdraw.status', id), {
            preserveScroll: true
        });
    }
};

// Fungsi Memberikan Hadiah Bonus Pencapaian Target
const giveTargetReward = (user) => {
    const amount = prompt(`Masukkan NOMINAL bonus hadiah untuk ${user.name} (contoh: 100000):`, "100000");
    if (amount === null || amount.trim() === "") return;

    let typeInput = prompt(`Pilih jenis label pengumuman reward:\nKetik "1" untuk REWARD MINGGUAN\nKetik "2" untuk REWARD BULANAN`, "2");
    if (typeInput === null || typeInput.trim() === "") return;
    
    const type = typeInput === "1" ? "REWARD-WEEKLY" : "REWARD-MONTHLY";

    rewardForm.amount = amount;
    rewardForm.type = type;
    rewardForm.post(route('admin.affiliate.reward', user.id), {
        preserveScroll: true
    });
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
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Kelola Program Afiliasi (Admin)" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Kelola Program Afiliasi</h1>
                <p class="text-xs text-slate-500 font-medium tracking-wide mt-1">
                    Pantau mitra yang bergabung, kalkulasi pendapatan komisi, serta berikan reward pencapaian.
                </p>
            </div>

            <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-xs font-medium">
                {{ flash.success }}
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-1">Total Pengguna Gabung</p>
                    <p class="text-lg md:text-xl font-medium text-slate-800 tracking-tighter">{{ summary.total_partners }} Partner</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-1">Total Saldo Tertahan</p>
                    <p class="text-lg md:text-xl font-medium text-indigo-600 tracking-tighter">{{ formatCurrency(summary.total_allocated_balance) }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-1">Komisi Sukses Cair</p>
                    <p class="text-lg md:text-xl font-medium text-emerald-600 tracking-tighter">{{ formatCurrency(summary.total_payouts) }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-[1.25rem] p-4 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-1">Menunggu Persetujuan</p>
                    <p class="text-lg md:text-xl font-medium text-amber-500 tracking-tighter">{{ summary.pending_requests }} Pengajuan</p>
                </div>
            </div>

            <div class="flex border-b border-slate-200/60 p-0.5 gap-2 overflow-x-auto custom-scrollbar">
                <button @click="currentTab = 'users_list'" :class="currentTab === 'users_list' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                    Daftar Mitra Afiliasi
                </button>
                <button @click="currentTab = 'withdraw_requests'" :class="currentTab === 'withdraw_requests' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap">
                    Permintaan Tarik Komisi
                </button>
            </div>

            <div v-if="currentTab === 'users_list'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] text-slate-400 uppercase tracking-wider">
                                    <th class="p-4 font-medium">Nama / Email</th>
                                    <th class="p-4 font-medium">Kode Afiliasi</th>
                                    <th class="p-4 font-medium">Total Rujukan</th>
                                    <th class="p-4 font-medium">Saldo Saat Ini</th>
                                    <th class="p-4 font-medium">Info Rekening</th>
                                    <th class="p-4 font-medium text-center">Aksi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <tr v-for="member in users" :key="member.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4">
                                        <div class="text-slate-900 font-medium">{{ member.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ member.email }}</div>
                                    </td>
                                    <td class="p-4 font-mono tracking-wider text-indigo-600">{{ member.affiliate_code }}</td>
                                    <td class="p-4 text-slate-600">{{ member.total_referrals }} Orang</td>
                                    <td class="p-4 text-emerald-600 font-medium">{{ formatCurrency(member.current_balance) }}</td>
                                    <td class="p-4">
                                        <div v-if="member.bank_info" class="text-[11px] text-slate-600 font-normal leading-normal">
                                            <div>{{ member.bank_info.bank_name }}</div>
                                            <div class="font-mono">{{ member.bank_info.account_number }}</div>
                                            <div>an. {{ member.bank_info.account_name }}</div>
                                        </div>
                                        <span v-else class="text-[10px] text-slate-400 font-normal">Belum diatur</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button @click="giveTargetReward(member)" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-600 rounded-lg text-[10px] uppercase font-medium tracking-wide transition-colors whitespace-nowrap active:scale-95 border border-pink-100">
                                            Beri Hadiah
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.length === 0">
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada pengguna yang terdaftar program afiliasi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'withdraw_requests'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] text-slate-400 uppercase tracking-wider">
                                    <th class="p-4 font-medium">Tanggal</th>
                                    <th class="p-4 font-medium">Nama Mitra</th>
                                    <th class="p-4 font-medium">Nominal Tarik</th>
                                    <th class="p-4 font-medium">Rekening Tujuan</th>
                                    <th class="p-4 font-medium">Status</th>
                                    <th class="p-4 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <tr v-for="wd in withdrawals" :key="wd.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 text-slate-500 whitespace-nowrap">{{ formatDate(wd.created_at) }}</td>
                                    <td class="p-4">
                                        <div class="text-slate-900 font-medium">{{ wd.user_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ wd.user_email }}</div>
                                    </td>
                                    <td class="p-4 text-emerald-600 whitespace-nowrap font-medium">{{ formatCurrency(wd.amount) }}</td>
                                    <td class="p-4">
                                        <div class="text-[11px] text-slate-600 font-normal leading-normal">
                                            <div>Bank: {{ wd.bank_name }}</div>
                                            <div class="font-mono">No: {{ wd.account_number }}</div>
                                            <div>Nama: {{ wd.account_name }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <span :class="wd.status === 'approved' ? 'text-emerald-600 bg-emerald-50' : wd.status === 'pending' ? 'text-amber-600 bg-amber-50' : 'text-rose-600 bg-rose-50'" class="text-[9px] font-medium uppercase tracking-wider px-2.5 py-1 rounded-lg">
                                            {{ wd.status === 'approved' ? 'Selesai Transfer' : wd.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center whitespace-nowrap">
                                        <div v-if="wd.status === 'pending'" class="flex items-center justify-center gap-1.5">
                                            <button @click="changeWithdrawStatus(wd.id, 'approved')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-[10px] uppercase font-medium tracking-wide transition-colors">
                                                Setujui
                                            </button>
                                            <button @click="changeWithdrawStatus(wd.id, 'rejected')" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-md text-[10px] uppercase font-medium tracking-wide transition-colors">
                                                Tolak
                                            </button>
                                        </div>
                                        <span v-else class="text-[11px] text-slate-400 font-normal">Selesai diproses</span>
                                    </td>
                                </tr>
                                <tr v-if="withdrawals.length === 0">
                                    <td colspan="6" class="p-8 text-center text-slate-400 font-medium">Belum ada pengajuan pencairan komisi masuk.</td>
                                </tr>
                            </tbody>
                        </table>
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