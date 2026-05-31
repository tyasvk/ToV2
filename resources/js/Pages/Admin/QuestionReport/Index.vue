<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    reports: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    router.get(route('admin.question-reports.index'), { search: value }, { 
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

// Update Status (Tandai Selesai / Pending)
const updateStatus = (id, currentStatus) => {
    const newStatus = currentStatus === 'resolved' ? 'pending' : 'resolved';
    const message = newStatus === 'resolved' 
        ? 'Tandai laporan ini sudah diperbaiki/diselesaikan?' 
        : 'Kembalikan status laporan ini menjadi pending/belum selesai?';
        
    if (confirm(message)) {
        router.patch(route('admin.question-reports.update-status', id), { status: newStatus }, {
            preserveScroll: true
        });
    }
};

const deleteReport = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus log laporan ini secara permanen?')) {
        router.delete(route('admin.question-reports.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Laporan Kesalahan Soal - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- OPTIMALISASI PADAT KANVAS (Mepet Ujung) -->
        <div class="space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-700 w-full p-1 sm:p-2 -mx-1 sm:-mx-2 -mt-2 sm:-mt-4">
            
            <!-- HEADER -->
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-rose-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>

                <div class="relative z-10 space-y-0.5 text-left">
                    <h1 class="text-base font-bold text-slate-900 tracking-tight uppercase leading-tight">Laporan Kesalahan Soal</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Tinjau dan perbaiki soal yang dilaporkan salah oleh peserta.</p>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto relative z-10 shrink-0">
                    <div class="relative flex-1 sm:w-60 md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-3.5 w-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari pelapor atau jenis masalah..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none transition-all font-medium text-slate-800 shadow-sm" 
                        />
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-slate-600 table-auto">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3 w-48">Pelapor & Waktu</th>
                                <th class="px-4 py-3 w-56">Jenis Laporan</th>
                                <th class="px-4 py-3 min-w-[200px]">Detail Laporan & Cuplikan Soal</th>
                                <th class="px-4 py-3 text-center w-28">Status</th>
                                <th class="px-4 py-3 text-right w-36">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="report in reports.data" :key="report.id" class="hover:bg-slate-50/80 transition-colors group">
                                
                                <!-- PELAPOR & WAKTU -->
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900 text-xs line-clamp-1" :title="report.user_name">{{ report.user_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ report.user_email }}</div>
                                    <div class="text-[10px] font-semibold text-slate-500 mt-1.5 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ report.created_at }}
                                    </div>
                                </td>

                                <!-- JENIS MASALAH -->
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider whitespace-nowrap"
                                        :class="report.status === 'resolved' ? 'bg-slate-100 text-slate-500 border-slate-200' : 'bg-rose-50 text-rose-600 border-rose-200'">
                                        {{ report.reason }}
                                    </span>
                                </td>

                                <!-- DESKRIPSI & SOAL -->
                                <td class="px-4 py-3">
                                    <p class="text-[11px] sm:text-xs font-medium text-slate-800 leading-snug mb-1">
                                        "{{ report.description || 'Tidak ada deskripsi tambahan dari pelapor.' }}"
                                    </p>
                                    <!-- Cuplikan Soal -->
                                    <div class="bg-slate-50 border border-slate-100 p-2 rounded text-[10px] text-slate-500 line-clamp-2 italic" v-html="report.question_content"></div>
                                </td>

                                <!-- STATUS -->
                                <td class="px-4 py-3 text-center">
                                    <span v-if="report.status === 'resolved'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        Selesai
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 border border-amber-200 text-amber-600 text-[10px] font-bold uppercase tracking-wider">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                                        Pending
                                    </span>
                                </td>

                                <!-- AKSI -->
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- Tombol Pergi ke Soal (Jika id tryout dan question tersedia) -->
                                        <Link v-if="report.tryout_id" :href="route('admin.tryouts.questions.index', report.tryout_id)" title="Cari/Perbaiki Soal Ini" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        </Link>

                                        <!-- Tombol Update Status (Selesai/Pending) -->
                                        <button @click="updateStatus(report.id, report.status)" :title="report.status === 'resolved' ? 'Ubah jadi Pending' : 'Tandai Selesai'" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 rounded-lg shadow-sm transition active:scale-95 shrink-0" :class="report.status === 'resolved' ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'">
                                            <svg v-if="report.status === 'resolved'" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </button>

                                        <!-- Tombol Hapus Log -->
                                        <button @click="deleteReport(report.id)" title="Hapus Laporan" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!reports.data.length">
                                <td colspan="5" class="px-4 py-12 text-center text-xs text-slate-400 font-medium">
                                    Hore! Tidak ada laporan kesalahan soal yang masuk.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="flex justify-end" v-if="reports.links && reports.links.length > 3">
                <div class="flex items-center space-x-0.5 bg-white border border-slate-200 rounded-lg shadow-sm p-0.5">
                    <template v-for="(link, key) in reports.links" :key="key">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-2.5 py-1 rounded text-xs font-semibold transition-all"
                            :class="link.active ? 'bg-rose-50 border border-rose-200 text-rose-600' : 'text-slate-600 hover:bg-slate-50 border border-transparent'"
                        />
                        <span v-else class="px-2.5 py-1 text-xs text-slate-400" v-html="link.label"></span>
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
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>