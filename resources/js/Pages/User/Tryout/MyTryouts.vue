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

const isStarted = (startTime) => {
    if (!startTime) return true;
    return new Date() >= new Date(startTime);
};

const formatDateTime = (dateTime) => {
    if (!dateTime) return 'Sekarang';
    return new Date(dateTime).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Tryout Saya" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex gap-6 overflow-x-auto whitespace-nowrap scrollbar-hide">
                <Link :href="route('tryout.index')" class="pb-2 text-sm font-bold text-slate-500 hover:text-[#004a87] transition-colors border-b-2 border-transparent hover:border-slate-300">
                    Katalog Paket
                </Link>
                <Link :href="route('tryout.my')" class="pb-2 text-sm font-bold text-[#004a87] border-b-2 border-[#004a87]">
                    Tryout Saya
                </Link>
            </div>
        </template>

        <div class="pt-2 pb-12 md:pt-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div v-if="tryoutList.length === 0" class="py-12 md:py-20 text-center bg-white rounded-3xl border border-dashed border-slate-300 mx-auto px-4 mt-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg">Belum Ada Tryout</h3>
                    <p class="text-slate-500 text-sm mt-1 mb-4">Anda belum membeli paket tryout apapun.</p>
                    <Link :href="route('tryout.index')" class="inline-block px-6 py-2.5 bg-[#004a87] text-white rounded-xl text-sm font-bold shadow-md active:scale-95 transition-all">
                        Ke Katalog
                    </Link>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mt-2">
                    <div v-for="tryout in tryoutList" :key="tryout.id" class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden relative">
                        
                        <div class="h-32 bg-gradient-to-br from-[#004a87] to-blue-900 p-4 relative overflow-hidden">
                            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                            
                            <div class="absolute top-3 left-3">
                                <span class="px-2 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase rounded flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    Terdaftar
                                </span>
                            </div>

                            <h3 class="text-white font-bold text-lg mt-12 line-clamp-2 leading-tight relative z-10">{{ tryout.title }}</h3>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500 uppercase">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ tryout.duration_minutes }} Menit
                                </div>
                                <div class="p-2 bg-blue-50 rounded-lg border border-blue-100 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-[10px] font-black text-blue-700 uppercase">Akses: {{ formatDateTime(tryout.started_at) }}</span>
                                </div>
                                <div class="text-[10px] text-center font-bold text-slate-500">
                                    Kesempatan: <span :class="tryout.exam_attempts_count >= 3 ? 'text-red-600' : 'text-emerald-600'">{{ tryout.exam_attempts_count }}/3</span> Kali
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-slate-100 flex flex-col gap-2 w-full">
                                
                                <Link v-if="isStarted(tryout.started_at) && tryout.exam_attempts_count < 3"
                                    :href="route('tryout.wait', tryout.id)"
                                    class="w-full text-center px-5 py-2.5 bg-[#004a87] hover:bg-blue-800 text-white text-xs font-black uppercase rounded-xl transition-all shadow-md active:scale-95 flex items-center justify-center gap-2"
                                >
                                    Mulai Ujian
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                </Link>

                                <button v-else-if="!isStarted(tryout.started_at)"
                                    disabled
                                    class="w-full text-center px-5 py-2.5 bg-slate-200 text-slate-400 text-xs font-black uppercase rounded-xl cursor-not-allowed border border-slate-300 flex items-center justify-center gap-2"
                                >
                                    Belum Dimulai
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </button>
                                
                                <button v-else
                                    disabled
                                    class="w-full text-center px-5 py-2.5 bg-red-100 text-red-500 text-xs font-black uppercase rounded-xl cursor-not-allowed border border-red-200 flex items-center justify-center gap-2"
                                >
                                    Kesempatan Habis
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </button>

                                <Link :href="route('tryout.history.detail', tryout.id)"
                                    class="w-full text-center px-5 py-2.5 bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 text-xs font-black uppercase rounded-xl transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2"
                                >
                                    Detail Riwayat
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
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