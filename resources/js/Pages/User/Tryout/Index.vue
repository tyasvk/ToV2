<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

/** * BAGIAN KRUSIAL: 
 * Pastikan nama di defineProps ini ADALAH 'tryouts' (plural dengan 's'). 
 * Jika di Controller Anda mengirim ['tryout' => $tryouts], 
 * maka di sini harus 'tryout'. Sesuaikan dengan Controller Anda.
 */
const props = defineProps({
    tryouts: {
        type: Array,
        default: () => []
    },
});

// Helper: Format Mata Uang (IDR)
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

// Helper: Cek apakah Tryout sudah bisa dikerjakan berdasarkan tanggal mulai
const isLocked = (startDate) => {
    if (!startDate) return false;
    return new Date(startDate) > new Date();
};

// Helper: Format Tanggal yang Manusiawi
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Katalog Simulasi CAT" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Katalog Tryout</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pilih paket simulasi untuk menguji kemampuan Anda</p>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="tryouts.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-[3rem] border-2 border-dashed border-slate-100 shadow-inner">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-4xl mb-6 grayscale opacity-50">📂</div>
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em]">Belum Ada Paket Tersedia</h3>
                    <p class="text-[9px] font-bold text-slate-300 uppercase mt-2">Pastikan status paket sudah 'Published' di Admin Panel</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="tryout in tryouts" :key="tryout.id" 
                        class="group bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500 overflow-hidden flex flex-col"
                    >
                        <div class="p-8 pb-0 flex justify-between items-start">
                            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg group-hover:bg-indigo-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div :class="tryout.is_paid ? 'bg-indigo-50 text-indigo-600 border-indigo-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'" 
                                class="px-3 py-1.5 rounded-xl border text-[8px] font-black uppercase tracking-widest shadow-sm">
                                {{ tryout.is_paid ? 'Premium Access' : 'Gratis' }}
                            </div>
                        </div>

                        <div class="p-8 flex-1">
                            <div class="mb-4">
                                <span class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">ID: #{{ String(tryout.id).padStart(4, '0') }}</span>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-tight mt-1">{{ tryout.title }}</h3>
                            </div>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter line-clamp-2 leading-relaxed mb-6">{{ tryout.description || 'Simulasi CAT dengan standar sistem BKN terbaru.' }}</p>
                            
                            <div class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-6">
                                <div>
                                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Durasi</span>
                                    <span class="text-xs font-black text-slate-900 uppercase">{{ tryout.duration_minutes }} Menit</span>
                                </div>
                                <div>
                                    <span class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Biaya</span>
                                    <span class="text-xs font-black text-slate-900 uppercase">
                                        {{ tryout.is_paid ? formatPrice(tryout.price) : 'Tanpa Biaya' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="px-8 pb-8 pt-0">
                            <div v-if="isLocked(tryout.started_at)" class="w-full py-4 bg-slate-50 border border-slate-100 rounded-2xl flex flex-col items-center justify-center opacity-80">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Akan Dibuka Pada</span>
                                <span class="text-[9px] font-black text-slate-900 uppercase tracking-tighter italic">{{ formatDate(tryout.started_at) }}</span>
                            </div>

                            <Link v-else :href="route('tryout.show', tryout.id)" 
                                class="w-full block text-center py-4 bg-slate-900 text-white rounded-2xl text-[9px] font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-indigo-600 hover:-translate-y-1 transition-all active:scale-95"
                            >
                                {{ tryout.is_paid ? 'Ambil Paket Ini' : 'Mulai Sekarang' }}
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Paksa font tegak untuk gaya industrial */
*:not(.italic) { font-style: normal !important; }

/* Animasi transisi smooth */
.transition-all {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Hover Effect Line-clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>