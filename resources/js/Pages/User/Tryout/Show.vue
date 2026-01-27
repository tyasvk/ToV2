<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object
});

// Tata tertib pengerjaan (Tegak & Tegas)
const instructions = [
    "SISTEM AKAN MENGUNCI JAWABAN ANDA SECARA OTOMATIS SAAT WAKTU BERAKHIR.",
    "DILARANG MENYALIN ATAU MENYEBARLUASKAN SOAL DALAM BENTUK APAPUN.",
    "PASTIKAN PERANGKAT ANDA MEMILIKI DAYA BATERAI YANG CUKUP.",
    "HASIL AKAN LANGSUNG MUNCUL SETELAH ANDA MENYELESAIKAN SEMUA SOAL.",
    "KLIK TOMBOL KONFIRMASI DI BAWAH UNTUK MEMULAI SESI UJIAN."
];
</script>

<template>
    <Head :title="'Konfirmasi: ' + tryout.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-700">
                <h2 class="font-black text-4xl text-gray-900 tracking-tighter uppercase leading-none">
                    Detail Paket Ujian
                </h2>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em]">
                    Tinjau informasi dan tata tertib sebelum memulai simulasi
                </p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-10 pb-24 animate-in fade-in slide-in-from-bottom-4 duration-1000">
            
            <div class="bg-gradient-to-br from-gray-900 to-indigo-950 rounded-[3.5rem] p-10 md:p-14 shadow-2xl shadow-indigo-100/50 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-64 h-full bg-white/5 -skew-x-12 translate-x-24"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-10 text-center md:text-left">
                    <div class="space-y-5">
                        <div class="inline-flex px-5 py-2 bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 text-[10px] font-black uppercase tracking-widest rounded-full">
                            PAKET SOAL #{{ String(tryout.id).padStart(3, '0') }}
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black text-white tracking-tighter uppercase leading-tight">
                            {{ tryout.title }}
                        </h1>
                        <p class="text-[10px] text-indigo-200/60 font-bold uppercase tracking-[0.4em]">
                            STATUS: TERSEDIA UNTUK DIKERJAKAN
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 shrink-0 w-full md:w-auto">
                        <div class="bg-white/5 border border-white/10 p-6 rounded-[2.5rem] text-center backdrop-blur-md">
                            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">DURASI</p>
                            <p class="text-xl font-black text-white uppercase tracking-tighter">90 MENIT</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-6 rounded-[2.5rem] text-center backdrop-blur-md">
                            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">SOAL</p>
                            <p class="text-xl font-black text-white uppercase tracking-tighter">{{ tryout.questions_count }} BUTIR</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3.5rem] border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-10 md:p-14">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl">📋</div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter">Tata Tertib Pengerjaan</h3>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(rule, index) in instructions" :key="index" class="flex gap-6 items-start group">
                            <span class="w-10 h-10 bg-gray-50 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 rounded-2xl flex items-center justify-center text-[11px] font-black text-gray-400 shrink-0 border border-gray-100">
                                {{ index + 1 }}
                            </span>
                            <p class="text-[11px] font-bold text-gray-600 uppercase tracking-widest leading-relaxed pt-2.5">
                                {{ rule }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-6">
                <Link :href="route('tryout.index')" 
                    class="w-full md:w-1/3 text-center py-5 border-2 border-gray-100 text-gray-400 rounded-[2rem] font-black text-[11px] uppercase tracking-[0.2em] hover:bg-gray-50 hover:text-gray-600 transition-all active:scale-95">
                    ← KEMBALI KE KATALOG
                </Link>
                
                <Link :href="route('tryout.exam', tryout.id)" 
                    class="w-full md:w-2/3 text-center py-6 bg-gray-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-[0.3em] shadow-2xl shadow-gray-200 hover:bg-indigo-600 transition-all focus:ring-8 focus:ring-indigo-50 outline-none active:scale-95">
                    KONFIRMASI & KERJAKAN SOAL 🚀
                </Link>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Menghilangkan semua gaya tulisan miring */
* {
    font-style: normal !important;
}

.transition-all {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
}
</style>