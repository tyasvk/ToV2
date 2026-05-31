<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ 
    tryouts: Object,
    filters: Object 
});

const page = usePage();

// --- FITUR PENCARIAN ---
const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    router.get(route('admin.tryout-akbar.index'), { search: value }, { 
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

// --- FUNGSI HAPUS EVENT ---
const deleteEvent = (id) => {
    if(confirm('PERINGATAN: Yakin ingin menghapus event ini? Semua data soal dan peserta di dalamnya juga akan ikut terhapus permanen!')) {
        router.delete(route('admin.tryout-akbar.destroy', id), { 
            preserveScroll: true,
            onSuccess: (response) => {
                if (response.props.flash && response.props.flash.success) {
                    alert('BERHASIL: ' + response.props.flash.success);
                } else {
                    alert('Data berhasil dihapus!');
                }
            },
            onError: (errors) => {
                console.error(errors);
                alert('Terjadi kesalahan sistem saat menghapus data.');
            }
        });
    }
};

// --- FORMATTERS ---
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};

const formatCurrency = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price || 0);
};
</script>

<template>
    <Head title="Manajemen Tryout Akbar - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- OPTIMALISASI PADAT: Menggunakan p-1 sm:p-2 dan margin negatif untuk memotong paksa padding bawaan AuthenticatedLayout -->
        <div class="space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-700 w-full p-1 sm:p-2 -mx-1 sm:-mx-2 -mt-2 sm:-mt-4">
            
            <!-- TOP BAR / HEADER (Sangat rapat & Efisien) -->
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>

                <div class="relative z-10 space-y-0.5 text-left">
                    <h1 class="text-base font-bold text-slate-900 tracking-tight uppercase leading-tight">Katalog Tryout Akbar</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Manajemen event akbar, penjadwalan, harga, dan verifikasi pendaftar.</p>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto relative z-10 shrink-0">
                    <div class="relative flex-1 sm:w-60 md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari nama event..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-800 shadow-sm" 
                        />
                    </div>

                    <Link :href="route('admin.tryout-akbar.create')" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 shrink-0 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Event
                    </Link>
                </div>
            </div>

            <!-- CARD TABLE ULTRA COMPACT (Mepet Ujung ke Ujung) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-slate-600 table-auto">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Identitas Event</th>
                                <th class="px-4 py-3 w-32">Akses & Harga</th>
                                <th class="px-4 py-3 w-56">Jadwal Pelaksanaan</th>
                                <th class="px-4 py-3 text-center w-24">Status</th>
                                <th class="px-4 py-3 text-right w-44">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tryout in tryouts.data" :key="tryout.id" class="hover:bg-slate-50/80 transition-colors">
                                
                                <td class="px-4 py-3 max-w-[240px]">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-center text-blue-600 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 text-sm line-clamp-1 leading-tight" :title="tryout.title">{{ tryout.title }}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-xs text-slate-400 font-medium">
                                                <span>{{ tryout.duration }} Menit</span>
                                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                <span>{{ tryout.questions_count || 0 }} Soal</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-0.5 items-start">
                                        <span class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider"
                                            :class="tryout.is_paid ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'">
                                            {{ tryout.is_paid ? 'Berbayar' : 'Gratis' }}
                                        </span>
                                        <span v-if="tryout.is_paid" class="text-sm font-bold text-slate-800 mt-0.5 tracking-tight">
                                            {{ formatCurrency(tryout.price) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="px-4 py-3">
                                    <div class="flex flex-col text-xs text-slate-600 gap-0.5 font-medium whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <span class="text-slate-400 w-8">Mulai</span>
                                            <span class="text-slate-700 font-semibold">: {{ formatDate(tryout.started_at || tryout.start_date) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-slate-400 w-8">Akhir</span>
                                            <span class="text-slate-700 font-semibold">: {{ formatDate(tryout.ended_at || tryout.end_date) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider"
                                          :class="tryout.is_published ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200'">
                                        <span :class="tryout.is_published ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1 h-1 rounded-full"></span>
                                        {{ tryout.is_published ? 'Live' : 'Draft' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <Link :href="route('admin.tryout-akbar.edit', tryout.id)" title="Edit Event" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>

                                        <Link :href="route('admin.tryout-akbar.results', tryout.id)" title="Lihat Hasil & Peringkat" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-amber-600 hover:bg-amber-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012-2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </Link>

                                        <Link :href="route('admin.tryout-akbar.show', tryout.id)" title="Verifikasi Peserta" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-purple-600 hover:bg-purple-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                            </svg>
                                        </Link>

                                        <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" title="Manajemen Soal" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </Link>
                                        
                                        <button @click="deleteEvent(tryout.id)" title="Hapus Event" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!tryouts.data?.length">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400 font-medium">
                                    Belum ada event Tryout Akbar yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINASI -->
            <div class="flex justify-end" v-if="tryouts.links && tryouts.links.length > 3">
                <div class="flex items-center space-x-0.5 bg-white border border-slate-200 rounded-lg shadow-sm p-0.5">
                    <template v-for="(link, key) in tryouts.links" :key="key">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition-all"
                            :class="link.active ? 'bg-blue-50 border border-blue-200 text-blue-600' : 'text-slate-600 hover:bg-slate-50 border border-transparent'"
                        />
                        <span v-else v-html="link.label" class="px-2.5 py-1 text-xs text-slate-400"></span>
                    </template>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>