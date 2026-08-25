<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    registrations: Object,
    filters: Object,
    pendingCount: Number
});

const search = ref(props.filters?.search || '');
const activeStatus = ref(props.filters?.status || 'pending');

// Modal Lihat Gambar
const showImageModal = ref(false);
const currentImage = ref('');

const openImage = (path) => {
    currentImage.value = `/storage/${path}`;
    showImageModal.value = true;
};

const performSearch = () => {
    router.get(route('admin.tryouts.verifikasi.index'), { 
        search: search.value,
        status: activeStatus.value
    }, { preserveState: true, preserveScroll: true, replace: true });
};

let searchTimeout;
watch([search, activeStatus], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => performSearch(), 400);
});

// Aksi Terima / Tolak
const updateStatus = (id, newStatus) => {
    const actionName = newStatus === 'approved' ? 'menyetujui' : 'menolak';
    if (confirm(`Apakah Anda yakin ingin ${actionName} pengajuan ini?`)) {
        router.post(route('admin.tryouts.verifikasi.update', id), { status: newStatus }, { preserveScroll: true });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    }).replace(/\./g, ':');
};
</script>

<template>
    <Head title="Verifikasi Syarat Tryout" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent w-full pb-20 animate-in fade-in duration-300 overflow-x-hidden">
            <div class="max-w-[1400px] mx-auto px-2 md:px-4 pt-4 md:pt-6 space-y-3 md:space-y-4">
                
                <!-- HEADER -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
                    <div class="pl-1">
                        <Link :href="route('admin.tryouts.index')" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[12px] font-bold mb-1">
                            &larr; Kembali ke Katalog Tryout
                        </Link>
                        <h1 class="text-xl md:text-2xl font-semibold text-slate-900 tracking-tight">Verifikasi Akses Gratis</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-0.5">Persetujuan bukti follow dan komen pengguna</p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
                        <div class="relative w-full sm:w-60">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                            </div>
                            <input v-model="search" type="text" placeholder="Cari nama / email..." class="w-full bg-[#E3E3E8] border-transparent rounded-[10px] pl-9 pr-3 py-2 text-[12px] md:text-[13px] focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition-all text-slate-900 outline-none font-medium">
                        </div>
                    </div>
                </div>

                <!-- FILTER TABS -->
                <div class="bg-white rounded-[12px] p-1 flex items-center shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-slate-100 w-full md:w-max">
                    <button @click="activeStatus = 'pending'" :class="activeStatus === 'pending' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 md:w-32 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all relative">
                        Menunggu
                        <span v-if="pendingCount > 0" class="absolute top-1 right-2 w-2 h-2 bg-rose-500 rounded-full"></span>
                    </button>
                    <button @click="activeStatus = 'approved'" :class="activeStatus === 'approved' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 md:w-32 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all">Disetujui</button>
                    <button @click="activeStatus = 'rejected'" :class="activeStatus === 'rejected' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 md:w-32 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all">Ditolak</button>
                </div>

                <!-- TABLE CARD -->
                <div class="bg-white rounded-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-slate-100 flex flex-col w-full overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left text-slate-600 table-auto border-collapse">
                            <thead>
                                <tr class="bg-white border-b border-slate-100 text-[10px] md:text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                    <th class="px-4 py-3 w-10 text-center">No</th>
                                    <th class="px-4 py-3 min-w-[200px]">Peserta</th>
                                    <th class="px-4 py-3 min-w-[150px]">Paket Tryout</th>
                                    <th class="px-4 py-3 text-center w-40">Bukti (Klik)</th>
                                    <th class="px-4 py-3 text-right w-44">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100/80 text-[11px] md:text-[12px]">
                                <tr v-for="(reg, index) in registrations.data" :key="reg.id" class="hover:bg-[#F9F9FB] transition-colors align-middle">
                                    <td class="px-4 py-4 text-center font-medium text-slate-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-900 text-[13px]">{{ reg.user?.name || 'User Dihapus' }}</span>
                                            <span class="text-[11px] text-slate-500">{{ reg.user?.email }}</span>
                                            <span class="text-[9px] text-slate-400 mt-1 uppercase">Diajukan: {{ formatDate(reg.created_at) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="font-semibold text-slate-700">{{ reg.tryout?.title || 'Tryout Dihapus' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Tombol Lihat Bukti Follow -->
                                            <button @click="openImage(reg.proof_follow)" title="Lihat Bukti Follow" class="w-8 h-8 rounded-[8px] bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors border border-blue-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </button>
                                            <!-- Tombol Lihat Bukti Komen -->
                                            <button @click="openImage(reg.proof_comment)" title="Lihat Bukti Komen" class="w-8 h-8 rounded-[8px] bg-purple-50 text-purple-600 flex items-center justify-center hover:bg-purple-600 hover:text-white transition-colors border border-purple-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                 <td class="px-4 py-4 text-right">
    <!-- JIKA USER TERNYATA SUDAH BELI PREMIUM (BERALIH) -->
    <span v-if="reg.has_paid" class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide bg-amber-50 text-amber-600 border border-amber-200">
        Sudah Berbayar
    </span>

    <!-- TOMBOL AKSI JIKA MASIH PENDING -->
    <div v-else-if="reg.status === 'pending'" class="flex items-center justify-end gap-1.5">
        <button @click="updateStatus(reg.id, 'rejected')" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white rounded-[8px] font-bold text-[10px] transition-colors">
            Tolak
        </button>
        <button @click="updateStatus(reg.id, 'approved')" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white rounded-[8px] font-bold text-[10px] transition-colors">
            Setujui
        </button>
    </div>

    <!-- JIKA SUDAH DIPROSES (DISETUJUI / DITOLAK) -->
    <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wide" :class="reg.status === 'approved' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
        {{ reg.status === 'approved' ? 'Disetujui' : 'Ditolak' }}
    </span>
</td>
                                </tr>
                                <tr v-if="!registrations.data.length">
                                    <td colspan="5" class="px-4 py-12 text-center text-[12px] text-slate-400 font-medium">
                                        Tidak ada data pengajuan pada status ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginasi & Info -->
                <div class="flex justify-between items-center px-1" v-if="registrations.data.length > 0">
                    <span class="text-[11px] text-slate-500 font-medium">Total: {{ registrations.total }} data</span>
                </div>
            </div>
        </div>

        <!-- MODAL LIHAT GAMBAR -->
        <Teleport to="body">
            <div v-if="showImageModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" @click="showImageModal = false">
                <div class="relative bg-transparent max-w-3xl w-full flex flex-col items-center">
                    <img :src="currentImage" alt="Bukti Persyaratan" class="max-h-[85vh] object-contain rounded-lg shadow-2xl" @click.stop>
                    <button @click="showImageModal = false" class="mt-4 px-6 py-2 bg-white text-slate-900 rounded-full font-bold text-[12px] hover:bg-slate-200">
                        Tutup Gambar
                    </button>
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