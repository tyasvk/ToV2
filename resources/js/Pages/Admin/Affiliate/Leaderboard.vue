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

/**
 * Logika Filter Saling Meniadakan (Mutual Exclusion)
 */
const onDateChange = () => { 
    filterForm.month = ''; 
};

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

// Generasi Tahun Dinamis 2024 - 2050
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

/**
 * Pengiriman Hadiah Manual dengan Deskripsi Periode Otomatis
 */
const handleReward = (user, rank, type) => {
    const config = {
        weekly: { 
            label: 'MINGGUAN', 
            amounts: { 1: 50000, 2: 30000, 3: 20000 }, 
            color: '#3B82F6' 
        },
        monthly: { 
            label: 'BULANAN', 
            amounts: { 1: 100000, 2: 70000, 3: 40000 }, 
            color: '#10B981' 
        },
        special: { 
            label: 'BONUS TARGET 100K', 
            amounts: { 0: 100000 }, 
            color: '#6366F1' 
        }
    };

    const selected = config[type];
    const amount = (type === 'special') ? selected.amounts[0] : selected.amounts[rank];

    Swal.fire({
        title: `KIRIM HADIAH ${selected.label}`,
        text: `KIRIM ${formatPrice(amount)} KE SALDO AFILIASI ${user.name.toUpperCase()}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: selected.color,
        confirmButtonText: 'YA, KIRIM SEKARANG',
        cancelButtonText: 'BATAL',
        customClass: { popup: 'rounded-[2.5rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.affiliate.send-reward'), { 
                user_id: user.id, 
                amount: amount, 
                rank: rank,
                reward_type: type,
                // Kirim data filter ke server untuk deskripsi mutasi user
                start_date: props.filters.start_date,
                end_date: props.filters.end_date
            }, {
                onSuccess: () => {
                    Swal.fire({ 
                        title: 'BERHASIL!', 
                        text: 'HADIAH DAN DESKRIPSI PERIODE TELAH TERKIRIM.', 
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
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8 mb-10">
            <div>
                <h2 class="font-black text-4xl text-slate-900 tracking-tighter uppercase leading-none">🏆 Leaderboard Affiliate</h2>
                <div class="mt-5">
                    <a :href="route('admin.affiliate.export-pdf', filters)" 
                       class="bg-rose-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-100 hover:bg-rose-700 transition-all">
                       📄 Export Laporan PDF
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 bg-white p-5 rounded-[3rem] border border-slate-100 shadow-xl w-full xl:w-auto">
                <div class="flex items-center gap-2 border-r border-slate-100 pr-5">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Kalender:</span>
                    <input v-model="filterForm.start_date" @change="onDateChange" type="date" class="border-none bg-slate-50 rounded-xl text-[10px] font-bold py-2 focus:ring-2 focus:ring-slate-900">
                    <input v-model="filterForm.end_date" @change="onDateChange" type="date" class="border-none bg-slate-50 rounded-xl text-[10px] font-bold py-2 focus:ring-2 focus:ring-slate-900">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Bulan:</span>
                    <select v-model="filterForm.month" @change="onMonthChange" class="border-none bg-slate-50 rounded-xl text-[10px] font-bold py-2 px-4 focus:ring-2 focus:ring-slate-900">
                        <option value="">PILIH BULAN</option>
                        <option v-for="m in months" :key="m.v" :value="m.v">{{ m.n }}</option>
                    </select>
                    <select v-model="filterForm.year" class="border-none bg-slate-50 rounded-xl text-[10px] font-bold py-2 px-4 focus:ring-2 focus:ring-slate-900">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <button @click="applyFilter" class="bg-slate-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all ml-auto">
                    Terapkan
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 leading-none">Total Komisi Terdistribusi</p>
                <h4 class="text-4xl font-black text-slate-900 leading-none">{{ formatPrice(stats.total_commission) }}</h4>
            </div>
            <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm text-right">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 leading-none">Total Referal Terdeteksi</p>
                <h4 class="text-4xl font-black text-slate-900 leading-none">{{ stats.total_referrals }} <span class="text-xs text-slate-300 uppercase ml-2 tracking-widest">User</span></h4>
            </div>
        </div>

        <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="px-10 py-6 text-center w-24">Rank</th>
                            <th class="px-8 py-6">Partner Affiliate</th>
                            <th class="px-8 py-6 text-center">Volume Referal</th>
                            <th class="px-8 py-6 text-right">Aksi Hadiah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="(rank, index) in rankings.data" :key="index" class="hover:bg-slate-50/30 transition-all group">
                            <td class="px-10 py-8 text-center">
                                <span class="text-xl font-black leading-none" 
                                      :class="index + rankings.from <= 3 ? 'text-indigo-600' : 'text-slate-200'">
                                    #{{ index + rankings.from }}
                                </span>
                            </td>
                            <td class="px-8 py-8">
                                <p class="font-black text-xs uppercase text-slate-900 leading-none tracking-tight">{{ rank.referrer?.name }}</p>
                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest mt-2 block leading-none">
                                    {{ rank.referrer?.affiliate_code }}
                                </span>
                            </td>
                            <td class="px-8 py-8 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-black text-xs uppercase text-slate-900 leading-none">{{ rank.total_referrals }} USER</span>
                                    <span v-if="rank.total_referrals >= 100" class="mt-2 text-[8px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md uppercase tracking-tighter border border-emerald-100">
                                        TARGET 100 TERCAPAI
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-8 text-right">
                                <div class="flex justify-end gap-3 items-center">
                                    <template v-if="index + rankings.from <= 3">
                                        <div v-if="rank.referrer.weekly_sent > 0" class="text-[8px] font-black text-slate-300 uppercase px-3 py-2 border border-slate-100 rounded-xl bg-slate-50 cursor-default">
                                            MINGGUAN ✅
                                        </div>
                                        <button v-else @click="handleReward(rank.referrer, index + rankings.from, 'weekly')" 
                                                class="bg-blue-50 text-blue-600 px-4 py-2.5 rounded-xl text-[8px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            🎁 MINGGUAN
                                        </button>
                                        
                                        <div v-if="rank.referrer.monthly_sent > 0" class="text-[8px] font-black text-slate-300 uppercase px-3 py-2 border border-slate-100 rounded-xl bg-slate-50 cursor-default">
                                            BULANAN ✅
                                        </div>
                                        <button v-else @click="handleReward(rank.referrer, index + rankings.from, 'monthly')" 
                                                class="bg-emerald-50 text-emerald-600 px-4 py-2.5 rounded-xl text-[8px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            🏆 BULANAN
                                        </button>
                                    </template>

                                    <template v-if="rank.total_referrals >= 100">
                                        <div v-if="rank.referrer.bonus_sent_count > 0" class="text-[8px] font-black text-indigo-300 uppercase px-3 py-2 border border-indigo-50 rounded-xl bg-indigo-50/30 cursor-default">
                                            BONUS 100K ✅
                                        </div>
                                        <button v-else @click="handleReward(rank.referrer, 0, 'special')" 
                                                class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-[8px] font-black uppercase tracking-widest hover:bg-indigo-900 transition-all shadow-sm tracking-tighter">
                                            💎 BONUS 100K
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="rankings.data.length === 0">
                            <td colspan="4" class="px-8 py-24 text-center">
                                <p class="text-[10px] text-slate-300 font-black uppercase tracking-[0.4em]">Data tidak tersedia untuk periode ini</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="rankings.links.length > 3" class="px-10 py-8 border-t border-slate-50 bg-slate-50/20 flex justify-center gap-2">
                <template v-for="(link, k) in rankings.links" :key="k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" 
                          class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm" 
                          :class="link.active ? 'bg-slate-900 text-white shadow-lg scale-105' : 'bg-white text-slate-400 border border-slate-100 hover:bg-slate-50'" />
                    <span v-else v-html="link.label" class="px-5 py-3 text-[10px] font-black text-slate-200 uppercase tracking-widest bg-slate-50/50 rounded-2xl"></span>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Pembersihan Tampilan Scrollbar */
.overflow-x-auto::-webkit-scrollbar { height: 4px; }
.overflow-x-auto::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>