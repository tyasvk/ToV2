<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    catalogTryouts: {
        type: Array,
        default: () => []
    },
    myTryouts: {
        type: Array,
        default: () => []
    },
    isPremiumMember: {
        type: Boolean,
        default: false
    }
});

const searchQuery = ref('');
const activeTab = ref('catalog'); 
const selectedCategory = ref('all'); 

// --- FITUR FILTER & PENCARIAN ---
const filteredTryouts = computed(() => {
    const baseData = activeTab.value === 'catalog' ? props.catalogTryouts : props.myTryouts;
    
    return (baseData || []).filter(t => {
        const matchesSearch = t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        let matchesCategory = false;
        if (selectedCategory.value === 'all') {
            matchesCategory = true;
        } else if (selectedCategory.value === 'free') {
            matchesCategory = (t.price == 0 || t.is_paid == false || t.is_paid == 0);
        } else if (selectedCategory.value === 'premium') {
            matchesCategory = (t.price > 0 || t.is_paid == true || t.is_paid == 1);
        }
        
        return matchesSearch && matchesCategory;
    });
});
</script>

<template>
    <Head title="Katalog Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-7xl mx-auto">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-2xl md:text-3xl text-slate-900 tracking-tight uppercase">Katalog Tryout</h1>
                    <p class="text-sm text-slate-500 font-medium">Pilih paket simulasi CAT untuk mengasah kemampuanmu.</p>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
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

            <div class="flex border-b border-slate-200 max-w-md">
                <button 
                    @click="activeTab = 'catalog'"
                    :class="[activeTab === 'catalog' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']"
                    class="flex-1 py-3 text-center border-b-2 text-xs uppercase tracking-wider transition-all"
                >
                    Katalog ({{ props.catalogTryouts.length }})
                </button>
                <button 
                    @click="activeTab = 'my_tryouts'"
                    :class="[activeTab === 'my_tryouts' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600']"
                    class="flex-1 py-3 text-center border-b-2 text-xs uppercase tracking-wider transition-all"
                >
                    Tryout Saya ({{ props.myTryouts.length }})
                </button>
            </div>

            <div class="flex items-center gap-2.5 overflow-x-auto pb-2 no-scrollbar px-1">
                <button 
                    v-for="cat in ['all', 'free', 'premium']"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="[selectedCategory === cat ? 'bg-blue-600 text-white shadow-sm border-blue-600' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 hover:text-slate-700']"
                    class="px-5 py-2.5 md:py-2 rounded-xl border text-xs md:text-[11px] uppercase tracking-wider transition-all whitespace-nowrap"
                >
                    {{ cat === 'all' ? 'Semua Tipe' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                </button>
            </div>

            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 pb-10">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col"
                >
                    <div class="relative h-28 md:h-32 bg-slate-100 overflow-hidden border-b border-slate-100 font-sans">
                        <img 
                            :src="tryout.image_url || '/images/logo.png'" 
                            class="w-full h-full group-hover:scale-105 transition-transform duration-700"
                            :class="tryout.image_url ? 'object-cover object-center' : 'object-contain object-top pt-1 px-2 pb-10 md:pb-11 bg-slate-50/50'"
                            @error="(e) => { e.target.src = '/images/logo.png'; e.target.className = 'w-full h-full group-hover:scale-105 transition-transform duration-700 object-contain object-top pt-1 px-2 pb-10 md:pb-11 bg-slate-50/50'; }"
                        >
                        <div class="absolute top-2 left-2 flex gap-1">
                            <span :class="tryout.price > 0 || tryout.is_paid ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white'" class="px-2 py-0.5 rounded text-[8px] uppercase tracking-widest shadow-sm backdrop-blur-md border border-white/20 font-medium">
                                {{ tryout.price > 0 || tryout.is_paid ? 'PREMIUM' : 'GRATIS' }}
                            </span>
                            <span v-if="activeTab === 'my_tryouts'" class="px-2 py-0.5 rounded text-[8px] uppercase tracking-widest bg-blue-600 text-white shadow-sm border border-white/20 font-medium">
                                TERSEDIA
                            </span>
                        </div>
                        
                        <div class="absolute bottom-2 left-2 right-2">
                            <div class="bg-slate-900/70 backdrop-blur-md border border-white/10 rounded-lg p-1.5 flex items-center gap-1.5">
                                <div class="w-5 h-5 bg-white/20 rounded flex items-center justify-center text-white shrink-0 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 1.875 1.875v5.25a1.875 1.875 0 0 1-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V6.375A1.875 1.875 0 0 1 5.625 4.5Z" />
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[6px] text-slate-300 uppercase tracking-widest leading-none">Akses Simulasi</span>
                                    <span class="text-[9px] text-white truncate tracking-wide leading-none mt-0.5">
                                        {{ activeTab === 'my_tryouts' ? 'Siap Dikerjakan' : 'Sistem CAT Reguler' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 md:p-4 flex-1 flex flex-col">
                        <div class="flex-1 space-y-1.5">
                            <h2 class="text-sm md:text-base font-semibold text-slate-900 leading-tight tracking-tight group-hover:text-blue-600 transition-colors uppercase line-clamp-2">
                                {{ tryout.title }}
                            </h2>
                            
                            <p class="text-[9px] md:text-[10px] text-slate-500 font-medium line-clamp-2 leading-snug italic mt-1">
                                {{ tryout.description || 'Wujudkan impianmu menjadi Abdi Negara! Terus berlatih, pantang menyerah, dan raih NIP tahun ini.' }}
                            </p>

                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <div class="flex items-center gap-1 text-slate-600 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded">
                                    <svg class="w-2.5 h-2.5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <span class="text-[8px] md:text-[9px] uppercase tracking-widest font-medium">{{ tryout.questions_count || 110 }} Soal</span>
                                </div>
                                <div class="flex items-center gap-1 text-slate-600 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded">
                                    <svg class="w-2.5 h-2.5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span class="text-[8px] md:text-[9px] uppercase tracking-widest font-medium">{{ tryout.duration || 100 }} Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div class="flex flex-col shrink-0">
                                <span class="text-[7px] md:text-[8px] text-slate-400 uppercase tracking-widest mb-0.5">Biaya Akses</span>
                                <span class="text-xs md:text-sm font-bold text-slate-900 tracking-tight leading-none">
                                    <template v-if="activeTab === 'my_tryouts'">
                                        <span class="text-emerald-600 text-[10px] md:text-xs uppercase">Dimiliki</span>
                                    </template>
                                    <template v-else>
                                        {{ tryout.price > 0 ? `Rp ${Number(tryout.price).toLocaleString('id-ID')}` : 'GRATIS' }}
                                    </template>
                                </span>
                            </div>

                            <Link 
                                :href="route('tryout.show', tryout.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-1.5 rounded-lg text-[9px] md:text-[10px] uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center font-medium"
                            >
                                {{ activeTab === 'my_tryouts' ? 'Mulai' : 'Detail' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-16 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-base text-slate-900 mb-1">
                    {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Akses Premium Aktif' : 'Tidak Ditemukan' }}
                </h3>
                <p class="text-sm text-slate-500 font-medium max-w-sm">
                    <template v-if="activeTab === 'catalog' && props.isPremiumMember">
                        Sebagai akun Premium, seluruh paket simulasi telah diklaim dan otomatis dipindahkan ke tab "Tryout Saya".
                    </template>
                    <template v-else>
                        Paket simulasi ujian dengan kategori ini belum tersedia atau Anda sudah mendaftar di paket tersebut.
                    </template>
                </p>
                <button 
                    v-if="activeTab === 'catalog' && props.isPremiumMember"
                    @click="activeTab = 'my_tryouts'"
                    class="mt-5 px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs uppercase tracking-wider hover:bg-slate-800 transition"
                >
                    Buka Tab Tryout Saya
                </button>
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