<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { debounce } from 'lodash'; 

const props = defineProps({
    tryouts: Object,
    filters: Object
});

// --- STATE MODAL ---
const showModal = ref(false);
const isEdit = ref(false);
const editingId = ref(null);
const search = ref(props.filters?.search || '');

// --- SEARCH LOGIC ---
const handleSearch = debounce(() => {
    router.get(route('admin.adidaya-manage.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 500);

// --- FORM DATA ---
const form = useForm({
    title: '',
    duration: 110,
    description: '',
    is_published: false,
    published_at: '',
    started_at: '',
    is_paid: true, 
    price: 0,
});

// --- LOGIKA MODAL ---
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
    form.is_paid = !!tryout.is_paid;
    form.price = tryout.price;
    
    showModal.value = true;
};

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.adidaya-manage.update', editingId.value), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.adidaya-manage.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Hapus paket adidaya ini?')) {
        router.delete(route('admin.adidaya-manage.destroy', id), { preserveScroll: true });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <Head title="Manajemen Adidaya" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto py-8 px-4 md:px-6 space-y-8 animate-in fade-in duration-700">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-1">
                <div class="space-y-1">
                    <h2 class="font-medium text-2xl md:text-3xl text-slate-900 tracking-tight uppercase leading-none">Paket Adidaya</h2>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Eksklusif Premium & Manajemen Konten</p>
                </div>
                <button @click="openCreateModal" class="bg-slate-900 hover:bg-indigo-600 text-white px-8 py-4 rounded-2xl font-medium text-[10px] md:text-xs transition-all shadow-xl shadow-slate-200 uppercase tracking-widest active:scale-95">
                    + Buat Paket Adidaya
                </button>
            </div>
            
            <div class="px-1">
                <div class="relative group max-w-md">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300">🔍</span>
                    <input 
                        v-model="search" 
                        @input="handleSearch"
                        type="text" 
                        placeholder="Cari paket adidaya..." 
                        class="w-full pl-12 pr-6 py-4 rounded-2xl border-none shadow-sm text-xs font-medium focus:ring-1 focus:ring-indigo-500 bg-white uppercase tracking-wider"
                    >
                </div>
            </div>

            <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left min-w-[900px] table-fixed">
                        <thead>
                            <tr class="bg-slate-50/30 border-b border-slate-100 text-[9px] md:text-[10px] font-medium text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-5 w-auto">Informasi Paket</th>
                                <th class="px-8 py-5 w-48">Jadwal & Harga</th>
                                <th class="px-8 py-5 text-center w-32">Status</th>
                                <th class="px-8 py-5 text-right w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="tryouts.data.length === 0">
                                <td colspan="4" class="px-8 py-20 text-center text-slate-300">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.4em]">Belum ada paket adidaya ditemukan</p>
                                </td>
                            </tr>

                            <tr v-for="tryout in tryouts.data" :key="tryout.id" class="hover:bg-slate-50/50 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-11 h-11 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-lg shrink-0">⚡</div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900 uppercase text-sm tracking-tight leading-snug break-words">{{ tryout.title }}</p>
                                            <p class="text-[9px] text-slate-400 font-medium uppercase tracking-widest mt-1.5">⏱️ {{ tryout.duration }} Menit | 📄 {{ tryout.questions_count || 0 }} Soal</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2">
                                            <span :class="tryout.is_paid ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'" class="px-2 py-0.5 rounded text-[8px] font-medium uppercase tracking-widest border">
                                                {{ tryout.is_paid ? 'PREMIUM' : 'FREE' }}
                                            </span>
                                            <span v-if="tryout.is_paid" class="text-[10px] font-medium text-slate-900">
                                                {{ formatPrice(tryout.price) }}
                                            </span>
                                        </div>
                                        <p class="text-[9px] font-medium text-slate-400 uppercase tracking-wide leading-tight mt-1">Mulai: {{ formatDate(tryout.started_at) }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span :class="[tryout.is_published ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-100 text-slate-400 border-slate-200']" 
                                          class="px-3 py-1.5 rounded-full text-[8px] font-medium uppercase tracking-widest border whitespace-nowrap">
                                        {{ tryout.is_published ? 'LIVE' : 'DRAFT' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right whitespace-nowrap">
                                    <div class="flex justify-end items-center gap-2 flex-nowrap">
                                        <button @click="openEditModal(tryout)" class="px-4 py-2 bg-white border border-slate-100 rounded-xl text-[9px] font-medium uppercase tracking-widest transition-all hover:bg-slate-50">
                                            Edit
                                        </button>
                                        
                                        <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[9px] font-medium uppercase tracking-widest hover:bg-slate-900 transition-all shadow-sm">
                                            Soal
                                        </Link>
                                        
                                        <button @click="deleteTryout(tryout.id)" class="w-9 h-9 flex items-center justify-center bg-slate-50 text-slate-400 hover:bg-rose-600 hover:text-white rounded-xl transition-all">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 flex justify-center" v-if="tryouts.links && tryouts.links.length > 3">
                <div class="flex gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
                    <template v-for="(link, key) in tryouts.links" :key="key">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-4 py-2 rounded-xl text-[10px] font-medium uppercase tracking-widest transition-all"
                            :class="link.active ? 'bg-slate-900 text-white shadow-md' : 'text-slate-400 hover:bg-slate-50'"
                        />
                        <span v-else v-html="link.label" class="px-4 py-2 text-[10px] font-medium text-slate-200 uppercase tracking-widest"></span>
                    </template>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]" @click="showModal = false"></div>
                
                <div class="relative bg-white w-full max-w-xl rounded-[2.5rem] p-8 md:p-12 shadow-2xl animate-in zoom-in-95 duration-200 overflow-y-auto max-h-[90vh] custom-scrollbar">
                    <div class="mb-10 text-center md:text-left">
                        <h3 class="font-medium text-2xl text-slate-900 uppercase tracking-tight">
                            {{ isEdit ? 'Perbarui Adidaya' : 'Konfigurasi Adidaya' }}
                        </h3>
                        <p class="text-[9px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Detail Konten, Harga & Penjadwalan</p>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2 px-1">
                            <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Nama Paket Adidaya</label>
                            <input v-model="form.title" type="text" class="w-full border-transparent bg-slate-50 rounded-2xl p-4 focus:ring-1 focus:ring-indigo-500 font-medium text-xs md:text-sm uppercase tracking-tight" placeholder="Nama Paket..." required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2 px-1">
                                <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Durasi (Menit)</label>
                                <input v-model="form.duration" type="number" class="w-full border-transparent bg-slate-50 rounded-2xl p-4 focus:ring-1 focus:ring-indigo-500 font-medium text-xs md:text-sm" required />
                            </div>
                            <div class="space-y-2 px-1">
                                <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Tipe Akses</label>
                                <button type="button" @click="form.is_paid = !form.is_paid" 
                                    :class="form.is_paid ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-slate-100 text-slate-400'" 
                                    class="w-full h-[54px] rounded-2xl text-[9px] font-medium uppercase tracking-widest transition-all">
                                    {{ form.is_paid ? 'PREMIUM (PAID)' : 'FREE (GRATIS)' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="form.is_paid" class="animate-in slide-in-from-top-2 px-1">
                            <label class="text-[8px] font-medium text-indigo-400 uppercase tracking-widest ml-1">Harga Jual (IDR)</label>
                            <div class="relative mt-2">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-xs">Rp</span>
                                <input v-model="form.price" type="number" class="w-full pl-12 border-transparent bg-indigo-50/50 rounded-2xl p-4 focus:ring-1 focus:ring-indigo-500 font-medium text-indigo-700 text-lg tracking-tighter" placeholder="50000" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2 px-1">
                                <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Tgl Publish</label>
                                <input v-model="form.published_at" type="datetime-local" class="w-full border-transparent bg-slate-50 rounded-2xl p-4 font-medium text-[10px] md:text-xs" />
                            </div>
                            <div class="space-y-2 px-1">
                                <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest ml-1">Tgl Mulai Ujian</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full border-transparent bg-slate-50 rounded-2xl p-4 font-medium text-[10px] md:text-xs" />
                            </div>
                        </div>

                        <div class="space-y-2 px-1 pt-2">
                            <label class="text-[8px] font-medium text-slate-400 uppercase tracking-widest text-center block mb-3">Status Visibilitas</label>
                            <div class="flex p-1.5 bg-slate-100 rounded-2xl gap-1.5">
                                <button type="button" @click="form.is_published = false" :class="[!form.is_published ? 'bg-white shadow-sm text-slate-900 border-slate-200' : 'text-slate-400 border-transparent']" class="flex-1 py-3.5 rounded-xl font-medium text-[9px] uppercase tracking-widest transition border">Draft</button>
                                <button type="button" @click="form.is_published = true" :class="[form.is_published ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100 border-transparent' : 'text-slate-400 border-transparent']" class="flex-1 py-3.5 rounded-xl font-medium text-[9px] uppercase tracking-widest transition border">Publish</button>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-4 pt-6">
                            <button type="button" @click="showModal = false" class="order-2 md:order-1 flex-1 py-4 text-slate-400 font-medium text-[9px] uppercase tracking-widest hover:text-slate-600 transition-colors">Batal</button>
                            <button type="submit" :disabled="form.processing" class="order-1 md:order-2 flex-[2] py-4 bg-slate-950 text-white rounded-2xl font-medium text-[9px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 transition active:scale-95 disabled:opacity-50">
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
    width: 4px;
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}
</style>