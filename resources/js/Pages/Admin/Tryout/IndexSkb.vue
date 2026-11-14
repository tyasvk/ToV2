<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array
});

// State untuk Modal Tambah/Edit Formasi
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    icon: '💼',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.name = item.name;
    form.icon = item.icon;
    form.description = item.description;
    form.is_active = Boolean(item.is_active);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.tryout.skb.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.tryout.skb.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (id, name) => {
    if (confirm(`Yakin ingin menghapus formasi "${name}"? Semua paket soal di dalamnya akan ikut terhapus.`)) {
        router.delete(route('admin.tryout.skb.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Kelola Formasi SKB - Admin" />

    <AuthenticatedLayout>
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-24 font-sans animate-in fade-in duration-500">
            <div class="max-w-5xl mx-auto sm:px-6 pt-6 sm:pt-10 space-y-6">

                <!-- Header -->
                <div class="px-4 sm:px-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-[#1D1D1F]">Kelola Formasi SKB</h1>
                        <p class="text-[14px] text-[#86868B] mt-0.5">Atur daftar formasi yang muncul di katalog user.</p>
                    </div>
                    <button @click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full font-semibold text-[14px] transition-all active:scale-[0.98] shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Tambah Formasi
                    </button>
                </div>

                <!-- Tabel Daftar Formasi -->
                <div class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#F5F5F7]/60 border-b border-black/5 text-[12px] uppercase tracking-wider text-[#86868B] font-semibold">
                                    <th class="p-4 sm:px-6">Nama Formasi</th>
                                    <th class="p-4 sm:px-6">Status</th>
                                    <th class="p-4 sm:px-6 text-center">Paket Tryout</th>
                                    <th class="p-4 sm:px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5">
                                <tr v-for="item in categories" :key="item.id" class="hover:bg-[#F5F5F7]/40 transition-colors">
                                    
                                    <!-- Formasi Info -->
                                    <td class="p-4 sm:px-6 max-w-md">
                                        <div class="flex items-start gap-3.5">
                                            <div class="w-11 h-11 bg-[#F5E6FF] text-[#AF52DE] rounded-[12px] flex items-center justify-center text-xl shrink-0">
                                                {{ item.icon }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[15px] font-bold text-[#1D1D1F] truncate">{{ item.name }}</p>
                                                <p class="text-[13px] text-[#86868B] line-clamp-1 mt-0.5">{{ item.description || 'Tidak ada deskripsi' }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="p-4 sm:px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                                            :class="item.is_active ? 'bg-[#E5F5EA] text-[#34C759]' : 'bg-black/5 text-[#86868B]'">
                                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>

                                    <!-- Jumlah Paket & Tombol Kelola -->
                                    <td class="p-4 sm:px-6 text-center">
                                        <Link :href="route('admin.tryout.skb.packages', item.slug)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#F0F4FF] hover:bg-[#E0EAFF] text-[#007AFF] rounded-full text-[12px] font-bold transition-colors">
                                            <span>{{ item.tryouts_count }} Paket</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </Link>
                                    </td>

                                    <!-- Aksi Edit / Hapus -->
                                    <td class="p-4 sm:px-6 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditModal(item)" class="p-2 text-[#1D1D1F] hover:bg-black/5 rounded-lg transition-colors" title="Edit Formasi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deleteCategory(item.id, item.name)" class="p-2 text-[#FF3B30] hover:bg-[#FFEBEA] rounded-lg transition-colors" title="Hapus Formasi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="categories.length === 0" class="p-10 text-center text-[#86868B] font-medium">
                            Belum ada formasi SKB yang dibuat.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL TAMBAH / EDIT FORMASI -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-[24px] max-w-md w-full p-6 shadow-xl border border-black/5 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[18px] font-bold text-[#1D1D1F]">
                        {{ isEditing ? 'Edit Formasi SKB' : 'Tambah Formasi SKB' }}
                    </h3>
                    <button @click="closeModal" class="w-8 h-8 rounded-full bg-black/5 flex items-center justify-center text-[#86868B] hover:text-[#1D1D1F]">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-4 gap-3">
                        <div class="col-span-1">
                            <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Ikon</label>
                            <input v-model="form.icon" type="text" required class="w-full text-center text-xl rounded-xl border-black/15 focus:border-[#007AFF] focus:ring-[#007AFF]" placeholder="👮" />
                        </div>
                        <div class="col-span-3">
                            <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Nama Formasi</label>
                            <input v-model="form.name" type="text" required class="w-full rounded-xl border-black/15 text-[14px] focus:border-[#007AFF] focus:ring-[#007AFF]" placeholder="Contoh: Penjaga Tahanan" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Deskripsi Singkat</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-xl border-black/15 text-[14px] focus:border-[#007AFF] focus:ring-[#007AFF]" placeholder="Deskripsi kisi-kisi formasi ini..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[14px] font-semibold text-[#1D1D1F]">Tampilkan ke User</span>
                        <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-[#007AFF] focus:ring-[#007AFF] border-black/20" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-black/5">
                        <button type="button" @click="closeModal" class="px-4 py-2.5 rounded-full text-[13px] font-semibold text-[#86868B] hover:bg-black/5">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[13px] font-semibold disabled:opacity-50">
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Formasi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>