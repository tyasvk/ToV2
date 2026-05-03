<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({ 
    rankings: Object, 
    filters: Object,
    stats: Object 
});

const filterForm = useForm({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    month: props.filters.month || '',
    year: props.filters.year || new Date().getFullYear(),
});

const onDateChange = () => { filterForm.month = ''; };
const onMonthChange = () => { 
    filterForm.start_date = ''; 
    filterForm.end_date = ''; 
};

const months = [
    { v: 1, n: 'JANUARI' }, { v: 2, n: 'FEBRUARI' }, { v: 3, n: 'MARET' },
    { v: 4, n: 'APRIL' }, { v: 5, n: 'MEI' }, { v: 6, n: 'JUNI' },
    { v: 7, n: 'JULI' }, { v: 8, n: 'AGUSTUS' }, { v: 9, n: 'SEPTEMBER' },
    { v: 10, n: 'OKTOBER' }, { v: 11, n: 'NOVEMBER' }, { v: 12, n: 'DESEMBER' }
];

const years = Array.from({ length: 2050 - 2024 + 1 }, (_, i) => 2024 + i);

const applyFilter = () => {
    filterForm.get(route('admin.affiliate.leaderboard'), { 
        preserveState: true,
        preserveScroll: true 
    });
};

const formatPrice = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0 
}).format(num || 0);

const handleReward = (user, rank, type) => {
    const config = {
        weekly: { label: 'MINGGUAN', amounts: { 1: 50000, 2: 30000, 3: 20000 }, color: '#4f46e5' },
        monthly: { label: 'BULANAN', amounts: { 1: 100000, 2: 70000, 3: 40000 }, color: '#10b981' },
        special: { label: 'BONUS TARGET 100K', amounts: { 0: 100000 }, color: '#6366f1' }
    };

    const selected = config[type];
    const amount = (type === 'special') ? selected.amounts[0] : selected.amounts[rank];

    Swal.fire({
        title: `Kirim Hadiah ${selected.label}`,
        text: `Kirim ${formatPrice(amount)} ke saldo afiliasi ${user.name.toUpperCase()}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: selected.color,
        confirmButtonText: 'Ya, Kirim Sekarang',
        cancelButtonText: 'Batal',
        customClass: { 
            popup: 'rounded-[2.5rem] p-8 md:p-12',
            title: 'font-medium uppercase tracking-tight',
            htmlContainer: 'text-slate-500 font-medium text-xs mt-2',
            confirmButton: 'rounded-xl font-medium uppercase tracking-widest text-[10px] py-4 px-8',
            cancelButton: 'rounded-xl font-medium uppercase tracking-widest text-[10px] py-4 px-8'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.affiliate.send-reward'), { 
                user_id: user.id, amount, rank, reward_type: type,
                start_date: props.filters.start_date,
                end_date: props.filters.end_date
            }, {
                onSuccess: () => {
                    Swal.fire({ 
                        title: 'Berhasil', 
                        text: 'Hadiah telah berhasil dikirim.', 
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
    <Head title="Leaderboard Admin" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto py-6 md:py-10 px-4 md:px-6 space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                <div class="space-y-2">
                    <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tight uppercase leading-none italic">🏆 Leaderboard Affiliate</h2>
                    <p class="text-[9px] md:text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em]">Manajemen peringkat dan distribusi hadiah partner</p>
                </div>
                <a :href="route('admin.affiliate.export-pdf', filters)" 
                   class="bg-rose-50 text-rose-600 border border-rose-100 px-6 py-3.5 rounded-2xl text-[10px] font-medium uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm shadow-rose-100">
                    Export Laporan PDF
                </a>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 items-end">
                    <div class="lg:col-span-4 space-y-2">
                        <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Filter Jarak Tanggal</label>
                        <div class="flex items-center gap-2">
                            <input v-model="filterForm.start_date" @change="onDateChange" type="date" class="w-full border-transparent bg-slate-50 rounded-xl text-[10px] font-medium py-3 focus:ring-1 focus:ring-slate-900">
                            <span class="text-slate-300">/</span>
                            <input v-model="filterForm.end_date" @change="onDateChange" type="date" class="w-full border-transparent bg-slate-50 rounded-xl text-[10px] font-medium py-3 focus:ring-1 focus:ring-slate-900">
                        </div>
                    </div>
                    <div class="lg:col-span-5 space-y-2">
                        <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Filter Per Bulan & Tahun</label>
                        <div class="flex items-center gap-2">
                            <select v-model="filterForm.month" @change="onMonthChange" class="w-full border-transparent bg-slate-50 rounded-xl text-[10px] font-medium py-3 px-4 focus:ring-1 focus:ring-slate-900">
                                <option value="">PILIH BULAN</option>
                                <option v-for="m in months" :key="m.v" :value="m.v">{{ m.n }}</option>
                            </select>
                            <select v-model="filterForm.year" class="w-full border-transparent bg-slate-50 rounded-xl text-[10px] font-medium py-3 px-4 focus:ring-1 focus:ring-slate-900">
                                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="lg:col-span-3">
                        <button @click="applyFilter" class="w-full bg-slate-950 text-white py-3.5 rounded-xl text-[10px] font-medium uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg active:scale-95">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mb-3">Total Komisi Terdistribusi</p>
                    <h4 class="text-3xl font-medium text-slate-900 tracking-tighter">{{ formatPrice(stats.total_commission) }}</h4>
                </div>
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-slate-100 shadow-sm md:text-right">
                    <p class="text-[9px] font-medium text-slate-400 uppercase tracking-widest mb-3">Total Referal Terdeteksi</p>
                    <h4 class="text-3xl font-medium text-slate-900 tracking-tighter">{{ stats.total_referrals }} <span class="text-[10px] text-slate-300 uppercase ml-1 tracking-widest">User</span></h4>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="hidden md:block overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[9px] font-medium text-slate-400 uppercase tracking-[0.2em]">
                                <th class="px-10 py-6 text-center w-24">Rank</th>
                                <th class="px-8 py-6">Partner Affiliate</th>
                                <th class="px-8 py-6 text-center">Volume Referal</th>
                                <th class="px-8 py-6 text-right">Aksi Hadiah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="(rank, index) in rankings.data" :key="index" class="hover:bg-slate-50/30 transition-all group">
                                <td class="px-10 py-8 text-center">
                                    <span class="text-2xl font-medium tracking-tighter" 
                                          :class="index + rankings.from <= 3 ? 'text-indigo-600' : 'text-slate-200'">
                                        #{{ index + rankings.from }}
                                    </span>
                                </td>
                                <td class="px-8 py-8">
                                    <p class="font-medium text-xs uppercase text-slate-900 tracking-tight">{{ rank.referrer?.name }}</p>
                                    <span class="text-[9px] font-medium text-indigo-400 uppercase tracking-widest mt-1.5 block leading-none">
                                        {{ rank.referrer?.affiliate_code }}
                                    </span>
                                </td>
                                <td class="px-8 py-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="font-medium text-xs uppercase text-slate-900 leading-none">{{ rank.total_referrals }} User</span>
                                        <span v-if="rank.total_referrals >= 100" class="text-[7px] font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md uppercase tracking-wider border border-emerald-100">
                                            Target 100 Tercapai
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-8 text-right">
                                    <div class="flex justify-end gap-2 items-center">
                                        <template v-if="index + rankings.from <= 3">
                                            <div v-if="rank.referrer.weekly_sent > 0" class="text-[8px] font-medium text-slate-300 uppercase px-3 py-2 border border-slate-100 rounded-xl bg-slate-50">MINGGUAN ✅</div>
                                            <button v-else @click="handleReward(rank.referrer, index + rankings.from, 'weekly')" 
                                                    class="bg-white border border-slate-100 text-indigo-600 px-4 py-2.5 rounded-xl text-[8px] font-medium uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                                🎁 Mingguan
                                            </button>
                                            
                                            <div v-if="rank.referrer.monthly_sent > 0" class="text-[8px] font-medium text-slate-300 uppercase px-3 py-2 border border-slate-100 rounded-xl bg-slate-50">BULANAN ✅</div>
                                            <button v-else @click="handleReward(rank.referrer, index + rankings.from, 'monthly')" 
                                                    class="bg-white border border-slate-100 text-emerald-600 px-4 py-2.5 rounded-xl text-[8px] font-medium uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                🏆 Bulanan
                                            </button>
                                        </template>

                                        <template v-if="rank.total_referrals >= 100">
                                            <div v-if="rank.referrer.bonus_sent_count > 0" class="text-[8px] font-medium text-indigo-300 uppercase px-3 py-2 border border-indigo-50 rounded-xl bg-indigo-50/30">BONUS 100K ✅</div>
                                            <button v-else @click="handleReward(rank.referrer, 0, 'special')" 
                                                    class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-[8px] font-medium uppercase tracking-widest hover:bg-slate-900 transition-all shadow-sm active:scale-95">
                                                💎 Bonus 100k
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-slate-50">
                    <div v-for="(rank, index) in rankings.data" :key="index" class="p-6 space-y-6">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                <span class="text-xl font-medium" :class="index + rankings.from <= 3 ? 'text-indigo-600' : 'text-slate-200'">#{{ index + rankings.from }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-xs uppercase text-slate-900 truncate">{{ rank.referrer?.name }}</p>
                                    <p class="text-[9px] font-medium text-indigo-400 uppercase tracking-widest mt-1">{{ rank.referrer?.affiliate_code }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-slate-900 uppercase">{{ rank.total_referrals }} User</p>
                                <span v-if="rank.total_referrals >= 100" class="text-[7px] font-medium text-emerald-600 uppercase tracking-wider">Target 100 ✅</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <template v-if="index + rankings.from <= 3">
                                <button v-if="rank.referrer.weekly_sent == 0" @click="handleReward(rank.referrer, index + rankings.from, 'weekly')" class="flex-1 py-3 bg-indigo-50 text-indigo-600 rounded-xl text-[8px] font-medium uppercase tracking-widest">🎁 Mingguan</button>
                                <button v-if="rank.referrer.monthly_sent == 0" @click="handleReward(rank.referrer, index + rankings.from, 'monthly')" class="flex-1 py-3 bg-emerald-50 text-emerald-600 rounded-xl text-[8px] font-medium uppercase tracking-widest">🏆 Bulanan</button>
                            </template>
                            <button v-if="rank.total_referrals >= 100 && rank.referrer.bonus_sent_count == 0" @click="handleReward(rank.referrer, 0, 'special')" class="w-full py-3 bg-indigo-600 text-white rounded-xl text-[8px] font-medium uppercase tracking-widest">💎 Bonus 100k</button>
                        </div>
                    </div>
                </div>

                <div v-if="rankings.data.length === 0" class="px-8 py-20 text-center">
                    <p class="text-[10px] text-slate-300 font-medium uppercase tracking-[0.4em]">Data tidak tersedia untuk periode ini</p>
                </div>
            </div>

            <div v-if="rankings.links.length > 3" class="flex justify-center gap-2 pb-12">
                <template v-for="(link, k) in rankings.links" :key="k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" 
                          class="px-5 py-3 rounded-2xl text-[10px] font-medium uppercase tracking-widest transition-all" 
                          :class="link.active ? 'bg-slate-900 text-white shadow-md scale-105' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>