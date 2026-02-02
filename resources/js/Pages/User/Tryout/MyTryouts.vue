<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryouts: [Object, Array],
    filters: Object,
});

const page = usePage();

const tryoutList = computed(() => {
    if (props.tryouts && props.tryouts.data) return props.tryouts.data;
    return Array.isArray(props.tryouts) ? props.tryouts : [];
});

const paginationLinks = computed(() => {
    return (props.tryouts && props.tryouts.links) ? props.tryouts.links : [];
});

// Helper Format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return 'Fleksibel';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};
</script>

<template>
    <Head title="Tryout Saya" />

    <AuthenticatedLayout>
        
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-violet-500/10 rounded-full blur-[80px]"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-indigo-500/20 border border-indigo-400/20 text-indigo-200 text-[10px] font-black tracking-[0.2em] uppercase mb-6 backdrop-blur-sm shadow-lg shadow-indigo-900/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span> Ruang Ujian
                </span>
                
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4 leading-tight">
                    KOLEKSI <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400 italic">SAYA.</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-sm md:text-base text-slate-400 font-medium leading-relaxed">
                    Daftar paket tryout yang telah Anda miliki. 
                    Fokus pada tujuan dan kerjakan simulasi dengan performa terbaik.
                </p>
            </div>
        </div>

        <div class="flex justify-center -mt-16 relative z-10 mb-10">
            <div class="bg-white p-1.5 rounded-full shadow-2xl shadow-indigo-900/10 border border-white flex gap-1">
                <Link :href="route('tryout.index')" 
                    class="px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50"
                >
                    <span>📝</span> Katalog Paket
                </Link>
                <Link :href="route('tryout.my')" 
                    class="px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 bg-slate-900 text-white shadow-lg transform scale-105"
                >
                    <span>👤</span> Tryout Saya
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-2 pb-20">
            
            <div v-if="tryoutList.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-[3rem] border border-dashed border-slate-200 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-4xl mb-4 grayscale opacity-50">📂</div>
                <h3 class="text-xl font-bold text-slate-800">Belum Ada Tryout</h3>
                <p class="text-slate-500 text-sm mt-2 max-w-md">Anda belum memiliki paket ujian aktif. Silakan beli di Katalog.</p>
                <Link :href="route('tryout.index')" class="mt-6 px-6 py-3 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition">
                    Ke Katalog Tryout
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tryout in tryoutList" :key="tryout.id" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border border-slate-200 overflow-hidden transition-all duration-500 hover:-translate-y-2 flex flex-col h-full relative">
                    
                    <div class="absolute top-4 right-4 z-20">
                        <span class="px-3 py-1 bg-slate-900/90 backdrop-blur-md text-white text-[9px] font-black uppercase tracking-wider rounded-lg shadow-lg flex items-center gap-1.5">
                            <span class="text-indigo-400 text-xs">✔</span> Terdaftar
                        </span>
                    </div>

                    <div class="h-20 bg-gradient-to-br from-indigo-50 via-white to-slate-50 relative overflow-hidden border-b border-slate-100">
                        <div class="absolute -left-4 -top-4 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition duration-700"></div>
                        <div class="absolute right-2 top-2 text-6xl opacity-[0.04] font-black text-slate-900 select-none italic tracking-tighter">
                            MY
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-0 flex-1 flex flex-col -mt-10 relative z-10">
                        <div class="w-14 h-14 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:rotate-3 transition duration-500 text-indigo-600">
                            🚀
                        </div>

                        <h3 class="text-lg font-black text-slate-800 leading-tight mb-1 group-hover:text-indigo-600 transition truncate" :title="tryout.title">
                            {{ tryout.title }}
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-5">Siap Dikerjakan</p>

                        <div class="mb-6 bg-slate-50 p-1.5 rounded-2xl border border-slate-100 group-hover:border-indigo-100 transition-colors">
                            <div class="grid grid-cols-2 gap-px bg-slate-200/50 rounded-xl overflow-hidden">
                                <div class="bg-white p-3 flex flex-col items-center justify-center text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Waktu</p>
                                    <p class="text-sm font-black text-slate-800 leading-none">{{ tryout.duration }}m</p>
                                </div>
                                <div class="bg-white p-3 flex flex-col items-center justify-center text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Percobaan</p>
                                    <p class="text-sm font-black text-slate-800 leading-none">{{ tryout.exam_attempts_count ?? 0 }}x</p>
                                </div>
                            </div>
                            
                            <div class="mt-1.5 px-3 py-2 bg-white rounded-xl flex items-center justify-between border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Akses</span>
                                <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded text-right">
                                    {{ formatDate(tryout.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto flex flex-col gap-2">
                            
                            <Link :href="route('tryout.show', tryout.id)" 
                                class="block w-full py-3.5 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] text-center bg-slate-900 text-white shadow-lg shadow-slate-200 hover:bg-indigo-600 hover:shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-2 group-hover:gap-3"
                            >
                                <span v-if="tryout.exam_attempts_count > 0">Kerjakan Lagi</span>
                                <span v-else>Mulai Ujian</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>

                            <Link v-if="tryout.exam_attempts_count > 0" 
                                :href="route('tryout.history.detail', tryout.id)" 
                                class="block w-full py-2.5 rounded-xl font-bold text-[9px] uppercase tracking-widest text-center text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-200 transition active:scale-95 flex items-center justify-center gap-1.5"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Lihat Riwayat
                            </Link>

                        </div>
                    </div>
                </div>
            </div>

            <div v-if="paginationLinks.length > 3" class="mt-12 flex justify-center">
                <div class="bg-white p-1.5 rounded-full shadow-lg border border-slate-100 inline-flex gap-1">
                    <template v-for="(link, k) in paginationLinks" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            v-html="link.label" 
                            class="w-9 h-9 flex items-center justify-center text-[10px] font-bold rounded-full transition-all" 
                            :class="link.active 
                                ? 'bg-slate-900 text-white shadow-md' 
                                : 'text-slate-400 hover:bg-slate-50 hover:text-slate-900'" 
                        />
                        <span v-else 
                            v-html="link.label" 
                            class="w-9 h-9 flex items-center justify-center text-[10px] text-slate-300 cursor-default font-bold">
                        </span>
                    </template>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>