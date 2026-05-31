<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash'; 

const props = defineProps({
    tryouts: Object,
    filters: Object
});

// --- FITUR PENCARIAN ---
const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    router.get(route('admin.adidaya.index'), { search: value }, { 
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

// --- STATE: MODAL FORM (CREATE/EDIT) ---
const showModal = ref(false);
const isEdit = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    duration: 110,
    description: '',
    is_published: false,
    published_at: '',
    started_at: '',
    end_date: '',
    is_paid: false,
    price: 0,
    type: 'adidaya' 
});

const openCreateModal = () => {
    isEdit.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (tryout) => {
    isEdit.value = true;
    editingId.value = tryout.id;
    form.title = tryout.title;
    form.duration = tryout.duration; 
    form.description = tryout.description;
    form.is_published = !!tryout.is_published;
    form.published_at = tryout.published_at ? tryout.published_at.substring(0, 16) : '';
    form.started_at = tryout.started_at ? tryout.started_at.substring(0, 16) : '';
    form.end_date = tryout.end_date ? tryout.end_date.substring(0, 16) : '';
    form.is_paid = !!tryout.is_paid;
    form.price = tryout.price;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.adidaya.update', editingId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.adidaya.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus paket tryout ini?')) {
        router.delete(route('admin.adidaya.destroy', id), { preserveScroll: true });
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
    <Head title="Manajemen Adidaya - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-3 animate-in fade-in slide-in-from-bottom-4 duration-700 w-full p-1 sm:p-2 -mx-1 sm:-mx-2 -mt-2 sm:-mt-4">
            
            <div class="bg-white p-3 sm:p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>

                <div class="relative z-10 space-y-0.5 text-left">
                    <h1 class="text-base font-bold text-slate-900 tracking-tight uppercase leading-tight">Katalog Adidaya</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Manajemen penjadwalan program adidaya, akses, dan bank soal.</p>
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
                            placeholder="Cari paket adidaya..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-800 shadow-sm" 
                        />
                    </div>

                    <button @click="openCreateModal" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 shrink-0 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Paket
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-slate-600 table-auto">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Identitas Paket</th>
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
                                            {{ tryout.is_paid ? 'Premium' : 'Gratis' }}
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
                                            <span class="text-slate-700 font-semibold">: {{ formatDate(tryout.started_at) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-slate-400 w-8">Akhir</span>
                                            <span class="text-slate-700 font-semibold">: {{ formatDate(tryout.end_date) }}</span>
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
                                        
                                        <button @click="openEditModal(tryout)" title="Edit Paket" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        
                                        <Link :href="route('admin.adidaya.results', tryout.id)" title="Lihat Hasil & Peringkat" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-amber-600 hover:bg-amber-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012-2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </Link>
                                        
                                        <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" title="Manajemen Soal" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </Link>
                                        
                                        <button @click="deleteTryout(tryout.id)" title="Hapus Paket" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition active:scale-95 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!tryouts.data.length">
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400 font-medium">
                                    Tidak ada paket adidaya ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

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
                        <span v-else class="px-2.5 py-1 text-xs text-slate-400" v-html="link.label"></span>
                    </template>
                </div>
            </div>

        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal"></div>
                <div class="relative bg-white w-full max-w-lg rounded-2xl p-6 sm:p-8 shadow-xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                    <h3 class="font-semibold text-lg text-slate-900 mb-6 shrink-0">
                        {{ isEdit ? 'Perbarui Paket Adidaya' : 'Buat Paket Adidaya' }}
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-4 overflow-y-auto pr-2 custom-scrollbar flex-1 text-left">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-rose-500">*</span></label>
                            <input v-model="form.title" type="text" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="Contoh: Paket Adidaya 2026" required />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Durasi (Menit) <span class="text-rose-500">*</span></label>
                                <input v-model="form.duration" type="number" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" required />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Akses Pengguna</label>
                                <div class="grid grid-cols-2 gap-2 h-[42px]">
                                    <button type="button" @click="form.is_paid = false"
                                        :class="!form.is_paid ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                        class="rounded-xl text-xs font-semibold transition-all border shadow-sm flex items-center justify-center">
                                        Gratis
                                    </button>
                                    <button type="button" @click="form.is_paid = true"
                                        :class="form.is_paid ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                        class="rounded-xl text-xs font-semibold transition-all border shadow-sm flex items-center justify-center">
                                        Premium
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.is_paid" class="bg-slate-50 border border-slate-200 p-4 rounded-xl space-y-1.5 animate-in fade-in zoom-in-95 duration-200">
                            <label class="block text-xs font-semibold text-slate-700">Harga Paket (IDR) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-500 text-sm font-medium">Rp</span>
                                </div>
                                <input v-model="form.price" type="number" class="w-full bg-white border border-slate-300 rounded-xl pl-9 pr-4 py-2 text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="50000" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Waktu Mulai</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Waktu Berakhir</label>
                                <input v-model="form.end_date" type="datetime-local" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Waktu Publish (Optional)</label>
                            <input v-model="form.published_at" type="datetime-local" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2.5 text-xs font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Status Visibilitas</label>
                            <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                                <button type="button" @click="form.is_published = false" :class="[!form.is_published ? 'bg-white text-slate-800 shadow-sm border border-slate-200' : 'text-slate-500']" class="py-2 rounded-lg font-semibold text-xs transition">
                                    Draft (Sembunyikan)
                                </button>
                                <button type="button" @click="form.is_published = true" :class="[form.is_published ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500']" class="py-2 rounded-lg font-semibold text-xs transition">
                                    Live (Publikasi)
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-6 pb-2 shrink-0 border-t border-slate-100 mt-4">
                            <button type="button" @click="closeModal" class="flex-1 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                            <button type="submit" :disabled="form.processing" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-xl font-semibold text-xs shadow-sm active:scale-95 transition-all">
                                {{ isEdit ? 'Simpan Perubahan' : 'Buat Paket Adidaya' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>