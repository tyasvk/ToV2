<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- 1. DEFINE PROPS DENGAN DEFAULT VALUE ---
// Menambahkan default array kosong [] agar props.tryouts tidak undefined
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
    // Menambahkan (props.tryouts || []) untuk memastikan filter selalu berjalan pada array
    return (props.tryouts || []).filter(t => {
        // Safe access t.title dengan optional chaining (?)
        const matchesSearch = t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'all' || t.type === selectedCategory.value;
        return matchesSearch && matchesCategory;
    });
});
</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        <div class="space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-medium text-slate-900 tracking-tight">Katalog Tryout</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Pilih paket latihan untuk mengasah kemampuanmu.</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full md:w-64">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari tryout..."
                            class="w-full bg-white border-slate-200 rounded-2xl px-4 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium placeholder:text-slate-400"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar">
                <button 
                    v-for="cat in ['all', 'free', 'premium']"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[selectedCategory === cat ? 'bg-slate-900 text-white' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50']"
                    class="px-5 py-2 rounded-xl border text-[10px] uppercase tracking-widest font-medium transition-all whitespace-nowrap"
                >
                    {{ cat === 'all' ? 'Semua' : cat }}
                </button>
            </div>

            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group bg-white border border-slate-200/60 rounded-[2rem] overflow-hidden hover:shadow-xl transition-all duration-500 flex flex-col"
                >
                    <div class="relative h-44 bg-slate-100 overflow-hidden">
                        <img 
                            :src="tryout.image_url || '/images/default-tryout.png'" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        >
                        <div class="absolute top-4 left-4">
                            <span :class="tryout.type === 'free' ? 'bg-emerald-500' : 'bg-amber-500'" class="px-3 py-1 rounded-lg text-[8px] font-medium text-white uppercase tracking-widest shadow-lg">
                                {{ tryout.type }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-base font-medium text-slate-900 leading-snug line-clamp-2 mb-4 group-hover:text-indigo-600">
                            {{ tryout.title }}
                        </h3>

                        <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between gap-4">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-slate-400 uppercase tracking-widest font-medium">Investasi</span>
                                <span class="text-sm font-medium text-slate-900">
                                    {{ tryout.price > 0 ? `Rp ${tryout.price.toLocaleString('id-ID')}` : 'GRATIS' }}
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout.show', tryout.id)"
                                class="bg-slate-900 hover:bg-indigo-600 text-white px-5 py-2 rounded-xl text-[9px] font-medium uppercase tracking-widest transition-all active:scale-95 shadow-md"
                            >
                                Detail
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-100 rounded-[2.5rem] p-16 flex flex-col items-center text-center">
                <p class="text-sm text-slate-500 font-medium uppercase tracking-widest">Data Tryout Belum Tersedia</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>