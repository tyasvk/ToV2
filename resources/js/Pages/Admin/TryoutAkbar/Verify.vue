<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    tryout: { type: Object, default: () => ({}) },
    participants: { type: Object, default: () => ({ data: [] }) },
    stats: { type: Object, default: () => ({ total: 0, pending: 0, paid: 0 }) },
    filters: { type: Object, default: () => ({ search: '', status: '' }) }
});

// --- STATE ---
const search = ref(props.filters?.search || '');
const showModal = ref(false);
const selectedTrx = ref(null);
const rejectionReason = ref('');

// --- SEARCH & FILTER ---
const handleSearch = debounce(() => {
    router.get(
        route('admin.tryout-akbar.show', props.tryout.id),
        { status: props.filters?.status || '', search: search.value },
        { preserveState: true, replace: true, preserveScroll: true }
    );
}, 500);

const filterStatus = (status) => {
    router.get(
        route('admin.tryout-akbar.show', props.tryout.id),
        { status, search: search.value },
        { preserveState: true, preserveScroll: true }
    );
};

// --- HELPER ---
const getImageUrl = (path) => {
    if (!path) return '';
    
    // Ambil string asli
    let strPath = Array.isArray(path) ? path[0] : path;
    strPath = String(strPath || '');
    if (!strPath) return '';

    // Bersihkan dari array/kurung siku/quotes yang terikut
    let cleanPath = strPath.replace(/["\[\]\\]/g, '');
    
    // Jika path berupa link URL utuh dari luar
    if (cleanPath.startsWith('http://') || cleanPath.startsWith('https://')) {
        return cleanPath;
    }

    // Buang kata public/ jika ada
    if (cleanPath.startsWith('public/')) {
        cleanPath = cleanPath.replace('public/', '');
    }
    
    // Pastikan diawali dengan /storage/
    if (cleanPath.startsWith('/storage/')) return cleanPath;
    if (cleanPath.startsWith('storage/')) return '/' + cleanPath;
    
    return '/storage/' + cleanPath;
};

const getInitials = (name) => {
    if (!name) return 'NN'; 
    return name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
};

// --- MODAL ACTION ---
const openVerify = (trx) => {
    if (!trx) return;
    selectedTrx.value = trx;
    rejectionReason.value = trx?.rejection_note || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => { selectedTrx.value = null; }, 200);
};

const processVerification = (action) => {
    if (action === 'reject' && !rejectionReason.value && !confirm('Alasan penolakan kosong. Lanjutkan?')) return;
    if (action !== 'reject' && !confirm(`Yakin ingin ${action === 'approve' ? 'Menerima' : 'Menolak'} peserta ini?`)) return;

    router.post(route('admin.tryout-akbar.verify', selectedTrx.value.id), {
        action: action,
        reason: rejectionReason.value
    }, {
        onSuccess: () => closeModal(),
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Verifikasi Peserta" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <Link :href="route('admin.tryout-akbar.index')" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors mb-2 w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        Kembali ke Katalog
                    </Link>
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Manajemen Peserta</h1>
                    <p class="text-sm text-slate-500 font-medium">Event: <span class="font-semibold text-blue-600">{{ tryout?.title }}</span></p>
                </div>

                <div class="relative z-10 flex gap-3">
                    <div class="bg-slate-50 px-4 py-2 rounded-xl border border-slate-200 text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Total</span>
                        <p class="text-xl font-bold text-slate-800">{{ stats?.total || 0 }}</p>
                    </div>
                    <div class="bg-amber-50 px-4 py-2 rounded-xl border border-amber-200 text-center">
                        <span class="text-[10px] font-bold text-amber-600 uppercase">Perlu Cek</span>
                        <p class="text-xl font-bold text-amber-600">{{ stats?.pending || 0 }}</p>
                    </div>
                    <div class="bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-200 text-center">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase">Diterima</span>
                        <p class="text-xl font-bold text-emerald-600">{{ stats?.paid || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="relative w-full md:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input v-model="search" @input="handleSearch" type="text" placeholder="Cari nama atau email..." class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" />
                </div>

                <div class="flex gap-2 bg-slate-100 p-1 rounded-xl">
                    <button type="button" @click.prevent="filterStatus('')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', !(filters?.status) ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Semua</button>
                    <button type="button" @click.prevent="filterStatus('pending')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters?.status === 'pending' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Pending</button>
                    <button type="button" @click.prevent="filterStatus('paid')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters?.status === 'paid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Valid</button>
                    <button type="button" @click.prevent="filterStatus('failed')" :class="['px-4 py-2 rounded-lg text-xs font-bold transition-all', filters?.status === 'failed' ? 'bg-white text-rose-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Ditolak</button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Peserta</th>
                                <th class="px-6 py-4">Info Pendaftaran</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="trx in participants.data" :key="trx.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs uppercase">{{ getInitials(trx.user?.name) }}</div>
                                        <div>
                                            <p class="font-semibold text-slate-900 text-sm">{{ trx.user?.name }}</p>
                                            <p class="text-xs text-slate-500">{{ trx.user?.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs space-y-1">
                                        <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">{{ trx.invoice_code }}</span>
                                        <p class="text-slate-400">{{ trx.created_at ? new Date(trx.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute:'2-digit'}) : '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border"
                                        :class="{
                                            'bg-amber-50 text-amber-600 border-amber-200': trx.status === 'pending',
                                            'bg-emerald-50 text-emerald-600 border-emerald-200': trx.status === 'paid',
                                            'bg-rose-50 text-rose-600 border-rose-200': trx.status === 'failed'
                                        }">
                                        {{ trx.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        type="button" 
                                        @click.prevent="openVerify(trx)" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors cursor-pointer shadow-sm"
                                    >
                                        <svg v-if="trx.proof_payment" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                        {{ trx.status === 'paid' ? 'Detail' : 'Proses' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showModal && selectedTrx" class="fixed inset-0 flex items-center justify-center z-[100000] p-4">
                
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>
                
                <div class="relative bg-white w-full max-w-4xl rounded-2xl p-6 md:p-8 shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-semibold text-lg text-slate-900">Verifikasi Peserta</h3>
                        <button type="button" @click="closeModal" class="p-2 text-slate-400 hover:bg-slate-100 rounded-full transition-colors cursor-pointer">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto space-y-6 pr-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-slate-50 p-4 rounded-xl space-y-2">
                                <p class="text-xs text-slate-500 font-bold uppercase">Detail User</p>
                                <p class="text-sm font-semibold text-slate-900">{{ selectedTrx.user?.name }}</p>
                                <p class="text-xs text-slate-500">{{ selectedTrx.user?.email }}</p>
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-xl">
                                <p class="text-xs text-slate-500 font-bold uppercase mb-2">Lampiran Bukti</p>
                                
                                <div v-if="selectedTrx.proof_payment" class="bg-slate-200 border border-slate-300 rounded-lg overflow-hidden flex flex-col items-center justify-center p-3">
                                    
                                    <a :href="getImageUrl(selectedTrx.proof_payment)" target="_blank" class="w-full text-center py-1.5 mb-2 bg-blue-100 text-blue-700 text-xs font-bold rounded hover:bg-blue-200 transition-colors flex items-center justify-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                        Buka Gambar di Tab Baru
                                    </a>

                                    <img :src="getImageUrl(selectedTrx.proof_payment)" class="max-h-64 object-contain" alt="Bukti Pembayaran"/>
                                    
                                </div>
                                <p v-else class="text-xs text-slate-400 italic">Peserta tidak mengunggah bukti pembayaran.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan (Opsional)</label>
                            <textarea v-model="rejectionReason" rows="2" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-6 border-t mt-6">
                        <button type="button" @click.prevent="processVerification('reject')" class="flex-1 py-2.5 bg-rose-50 text-rose-600 rounded-xl font-semibold text-sm hover:bg-rose-100 transition-colors cursor-pointer">Tolak</button>
                        <button type="button" @click.prevent="processVerification('approve')" class="flex-[2] py-2.5 bg-emerald-600 text-white rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-colors cursor-pointer">Terima</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>