<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tryouts: Array
});

// --- STATE MODAL ---
const showModal = ref(false);
const isEdit = ref(false);
const editingId = ref(null);

// --- FORM DATA ---
const form = useForm({
    title: '',
    duration_minutes: 110,
    description: '',
    is_active: false,
    published_at: '',
    started_at: '',
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
    
    // Isi data ke form
    form.title = tryout.title;
    form.duration_minutes = tryout.duration_minutes;
    form.description = tryout.description;
    form.is_active = !!tryout.is_active;
    
    // Format tanggal untuk input datetime-local (YYYY-MM-DDTHH:MM)
    form.published_at = tryout.published_at ? tryout.published_at.substring(0, 16) : '';
    form.started_at = tryout.started_at ? tryout.started_at.substring(0, 16) : '';
    
    showModal.value = true;
};

const submit = () => {
    if (isEdit.value) {
        form.patch(route('admin.tryouts.update', editingId.value), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.tryouts.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Hapus paket ini? Seluruh soal di dalamnya akan ikut terhapus.')) {
        form.delete(route('admin.tryouts.destroy', id));
    }
};

// --- HELPER FORMAT TANGGAL ---
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};
</script>

<template>
    <Head title="Manajemen Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase">Katalog Tryout</h2>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Penjadwalan & Manajemen Konten</p>
                </div>
                <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-black text-xs transition shadow-xl shadow-indigo-100 uppercase tracking-widest active:scale-95">
                    + Buat Paket Baru
                </button>
            </div>
        </template>

        <div class="max-w-6xl mx-auto mt-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[800px]">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                <th class="px-8 py-5">Informasi Paket</th>
                                <th class="px-8 py-5">Jadwal</th>
                                <th class="px-8 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="tryout in tryouts" :key="tryout.id" class="hover:bg-gray-50/50 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl">📑</div>
                                        <div class="min-w-0">
                                            <p class="font-black text-gray-900 uppercase text-sm tracking-tight truncate">{{ tryout.title }}</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">⏱️ {{ tryout.duration_minutes }} Menit | 📄 {{ tryout.questions_count }} Soal</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">📢 Publish: <span class="text-gray-700">{{ formatDate(tryout.published_at) }}</span></p>
                                        <p class="text-[9px] font-bold text-indigo-400 uppercase">🚀 Mulai: <span class="text-indigo-700">{{ formatDate(tryout.started_at) }}</span></p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span :class="[tryout.is_active ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700']" 
                                          class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-current opacity-80">
                                        {{ tryout.is_active ? 'PUBLISHED' : 'DRAFT' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right space-x-2 whitespace-nowrap">
                                    <button @click="openEditModal(tryout)" class="inline-flex items-center bg-white border border-gray-200 hover:border-indigo-600 hover:text-indigo-600 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition shadow-sm">
                                        Edit Paket
                                    </button>
                                    <Link :href="route('admin.questions.index', tryout.id)" class="inline-flex items-center bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-900 transition shadow-sm">
                                        Isi Soal
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
        </div>

        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md animate-in fade-in duration-300" @click="showModal = false"></div>
                
                <div class="relative bg-white w-full max-w-xl rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200 overflow-y-auto max-h-[90vh]">
                    <div class="mb-8">
                        <h3 class="font-black text-2xl text-gray-900 uppercase tracking-tighter">
                            {{ isEdit ? 'Perbarui Paket' : 'Konfigurasi Paket' }}
                        </h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Pengaturan Akses & Durasi</p>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2 space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Nama Paket</label>
                                <input v-model="form.title" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-sm" placeholder="Contoh: Tryout Akbar #1" required />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2 text-center block">Durasi (M)</label>
                                <input v-model="form.duration_minutes" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-sm text-center" required />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Tanggal Publish</label>
                                <input v-model="form.published_at" type="datetime-local" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-xs" required />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Tanggal Mulai</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 focus:ring-indigo-500 font-bold text-xs" required />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 text-center block mb-2">Status Publikasi</label>
                            <div class="flex p-1.5 bg-gray-100 rounded-2xl gap-1">
                                <button type="button" @click="form.is_active = false" :class="[!form.is_active ? 'bg-white shadow-sm text-gray-900' : 'text-gray-400']" class="flex-1 py-3 rounded-xl font-black text-[10px] uppercase transition">Draft</button>
                                <button type="button" @click="form.is_active = true" :class="[form.is_active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-400']" class="flex-1 py-3 rounded-xl font-black text-[10px] uppercase transition">Publish</button>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 text-gray-400 font-black text-[10px] uppercase">Batal</button>
                            <button type="submit" :disabled="form.processing" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl transition active:scale-95">
                                {{ isEdit ? 'Simpan Perubahan' : 'Simpan Konfigurasi' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>