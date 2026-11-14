<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    category: Object,
    packages: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    duration: 90,
    price: 0,
    is_active: true,
});

const formatCurrency = (val) => {
    if (val === 0) return 'Gratis';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.title = `Tryout SKB ${props.category.name} - Paket ${props.packages.length + 1}`;
    showModal.value = true;
};

const openEditModal = (pkg) => {
    isEditing.value = true;
    editingId.value = pkg.id;
    form.title = pkg.title;
    form.duration = pkg.duration;
    form.price = pkg.price;
    form.is_active = Boolean(pkg.is_active);
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.tryout.skb.packages.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.tryout.skb.packages.store', props.category.slug), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deletePackage = (id, title) => {
    if (confirm(`Yakin ingin menghapus paket "${title}"?`)) {
        router.delete(route('admin.tryout.skb.packages.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="`Kelola Paket ${category.name} - Admin`" />

    <AuthenticatedLayout>
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-24 font-sans animate-in fade-in duration-500">
            <div class="max-w-5xl mx-auto sm:px-6 pt-6 sm:pt-10 space-y-6">

                <!-- Header -->
                <div class="px-4 sm:px-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <Link :href="route('admin.tryout.skb.index')" class="w-8 h-8 mt-1 flex items-center justify-center rounded-full bg-black/5 hover:bg-black/10 transition-colors text-[#1D1D1F] shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </Link>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#AF52DE] bg-[#F5E6FF] px-2.5 py-0.5 rounded-md">
                                Formasi SKB
                            </span>
                            <h1 class="text-2xl font-bold text-[#1D1D1F] mt-1">{{ category.name }}</h1>
                            <p class="text-[14px] text-[#86868B]">Kelola daftar paket tryout dan bank soal untuk formasi ini.</p>
                        </div>
                    </div>

                    <button @click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full font-semibold text-[14px] transition-all active:scale-[0.98] shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Tambah Paket Tryout
                    </button>
                </div>

                <!-- Tabel Paket Tryout -->
                <div class="bg-white sm:rounded-[24px] border-y sm:border-x border-black/5 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#F5F5F7]/60 border-b border-black/5 text-[12px] uppercase tracking-wider text-[#86868B] font-semibold">
                                    <th class="p-4 sm:px-6">Nama Paket Tryout</th>
                                    <th class="p-4 sm:px-6">Durasi & Harga</th>
                                    <th class="p-4 sm:px-6 text-center">Bank Soal</th>
                                    <th class="p-4 sm:px-6">Status</th>
                                    <th class="p-4 sm:px-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/5">
                                <tr v-for="pkg in packages" :key="pkg.id" class="hover:bg-[#F5F5F7]/40 transition-colors">
                                    
                                    <td class="p-4 sm:px-6 font-bold text-[15px] text-[#1D1D1F]">
                                        {{ pkg.title }}
                                    </td>

                                    <td class="p-4 sm:px-6 text-[13px]">
                                        <p class="font-bold text-[#1D1D1F]">{{ formatCurrency(pkg.price) }}</p>
                                        <p class="text-[#86868B]">{{ pkg.duration }} Menit</p>
                                    </td>

                                    <!-- Link Langsung ke Manajemen Soal yang Sudah Ada -->
                                    <td class="p-4 sm:px-6 text-center">
                                        <Link :href="route('admin.tryouts.questions.index', pkg.id)" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#FFF9E6] hover:bg-[#FFF0C2] text-[#FF9500] rounded-full text-[12px] font-bold transition-colors">
                                            <span>📝 {{ pkg.questions_count }} Soal</span>
                                        </Link>
                                    </td>

                                    <td class="p-4 sm:px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                                            :class="pkg.is_active ? 'bg-[#E5F5EA] text-[#34C759]' : 'bg-black/5 text-[#86868B]'">
                                            {{ pkg.is_active ? 'Publish' : 'Draft' }}
                                        </span>
                                    </td>

                                    <td class="p-4 sm:px-6 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditModal(pkg)" class="p-2 text-[#1D1D1F] hover:bg-black/5 rounded-lg transition-colors" title="Edit Paket">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <button @click="deletePackage(pkg.id, pkg.title)" class="p-2 text-[#FF3B30] hover:bg-[#FFEBEA] rounded-lg transition-colors" title="Hapus Paket">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="packages.length === 0" class="p-10 text-center text-[#86868B] font-medium">
                            Belum ada paket tryout untuk formasi ini. Klik "Tambah Paket Tryout" untuk memulai.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL TAMBAH / EDIT PAKET TRYOUT -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs animate-in fade-in duration-200">
            <div class="bg-white rounded-[24px] max-w-md w-full p-6 shadow-xl border border-black/5 space-y-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[18px] font-bold text-[#1D1D1F]">
                        {{ isEditing ? 'Edit Paket Tryout' : 'Tambah Paket Tryout' }}
                    </h3>
                    <button @click="closeModal" class="w-8 h-8 rounded-full bg-black/5 flex items-center justify-center text-[#86868B] hover:text-[#1D1D1F]">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Judul Paket Tryout</label>
                        <input v-model="form.title" type="text" required class="w-full rounded-xl border-black/15 text-[14px] focus:border-[#007AFF] focus:ring-[#007AFF]" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Durasi (Menit)</label>
                            <input v-model="form.duration" type="number" min="1" required class="w-full rounded-xl border-black/15 text-[14px] focus:border-[#007AFF] focus:ring-[#007AFF]" />
                        </div>
                        <div>
                            <label class="block text-[12px] font-bold text-[#1D1D1F] mb-1">Harga (Rp)</label>
                            <input v-model="form.price" type="number" min="0" required class="w-full rounded-xl border-black/15 text-[14px] focus:border-[#007AFF] focus:ring-[#007AFF]" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-[14px] font-semibold text-[#1D1D1F]">Status Publish (Tampil ke User)</span>
                        <input v-model="form.is_active" type="checkbox" class="w-5 h-5 rounded text-[#007AFF] focus:ring-[#007AFF] border-black/20" />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-black/5">
                        <button type="button" @click="closeModal" class="px-4 py-2.5 rounded-full text-[13px] font-semibold text-[#86868B] hover:bg-black/5">Batal</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[13px] font-semibold disabled:opacity-50">
                            {{ isEditing ? 'Simpan Perubahan' : 'Buat Paket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>