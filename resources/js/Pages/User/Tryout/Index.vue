<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    tryouts: Array,
    filters: {
        type: Object,
        default: () => ({})
    }
});

// --- LOGIKA PENCARIAN ---
const search = ref(props.filters?.search || '');

watch(search, (value) => {
    router.get(route('tryout.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

// Helper Format Rupiah
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-2">
                    <h2 class="font-black text-4xl text-gray-900 tracking-tighter uppercase leading-none">
                        Katalog Tryout
                    </h2>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em]">
                        Pilih paket soal terbaik untuk mengasah kemampuan anda
                    </p>
                </div>

                <div class="relative w-full md:w-96 group">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-xl">🔍</span>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="CARI PAKET SOAL..." 
                        class="w-full bg-white border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-[10px] font-black tracking-widest uppercase placeholder:text-gray-300 focus:border-indigo-600 transition-all outline-none shadow-sm"
                    />
                </div>
            </div>
        </template>

        <div v-if="tryouts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="tryout in tryouts" :key="tryout.id" 
                class="bg-white rounded-[3rem] border border-gray-100 overflow-hidden shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 group flex flex-col">
                
                <div class="p-8 pb-0 flex justify-between items-start">
                    <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
                        📑
                    </div>
                    <span :class="tryout.is_paid ? 'text-amber-500 border-amber-100 bg-amber-50' : 'text-gray-400 border-gray-100 bg-gray-50'" 
                        class="px-4 py-1.5 border text-[9px] font-black uppercase tracking-widest rounded-full">
                        {{ tryout.is_paid ? 'Premium' : 'Gratis' }}
                    </span>
                </div>

                <div class="p-8 pt-6 flex-1 flex flex-col">
                    <h3 class="font-black text-xl text-gray-900 leading-tight uppercase tracking-tighter mb-4 h-14 overflow-hidden">
                        {{ tryout.title }}
                    </h3>
                    
                    <div class="flex items-center gap-4 mb-8 py-4 border-y border-gray-50">
                        <div class="flex flex-col flex-1">
                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest mb-1">Jumlah Soal</span>
                            <span class="text-xs font-black text-gray-700 uppercase">{{ tryout.questions_count }} Butir</span>
                        </div>
                        <div class="w-px h-8 bg-gray-100"></div>
                        <div class="flex flex-col flex-1 pl-2">
                            <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest mb-1">Durasi</span>
                            <span class="text-xs font-black text-gray-700 uppercase">{{ tryout.duration_minutes || 90 }} Menit</span>
                        </div>
                    </div>

                    <div v-if="tryout.is_paid" class="mb-6 flex justify-between items-center px-2">
                        <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest">Biaya Pendaftaran:</span>
                        <span class="text-xs font-black text-indigo-600">
                            {{ formatPrice(tryout.price || 0) }}
                        </span>
                    </div>

                    <Link 
                        :href="tryout.is_paid ? route('tryout.register', tryout.id) : route('tryout.show', tryout.id)" 
                        class="mt-auto block w-full text-center bg-gray-900 text-white py-5 rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 transition-all active:scale-95"
                    >
                        {{ tryout.is_paid ? 'Daftar Ujian' : 'Mulai Simulasi' }}
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="bg-white rounded-[4rem] border-2 border-dashed border-gray-100 p-24 text-center">
            <h3 class="font-black text-2xl text-gray-900 uppercase tracking-tighter">Paket Tidak Ditemukan</h3>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Coba gunakan kata kunci pencarian yang berbeda</p>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-all {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>