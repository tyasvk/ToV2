<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ exclusiveTryouts: Array, generalTryouts: Array });
const activeTab = ref('exclusive');
</script>

<template>
    <Head title="Nusantara Adidaya - Akses Premium" />

    <AuthenticatedLayout>
        <div class="space-y-12">
            <div class="relative rounded-[4rem] bg-slate-900 p-12 md:p-20 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 w-1/2 h-full bg-indigo-500/5 -skew-x-12 translate-x-20"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-10">
                    <div class="text-center md:text-left space-y-6">
                        <div class="inline-flex items-center px-4 py-1.5 bg-indigo-500/20 border border-indigo-400/30 rounded-full">
                            <span class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.4em]">Akses Terbatas VIP</span>
                        </div>
                        <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-none">
                            NUSANTARA <br> <span class="italic text-indigo-500 text-6xl md:text-8xl">ADIDAYA.</span>
                        </h1>
                        <p class="text-slate-400 text-sm max-w-lg font-medium leading-relaxed italic">
                            "Persiapan tanpa batas dengan kurasi simulasi paling akurat berbasis Field Report terbaru."
                        </p>
                    </div>
                    
                    <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-8 rounded-[3rem] text-center min-w-[200px]">
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Paket Tersisa</p>
                        <p class="text-5xl font-black text-white leading-none">10</p>
                        <p class="text-[9px] font-bold text-slate-500 mt-4 uppercase tracking-widest">Eksklusif HOTS</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <div class="bg-white p-2.5 rounded-full shadow-xl border border-white flex gap-2">
                    <button @click="activeTab = 'exclusive'" :class="[activeTab === 'exclusive' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-400 hover:text-slate-600']" class="px-10 py-4 rounded-full text-[10px] font-black uppercase tracking-widest transition-all">
                        ⚡ Paket Adidaya
                    </button>
                    <button @click="activeTab = 'general'" :class="[activeTab === 'general' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-400 hover:text-slate-600']" class="px-10 py-4 rounded-full text-[10px] font-black uppercase tracking-widest transition-all">
                        🌐 Tryout Umum
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-20">
                <template v-if="activeTab === 'exclusive'">
                    <div v-for="i in 10" :key="i" class="bg-white rounded-[3.5rem] p-10 border border-white shadow-sm group hover:shadow-2xl hover:-translate-y-3 transition-all duration-500 relative overflow-hidden">
                        <div class="absolute -top-6 -right-6 w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center group-hover:bg-indigo-600 transition-colors duration-500">
                            <span class="text-slate-200 font-black text-4xl group-hover:text-white/20">{{ i }}</span>
                        </div>
                        <div class="relative z-10 space-y-8">
                            <div class="space-y-3">
                                <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase leading-tight group-hover:text-indigo-600 transition-colors">
                                    Simulasi Adidaya <br> Utama #{{ i }}
                                </h3>
                                <div class="flex items-center gap-3">
                                    <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl uppercase tracking-widest">FR 2024 / HOTS</span>
                                </div>
                            </div>
                            <Link href="#" class="block w-full text-center py-5 bg-slate-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200">
                                Mulai Simulasi
                            </Link>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>