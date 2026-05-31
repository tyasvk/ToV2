<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    results: Object, 
    filters: Object
});

// --- DETEKSI MENU AKTIF UNTUK LINK DINAMIS ---
const isAkbar = route().current('admin.tryout-akbar.*');
const isAdidaya = route().current('admin.adidaya.*');

const indexRouteName = computed(() => {
    if (isAkbar) return 'admin.tryout-akbar.index';
    if (isAdidaya) return 'admin.adidaya.index';
    return 'admin.tryouts.index';
});

const resultRouteName = computed(() => {
    if (isAkbar) return 'admin.tryout-akbar.results';
    if (isAdidaya) return 'admin.adidaya.results';
    return 'admin.tryouts.results';
});

// --- FITUR PENCARIAN REAL-TIME ---
const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    router.get(route(resultRouteName.value, props.tryout.id), { search: value }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
};

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch(value);
    }, 500);
});

// --- FITUR HAPUS DATA (RESET KESEMPATAN) ---
const deleteAttempt = (attemptId) => {
    if (confirm('Apakah Anda yakin ingin menghapus data pengerjaan peserta ini? Tindakan ini akan mereset riwayat dan mengizinkan peserta mengerjakan ulang ujian dari awal.')) {
        router.delete(route('admin.tryouts.attempts.destroy', attemptId), {
            preserveScroll: true
        });
    }
};

// --- FITUR RECALCULATE SKOR ---
const recalculateScores = () => {
    if (confirm('Apakah Anda yakin ingin menghitung ulang skor seluruh peserta pada Tryout ini? Proses ini akan menyesuaikan ulang nilai dan peringkat jika ada perubahan kunci jawaban.')) {
        router.post(route('admin.tryouts.recalculate', props.tryout.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                alert('Skor berhasil dikalkulasi ulang!');
            }
        });
    }
};
</script>

<template>
    <Head :title="`Hasil Ujian - ${tryout.title}`" />

    <AuthenticatedLayout>
        <!-- OPTIMALISASI PADAT KANVAS -->
        <div class="space-y-4 animate-in fade-in duration-500 w-full px-2 sm:px-4 py-2">
            
            <!-- HEADER -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-48 h-48 bg-blue-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>

                <div class="relative z-10 text-left space-y-1">
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight uppercase leading-tight">{{ tryout.title }}</h1>
                    <p class="text-xs text-slate-500 font-medium">Hasil SKD, kelulusan Passing Grade, dan kontrol reset data.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto relative z-10 shrink-0">
                    <div class="relative flex-1 sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari nama/email..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" 
                        />
                    </div>

                    <!-- TOMBOL KEMBALI DINAMIS -->
                    <!-- CONTAINER TOMBOL AKSI -->
<div class="flex items-center gap-2 w-full sm:w-auto relative z-10 shrink-0">
    
    <!-- TOMBOL BARU: KALKULASI ULANG SKOR -->
    <button @click="recalculateScores" class="px-3 py-2 bg-rose-50 border border-rose-200 text-rose-600 hover:bg-rose-100 hover:border-rose-300 font-bold text-[11px] sm:text-xs rounded-lg shadow-sm transition-all flex items-center gap-1.5 shrink-0 active:scale-95 uppercase tracking-wide">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span class="hidden sm:inline">Kalkulasi Ulang</span>
    </button>

    <!-- TOMBOL KEMBALI DINAMIS (Yang sudah ada sebelumnya) -->
    <Link :href="route(indexRouteName)" class="px-3 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-[11px] sm:text-xs rounded-lg shadow-sm transition-all flex items-center gap-1.5 shrink-0 active:scale-95 uppercase tracking-wide">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Kembali
    </Link>
</div>
                </div>
            </div>

            <!-- TABEL PEMERINGKATAN -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-slate-600 table-auto">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-4 text-center w-12">No</th>
                                <th class="px-4 py-4">Data Peserta</th>
                                <th class="px-4 py-4 text-center w-16">TWK</th>
                                <th class="px-4 py-4 text-center w-16">TIU</th>
                                <th class="px-4 py-4 text-center w-16">TKP</th>
                                <th class="px-4 py-4 text-center w-20">Skor</th>
                                <th class="px-4 py-4 text-center w-20">Status</th>
                                <th class="px-4 py-4 text-right">Tgl Selesai & Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="results.data.length === 0">
                                <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-400 font-medium">
                                    Tidak ada data pengerjaan ditemukan.
                                </td>
                            </tr>
                            <tr v-for="row in results.data" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full font-bold text-xs shadow-sm"
                                        :class="{
                                            'bg-amber-100 text-amber-600 border border-amber-200': row.rank === 1,
                                            'bg-slate-200 text-slate-600 border border-slate-300': row.rank === 2,
                                            'bg-orange-100 text-orange-600 border border-orange-200': row.rank === 3,
                                            'bg-transparent text-slate-500 border border-slate-200': row.rank > 3
                                        }">
                                        {{ row.rank }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 min-w-[200px]">
                                    <div class="font-bold text-slate-900 text-sm line-clamp-1 leading-tight" :title="row.user_name">{{ row.user_name }}</div>
                                    <div class="text-xs text-slate-400 font-medium mt-0.5 truncate">{{ row.user_email }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-sm tabular-nums" :class="row.twk_score >= 65 ? 'text-emerald-600' : 'text-rose-500'">{{ row.twk_score }}</td>
                                <td class="px-4 py-3 text-center font-bold text-sm tabular-nums" :class="row.tiu_score >= 80 ? 'text-emerald-600' : 'text-rose-500'">{{ row.tiu_score }}</td>
                                <td class="px-4 py-3 text-center font-bold text-sm tabular-nums" :class="row.tkp_score >= 166 ? 'text-emerald-600' : 'text-rose-500'">{{ row.tkp_score }}</td>
                                <td class="px-4 py-3 text-center font-black text-slate-800 text-lg tabular-nums">{{ row.total_score }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span v-if="row.is_passed" class="px-2.5 py-1 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-md text-xs font-bold uppercase tracking-wider">
                                        Lulus
                                    </span>
                                    <span v-else class="px-2.5 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-md text-xs font-bold uppercase tracking-wider">
                                        Gagal
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <span class="text-sm font-medium text-slate-500 hidden md:block text-right whitespace-nowrap tracking-tight">
                                            {{ row.completed_at }}
                                        </span>
                                        <button 
                                            @click="deleteAttempt(row.id)" 
                                            title="Hapus Hasil & Ujian Ulang" 
                                            class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 rounded-lg shadow-sm active:scale-95 shrink-0 transition-colors"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINASI -->
            <div class="flex justify-end" v-if="results.links && results.links.length > 3">
                <div class="flex items-center space-x-1 bg-white border border-slate-200 rounded-lg shadow-sm p-1">
                    <template v-for="(link, key) in results.links" :key="key">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all"
                            :class="link.active ? 'bg-blue-50 border border-blue-200 text-blue-600' : 'text-slate-600 hover:bg-slate-50 border border-transparent'"
                        />
                        <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-slate-400"></span>
                    </template>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.tabular-nums { font-variant-numeric: tabular-nums; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>