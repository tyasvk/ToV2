<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tryout: Object,
    participants: Object,
    stats: Object,
    filters: Object
});

// --- HELPER GAMBAR (CRUCIAL) ---
const getImageUrl = (path) => {
    if (!path) return '';
    
    // Bersihkan path jika ada sisa karakter aneh dari database lama
    let cleanPath = path;
    
    // Pastikan path tidak dimulai dengan slash ganda atau sudah ada /storage
    if (cleanPath.startsWith('/storage/')) {
        return cleanPath;
    }
    if (cleanPath.startsWith('storage/')) {
        return '/' + cleanPath;
    }
    
    // Default tambahkan /storage/
    return '/storage/' + cleanPath;
};

// --- MODAL LOGIC ---
const showModal = ref(false);
const selectedTrx = ref(null);

const openVerify = (trx) => {
    selectedTrx.value = trx;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => { selectedTrx.value = null; }, 200); // Delay biar animasi smooth
};

// --- ACTION LOGIC ---
const processVerification = (action) => {
    const actionText = action === 'approve' ? 'MENERIMA' : 'MENOLAK';
    if(!confirm(`Yakin ingin ${actionText} peserta ini?`)) return;

    router.post(route('admin.tryout-akbar.verify', selectedTrx.value.id), {
        action: action
    }, {
        onSuccess: () => closeModal(),
        preserveScroll: true
    });
};

const filterStatus = (status) => {
    router.get(route('admin.tryout-akbar.participants', props.tryout.id), { status }, { preserveState: true });
};
</script>

<template>
    <Head title="Verifikasi Peserta" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 py-8 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8">
                    <Link :href="route('admin.tryout-akbar.index')" class="text-sm font-bold text-slate-400 hover:text-indigo-600 mb-2 inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke Daftar Event
                    </Link>
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-black text-slate-800">Verifikasi Peserta</h1>
                            <p class="text-slate-500 text-sm">Event: <span class="font-bold text-indigo-600">{{ tryout.title }}</span></p>
                        </div>
                        
                        <div class="flex gap-3">
                            <div class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm text-center min-w-[80px]">
                                <p class="text-[10px] uppercase font-bold text-slate-400">Total</p>
                                <p class="font-black text-lg text-slate-800">{{ stats.total }}</p>
                            </div>
                            <div class="px-4 py-2 bg-amber-50 rounded-xl border border-amber-100 shadow-sm text-center min-w-[80px]">
                                <p class="text-[10px] uppercase font-bold text-amber-600">Pending</p>
                                <p class="font-black text-lg text-amber-700">{{ stats.pending }}</p>
                            </div>
                            <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm text-center min-w-[80px]">
                                <p class="text-[10px] uppercase font-bold text-emerald-600">Valid</p>
                                <p class="font-black text-lg text-emerald-700">{{ stats.paid }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <button @click="filterStatus('')" :class="['px-4 py-2 rounded-lg text-xs font-bold border transition whitespace-nowrap', !filters.status ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">Semua</button>
                    <button @click="filterStatus('pending')" :class="['px-4 py-2 rounded-lg text-xs font-bold border transition whitespace-nowrap', filters.status === 'pending' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">⏳ Perlu Verifikasi</button>
                    <button @click="filterStatus('paid')" :class="['px-4 py-2 rounded-lg text-xs font-bold border transition whitespace-nowrap', filters.status === 'paid' ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">✅ Diterima</button>
                    <button @click="filterStatus('failed')" :class="['px-4 py-2 rounded-lg text-xs font-bold border transition whitespace-nowrap', filters.status === 'failed' ? 'bg-red-500 text-white border-red-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50']">❌ Ditolak</button>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 border-b border-slate-100 text-xs uppercase font-bold text-slate-400">
                                <tr>
                                    <th class="px-6 py-4">Peserta</th>
                                    <th class="px-6 py-4">Invoice</th>
                                    <th class="px-6 py-4">Waktu Daftar</th>
                                    <th class="px-6 py-4 text-center">Bukti</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="trx in participants.data" :key="trx.id" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-800">{{ trx.user.name }}</p>
                                        <p class="text-xs text-slate-400">{{ trx.user.email }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs">{{ trx.invoice_code }}</td>
                                    <td class="px-6 py-4 text-xs">{{ new Date(trx.created_at).toLocaleString('id-ID') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button 
                                            v-if="trx.proof_payment" 
                                            @click="openVerify(trx)"
                                            class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100 transition"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            Lihat ({{ Array.isArray(trx.proof_payment) ? trx.proof_payment.length : 1 }})
                                        </button>
                                        <span v-else class="text-xs text-slate-400 italic">Kosong</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span v-if="trx.status === 'pending'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">⏳ Pending</span>
                                        <span v-else-if="trx.status === 'paid'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">✅ Valid</span>
                                        <span v-else-if="trx.status === 'failed'" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">❌ Invalid</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openVerify(trx)" class="text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 px-3 py-1.5 rounded-lg transition shadow-md active:scale-95">
                                            Proses
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="participants.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic bg-slate-50/50">Tidak ada data peserta ditemukan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="showModal && selectedTrx" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-200">
                        
                        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <span class="text-xl">🔍</span> Verifikasi Pembayaran & Syarat
                            </h3>
                            <button @click="closeModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none transition">&times;</button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-6 flex flex-col md:flex-row gap-8">
                            
                            <div class="w-full md:w-1/3 space-y-6">
                                <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase mb-4 border-b border-slate-50 pb-2">Informasi Peserta</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-[10px] text-slate-400 uppercase font-bold">Nama Lengkap</p>
                                            <p class="font-bold text-slate-800 text-sm">{{ selectedTrx.user.name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-400 uppercase font-bold">Email</p>
                                            <p class="font-medium text-slate-600 text-sm">{{ selectedTrx.user.email }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-400 uppercase font-bold">Nomor Invoice</p>
                                            <p class="font-mono text-sm bg-slate-100 px-2 py-1 rounded inline-block mt-1 select-all">{{ selectedTrx.invoice_code }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
                                    <h4 class="text-xs font-bold text-slate-400 uppercase mb-3 border-b border-slate-50 pb-2">Status Transaksi</h4>
                                    <span class="px-3 py-2 rounded-lg text-xs font-bold uppercase w-full block text-center" 
                                        :class="{
                                            'bg-amber-100 text-amber-700': selectedTrx.status === 'pending',
                                            'bg-emerald-100 text-emerald-700': selectedTrx.status === 'paid',
                                            'bg-red-100 text-red-700': selectedTrx.status === 'failed'
                                        }">
                                        {{ selectedTrx.status === 'paid' ? 'VALID / LUNAS' : selectedTrx.status }}
                                    </span>
                                </div>
                            </div>

                            <div class="w-full md:w-2/3 bg-slate-100 rounded-xl p-5 border border-slate-200">
                                <label class="text-xs font-bold text-slate-400 uppercase mb-3 block">Lampiran Bukti (Klik untuk Perbesar)</label>
                                
                                <div v-if="selectedTrx.proof_payment && selectedTrx.proof_payment.length > 0" class="grid gap-4">
                                    
                                    <div v-if="Array.isArray(selectedTrx.proof_payment)" class="grid grid-cols-2 gap-3">
                                        <div v-for="(path, idx) in selectedTrx.proof_payment" :key="idx" class="relative group">
                                            <a :href="getImageUrl(path)" target="_blank" class="block bg-white rounded-lg overflow-hidden border shadow-sm aspect-video">
                                                <img :src="getImageUrl(path)" class="w-full h-full object-cover transition duration-300 group-hover:scale-105" alt="Bukti Upload">
                                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-xs font-bold">
                                                    🔍 Perbesar
                                                </div>
                                            </a>
                                        </div>
                                    </div>

                                    <div v-else-if="typeof selectedTrx.proof_payment === 'string'">
                                        <a :href="getImageUrl(selectedTrx.proof_payment)" target="_blank" class="block bg-white rounded-lg overflow-hidden border shadow-sm">
                                            <img :src="getImageUrl(selectedTrx.proof_payment)" class="w-full rounded-lg" alt="Bukti Upload">
                                        </a>
                                    </div>

                                </div>
                                
                                <div v-else class="flex flex-col items-center justify-center py-10 text-slate-400 border-2 border-dashed border-slate-300 rounded-xl bg-white/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-xs italic">Tidak ada bukti yang diupload.</span>
                                </div>
                            </div>

                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                            <button @click="processVerification('reject')" class="px-5 py-2.5 bg-white border border-red-200 text-red-600 font-bold rounded-xl hover:bg-red-50 transition active:scale-95 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                Tolak (Invalid)
                            </button>
                            <button @click="processVerification('approve')" class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-200 transition active:scale-95 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Terima (Valid)
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>