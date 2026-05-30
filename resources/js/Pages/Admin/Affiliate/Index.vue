<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
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
    competitionSettings: Object,
    selectedFilters: Object
});

const currentTab = ref('users_list');
const statusForm = useForm({ status: '' });
const rewardForm = useForm({ amount: 0, type: '' });

// Filter Saat Ini (default ke minggu/bulan terbaru jika belum dipilih)
const filterWeek = ref(props.selectedFilters?.week || (props.archiveWeeks?.[0]?.value || ''));
const filterMonth = ref(props.selectedFilters?.month || (props.archiveMonths?.[0]?.value || ''));

// ==========================================
// 1. DETEKSI WAKTU UNTUK KUNCIAN TOMBOL
// ==========================================
// Mingguan = Hanya Hari Senin (1)
const isMonday = computed(() => {
    return new Date().getDay() === 1;
});
// Bulanan = Hanya Tanggal 1
const isFirstDayOfMonth = computed(() => {
    return new Date().getDate() === 1;
});

// ==========================================
// 2. STATE PENYIMPANAN NOMINAL OTOMATIS
// ==========================================
const savedRewards = reactive({
    rank1: 50000,
    rank2: 30000,
    rank3: 20000
});

const savedMonthlyRewards = reactive({
    rank1: 150000,
    rank2: 100000,
    rank3: 50000
});

// State untuk mencatat log hadiah permanen yang sudah terkirim
const sentRewardsLog = ref([]);

// Ambil data dari Local Storage saat halaman dimuat
onMounted(() => {
    // Muat Nominal Mingguan
    if (localStorage.getItem('reward_rank1')) savedRewards.rank1 = parseInt(localStorage.getItem('reward_rank1'));
    if (localStorage.getItem('reward_rank2')) savedRewards.rank2 = parseInt(localStorage.getItem('reward_rank2'));
    if (localStorage.getItem('reward_rank3')) savedRewards.rank3 = parseInt(localStorage.getItem('reward_rank3'));
    
    // Muat Nominal Bulanan
    if (localStorage.getItem('reward_monthly_rank1')) savedMonthlyRewards.rank1 = parseInt(localStorage.getItem('reward_monthly_rank1'));
    if (localStorage.getItem('reward_monthly_rank2')) savedMonthlyRewards.rank2 = parseInt(localStorage.getItem('reward_monthly_rank2'));
    if (localStorage.getItem('reward_monthly_rank3')) savedMonthlyRewards.rank3 = parseInt(localStorage.getItem('reward_monthly_rank3'));
    
    // Muat Riwayat Pengiriman
    if (localStorage.getItem('sent_rewards_history')) {
        try {
            sentRewardsLog.value = JSON.parse(localStorage.getItem('sent_rewards_history'));
        } catch (e) {
            sentRewardsLog.value = [];
        }
    }
});

// Otomatis simpan jika nominal mingguan diubah
watch(savedRewards, (newVals) => {
    localStorage.setItem('reward_rank1', newVals.rank1);
    localStorage.setItem('reward_rank2', newVals.rank2);
    localStorage.setItem('reward_rank3', newVals.rank3);
}, { deep: true });

// Otomatis simpan jika nominal bulanan diubah
watch(savedMonthlyRewards, (newVals) => {
    localStorage.setItem('reward_monthly_rank1', newVals.rank1);
    localStorage.setItem('reward_monthly_rank2', newVals.rank2);
    localStorage.setItem('reward_monthly_rank3', newVals.rank3);
}, { deep: true });


// ==========================================
// 3. FUNGSI PENGIRIMAN & VALIDASI LOG
// ==========================================

// Fungsi untuk mengecek apakah reward sudah pernah dikirim
const isRewardAlreadySent = (userId, type, period, rank) => {
    return sentRewardsLog.value.some(log => 
        log.userId === userId && 
        log.type === type &&
        (log.period === period || log.week === period) && // Dukungan untuk data lama
        log.rank === rank
    );
};

// Fungsi Kirim Mingguan
const sendWeeklyReward = (user, rewardTier, amount) => {
    if (!isMonday.value) {
        alert('Pemberian hadiah mingguan hanya bisa dilakukan pada hari Senin!');
        return;
    }

    if (isRewardAlreadySent(user.id, 'weekly', filterWeek.value, rewardTier)) {
        alert('Hadiah ini sudah pernah dikirim sebelumnya untuk minggu ini!');
        return;
    }

    if (confirm(`Kirim REWARD MINGGUAN ${rewardTier} sebesar ${formatCurrency(amount)} kepada ${user.name}?`)) {
        rewardForm.amount = amount;
        rewardForm.type = "REWARD-WEEKLY";
        rewardForm.post(route('admin.affiliate.reward', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                sentRewardsLog.value.push({
                    userId: user.id, type: 'weekly', period: filterWeek.value, rank: rewardTier, timestamp: new Date().getTime()
                });
                localStorage.setItem('sent_rewards_history', JSON.stringify(sentRewardsLog.value));
            }
        });
    }
};

// Fungsi Kirim Bulanan
const sendMonthlyReward = (user, rewardTier, amount) => {
    if (!isFirstDayOfMonth.value) {
        alert('Pemberian hadiah bulanan hanya bisa dilakukan pada tanggal 1 setiap bulannya!');
        return;
    }

    if (isRewardAlreadySent(user.id, 'monthly', filterMonth.value, rewardTier)) {
        alert('Hadiah ini sudah pernah dikirim sebelumnya untuk bulan ini!');
        return;
    }

    if (confirm(`Kirim REWARD BULANAN ${rewardTier} sebesar ${formatCurrency(amount)} kepada ${user.name}?`)) {
        rewardForm.amount = amount;
        rewardForm.type = "REWARD-MONTHLY";
        rewardForm.post(route('admin.affiliate.reward', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                sentRewardsLog.value.push({
                    userId: user.id, type: 'monthly', period: filterMonth.value, rank: rewardTier, timestamp: new Date().getTime()
                });
                localStorage.setItem('sent_rewards_history', JSON.stringify(sentRewardsLog.value));
            }
        });
    }
};


// ==========================================
// 4. FUNGSI LAIN-LAIN
// ==========================================
const textEditForm = useForm({
    title: props.competitionSettings?.title || '',
    description: props.competitionSettings?.description || ''
});

const submitTextUpdate = () => {
    textEditForm.post(route('admin.affiliate.competition.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Teks Kompetisi berhasil diperbarui!')
    });
};

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
    const amount = prompt(`Masukkan NOMINAL bonus hadiah manual untuk ${user.name} (contoh: 100000):`, "100000");
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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Kelola Program Afiliasi (Admin)" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4 md:space-y-6 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-lg md:text-xl font-medium text-slate-900 tracking-tight">Kelola Program Afiliasi</h1>
                <p class="text-[11px] text-slate-500 font-medium tracking-wide mt-0.5">
                    Pantau mitra yang bergabung, kalkulasi pendapatan komisi, serta berikan reward pencapaian.
                </p>
            </div>

            <div v-if="flash?.success" class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-[11px] font-medium">
                {{ flash.success }}
            </div>
            
            <div v-if="flash?.error" class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 text-[11px] font-medium">
                {{ flash.error }}
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 md:gap-3">
                <div class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-0.5">Total Pengguna Gabung</p>
                    <p class="text-base font-medium text-slate-800 tracking-tighter">{{ summary.total_partners }} Partner</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-0.5">Total Saldo Tertahan</p>
                    <p class="text-base font-medium text-indigo-600 tracking-tighter">{{ formatCurrency(summary.total_allocated_balance) }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-0.5">Komisi Sukses Cair</p>
                    <p class="text-base font-medium text-emerald-600 tracking-tighter">{{ formatCurrency(summary.total_payouts) }}</p>
                </div>
                <div class="bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-medium mb-0.5">Menunggu Persetujuan</p>
                    <p class="text-base font-medium text-amber-500 tracking-tighter">{{ summary.pending_requests }} Pengajuan</p>
                </div>
            </div>

            <div class="flex border-b border-slate-200/60 p-0.5 gap-1 bg-slate-50 rounded-lg max-w-full">
                <button @click="currentTab = 'users_list'" :class="currentTab === 'users_list' ? 'bg-white text-indigo-600 shadow-sm font-semibold border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-800'" class="flex-1 text-center py-1.5 px-2 text-[10px] sm:text-[11px] uppercase tracking-wider transition-all rounded-md whitespace-nowrap">
                    Mitra Afiliasi
                </button>
                <button @click="currentTab = 'withdraw_requests'" :class="currentTab === 'withdraw_requests' ? 'bg-white text-indigo-600 shadow-sm font-semibold border-b-2 border-indigo-500' : 'text-slate-500 hover:text-slate-800'" class="flex-1 text-center py-1.5 px-2 text-[10px] sm:text-[11px] uppercase tracking-wider transition-all rounded-md whitespace-nowrap">
                    Tarik Dana ({{ summary.pending_requests }})
                </button>
                <button @click="currentTab = 'leaderboard_weekly'" :class="currentTab === 'leaderboard_weekly' ? 'bg-white text-rose-600 shadow-sm font-semibold border-b-2 border-rose-500' : 'text-slate-500 hover:text-slate-800'" class="flex-1 text-center py-1.5 px-2 text-[10px] sm:text-[11px] uppercase tracking-wider transition-all rounded-md whitespace-nowrap">
                    Peringkat Mingguan
                </button>
                <button @click="currentTab = 'leaderboard_monthly'" :class="currentTab === 'leaderboard_monthly' ? 'bg-white text-rose-600 shadow-sm font-semibold border-b-2 border-rose-500' : 'text-slate-500 hover:text-slate-800'" class="flex-1 text-center py-1.5 px-2 text-[10px] sm:text-[11px] uppercase tracking-wider transition-all rounded-md whitespace-nowrap">
                    Peringkat Bulanan
                </button>
                <button @click="currentTab = 'edit_competition_text'" :class="currentTab === 'edit_competition_text' ? 'bg-white text-emerald-600 shadow-sm font-semibold border-b-2 border-emerald-500' : 'text-slate-500 hover:text-slate-800'" class="flex-1 text-center py-1.5 px-2 text-[10px] sm:text-[11px] uppercase tracking-wider transition-all rounded-md whitespace-nowrap">
                    ⚙️ Teks
                </button>
            </div>

            <!-- TAB 3: PERINGKAT MINGGUAN -->
            <div v-if="currentTab === 'leaderboard_weekly'" class="space-y-4">
                <div v-if="!isMonday" class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                    <span class="text-amber-500 text-lg leading-none">⚠️</span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wide">Aksi Distribusi Dikunci</h4>
                        <p class="text-[10px] text-amber-700 mt-0.5 leading-snug">Pemberian hadiah Peringkat Mingguan hanya dapat dilakukan pada <b>Hari Senin</b>. Namun, Anda tetap bisa mengedit nominal uang hadiah kapan saja.</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">🏆 Top Afiliasi Mingguan</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pilih minggu di bawah ini untuk melihat arsip pemenang.</p>
                        </div>
                        <select v-model="filterWeek" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer w-full sm:w-auto">
                            <option v-for="week in archiveWeeks" :key="week.value" :value="week.value">{{ week.label }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                    <th class="p-2 font-medium text-center w-12 rounded-l-lg">Rank</th>
                                    <th class="p-2 font-medium">Mitra Afiliasi</th>
                                    <th class="p-2 font-medium text-center">Referensi Sukses</th>
                                    <th class="p-2 font-medium text-right rounded-r-lg w-[380px]">Aksi Distribusi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="(user, index) in weeklyLeaderboard" :key="user.id" class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-2 text-center font-bold text-sm">
                                        <span v-if="index === 0" class="text-yellow-500 drop-shadow-sm">🥇 1</span>
                                        <span v-else-if="index === 1" class="text-slate-400 drop-shadow-sm">🥈 2</span>
                                        <span v-else-if="index === 2" class="text-amber-600 drop-shadow-sm">🥉 3</span>
                                        <span v-else class="text-slate-400 font-medium text-xs">{{ index + 1 }}</span>
                                    </td>
                                    <td class="p-2">
                                        <div class="font-medium text-slate-900">{{ user.name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ user.email }}</div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded font-bold tracking-tight">{{ user.total_referrals }}</span>
                                    </td>
                                    
                                    <td class="p-2 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <div v-if="index <= 2" class="relative rounded-md shadow-sm w-28">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-1.5">
                                                    <span class="text-[10px] text-slate-400">Rp</span>
                                                </div>
                                                <input v-if="index === 0" v-model="savedRewards.rank1" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                                <input v-else-if="index === 1" v-model="savedRewards.rank2" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                                <input v-else-if="index === 2" v-model="savedRewards.rank3" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                            </div>

                                            <template v-if="index <= 2">
                                                <span v-if="isRewardAlreadySent(user.id, 'weekly', filterWeek, index + 1)" class="px-2 py-1 bg-emerald-50 text-emerald-600 font-medium rounded text-[9px] uppercase tracking-wide border border-emerald-200 inline-flex items-center gap-1">
                                                    ✓ Dikirim
                                                </span>
                                                <template v-else>
                                                    <button v-if="index === 0" @click="sendWeeklyReward(user, 1, savedRewards.rank1)" :disabled="!isMonday" class="px-2 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-yellow-200 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 1</button>
                                                    <button v-if="index === 1" @click="sendWeeklyReward(user, 2, savedRewards.rank2)" :disabled="!isMonday" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 2</button>
                                                    <button v-if="index === 2" @click="sendWeeklyReward(user, 3, savedRewards.rank3)" :disabled="!isMonday" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-amber-200 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 3</button>
                                                </template>
                                            </template>
                                            
                                            <button v-else @click="giveTargetReward(user)" :disabled="!isMonday" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded text-[9px] uppercase font-medium tracking-wide transition-colors border border-rose-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                                Beri Reward
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="weeklyLeaderboard.length === 0">
                                    <td colspan="4" class="p-6 text-center text-slate-400 font-medium">Belum ada transaksi afiliasi pada minggu ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 4: PERINGKAT BULANAN -->
            <div v-if="currentTab === 'leaderboard_monthly'" class="space-y-4">
                
                <!-- ALERT TANGGAL 1 (Hanya Muncul Jika Bukan Tanggal 1) -->
                <div v-if="!isFirstDayOfMonth" class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
                    <span class="text-amber-500 text-lg leading-none">⚠️</span>
                    <div>
                        <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wide">Aksi Distribusi Dikunci</h4>
                        <p class="text-[10px] text-amber-700 mt-0.5 leading-snug">Pemberian hadiah Peringkat Bulanan hanya dapat dilakukan pada <b>Tanggal 1 setiap bulannya</b>. Namun, Anda tetap bisa mengedit nominal uang hadiah kapan saja.</p>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide">👑 Top Afiliasi Bulanan</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pilih bulan di bawah ini untuk melihat arsip pemenang.</p>
                        </div>
                        <select v-model="filterMonth" @change="updateLeaderboardFilter" class="bg-slate-50 border border-slate-200 text-slate-700 text-[10px] uppercase font-medium rounded-lg px-2 pr-8 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer w-full sm:w-auto">
                            <option v-for="month in archiveMonths" :key="month.value" :value="month.value">{{ month.label }}</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase tracking-wider bg-slate-50/50">
                                    <th class="p-2 font-medium text-center w-12 rounded-l-lg">Rank</th>
                                    <th class="p-2 font-medium">Mitra Afiliasi</th>
                                    <th class="p-2 font-medium text-center">Total Referensi Sukses</th>
                                    <th class="p-2 font-medium text-right rounded-r-lg w-[380px]">Aksi Distribusi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="(user, index) in monthlyLeaderboard" :key="user.id" class="hover:bg-slate-50/40 transition-colors">
                                    <td class="p-2 text-center font-bold text-sm">
                                        <span v-if="index === 0" class="text-yellow-500 drop-shadow-sm">🥇 1</span>
                                        <span v-else-if="index === 1" class="text-slate-400 drop-shadow-sm">🥈 2</span>
                                        <span v-else-if="index === 2" class="text-amber-600 drop-shadow-sm">🥉 3</span>
                                        <span v-else class="text-slate-400 font-medium text-xs">{{ index + 1 }}</span>
                                    </td>
                                    <td class="p-2">
                                        <div class="font-medium text-slate-900">{{ user.name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ user.email }}</div>
                                    </td>
                                    <td class="p-2 text-center">
                                        <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded font-bold tracking-tight">{{ user.total_referrals }}</span>
                                    </td>
                                    
                                    <!-- KOLOM AKSI BULANAN -->
                                    <td class="p-2 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            
                                            <!-- Kolom Input Jumlah Uang Bulanan -->
                                            <div v-if="index <= 2" class="relative rounded-md shadow-sm w-28">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-1.5">
                                                    <span class="text-[10px] text-slate-400">Rp</span>
                                                </div>
                                                <input v-if="index === 0" v-model="savedMonthlyRewards.rank1" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                                <input v-else-if="index === 1" v-model="savedMonthlyRewards.rank2" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                                <input v-else-if="index === 2" v-model="savedMonthlyRewards.rank3" type="number" class="block w-full rounded-md border-0 bg-slate-50 py-1 pl-6 pr-1 text-[10px] text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-1 focus:ring-indigo-500 font-mono" />
                                            </div>

                                            <!-- Tombol Reward Bulanan dengan Keterangan Permanen -->
                                            <template v-if="index <= 2">
                                                <span v-if="isRewardAlreadySent(user.id, 'monthly', filterMonth, index + 1)" class="px-2 py-1 bg-emerald-50 text-emerald-600 font-medium rounded text-[9px] uppercase tracking-wide border border-emerald-200 inline-flex items-center gap-1">
                                                    ✓ Dikirim
                                                </span>
                                                <template v-else>
                                                    <button v-if="index === 0" @click="sendMonthlyReward(user, 1, savedMonthlyRewards.rank1)" :disabled="!isFirstDayOfMonth" class="px-2 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-yellow-200 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 1</button>
                                                    <button v-if="index === 1" @click="sendMonthlyReward(user, 2, savedMonthlyRewards.rank2)" :disabled="!isFirstDayOfMonth" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 2</button>
                                                    <button v-if="index === 2" @click="sendMonthlyReward(user, 3, savedMonthlyRewards.rank3)" :disabled="!isFirstDayOfMonth" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold rounded text-[9px] uppercase tracking-wide transition-colors border border-amber-200 disabled:opacity-40 disabled:cursor-not-allowed">🎁 Reward 3</button>
                                                </template>
                                            </template>
                                            
                                            <!-- Tombol Default Bulanan di luar 1-3 -->
                                            <button v-else @click="giveTargetReward(user)" :disabled="!isFirstDayOfMonth" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded text-[9px] uppercase font-medium tracking-wide transition-colors border border-rose-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                                Beri Reward
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="monthlyLeaderboard.length === 0">
                                    <td colspan="4" class="p-6 text-center text-slate-400 font-medium">Belum ada transaksi afiliasi pada bulan ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB LAINNYA (Daftar Afiliasi, Tarik Dana, Teks Konten) TETAP SAMA -->
            <!-- TAB 1: Daftar Mitra Afiliasi -->
            <div v-if="currentTab === 'users_list'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] text-slate-400 uppercase tracking-wider">
                                    <th class="p-3 font-medium">Nama / Email</th>
                                    <th class="p-3 font-medium">Kode Afiliasi</th>
                                    <th class="p-3 font-medium">Total Rujukan</th>
                                    <th class="p-3 font-medium">Saldo Saat Ini</th>
                                    <th class="p-3 font-medium">Info Rekening</th>
                                    <th class="p-3 font-medium text-center">Aksi Hadiah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <tr v-for="member in users" :key="member.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-3">
                                        <div class="text-slate-900 font-medium">{{ member.name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ member.email }}</div>
                                    </td>
                                    <td class="p-3 font-mono tracking-wider text-indigo-600">{{ member.affiliate_code }}</td>
                                    <td class="p-3 text-slate-600">{{ member.total_referrals }} Orang</td>
                                    <td class="p-3 text-emerald-600 font-medium">{{ formatCurrency(member.current_balance) }}</td>
                                    <td class="p-3">
                                        <div v-if="member.bank_info" class="text-[10px] text-slate-600 font-normal leading-tight">
                                            <div>{{ member.bank_info.bank_name }}</div>
                                            <div class="font-mono">{{ member.bank_info.account_number }}</div>
                                            <div>an. {{ member.bank_info.account_name }}</div>
                                        </div>
                                        <span v-else class="text-[10px] text-slate-400 font-normal">Belum diatur</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button @click="giveTargetReward(member)" class="px-2 py-1 bg-pink-50 hover:bg-pink-100 text-pink-600 rounded-lg text-[9px] uppercase font-medium transition-colors whitespace-nowrap border border-pink-100">
                                            Beri Manual
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.length === 0">
                                    <td colspan="6" class="p-6 text-center text-slate-400 font-medium">Belum ada pengguna yang terdaftar program afiliasi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Permintaan Tarik Dana -->
            <div v-if="currentTab === 'withdraw_requests'" class="space-y-4">
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100 text-[10px] text-slate-400 uppercase tracking-wider">
                                    <th class="p-3 font-medium">Tanggal</th>
                                    <th class="p-3 font-medium">Nama Mitra</th>
                                    <th class="p-3 font-medium">Nominal Tarik</th>
                                    <th class="p-3 font-medium">Rekening Tujuan</th>
                                    <th class="p-3 font-medium">Status</th>
                                    <th class="p-3 font-medium text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <tr v-for="wd in withdrawals" :key="wd.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-3 text-slate-500 whitespace-nowrap">{{ formatDate(wd.created_at) }}</td>
                                    <td class="p-3">
                                        <div class="text-slate-900 font-medium">{{ wd.user_name }}</div>
                                        <div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ wd.user_email }}</div>
                                    </td>
                                    <td class="p-3 text-emerald-600 whitespace-nowrap font-medium">{{ formatCurrency(wd.amount) }}</td>
                                    <td class="p-3">
                                        <div class="text-[10px] text-slate-600 font-normal leading-tight">
                                            <div>Bank: {{ wd.bank_name }}</div>
                                            <div class="font-mono">No: {{ wd.account_number }}</div>
                                            <div>Nama: {{ wd.account_name }}</div>
                                        </div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        <span :class="wd.status === 'approved' ? 'text-emerald-600 bg-emerald-50 border border-emerald-100' : wd.status === 'pending' ? 'text-amber-600 bg-amber-50 border border-amber-100' : 'text-rose-600 bg-rose-50 border border-rose-100'" class="text-[9px] font-medium uppercase tracking-wider px-2 py-0.5 rounded">
                                            {{ wd.status === 'approved' ? 'Telah Dikirim' : wd.status === 'pending' ? 'Menunggu' : 'Ditolak' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <div v-if="wd.status === 'pending'" class="flex items-center justify-center gap-1">
                                            <button @click="changeWithdrawStatus(wd.id, 'approved')" class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] uppercase font-medium">
                                                Setujui
                                            </button>
                                            <button @click="changeWithdrawStatus(wd.id, 'rejected')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded text-[10px] uppercase font-medium">
                                                Tolak
                                            </button>
                                        </div>
                                        <span v-else class="text-[10px] text-slate-400 font-normal">Selesai diproses</span>
                                    </td>
                                </tr>
                                <tr v-if="withdrawals.length === 0">
                                    <td colspan="6" class="p-6 text-center text-slate-400 font-medium">Belum ada pengajuan pencairan komisi masuk.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: Edit Teks Konten -->
            <div v-if="currentTab === 'edit_competition_text'">
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm p-4 max-w-2xl">
                    <h3 class="text-xs font-semibold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2 mb-4">Pengaturan Konten Kompetisi Reward</h3>
                    
                    <form @submit.prevent="submitTextUpdate" class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Judul Kompetisi</label>
                            <input v-model="textEditForm.title" type="text" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-lg px-3 py-2 text-xs font-normal text-slate-800" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-medium text-slate-400 uppercase tracking-wider mb-1">Deskripsi / Peraturan Kompetisi</label>
                            <textarea v-model="textEditForm.description" rows="4" required class="w-full bg-slate-50 border border-slate-200 focus:border-indigo-500 rounded-lg px-3 py-2 text-xs font-normal text-slate-800 leading-relaxed"></textarea>
                        </div>

                        <div class="pt-1">
                            <button type="submit" :disabled="textEditForm.processing" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-[10px] uppercase font-medium tracking-wider transition-all disabled:opacity-50">
                                {{ textEditForm.processing ? 'Menyimpan...' : 'Simpan Perubahan Teks' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation-duration: 0.4s; animation-fill-mode: both; }
</style>