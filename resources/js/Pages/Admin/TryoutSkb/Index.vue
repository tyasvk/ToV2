<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryouts: {
        type: Array,
        default: () => []
    },
    filters: Object,
});

// --- State Modal & Pencarian ---
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const search = ref(props.filters?.search || '');

// --- Form Inertia ---
const form = useForm({
    title: '',
    duration: 90,
    description: '',
    is_paid: false,
    price: 0,
    category: 'skb', // Default langsung ke SKB
    passing_grade: 0, // Field khusus SKB
    is_published: false,
    registration_start_at: null,
    registration_end_at: null,
    started_at: null,
    end_date: null,
});

// --- Fitur Pencarian ---
watch(search, (value) => {
    router.get(
        route('admin.tryout-skb.index'),
        { search: value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
});

// --- Modal Handlers ---
const openModal = () => {
    isEditing.value = false;
    form.reset();
    form.category = 'skb';
    showModal.value = true;
};

const editModal = (tryout) => {
    isEditing.value = true;
    editingId.value = tryout.id;
    
    form.title = tryout.title;
    form.duration = tryout.duration;
    form.description = tryout.description;
    form.is_paid = !!tryout.is_paid;
    form.price = tryout.price;
    form.category = 'skb';
    form.passing_grade = tryout.passing_grade || 0;
    form.is_published = !!tryout.is_published;
    form.registration_start_at = tryout.registration_start_at ? tryout.registration_start_at.replace(' ', 'T') : null;
    form.registration_end_at = tryout.registration_end_at ? tryout.registration_end_at.replace(' ', 'T') : null;
    form.started_at = tryout.started_at ? tryout.started_at.replace(' ', 'T') : null;
    form.end_date = tryout.end_date ? tryout.end_date.replace(' ', 'T') : null;
    
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

// --- Aksi CRUD ---
const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.tryouts.update', editingId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.tryouts.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus Tryout SKB ini? Semua soal & data peserta terkait akan ikut terhapus.')) {
        router.delete(route('admin.tryouts.destroy', id));
    }
};

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
};
</script>

<template>
    <Head title="Kelola Tryout SKB - Admin" />

    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Header & Action -->
                <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Kelola Tryout SKB</h1>
                        <p class="text-sm text-gray-500 mt-1">Manajemen paket simulasi Seleksi Kompetensi Bidang</p>
                    </div>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <!-- Search -->
                        <div class="relative w-full sm:w-64">
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Cari Tryout SKB..." 
                                class="w-full pl-10 pr-4 py-2 bg-gray-50 border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                            <span class="absolute left-3 top-2.5 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </div>
                        
                        <!-- Btn Tambah -->
                        <button @click="openModal" class="shrink-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah SKB
                        </button>
                    </div>
                </div>

                <!-- Tabel Data -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wider">
                                    <th class="px-6 py-4 font-semibold">Judul Tryout</th>
                                    <th class="px-6 py-4 font-semibold">Status & Tipe</th>
                                    <th class="px-6 py-4 font-semibold text-center">Spesifikasi</th>
                                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="tryout in tryouts" :key="tryout.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ tryout.title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ tryout.description || 'Tidak ada deskripsi' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col items-start gap-2">
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md" 
                                                  :class="tryout.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                                                {{ tryout.is_published ? 'Published' : 'Draft' }}
                                            </span>
                                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md" 
                                                  :class="tryout.is_paid ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'">
                                                {{ tryout.is_paid ? formatRupiah(tryout.price) : 'Gratis' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm font-semibold text-gray-700">{{ tryout.questions_count || 0 }} Soal</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ tryout.duration }} Menit | PG: {{ tryout.passing_grade || 0 }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Tombol Hasil/Ranking -->
                                            <Link :href="route('admin.tryouts.results', tryout.id)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Lihat Hasil">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                            </Link>
                                            <!-- Tombol Edit -->
                                            <button @click="editModal(tryout)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                            <!-- Tombol Hapus -->
                                            <button @click="deleteTryout(tryout.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="tryouts.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada data Tryout SKB.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL FORM -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm" @click="closeModal"></div>

                <div class="relative inline-block w-full max-w-2xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-3">
                        {{ isEditing ? 'Edit Tryout SKB' : 'Tambah Tryout SKB Baru' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Judul -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tryout</label>
                            <input v-model="form.title" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <span v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Durasi -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Durasi (Menit)</label>
                                <input v-model="form.duration" type="number" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                            
                            <!-- Passing Grade SKB -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Passing Grade SKB</label>
                                <input v-model="form.passing_grade" type="number" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Singkat</label>
                            <textarea v-model="form.description" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <!-- Pengaturan Harga -->
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3 mb-3">
                                <input v-model="form.is_paid" type="checkbox" id="is_paid" class="rounded text-blue-600 focus:ring-blue-500 w-5 h-5">
                                <label for="is_paid" class="text-sm font-semibold text-gray-900 cursor-pointer">Jadikan Tryout Berbayar (Premium)</label>
                            </div>
                            <div v-if="form.is_paid">
                                <label class="block text-sm text-gray-700 mb-1">Harga (Rp)</label>
                                <input v-model="form.price" type="number" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <span v-if="form.errors.price" class="text-xs text-red-500 mt-1">{{ form.errors.price }}</span>
                            </div>
                        </div>

                        <!-- Publish -->
                        <div class="flex items-center gap-3 py-2">
                            <input v-model="form.is_published" type="checkbox" id="is_published" class="rounded text-green-600 focus:ring-green-500 w-5 h-5">
                            <label for="is_published" class="text-sm font-semibold text-gray-900 cursor-pointer">Publish Sekarang (Terlihat oleh User)</label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                            <button type="button" @click="closeModal" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
                                <span v-if="form.processing" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                Simpan SKB
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>