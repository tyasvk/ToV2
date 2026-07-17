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

// --- 1. SINKRONISASI DATA KATALOG (EVALUASI YANG BELUM DIBELI) ---
const availableCatalogTryouts = computed(() => {
    const ownedTryoutIds = (props.myTryouts || []).map(t => t.id);
    return (props.catalogTryouts || []).filter(t => !ownedTryoutIds.includes(t.id));
});

// --- 2. FITUR FILTER & PENCARIAN BERDASARKAN TAB AKTIF ---
const filteredTryouts = computed(() => {
    const baseData = activeTab.value === 'catalog' ? availableCatalogTryouts.value : (props.myTryouts || []);
    
    return baseData.filter(t => {
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

// Helper Menghitung Jumlah Pengerjaan secara Seksama
const getAttemptCount = (tryout) => {
    if (tryout.attempts && Array.isArray(tryout.attempts)) {
        return tryout.attempts.length;
    }
    return Number(tryout.attempts_count) || 0;
};
</script>

<template>
    <Head title="Katalog Tryout - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-7xl mx-auto">
            
            <!-- TAMPILAN MOBILE -->
            <div class="block md:hidden px-4 py-6 space-y-5">
                
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden flex flex-col gap-4 text-center">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-full blur-[40px] pointer-events-none -mr-10 -mt-10"></div>
                    
                    <div class="relative z-10">
                        <h1 class="text-xl text-slate-900 tracking-tight uppercase font-bold">Katalog Tryout</h1>
                        <p class="text-xs text-slate-500 font-medium mt-1">Pilih paket simulasi CAT untuk mengasah kemampuanmu.</p>
                    </div>

                    <div class="relative w-full z-10">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari simulasi..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 shadow-sm outline-none"
                        >
                    </div>
                </div>

                <div class="flex bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <button 
                        @click="activeTab = 'catalog'"
                        :class="[activeTab === 'catalog' ? 'bg-blue-50 text-blue-700 font-bold border-b-2 border-blue-600' : 'text-slate-500 font-medium hover:bg-slate-50 border-b-2 border-transparent']"
                        class="flex-1 py-3 text-center text-xs uppercase tracking-wider transition-all"
                    >
                        Katalog ({{ availableCatalogTryouts.length }})
                    </button>
                    <button 
                        @click="activeTab = 'my_tryouts'"
                        :class="[activeTab === 'my_tryouts' ? 'bg-blue-50 text-blue-700 font-bold border-b-2 border-blue-600' : 'text-slate-500 font-medium hover:bg-slate-50 border-b-2 border-transparent']"
                        class="flex-1 py-3 text-center text-xs uppercase tracking-wider transition-all"
                    >
                        Tryout Saya ({{ props.myTryouts.length }})
                    </button>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar -mx-4 px-4">
                    <button 
                        v-for="cat in ['all', 'free', 'premium']"
                        :key="cat"
                        @click="selectedCategory = cat"
                        :class="[selectedCategory === cat ? 'bg-blue-600 text-white shadow-sm font-bold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 font-medium']"
                        class="px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-all whitespace-nowrap shrink-0"
                    >
                        {{ cat === 'all' ? 'Semua Tipe' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                    </button>
                </div>

                <div v-if="filteredTryouts.length > 0" class="flex flex-col gap-4 pb-10">
                    <div 
                        v-for="tryout in filteredTryouts" 
                        :key="tryout.id"
                        class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col p-4 gap-3"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex flex-wrap gap-1.5">
                                <span :class="tryout.price > 0 || tryout.is_paid ? 'bg-amber-500' : 'bg-emerald-500'" class="px-2 py-1 text-white rounded-md text-[10px] font-bold uppercase tracking-widest shadow-sm">
                                    {{ tryout.price > 0 || tryout.is_paid ? 'PREMIUM' : 'GRATIS' }}
                                </span>
                                <span v-if="activeTab === 'my_tryouts'" class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-blue-600 text-white shadow-sm">
                                    TERSEDIA
                                </span>
                            </div>
                            <div class="w-7 h-7 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center text-slate-500 shrink-0 text-xs">
                                {{ activeTab === 'my_tryouts' ? '📝' : '📋' }}
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-2.5 flex flex-col gap-0.5">
                            <template v-if="activeTab === 'my_tryouts'">
                                <span class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold">Batas Ujian: Maks 3x</span>
                                <span class="text-[11px] truncate font-bold tracking-wide" :class="getAttemptCount(tryout) >= 3 ? 'text-amber-600' : 'text-slate-800'">
                                    Dikerjakan: {{ getAttemptCount(tryout) }} / 3 Kali
                                </span>
                            </template>
                            <template v-else>
                                <span class="text-[9px] text-slate-500 uppercase tracking-widest font-semibold">Akses Ujian</span>
                                <span class="text-[11px] text-slate-800 truncate font-bold tracking-wide">Sistem CAT Reguler</span>
                            </template>
                        </div>

                        <!-- KONTEN JUDUL TANPA DESKRIPSI -->
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 leading-snug uppercase line-clamp-2">
                                {{ tryout.title }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-1">
                            <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-200 px-2 py-1 rounded text-[10px] font-medium">
                                <span class="text-blue-500">📄</span> {{ tryout.questions_count || 110 }} Soal
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-600 bg-slate-50 border border-slate-200 px-2 py-1 rounded text-[10px] font-medium">
                                <span class="text-orange-500">⏰</span> {{ tryout.duration || 100 }} Menit
                            </div>
                        </div>

                        <div class="pt-3 mt-1 border-t border-slate-100 flex items-center justify-between gap-3 w-full">
                            <div class="flex flex-col shrink-0">
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Biaya Akses</span>
                                <span class="text-xs font-bold text-slate-800 tracking-tight">
                                    <template v-if="activeTab === 'my_tryouts'">
                                        <span class="text-emerald-600 text-[11px] uppercase font-bold">Milik Saya</span>
                                    </template>
                                    <template v-else>
                                        {{ tryout.price > 0 ? `Rp ${Number(tryout.price).toLocaleString('id-ID')}` : 'GRATIS' }}
                                    </template>
                                </span>
                            </div>

                            <template v-if="activeTab === 'my_tryouts'">
                                <Link 
                                    v-if="getAttemptCount(tryout) >= 3"
                                    :href="route('tryout.history.detail', tryout.id)"
                                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-[11px] uppercase font-bold tracking-wider active:scale-95 shadow-sm text-center shrink-0"
                                >
                                    Riwayat
                                </Link>
                                <Link 
                                    v-else
                                    :href="route('tryout.wait', tryout.id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-[11px] uppercase font-bold tracking-wider active:scale-95 shadow-sm text-center shrink-0"
                                >
                                    Mulai
                                </Link>
                            </template>
                            <template v-else>
                                <Link 
                                    :href="route('tryout.show', tryout.id)"
                                    class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-[11px] uppercase font-bold tracking-wider active:scale-95 shadow-sm text-center shrink-0"
                                >
                                    Detail
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center shadow-sm flex flex-col items-center">
                    <div class="text-4xl mb-3">📭</div>
                    <h3 class="text-sm text-slate-800 font-bold mb-1">
                        {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Akses Premium Aktif' : 'Tidak Ditemukan' }}
                    </h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                        <template v-if="activeTab === 'catalog' && props.isPremiumMember">
                            Seluruh paket simulasi telah diklaim dan dipindahkan ke tab "Tryout Saya".
                        </template>
                        <template v-else>
                            Paket simulasi tidak tersedia.
                        </template>
                    </p>
                    <button 
                        v-if="activeTab === 'catalog' && props.isPremiumMember"
                        @click="activeTab = 'my_tryouts'"
                        class="mt-4 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[11px] font-bold uppercase tracking-wider active:scale-95"
                    >
                        Buka Tryout Saya
                    </button>
                </div>
            </div>

            <!-- TAMPILAN DESKTOP -->
            <div class="hidden md:block space-y-8 px-4 py-8">
                
                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-row items-center justify-between gap-5 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                    <div class="relative z-10 space-y-1.5">
                        <h1 class="text-2xl text-slate-900 tracking-tight uppercase font-medium">Katalog Tryout</h1>
                        <p class="text-sm text-slate-500 font-normal">Pilih paket simulasi CAT untuk mengasah kemampuanmu.</p>
                    </div>

                    <div class="flex items-center gap-3 w-auto relative z-10">
                        <div class="relative w-72">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Cari simulasi..."
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex border-b border-slate-200 max-w-md">
                    <button 
                        @click="activeTab = 'catalog'"
                        :class="[activeTab === 'catalog' ? 'border-blue-600 text-blue-600 font-medium' : 'border-transparent text-slate-400 hover:text-slate-600']"
                        class="flex-1 py-3 text-center border-b-2 text-xs uppercase tracking-wider transition-all"
                    >
                        Katalog ({{ availableCatalogTryouts.length }})
                    </button>
                    <button 
                        @click="activeTab = 'my_tryouts'"
                        :class="[activeTab === 'my_tryouts' ? 'border-blue-600 text-blue-600 font-medium' : 'border-transparent text-slate-400 hover:text-slate-600']"
                        class="flex-1 py-3 text-center border-b-2 text-xs uppercase tracking-wider transition-all"
                    >
                        Tryout Saya ({{ props.myTryouts.length }})
                    </button>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-2 no-scrollbar px-1">
                    <button 
                        v-for="cat in ['all', 'free', 'premium']"
                        :key="cat"
                        @click="selectedCategory = cat"
                        :class="[selectedCategory === cat ? 'bg-blue-600 text-white shadow-sm border-blue-600' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 hover:text-slate-700']"
                        class="px-4 py-2 rounded-xl border text-[11px] uppercase tracking-wider transition-all whitespace-nowrap"
                    >
                        {{ cat === 'all' ? 'Semua Tipe' : (cat === 'free' ? 'Gratis' : 'Premium') }}
                    </button>
                </div>

                <div v-if="filteredTryouts.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pb-10">
                    <div 
                        v-for="tryout in filteredTryouts" 
                        :key="tryout.id"
                        class="group bg-white border border-slate-200 rounded-[1.5rem] overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 flex flex-col p-4"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex flex-wrap gap-1">
                                <span :class="tryout.price > 0 || tryout.is_paid ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white'" class="px-2 py-0.5 rounded text-[8px] uppercase tracking-widest shadow-sm font-medium">
                                    {{ tryout.price > 0 || tryout.is_paid ? 'PREMIUM' : 'GRATIS' }}
                                </span>
                                <span v-if="activeTab === 'my_tryouts'" class="px-2 py-0.5 rounded text-[8px] uppercase tracking-widest bg-blue-600 text-white shadow-sm font-medium">
                                    TERSEDIA
                                </span>
                            </div>
                            <div class="w-6 h-6 bg-slate-50 border border-slate-100 rounded-md flex items-center justify-center text-slate-500 shrink-0 text-xs">
                                {{ activeTab === 'my_tryouts' ? '📝' : '📋' }}
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-3">
                                
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 flex flex-col gap-0.5">
                                    <template v-if="activeTab === 'my_tryouts'">
                                        <span class="text-[8px] text-slate-500 uppercase tracking-widest font-semibold">Batas Ujian: Maks 3x</span>
                                        <span class="text-[10px] truncate font-bold tracking-wide" :class="getAttemptCount(tryout) >= 3 ? 'text-amber-600' : 'text-slate-800'">
                                            Dikerjakan: {{ getAttemptCount(tryout) }} / 3 Kali
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span class="text-[8px] text-slate-500 uppercase tracking-widest font-semibold">Akses Ujian</span>
                                        <span class="text-[10px] text-slate-800 truncate font-bold tracking-wide">Sistem CAT Reguler</span>
                                    </template>
                                </div>

                                <!-- KONTEN JUDUL TANPA DESKRIPSI -->
                                <div>
                                    <h2 class="text-sm font-medium text-slate-900 leading-tight tracking-tight group-hover:text-blue-600 transition-colors uppercase line-clamp-2">
                                        {{ tryout.title }}
                                    </h2>
                                </div>

                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <div class="flex items-center gap-1 text-slate-500 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded text-[9px] font-normal">
                                        <span class="text-blue-500">📄</span> {{ tryout.questions_count || 110 }} Soal
                                    </div>
                                    <div class="flex items-center gap-1 text-slate-500 bg-slate-50 border border-slate-100 px-1.5 py-0.5 rounded text-[9px] font-normal">
                                        <span class="text-orange-500">⏰</span> {{ tryout.duration || 100 }} Menit
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 w-full">
                                <div class="flex flex-col shrink-0">
                                    <span class="text-[8px] text-slate-400 uppercase tracking-widest mb-0.5">Biaya Akses</span>
                                    <span class="text-xs font-medium text-slate-700 tracking-tight leading-none">
                                        <template v-if="activeTab === 'my_tryouts'">
                                            <span class="text-emerald-600 text-[10px] uppercase font-medium">Milik Saya</span>
                                        </template>
                                        <template v-else>
                                            {{ tryout.price > 0 ? `Rp ${Number(tryout.price).toLocaleString('id-ID')}` : 'GRATIS' }}
                                        </template>
                                    </span>
                                </div>

                                <template v-if="activeTab === 'my_tryouts'">
                                    <Link 
                                        v-if="getAttemptCount(tryout) >= 3"
                                        :href="route('tryout.history.detail', tryout.id)"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-[10px] uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center font-medium shrink-0"
                                    >
                                        Riwayat
                                    </Link>
                                    <Link 
                                        v-else
                                        :href="route('tryout.wait', tryout.id)"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-[10px] uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center font-medium shrink-0"
                                    >
                                        Mulai
                                    </Link>
                                </template>
                                <template v-else>
                                    <Link 
                                        :href="route('tryout.show', tryout.id)"
                                        class="bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg text-[10px] uppercase tracking-wider transition-all active:scale-95 shadow-sm text-center font-medium shrink-0"
                                    >
                                        Detail
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-slate-200 rounded-[1.5rem] p-12 flex flex-col items-center text-center shadow-sm max-w-xl mx-auto">
                    <div class="w-12 h-12 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center mb-3 text-lg">
                        📭
                    </div>
                    <h3 class="text-sm text-slate-800 font-medium mb-1">
                        {{ (activeTab === 'catalog' && props.isPremiumMember) ? 'Akses Premium Aktif' : 'Tidak Ditemukan' }}
                    </h3>
                    <p class="text-xs text-slate-500 font-normal max-w-sm leading-relaxed">
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
                        class="mt-4 px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-medium uppercase tracking-wider hover:bg-slate-800 transition"
                    >
                        Buka Tab Tryout Saya
                    </button>
                </div>
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