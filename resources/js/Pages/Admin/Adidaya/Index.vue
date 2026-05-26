<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    tryouts: Object,
    filters: Object
});

// --- FITUR PENCARIAN ---
const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    // Sesuaikan rute dengan nama route Adidaya di web.php Anda (misalnya admin.adidaya.index)
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
    type: 'adidaya' // Bisa disesuaikan jika Adidaya menggunakan value type lain
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
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Katalog Adidaya</h1>
                    <p class="text-sm text-slate-500 font-medium">Manajemen penjadwalan program adidaya, akses, dan bank soal.</p>
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
                            placeholder="Cari paket adidaya..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none" 
                        />
                    </div>

                    <button @click="openCreateModal" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Paket
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Identitas Paket</th>
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
                                            {{ tryout.is_paid ? 'Premium' : 'Gratis' }}
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
                                            <span class="text-slate-700">: {{ formatDate(tryout.started_at) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 w-10">Akhir</span>
                                            <span class="text-slate-700">: {{ formatDate(tryout.end_date) }}</span>
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
                                        <button @click="openEditModal(tryout)" title="Edit Paket" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        
                                        <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" title="Manajemen Soal" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </Link>
                                        
                                        <button @click="deleteTryout(tryout.id)" title="Hapus Paket" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!tryouts.data.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 font-medium">
                                    Tidak ada paket adidaya ditemukan.
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

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal"></div>
                <div class="relative bg-white w-full max-w-lg rounded-2xl p-6 sm:p-8 shadow-xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh]">
                    
                    <h3 class="font-semibold text-lg text-slate-900 mb-6 shrink-0">
                        {{ isEdit ? 'Perbarui Paket Adidaya' : 'Buat Paket Adidaya' }}
                    </h3>
                    
                    <form @submit.prevent="submit" class="space-y-4 overflow-y-auto pr-2 custom-scrollbar flex-1">
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