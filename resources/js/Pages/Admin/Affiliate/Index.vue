<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array,
    withdrawals: Array,
    summary: Object,
    flash: Object,
    archiveMonths: Array,
    archiveWeeks: Array,
    monthlyLeaderboard: Array,
    weeklyLeaderboard: Array,
    competitionSettings: Object, // tampung props teks baru
    selectedFilters: Object
});

const currentTab = ref('users_list');
const statusForm = useForm({ status: '' });
const rewardForm = useForm({ amount: 0, type: '' });

// Form untuk menyunting teks kompetisi afiliasi
const textEditForm = useForm({
    title: props.competitionSettings?.title || '',
    description: props.competitionSettings?.description || ''
});

// Jalankan fungsi submit perubahan teks
const submitTextUpdate = () => {
    textEditForm.post(route('admin.affiliate.competition.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Teks Kompetisi berhasil diperbarui!')
    });
};

// State untuk filter arsip peringkat
const filterWeek = ref(props.selectedFilters?.week || '');
const filterMonth = ref(props.selectedFilters?.month || '');

const updateLeaderboardFilter = () => {
    router.get(route('admin.affiliate.index'), {
        week: filterWeek.value,
        month: filterMonth.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const changeWithdrawStatus = (id, newStatus) => {
    if (confirm(`Apakah Anda yakin ingin mengubah status penarikan ini menjadi ${newStatus}?`)) {
        statusForm.status = newStatus;
        statusForm.post(route('admin.affiliate.withdraw.status', id), {
            preserveScroll: true
        });
    }
};

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
                <button @click="currentTab = 'users_list'" :class="currentTab === 'users_list' ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap rounded-t-lg">
                    Daftar Mitra Afiliasi
                </button>
                <button @click="currentTab = 'withdraw_requests'" :class="currentTab === 'withdraw_requests' ? 'border-indigo-500 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap rounded-t-lg">
                    Permintaan Tarik
                </button>
                <button @click="currentTab = 'leaderboard_weekly'" :class="currentTab === 'leaderboard_weekly' ? 'border-rose-500 text-rose-600 bg-rose-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap rounded-t-lg">
                    Peringkat Mingguan
                </button>
                <button @click="currentTab = 'leaderboard_monthly'" :class="currentTab === 'leaderboard_monthly' ? 'border-rose-500 text-rose-600 bg-rose-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap rounded-t-lg">
                    Peringkat Bulanan
                </button>
                <button @click="currentTab = 'edit_competition_text'" :class="currentTab === 'edit_competition_text' ? 'border-emerald-500 text-emerald-600 bg-emerald-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-4 py-2.5 text-[11px] md:text-xs uppercase font-medium tracking-widest border-b-2 transition-colors whitespace-nowrap rounded-t-lg">
                    ⚙️ Edit Teks Kompetisi
                </button>
            </div>

            <div v-if="currentTab === 'edit_competition_text'">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm p-6 max-w-2xl">
                    <h3 class="text-sm font-medium text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-3 mb-5">Pengaturan Konten Kompetisi Reward</h3>
                    
                    <form @submit.prevent="submitTextUpdate" class="space-y-5">
                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Judul Kompetisi</label>
                            <input v-model="textEditForm.title" type="text" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl px-4 py-3 text-xs md:text-sm font-normal text-slate-800" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1.5">Deskripsi / Peraturan Kompetisi</label>
                            <textarea v-model="textEditForm.description" rows="5" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-xl px-4 py-3 text-xs md:text-sm font-normal text-slate-800 leading-relaxed"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="textEditForm.processing" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] uppercase font-medium tracking-[0.2em] transition-all disabled:opacity-50">
                                {{ textEditForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Teks' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="currentTab === 'leaderboard_weekly'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm p-5 md:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-sm font-medium text-slate-800 uppercase tracking-wide">🏆 Top Afiliasi Mingguan</h3>
                            <p class="text-[10px] text-slate-400 mt-1">Pilih minggu di bawah ini untuk melihat arsip pemenang.</p>
                        </div>
                        <select v-model="filterWeek" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[9px] md:text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1.5 focus:border-indigo-500 focus:ring-0 transition-colors cursor-pointer w-full sm:w-auto">     <option v-for="week in archiveWeeks" :key="week.value" :value="week.value">{{ week.label }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                    <th class="p-3 font-medium text-center w-16 rounded-l-lg">Peringkat</th>
                                    <th class="p-3 font-medium">Mitra Afiliasi</th>
                                    <th class="p-3 font-medium text-center">Total Referensi Sukses</th>
                                    <th class="p-3 font-medium text-right rounded-r-lg">Aksi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="(user, index) in weeklyLeaderboard" :key="user.id" class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-4 text-center font-bold text-lg">
                                        <span v-if="index === 0" class="text-yellow-500 drop-shadow-sm">🥇 1</span>
                                        <span v-else-if="index === 1" class="text-slate-400 drop-shadow-sm">🥈 2</span>
                                        <span v-else-if="index === 2" class="text-amber-600 drop-shadow-sm">🥉 3</span>
                                        <span v-else class="text-slate-400 font-medium text-sm">{{ index + 1 }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-900">{{ user.name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ user.email }}</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-bold tracking-tight">{{ user.total_referrals }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="giveTargetReward(user)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-[9px] uppercase font-medium tracking-wide transition-colors border border-rose-100">
                                            Beri Reward
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="weeklyLeaderboard.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-medium">Belum ada transaksi afiliasi pada minggu ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'leaderboard_monthly'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] shadow-sm p-5 md:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-sm font-medium text-slate-800 uppercase tracking-wide">👑 Top Afiliasi Bulanan</h3>
                            <p class="text-[10px] text-slate-400 mt-1">Pilih bulan di bawah ini untuk melihat arsip pemenang.</p>
                        </div>
                        <select v-model="filterMonth" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[9px] md:text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1.5 focus:border-indigo-500 focus:ring-0 transition-colors cursor-pointer w-full sm:w-auto">    <option v-for="month in archiveMonths" :key="month.value" :value="month.value">{{ month.label }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs md:text-sm">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                    <th class="p-3 font-medium text-center w-16 rounded-l-lg">Peringkat</th>
                                    <th class="p-3 font-medium">Mitra Afiliasi</th>
                                    <th class="p-3 font-medium text-center">Total Referensi Sukses</th>
                                    <th class="p-3 font-medium text-right rounded-r-lg">Aksi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="(user, index) in monthlyLeaderboard" :key="user.id" class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-4 text-center font-bold text-lg">
                                        <span v-if="index === 0" class="text-yellow-500 drop-shadow-sm">🥇 1</span>
                                        <span v-else-if="index === 1" class="text-slate-400 drop-shadow-sm">🥈 2</span>
                                        <span v-else-if="index === 2" class="text-amber-600 drop-shadow-sm">🥉 3</span>
                                        <span v-else class="text-slate-400 font-medium text-sm">{{ index + 1 }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-medium text-slate-900">{{ user.name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ user.email }}</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg font-bold tracking-tight">{{ user.total_referrals }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="giveTargetReward(user)" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-[9px] uppercase font-medium tracking-wide transition-colors border border-rose-100">
                                            Beri Reward
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="monthlyLeaderboard.length === 0">
                                    <td colspan="4" class="p-8 text-center text-slate-400 font-medium">Belum ada transaksi afiliasi pada bulan ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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
                                    <th class="p-4 font-medium text-center">Aksi Hadiah Khusus</th>
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
                                            Beri Manual
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
                                        <span :class="wd.status === 'approved' ? 'text-emerald-600 bg-emerald-50 border border-emerald-100' : wd.status === 'pending' ? 'text-amber-600 bg-amber-50 border border-amber-100' : 'text-rose-600 bg-rose-50 border border-rose-100'" class="text-[9px] font-medium uppercase tracking-wider px-2.5 py-1 rounded-lg">
                                            {{ wd.status === 'approved' ? 'Telah Dikirim' : wd.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
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