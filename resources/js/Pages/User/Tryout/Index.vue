<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryouts: [Object, Array], 
});

const tryoutList = computed(() => {
    if (props.tryouts && props.tryouts.data) return props.tryouts.data;
    return Array.isArray(props.tryouts) ? props.tryouts : [];
});

const paginationLinks = computed(() => {
    return (props.tryouts && props.tryouts.links) ? props.tryouts.links : [];
});

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex gap-6 overflow-x-auto whitespace-nowrap scrollbar-hide">
                <Link :href="route('tryout.index')" class="pb-2 text-sm font-bold text-[#004a87] border-b-2 border-[#004a87]">
                    Katalog Paket
                </Link>
                <Link :href="route('tryout.my')" class="pb-2 text-sm font-bold text-slate-500 hover:text-[#004a87] transition-colors border-b-2 border-transparent hover:border-slate-300">
                    Tryout Saya
                </Link>
            </div>
        </template>

        <div class="pt-2 pb-12 md:pt-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="tryoutList.length === 0" class="py-12 md:py-20 text-center bg-white rounded-3xl border border-dashed border-slate-300 mx-auto px-4 mt-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg">Semua Paket Sudah Anda Miliki</h3>
                    <p class="text-slate-500 text-sm mt-1">Anda telah membeli semua paket tryout yang tersedia.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mt-2">
                    <div v-for="tryout in tryoutList" :key="tryout.id" class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden relative">
                        
                        <div class="h-32 bg-gradient-to-br from-slate-800 to-[#004a87] p-4 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                            <div class="absolute top-3 right-3">
                                <span class="px-2 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase rounded">{{ tryout.price == 0 ? 'Gratis' : 'Premium' }}</span>
                            </div>
                            <h3 class="text-white font-bold text-lg mt-12 line-clamp-2 leading-tight relative z-10">{{ tryout.title }}</h3>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <div class="space-y-2 mb-6">
                                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500 uppercase">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ tryout.duration_minutes }} Menit | CAT BKN
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Harga Paket</p>
                                    <p class="text-sm font-black text-slate-900">{{ tryout.price > 0 ? formatRupiah(tryout.price) : 'Gratis' }}</p>
                                </div>

                                <Link :href="route('tryout.register', tryout.id)"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase rounded-xl transition-all shadow-md active:scale-95 flex items-center gap-2 whitespace-nowrap"
                                >
                                    Daftar
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="paginationLinks.length > 3" class="mt-8 md:mt-12 flex justify-center flex-wrap gap-1">
                    <template v-for="(link, k) in paginationLinks" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-2 md:px-4 text-xs font-bold rounded-lg transition-all border shadow-sm" :class="link.active ? 'bg-[#004a87] text-white border-[#004a87]' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'" />
                        <span v-else v-html="link.label" class="px-3 py-2 md:px-4 text-xs text-slate-300 border border-transparent"></span>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>