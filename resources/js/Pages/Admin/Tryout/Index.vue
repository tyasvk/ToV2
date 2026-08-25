<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    tryouts: [Object, Array],
    filters: Object
});

const tryoutList = computed(() => {
    return props.tryouts?.data ? props.tryouts.data : props.tryouts;
});

// --- FITUR PENCARIAN ---
const search = ref(props.filters?.search || '');

const performSearch = () => {
    router.get(route('admin.tryouts.index'), { 
        search: search.value,
    }, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch();
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
    registration_start_at: '', 
    registration_end_at: '',   
    started_at: '',            
    is_paid: false,
    price: 0,
    type: 'general'
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
    form.registration_start_at = tryout.registration_start_at ? tryout.registration_start_at.substring(0, 16) : '';
    form.registration_end_at = tryout.registration_end_at ? tryout.registration_end_at.substring(0, 16) : '';
    form.started_at = tryout.started_at ? tryout.started_at.substring(0, 16) : '';
    form.is_paid = !!tryout.is_paid;
    form.price = tryout.price;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submit = () => {
    if (!form.is_paid) {
        form.price = 0;
    }

    if (isEdit.value) {
        form.put(route('admin.tryouts.update', editingId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('admin.tryouts.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteTryout = (id) => {
    if (confirm('Hapus paket tryout ini? Semua soal & riwayat terkait akan terhapus.')) {
        router.delete(route('admin.tryouts.destroy', id), { preserveScroll: true });
    }
};

// --- FORMATTERS ---
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: '2-digit',
        hour: '2-digit', minute: '2-digit'
    }).replace(/\./g, ':');
};

const formatCurrency = (price) => {
    if (!price || price == 0) return 'Gratis';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <Head title="Manajemen Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden">
            
            <!-- Padding dan margin diperkecil (px-2 md:px-4, pt-4, space-y-3) -->
            <div class="max-w-6xl mx-auto px-2 md:px-4 pt-4 md:pt-6 space-y-3 md:space-y-4">
                
                <!-- HEADER & SEARCH -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
                    <div class="pl-1">
                        <h1 class="text-xl md:text-2xl font-semibold text-slate-900 tracking-tight">Katalog Tryout</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-0.5">Manajemen paket soal dan akses</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
                        <!-- Search Bar -->
                        <div class="relative w-full sm:w-60">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/>
                                </svg>
                            </div>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Cari paket..."
                                class="w-full bg-[#E3E3E8] border-transparent rounded-[10px] pl-9 pr-3 py-2 text-[12px] md:text-[13px] focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition-all text-slate-900 placeholder:text-slate-500 outline-none font-medium"
                            >
                        </div>

                        <!-- TOMBOL VERIFIKASI & BUAT PAKET -->
                        <div class="flex w-full sm:w-auto gap-2">
                            <!-- Tombol Verifikasi (Baru ditambahkan) -->
                            <Link :href="route('admin.tryouts.verifikasi.index')" class="flex-1 sm:flex-none px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-[10px] font-semibold text-[12px] md:text-[13px] transition-colors flex items-center justify-center gap-1.5 active:scale-95 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Verifikasi
                            </Link>

                            <!-- Tombol Buat Paket -->
                            <button @click="openCreateModal" class="flex-1 sm:flex-none px-4 py-2 bg-[#007AFF] hover:bg-[#0056b3] text-white rounded-[10px] font-semibold text-[12px] md:text-[13px] transition-colors flex items-center justify-center gap-1.5 active:scale-95 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Buat Paket
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-slate-100 flex flex-col w-full overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-slate-600 table-auto border-collapse">
                            <thead>
                                <tr class="bg-white border-b border-slate-100 text-[10px] md:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                    <!-- Padding cell tabel diperkecil (px-3 py-3) -->
                                    <th class="px-3 py-3 w-10 text-center">No</th>
                                    <th class="px-3 py-3 min-w-[200px]">Identitas Paket</th>
                                    <th class="px-3 py-3 w-28">Opsi Harga</th>
                                    <th class="px-3 py-3 min-w-[180px]">Jadwal Pelaksanaan</th>
                                    <th class="px-3 py-3 text-center w-24">Status</th>
                                    <th class="px-3 py-3 text-right w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80 text-[11px] md:text-[12px]">
                                <tr v-for="(tryout, index) in tryoutList" :key="tryout.id" class="hover:bg-[#F9F9FB] transition-colors align-top md:align-middle">
                                    
                                    <td class="px-3 py-3 text-center font-medium text-slate-500">
                                        {{ index + 1 }}
                                    </td>

                                    <td class="px-3 py-3">
                                        <div class="flex flex-col min-w-0 flex-1">
                                            <p class="font-semibold text-slate-900 text-[13px] md:text-[14px] leading-snug tracking-tight break-words">{{ tryout.title }}</p>
                                            <div class="flex items-center gap-1 mt-1 text-[10px] md:text-[11px] text-slate-500 font-medium">
                                                <span>{{ tryout.duration }} Menit</span>
                                                <span class="text-slate-300">•</span>
                                                <span>{{ tryout.questions_count || 0 }} Soal</span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col gap-0.5 items-start">
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] md:text-[10px] font-semibold tracking-wide"
                                                  :class="tryout.is_paid ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'">
                                                {{ tryout.is_paid ? 'Freemium' : 'Gratis' }}
                                            </span>
                                            <span v-if="tryout.is_paid" class="text-[12px] font-semibold text-slate-800 mt-0.5">
                                                {{ formatCurrency(tryout.price) }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-3 py-3">
                                        <div class="flex flex-col text-[10px] md:text-[11px] text-slate-500 gap-0.5 font-medium leading-tight">
                                            <div class="flex items-start gap-1">
                                                <span class="text-slate-400 w-9 shrink-0">Daftar:</span>
                                                <span class="text-slate-700">
                                                    {{ formatDate(tryout.registration_start_at) }} <br>s/d {{ formatDate(tryout.registration_end_at) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-[#007AFF] w-9 shrink-0">Ujian:</span>
                                                <span class="text-[#007AFF]">Mulai {{ formatDate(tryout.started_at) }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] md:text-[10px] font-semibold tracking-wide"
                                              :class="tryout.is_published ? 'bg-emerald-50 text-emerald-600' : 'bg-[#F2F2F7] text-slate-500'">
                                            <span :class="tryout.is_published ? 'bg-emerald-500' : 'bg-slate-400'" class="w-1.5 h-1.5 rounded-full"></span>
                                            {{ tryout.is_published ? 'Live' : 'Draft' }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditModal(tryout)" title="Edit Paket" class="w-7 h-7 flex items-center justify-center bg-[#F2F2F7] text-slate-600 hover:bg-[#007AFF] hover:text-white rounded-full transition-colors active:scale-95 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            
                                            <Link :href="route('admin.tryouts.results', tryout.id)" title="Lihat Hasil & Peringkat" class="w-7 h-7 flex items-center justify-center bg-[#F2F2F7] text-slate-600 hover:bg-amber-500 hover:text-white rounded-full transition-colors active:scale-95 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012-2h-2a2 2 0 01-2-2z" /></svg>
                                            </Link>
                                            
                                            <Link :href="route('admin.tryouts.questions.index', { tryout: tryout.id })" title="Manajemen Soal" class="w-7 h-7 flex items-center justify-center bg-[#F2F2F7] text-slate-600 hover:bg-emerald-500 hover:text-white rounded-full transition-colors active:scale-95 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </Link>
                                            
                                            <button @click="deleteTryout(tryout.id)" title="Hapus Paket" class="w-7 h-7 flex items-center justify-center bg-[#F2F2F7] text-slate-600 hover:bg-rose-500 hover:text-white rounded-full transition-colors active:scale-95 shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!tryoutList.length">
                                    <td colspan="6" class="px-3 py-8 text-center text-[12px] text-slate-400 font-medium">
                                        Tidak ada paket tryout ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-start pl-1" v-if="tryoutList.length > 0">
                    <div class="text-[11px] text-slate-500 font-medium">
                        Total <span class="font-bold text-slate-900">{{ tryoutList.length }}</span> data
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL FORM                                     -->
        <!-- ============================================== -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[999] flex items-center justify-center p-3 sm:p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
                
                <div class="relative bg-white w-full max-w-[32rem] rounded-[20px] shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[90vh] overflow-hidden">
                    
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white z-10">
                        <h3 class="font-semibold text-[15px] md:text-[16px] text-slate-900 tracking-tight">
                            {{ isEdit ? 'Perbarui Paket Tryout' : 'Buat Paket Baru' }}
                        </h3>
                        <button @click="closeModal" class="w-7 h-7 flex items-center justify-center bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-500 rounded-full transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto px-5 py-4 space-y-4 custom-scrollbar text-left bg-white">
                        
                        <div>
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Nama Paket Tryout <span class="text-rose-500">*</span></label>
                            <input v-model="form.title" type="text" class="w-full bg-[#F5F5F7] border-transparent rounded-[10px] px-3 py-2 text-[13px] font-medium focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 outline-none transition-all text-slate-900" placeholder="Contoh: Tryout Akbar" required />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Durasi (Mnt) <span class="text-rose-500">*</span></label>
                                <input v-model="form.duration" type="number" class="w-full bg-[#F5F5F7] border-transparent rounded-[10px] px-3 py-2 text-[13px] font-medium focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 outline-none transition-all text-slate-900" required />
                            </div>
                            
                            <div>
                                <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Publikasi</label>
                                <div class="flex bg-[#F2F2F7] p-1 rounded-[10px]">
                                    <button type="button" @click="form.is_published = false" :class="[!form.is_published ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500']" class="flex-1 py-1 rounded-[8px] text-[11px] font-semibold transition-all">Draft</button>
                                    <button type="button" @click="form.is_published = true" :class="[form.is_published ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500']" class="flex-1 py-1 rounded-[8px] text-[11px] font-semibold transition-all">Live</button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-1">
                            <label class="block text-[12px] font-semibold text-slate-900 mb-2">Tipe Akses Pengguna <span class="text-rose-500">*</span></label>
                            
                            <div class="flex flex-col gap-2">
                                <label class="p-3 border rounded-[12px] cursor-pointer transition-all"
                                       :class="!form.is_paid ? 'bg-[#F0F4FF] border-[#007AFF]' : 'bg-white border-slate-200'">
                                    <input type="radio" v-model="form.is_paid" :value="false" class="hidden">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="!form.is_paid ? 'border-[#007AFF]' : 'border-slate-300'">
                                            <div v-if="!form.is_paid" class="w-2 h-2 bg-[#007AFF] rounded-full"></div>
                                        </div>
                                        <span class="font-semibold text-slate-900 text-[12px]">Gratis Terbatas (Hanya Skor)</span>
                                    </div>
                                </label>

                                <label class="p-3 border rounded-[12px] cursor-pointer transition-all"
                                       :class="form.is_paid ? 'bg-[#FFF8F0] border-amber-500' : 'bg-white border-slate-200'">
                                    <input type="radio" v-model="form.is_paid" :value="true" class="hidden">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="form.is_paid ? 'border-amber-500' : 'border-slate-300'">
                                            <div v-if="form.is_paid" class="w-2 h-2 bg-amber-500 rounded-full"></div>
                                        </div>
                                        <span class="font-semibold text-slate-900 text-[12px]">Freemium (Sediakan Opsi Bayar)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div v-if="form.is_paid" class="animate-in fade-in slide-in-from-top-2 duration-300 ml-6">
                            <label class="block text-[12px] font-semibold text-slate-700 mb-1.5">Harga Premium (Rp) <span class="text-rose-500">*</span></label>
                            <input v-model="form.price" type="number" class="w-full bg-[#F5F5F7] border-transparent rounded-[10px] px-3 py-2 text-[13px] font-medium text-slate-900 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all" placeholder="50000" />
                        </div>

                        <div class="pt-1">
                            <label class="block text-[12px] font-semibold text-slate-900 mb-2">Penjadwalan Waktu</label>
                            
                            <div class="space-y-3">
                                <div class="bg-slate-50 border border-slate-100 p-3 rounded-[12px]">
                                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Masa Pendaftaran</span>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-medium text-slate-500 mb-1">Buka</label>
                                            <input v-model="form.registration_start_at" type="datetime-local" class="w-full bg-white border border-slate-200 rounded-[8px] px-2 py-1.5 text-[11px] focus:border-[#007AFF] outline-none" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-medium text-slate-500 mb-1">Tutup</label>
                                            <input v-model="form.registration_end_at" type="datetime-local" class="w-full bg-white border border-slate-200 rounded-[8px] px-2 py-1.5 text-[11px] focus:border-[#007AFF] outline-none" />
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-[#F0F4FF] border border-[#D9E6FF] p-3 rounded-[12px]">
                                        <label class="block text-[10px] font-bold text-[#007AFF] uppercase tracking-wider mb-1.5">Mulai Ujian</label>
                                        <input v-model="form.started_at" type="datetime-local" class="w-full bg-white border border-[#D9E6FF] rounded-[8px] px-2 py-1.5 text-[11px] focus:border-[#007AFF] outline-none" />
                                    </div>
                                    <div class="bg-slate-50 border border-slate-100 p-3 rounded-[12px]">
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Waktu Publish</label>
                                        <input v-model="form.published_at" type="datetime-local" class="w-full bg-white border border-slate-200 rounded-[8px] px-2 py-1.5 text-[11px] focus:border-[#007AFF] outline-none" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2.5 pt-4 shrink-0 mt-3 border-t border-slate-100">
                            <button type="button" @click="closeModal" class="flex-1 py-2.5 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 rounded-full font-semibold text-[12px] transition-colors">
                                Batal
                            </button>
                            <button type="submit" :disabled="form.processing" class="flex-[2] py-2.5 bg-[#007AFF] hover:bg-[#0056b3] disabled:opacity-50 text-white rounded-full font-semibold text-[12px] active:scale-95 transition-all">
                                {{ isEdit ? 'Simpan' : 'Buat Paket' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { display: none; }
.custom-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>