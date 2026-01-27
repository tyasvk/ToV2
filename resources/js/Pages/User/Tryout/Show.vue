<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object
});

const instructions = [
    "WAKTU BERJALAN OTOMATIS SAAT MASUK.",
    "PASTIKAN KONEKSI STABIL.",
    "DILARANG MENYALIN SOAL.",
    "JAWABAN TERSIMPAN OTOMATIS.",
    "KLIK 'SELESAI' JIKA SUDAH YAKIN."
];
</script>

<template>
    <Head :title="'Konfirmasi: ' + tryout.title" />

    <div class="fixed inset-0 bg-gray-50 flex flex-col font-sans select-none overflow-hidden">
        
        <header class="h-14 bg-white border-b border-gray-100 px-6 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center font-black text-[9px] text-white">CAT</div>
                <h1 class="font-black text-gray-900 uppercase text-[10px] tracking-widest">KONFIRMASI UJIAN</h1>
            </div>
            <Link :href="route('tryout.index')" 
                class="px-3 py-1.5 border border-gray-200 rounded-lg text-[9px] font-black text-gray-400 uppercase tracking-widest hover:bg-gray-50 transition-colors">
                KEMBALI
            </Link>
        </header>

        <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 p-4 gap-4 overflow-hidden">
            
            <div class="lg:col-span-7 h-full">
                <div class="h-full bg-gray-900 rounded-[2rem] p-8 relative overflow-hidden flex flex-col justify-between shadow-xl group border border-gray-800">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-indigo-500/30 transition-all duration-1000"></div>
                    
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full border border-white/10 backdrop-blur-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[8px] font-black text-white uppercase tracking-[0.2em]">Siap Dikerjakan</span>
                        </div>
                        
                        <h1 class="text-2xl md:text-4xl font-black text-white uppercase leading-tight tracking-tight">
                            {{ tryout.title }}
                        </h1>
                        
                        <div class="space-y-0.5">
                            <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest">KODE PAKET</p>
                            <p class="text-base font-black text-indigo-400 tracking-widest uppercase">#CAT-{{ String(tryout.id).padStart(4, '0') }}</p>
                        </div>
                    </div>

                    <div class="relative z-10 grid grid-cols-2 gap-3 mt-4">
                        <div class="bg-white/5 border border-white/10 p-5 rounded-2xl backdrop-blur-md">
                            <div class="text-xl mb-2">⏱️</div>
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">DURASI</p>
                            <p class="text-xl font-black text-white uppercase tracking-tighter">{{ tryout.duration_minutes }} MENIT</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-5 rounded-2xl backdrop-blur-md">
                            <div class="text-xl mb-2">📝</div>
                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1">JUMLAH SOAL</p>
                            <p class="text-xl font-black text-white uppercase tracking-tighter">{{ tryout.questions_count ?? 0 }} BUTIR</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 h-full flex flex-col gap-4">
                
                <div class="flex-1 bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm flex flex-col overflow-hidden">
                    <div class="flex items-center gap-3 mb-4 shrink-0">
                        <div class="w-8 h-8 bg-gray-900 rounded-lg flex items-center justify-center text-sm">🛡️</div>
                        <div>
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Tata Tertib</h3>
                            <p class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Wajib Ditaati</p>
                        </div>
                    </div>

                    <div class="space-y-2 overflow-y-auto custom-scrollbar pr-1">
                        <div v-for="(rule, index) in instructions" :key="index" class="flex gap-3 items-start p-2.5 rounded-xl bg-gray-50 border border-gray-100">
                            <span class="w-5 h-5 bg-gray-900 text-white rounded-md flex items-center justify-center text-[8px] font-black shrink-0">
                                {{ index + 1 }}
                            </span>
                            <p class="text-[8px] font-bold text-gray-600 uppercase leading-relaxed pt-0.5">
                                {{ rule }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="shrink-0 space-y-3"> <Link :href="route('tryout.exam', tryout.id)" 
        class="w-full block py-5 bg-indigo-600 text-white rounded-[1.5rem] text-center font-black text-xs uppercase tracking-[0.2em] shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
        <span class="relative z-10 flex items-center justify-center gap-2">
            MULAI UJIAN (MODE MODERN) <span class="group-hover:translate-x-1 transition-transform">🚀</span>
        </span>
    </Link>

    <Link :href="route('tryout.exam.bkn', tryout.id)" 
        class="w-full block py-4 bg-white border-2 border-gray-200 text-gray-500 rounded-[1.5rem] text-center font-black text-[10px] uppercase tracking-[0.2em] hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700 transition-all duration-300">
        SIMULASI TAMPILAN CAT BKN 🇮🇩
    </Link>
</div>

            </div>

        </main>
    </div>
</template>

<style scoped>
/* PAKSA TEGAK (NO ITALIC) */
* { font-style: normal !important; }

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
</style>