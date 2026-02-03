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
    router.get(route('admin.tryouts.index'), { search: search.value }, {
        preserveState: true,
        replace: true
    });
}, 500);

// --- FORM DATA ---
const form = useForm({
    title: '',
    duration: 110, // Menggunakan 'duration' sesuai pembaruan model
    description: '',
    is_published: false,
    published_at: '',
    started_at: '',
    is_paid: false,
    price: 0,
    type: 'general'
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
        form.put(route('admin.tryouts.update', editingId.value), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    } else {
        form.post(route('admin.tryouts.store'), {
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Hapus paket ini?')) {
        form.delete(route('admin.tryouts.destroy', id));
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
    <Head title="Manajemen Tryout" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto mt-8 px-4 sm:px-0">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase italic">Katalog Tryout</h2>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Penjadwalan & Manajemen Konten</p>
                </div>
                <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-black text-xs transition shadow-xl shadow-indigo-100 uppercase tracking-widest active:scale-95">
                    + Buat Paket Baru
                </button>
            </div>
            
            <div class="mb-6 relative">
                <input 
                    v-model="search" 
                    @input="handleSearch"
                    type="text" 
                    placeholder="Cari paket tryout..." 
                    class="w-full pl-12 pr-4 py-3 rounded-2xl border-none shadow-sm text-sm font-bold focus:ring-2 focus:ring-indigo-500"
                >
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[800px]">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                <th class="px-8 py-5">Informasi Paket</th>
                                <th class="px-8 py-5">Jadwal & Harga</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="tryouts.data.length === 0">
                                <td colspan="4" class="px-8 py-12 text-center text-gray-400">
                                    <p class="text-xs font-bold uppercase tracking-widest">Belum ada paket tryout</p>
                                </td>
                            </tr>

                            <tr v-for="tryout in tryouts.data" :key="tryout.id" class="hover:bg-gray-50/50 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl">📑</div>
                                        <div class="min-w-0">
                                            <p class="font-black text-gray-900 uppercase text-sm tracking-tight truncate">{{ tryout.title }}</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">⏱️ {{ tryout.duration }} Menit | 📄 {{ tryout.questions_count || 0 }} Soal</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span :class="tryout.is_paid ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest">
                                                {{ tryout.is_paid ? 'PREMIUM' : 'GRATIS' }}
                                            </span>
                                            <span v-if="tryout.is_paid" class="text-[10px] font-black text-gray-900">
                                                {{ formatPrice(tryout.price) }}
                                            </span>
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">🚀 Mulai: <span class="text-indigo-700">{{ formatDate(tryout.started_at) }}</span></p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span :class="[tryout.is_published ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700']" 
                                          class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-current opacity-80">
                                        {{ tryout.is_published ? 'PUBLISHED' : 'DRAFT' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right space-x-2 whitespace-nowrap">
                                    <button @click="openEditModal(tryout)" class="inline-flex items-center bg-white border border-gray-200 hover:border-indigo-600 hover:text-indigo-600 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                                        Edit
                                    </button>
                                    
                                    <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" class="inline-flex items-center bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-900 transition shadow-sm">
                                        Soal
                                    </Link>
                                    
                                    <button @click="deleteTryout(tryout.id)" class="p-2.5 text-gray-300 hover:text-red-500 transition">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex justify-center" v-if="tryouts.links && tryouts.links.length > 3">
                <div class="flex gap-1 bg-white p-2 rounded-xl shadow-sm">
                    <template v-for="(link, key) in tryouts.links" :key="key">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition"
                            :class="link.active ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-50'"
                        />
                        <span v-else v-html="link.label" class="px-3 py-1.5 text-xs text-gray-300"></span>
                    </template>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md animate-in fade-in duration-300" @click="showModal = false"></div>
                
                <div class="relative bg-white w-full max-w-xl rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200 overflow-y-auto max-h-[90vh] no-scrollbar">
                    <div class="mb-8">
                        <h3 class="font-black text-2xl text-gray-900 uppercase tracking-tighter italic">
                            {{ isEdit ? 'Perbarui Paket' : 'Konfigurasi Paket' }}
                        </h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Detail, Harga & Jadwal</p>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Nama Paket</label>
                            <input v-model="form.title" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-sm" placeholder="Contoh: Tryout UTBK #1" required />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Durasi (Menit)</label>
                                <input v-model="form.duration" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-sm" required />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Tipe Akses</label>
                                <button type="button" @click="form.is_paid = !form.is_paid" 
                                    :class="form.is_paid ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-gray-100 text-gray-400'" 
                                    class="w-full h-[54px] rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-transparent">
                                    {{ form.is_paid ? 'PREMIUM (BERBAYAR)' : 'GRATIS (FREE)' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="form.is_paid" class="animate-in slide-in-from-top-2">
                            <label class="text-[9px] font-black text-indigo-400 uppercase ml-2">Harga Jual (IDR)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                                <input v-model="form.price" type="number" class="w-full pl-10 border-indigo-100 bg-indigo-50/50 rounded-2xl p-4 focus:ring-indigo-500 font-black text-indigo-700 text-lg" placeholder="50000" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Tanggal Publish</label>
                                <input v-model="form.published_at" type="datetime-local" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-3 focus:ring-indigo-500 font-bold text-xs" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Tanggal Mulai Ujian</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-3 focus:ring-indigo-500 font-bold text-xs" />
                            </div>
                        </div>

                        <div class="space-y-1 pt-2">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 text-center block mb-2">Status Penayangan</label>
                            <div class="flex p-1.5 bg-gray-100 rounded-2xl gap-1">
                                <button type="button" @click="form.is_published = false" :class="[!form.is_published ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400']" class="flex-1 py-3 rounded-xl font-black text-[10px] uppercase transition">Draft (Sembunyikan)</button>
                                <button type="button" @click="form.is_published = true" :class="[form.is_published ? 'bg-green-500 text-white shadow-lg shadow-green-100' : 'text-gray-400']" class="flex-1 py-3 rounded-xl font-black text-[10px] uppercase transition">Publish (Tayangkan)</button>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-6">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 text-gray-400 font-black text-[10px] uppercase hover:text-gray-600">Batal</button>
                            <button type="submit" :disabled="form.processing" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl hover:bg-indigo-600 transition active:scale-95">
                                {{ isEdit ? 'Simpan Perbarui' : 'Buat Paket' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>