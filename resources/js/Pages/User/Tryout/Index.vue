<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- 1. DEFINE PROPS DENGAN DEFAULT VALUE ---
const props = defineProps({
    tryouts: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const selectedCategory = ref('all');

// --- 2. COMPUTED DENGAN SAFE CHECK ---
const filteredTryouts = computed(() => {
    return (props.tryouts || []).filter(t => {
        const matchesSearch = t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'all' || t.type === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
});
</script>

<template>
    <Head title="Katalog Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Katalog Tryout</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1.5">Pilih paket simulasi CAT untuk mengasah kemampuanmu.</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari simulasi..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 overflow-x-auto pb-2 no-scrollbar px-1">
                <button 
                    v-for="cat in ['all', 'free', 'premium']"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[selectedCategory === cat ? 'bg-blue-600 text-white shadow-sm border-blue-600' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 hover:text-slate-700']"
                    class="px-5 py-2.5 md:py-2 rounded-xl border text-xs md:text-[11px] font-bold uppercase tracking-wider transition-all whitespace-nowrap"
                >
                    {{ cat === 'all' ? 'Semua Paket' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                </button>
            </div>

            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6 pb-10">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col"
                >
                    <div class="relative h-44 bg-slate-100 overflow-hidden border-b border-slate-100">
                        <img 
                            :src="tryout.image_url || '/images/default-tryout.png'" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >
                        <div class="absolute top-4 left-4">
                            <span :class="tryout.type === 'free' ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'" class="px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest shadow-sm">
                                {{ tryout.type }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-base font-bold text-slate-900 leading-snug line-clamp-2 mb-4 group-hover:text-blue-600 transition-colors">
                            {{ tryout.title }}
                        </h3>

                        <div class="mt-auto pt-5 border-t border-slate-100 flex items-center justify-between gap-4">
                            <div class="flex flex-col">
                                <span class="text-[9px] text-slate-400 uppercase tracking-widest font-semibold">Investasi</span>
                                <span class="text-sm font-bold text-slate-900">
                                    {{ tryout.price > 0 ? `Rp ${tryout.price.toLocaleString('id-ID')}` : 'GRATIS' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout.show', tryout.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-wider transition-all active:scale-95 shadow-sm"
                            >
                                Detail
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-16 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Tidak Ditemukan</h3>
                <p class="text-sm text-slate-500 font-medium max-w-sm">Maaf, simulasi tryout yang Anda cari belum tersedia saat ini.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>