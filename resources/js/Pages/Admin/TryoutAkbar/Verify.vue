<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash'; // Pastikan lodash terinstall, atau hapus debounce jika error

const props = defineProps({
    tryout: Object,
    participants: Object,
    stats: Object,
    filters: Object
});

// --- STATE ---
const search = ref(props.filters.search || '');
const showModal = ref(false);
const selectedTrx = ref(null);
const rejectionReason = ref('');

// --- SEARCH & FILTER ---
const handleSearch = debounce(() => {
    router.get(
        route('admin.tryout-akbar.participants', props.tryout.id),
        { status: props.filters.status, search: search.value },
        { preserveState: true, replace: true }
    );
}, 500);

const filterStatus = (status) => {
    router.get(
        route('admin.tryout-akbar.participants', props.tryout.id),
        { status, search: search.value },
        { preserveState: true }
    );
};

// --- HELPER IMAGE ---
const getImageUrl = (path) => {
    if (!path) return '';
    let cleanPath = path.replace(/["\[\]]/g, ''); // Bersihkan karakter array JSON jika ada
    if (cleanPath.startsWith('/storage/')) return cleanPath;
    if (cleanPath.startsWith('storage/')) return '/' + cleanPath;
    return '/storage/' + cleanPath;
};

const getInitials = (name) => name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();

// --- MODAL ACTION ---
const openVerify = (trx) => {
    selectedTrx.value = trx;
    rejectionReason.value = trx.rejection_note || ''; // Load alasan lama jika ada
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => { selectedTrx.value = null; }, 200);
};

const processVerification = (action) => {
    const actionText = action === 'approve' ? 'MENERIMA' : 'MENOLAK';
    
    if (action === 'reject' && !rejectionReason.value) {
        if(!confirm('Alasan penolakan kosong. Lanjutkan?')) return;
    } else {
        if(!confirm(`Yakin ingin ${actionText} peserta ini?`)) return;
    }

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
        <div class="min-h-screen bg-slate-50 py-8 font-sans text-slate-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                    <div>
                        <Link :href="route('admin.tryout-akbar.index')" class="text-xs font-bold text-slate-400 hover:text-indigo-600 flex items-center gap-1 mb-2">
                            &larr; Kembali ke Daftar
                        </Link>
                        <h1 class="text-2xl font-black text-slate-800">Manajemen Peserta</h1>
                        <p class="text-sm text-slate-500">Event: <span class="font-bold text-indigo-600">{{ tryout.title }}</span></p>
                    </div>

                    <div class="flex gap-3 overflow-x-auto pb-2">
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm min-w-[100px] text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</span>
                            <p class="text-2xl font-black text-slate-800">{{ stats.total }}</p>
                        </div>
                        <div class="bg-amber-50 p-3 rounded-xl border border-amber-100 shadow-sm min-w-[100px] text-center">
                            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Perlu Cek</span>
                            <p class="text-2xl font-black text-amber-600">{{ stats.pending }}</p>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100 shadow-sm min-w-[100px] text-center">
                            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Diterima</span>
                            <p class="text-2xl font-black text-emerald-600">{{ stats.paid }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="relative w-full md:w-96">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                        <input 
                            v-model="search" 
                            @input="handleSearch"
                            type="text" 
                            placeholder="Cari Nama, Email, atau Invoice..." 
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border-transparent focus:bg-white focus:border-indigo-500 rounded-xl text-sm transition"
                        >
                    </div>

                    <div class="flex bg-slate-100 p-1 rounded-xl overflow-x-auto">
                        <button @click="filterStatus('')" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap', !filters.status ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">Semua</button>
                        <button @click="filterStatus('pending')" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap', filters.status === 'pending' ? 'bg-white text-amber-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">⏳ Pending</button>
                        <button @click="filterStatus('paid')" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap', filters.status === 'paid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">✅ Valid</button>
                        <button @click="filterStatus('failed')" :class="['px-4 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap', filters.status === 'failed' ? 'bg-white text-red-600 shadow-sm' : 'text-slate-500 hover:text-slate-700']">❌ Ditolak</button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Peserta</th>
                                    <th class="px-6 py-4">Info Pendaftaran</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="trx in participants.data" :key="trx.id" class="hover:bg-slate-50/80 transition">
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-xs shrink-0">
                                                {{ getInitials(trx.user.name) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 line-clamp-1">{{ trx.user.name }}</p>
                                                <p class="text-xs text-slate-500 line-clamp-1">{{ trx.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded w-fit select-all">{{ trx.invoice_code }}</span>
                                            <span class="text-xs text-slate-400">
                                                {{ new Date(trx.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour:'2-digit', minute:'2-digit'}) }}
                                            </span>
                                            <button v-if="trx.proof_payment" class="text-xs text-indigo-600 font-bold flex items-center gap-1 mt-1 hover:underline w-fit" @click="openVerify(trx)">
                                                📎 Lihat Bukti
                                            </button>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span v-if="trx.status === 'pending'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu
                                        </span>
                                        <span v-else-if="trx.status === 'paid'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            ✅ Diterima
                                        </span>
                                        <span v-else-if="trx.status === 'failed'" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                            ❌ Ditolak
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <div v-if="trx.status === 'paid'" class="text-xs font-bold text-emerald-600 opacity-60 flex items-center justify-end gap-1">
                                            <span>Terverifikasi</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        </div>

                                        <button 
                                            v-else 
                                            @click="openVerify(trx)"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-indigo-600 shadow-md transition active:scale-95"
                                        >
                                            {{ trx.status === 'failed' ? 'Cek Ulang' : 'Proses' }}
                                        </button>
                                    </td>
                                </tr>
                                
                                <tr v-if="participants.data.length === 0">
                                    <td colspan="4" class="py-12 text-center text-slate-400 italic">
                                        Tidak ada data peserta ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-center" v-if="participants.links.length > 3">
                    <div class="flex gap-1">
                        <template v-for="(link, key) in participants.links" :key="key">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                v-html="link.label"
                                class="px-3 py-1 rounded-lg text-xs font-bold border transition"
                                :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50'"
                            />
                        </template>
                    </div>
                </div>

                <div v-if="showModal && selectedTrx" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200">
                        
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <span class="text-xl">🔍</span> Verifikasi Pembayaran
                            </h3>
                            <button @click="closeModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 flex flex-col md:flex-row gap-6 bg-white">
                            
                            <div class="w-full md:w-1/3 space-y-4">
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 shadow-sm">
                                    <div class="text-center mb-4">
                                        <div class="w-16 h-16 mx-auto bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-xl font-black mb-2">
                                            {{ getInitials(selectedTrx.user.name) }}
                                        </div>
                                        <h4 class="font-bold text-slate-800">{{ selectedTrx.user.name }}</h4>
                                        <p class="text-xs text-slate-500">{{ selectedTrx.user.email }}</p>
                                    </div>
                                    <div class="space-y-3 pt-4 border-t border-slate-200">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-slate-500">Invoice</span>
                                            <span class="font-mono font-bold text-slate-700">{{ selectedTrx.invoice_code }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="text-slate-500">Status</span>
                                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" 
                                                :class="{
                                                    'bg-amber-100 text-amber-700': selectedTrx.status === 'pending',
                                                    'bg-emerald-100 text-emerald-700': selectedTrx.status === 'paid',
                                                    'bg-red-100 text-red-700': selectedTrx.status === 'failed'
                                                }">
                                                {{ selectedTrx.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-2/3 bg-slate-50 rounded-xl p-5 border border-slate-200 flex flex-col">
                                <label class="text-xs font-bold text-slate-400 uppercase mb-3 flex items-center gap-2">Lampiran Bukti</label>
                                
                                <div class="flex-1 overflow-y-auto max-h-[400px] pr-2">
                                    <div v-if="selectedTrx.proof_payment && selectedTrx.proof_payment.length > 0" class="grid gap-4">
                                        <div v-if="Array.isArray(selectedTrx.proof_payment)" class="grid grid-cols-2 gap-3">
                                            <div v-for="(path, idx) in selectedTrx.proof_payment" :key="idx" class="relative group">
                                                <a :href="getImageUrl(path)" target="_blank" class="block bg-white rounded-lg overflow-hidden border shadow-sm aspect-video">
                                                    <img :src="getImageUrl(path)" class="w-full h-full object-cover transition hover:scale-105" alt="Bukti">
                                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-xs font-bold">Buka</div>
                                                </a>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <a :href="getImageUrl(selectedTrx.proof_payment)" target="_blank" class="block bg-white rounded-lg overflow-hidden border shadow-sm">
                                                <img :src="getImageUrl(selectedTrx.proof_payment)" class="w-full rounded-lg" alt="Bukti">
                                            </a>
                                        </div>
                                    </div>
                                    <div v-else class="h-full flex flex-col items-center justify-center text-slate-400 py-10">
                                        <span class="text-xs italic">Tidak ada bukti terlampir</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                            
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Catatan Verifikasi (Diisi jika menolak)</label>
                                <textarea 
                                    v-model="rejectionReason" 
                                    rows="2" 
                                    class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                    placeholder="Contoh: Bukti buram, mohon upload ulang..."
                                ></textarea>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button @click="processVerification('reject')" class="px-5 py-2.5 bg-white border border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 transition active:scale-95 text-sm flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    Tolak Data
                                </button>
                                <button @click="processVerification('approve')" class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition active:scale-95 text-sm flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Terima Peserta
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>