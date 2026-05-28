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

// --- FUNGSI HAPUS DENGAN PELACAK ERROR ---
// --- FUNGSI HAPUS YANG BENAR ---
const deleteEvent = (id) => {
    if(confirm('PERINGATAN: Yakin ingin menghapus event ini? Semua data soal dan peserta di dalamnya juga akan ikut terhapus permanen!')) {
        router.delete(route('admin.tryout-akbar.destroy', id), { 
            preserveScroll: true,
            onSuccess: (response) => {
                // Menangkap pesan sukses langsung dari response Inertia
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
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Katalog Tryout Akbar</h1>
                    <p class="text-sm text-slate-500 font-medium">Manajemen event akbar, penjadwalan, harga, dan verifikasi pendaftar.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-72 lg:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari event akbar..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none" 
                        />
                    </div>

                    <Link :href="route('admin.tryout-akbar.create')" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Event Akbar
                    </Link>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Identitas Event</th>
                                <th class="px-6 py-4">Akses & Harga</th>
                                <th class="px-6 py-4">Jadwal Pelaksanaan</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tryout in tryouts.data" :key="tryout.id" class="hover:bg-slate-50/80 transition-colors">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-blue-600 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900 text-sm truncate max-w-[250px]" :title="tryout.title">{{ tryout.title }}</p>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                                                <span>{{ tryout.duration }} Menit</span>
                                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                                <span>{{ tryout.questions_count || 0 }} Soal</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span 
                                            class="px-2.5 py-1 rounded-md text-[10px] font-semibold uppercase tracking-wider border"
                                            :class="tryout.is_paid ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'"
                                        >
                                            {{ tryout.is_paid ? 'Berbayar' : 'Gratis' }}
                                        </span>
                                        <span v-if="tryout.is_paid" class="text-sm font-medium text-slate-900 mt-0.5">
                                            {{ formatCurrency(tryout.price) }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="flex flex-col text-xs text-slate-600 gap-1 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 w-10">Mulai</span>
                                            <!-- Perbaikan nama variabel tanggal mulai -->
                                            <span class="text-slate-700">: {{ formatDate(tryout.started_at || tryout.start_date) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 w-10">Akhir</span>
                                            <!-- Perbaikan nama variabel tanggal akhir -->
                                            <span class="text-slate-700">: {{ formatDate(tryout.ended_at || tryout.end_date) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-semibold uppercase tracking-wider border"
                                          :class="tryout.is_published ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200'">
                                        <span :class="tryout.is_published ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full"></span>
                                        {{ tryout.is_published ? 'Live' : 'Draft' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <Link :href="route('admin.tryout-akbar.edit', tryout.id)" title="Edit Event" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>

                                        <Link :href="route('admin.tryout-akbar.show', tryout.id)" title="Verifikasi Peserta" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                            </svg>
                                        </Link>

                                        <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" title="Manajemen Soal" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </Link>
                                        
                                        <button @click="deleteEvent(tryout.id)" title="Hapus Event" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!tryouts.data?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 font-medium">
                                    Belum ada event Tryout Akbar yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4 flex justify-end" v-if="tryouts.links && tryouts.links.length > 3">
                <div class="flex items-center space-x-1 bg-white border border-slate-200 rounded-lg shadow-sm p-1">
                    <template v-for="(link, key) in tryouts.links" :key="key">
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
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>