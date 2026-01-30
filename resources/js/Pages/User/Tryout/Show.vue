<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryout: Object
});

// --- LOGIKA TOMBOL KEMBALI (X) ---
const backRoute = computed(() => {
    // Jika tipe tryout adalah 'akbar', kembali ke halaman Tryout Akbar
    if (props.tryout.type === 'akbar') {
        return route('tryout-akbar.index');
    }
    // Default: Kembali ke halaman Tryout Umum
    return route('tryout.index');
});

const instructions = [
    { icon: "⚡", title: "Instan", desc: "Waktu mulai dihitung saat klik." },
    { icon: "📡", title: "Koneksi", desc: "Pastikan internet Anda stabil." },
    { icon: "🛡️", title: "Jujur", desc: "Dilarang curang / rekam layar." },
    { icon: "💾", title: "Auto-Save", desc: "Jawaban tersimpan otomatis." },
    { icon: "🏁", title: "Submit", desc: "Klik selesai di nomor terakhir." }
];
</script>

<template>
    <Head :title="tryout.title" />

    <div class="min-h-screen bg-[#F5F5F7] font-sans text-slate-800 flex flex-col relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.4] pointer-events-none" 
             style="background-image: radial-gradient(#CBD5E1 1px, transparent 1px); background-size: 24px 24px;">
        </div>

        <nav class="sticky top-4 z-50 px-4 mb-4">
            <div class="max-w-5xl mx-auto bg-white/80 backdrop-blur-xl border border-white/40 shadow-sm rounded-full px-6 py-3 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs">
                        TO
                    </div>
                    <span class="font-bold text-slate-900 text-sm tracking-tight">Tryout Platform</span>
                </div>
                
                <Link :href="backRoute" 
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors text-slate-500 hover:text-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </Link>
            </div>
        </nav>

        <main class="flex-1 w-full max-w-5xl mx-auto p-4 pb-12">
            
            <div class="grid grid-cols-1 md:grid-cols-6 lg:grid-cols-12 gap-4 auto-rows-min">
                
                <div class="md:col-span-6 lg:col-span-8 bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full blur-3xl opacity-60 group-hover:scale-110 transition-transform duration-700"></div>
                    
                    <div class="relative z-10 flex flex-col h-full justify-between gap-6">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-full mb-6">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                                <span class="text-[10px] font-bold uppercase tracking-widest">Siap Ujian</span>
                            </div>
                            <h1 class="text-3xl lg:text-5xl font-black text-slate-900 leading-[1.1] tracking-tight mb-2">
                                {{ tryout.title }}
                            </h1>
                            <p class="font-medium text-slate-400">Kode Paket: #CAT-{{ String(tryout.id).padStart(4, '0') }}</p>
                        </div>

                        <div class="flex gap-4">
                            <div class="bg-slate-50 border border-slate-100 px-5 py-3 rounded-2xl">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Durasi</span>
                                <span class="block text-xl font-bold text-slate-900">{{ tryout.duration_minutes ?? 0 }}m</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 px-5 py-3 rounded-2xl">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Soal</span>
                                <span class="block text-xl font-bold text-slate-900">{{ tryout.questions_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-6 lg:col-span-4 bg-slate-900 text-white rounded-[2rem] p-6 flex flex-col justify-between shadow-xl shadow-slate-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="font-bold text-xl mb-1">Mulai Sekarang</h3>
                        <p class="text-slate-400 text-sm mb-6">Pilih mode tampilan ujian.</p>
                        
                        <div class="space-y-3">
                            <Link :href="route('tryout.exam', tryout.id)" 
                                class="flex items-center justify-between w-full p-4 bg-white text-slate-900 rounded-xl font-bold hover:bg-indigo-50 transition-colors group">
                                <span>Mode Modern</span>
                                <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs group-hover:scale-110 transition-transform">➜</span>
                            </Link>
                            
                            <Link :href="route('tryout.exam.bkn', tryout.id)" 
                                class="flex items-center justify-between w-full p-4 bg-white/10 text-white border border-white/10 rounded-xl font-medium hover:bg-white/20 transition-colors">
                                <span>Mode CAT BKN</span>
                                <span class="opacity-50">↗</span>
                            </Link>
                        </div>
                    </div>

                    <div class="absolute bottom-0 right-0 translate-x-1/3 translate-y-1/3 w-48 h-48 bg-indigo-600 blur-[60px] opacity-50 pointer-events-none"></div>
                </div>

                <div class="col-span-1 md:col-span-3 lg:col-span-3 bg-white p-6 rounded-[2rem] border border-slate-100 flex flex-col justify-center items-center text-center gap-2 hover:shadow-md transition-shadow">
                    <div class="text-4xl mb-2">⏱️</div>
                    <h4 class="font-bold text-slate-900">Waktu Otomatis</h4>
                    <p class="text-xs text-slate-500">Berjalan saat masuk</p>
                </div>

                <div class="col-span-1 md:col-span-3 lg:col-span-3 bg-white p-6 rounded-[2rem] border border-slate-100 flex flex-col justify-center items-center text-center gap-2 hover:shadow-md transition-shadow">
                    <div class="text-4xl mb-2">📡</div>
                    <h4 class="font-bold text-slate-900">Internet Stabil</h4>
                    <p class="text-xs text-slate-500">Wajib koneksi baik</p>
                </div>

                <div class="col-span-1 md:col-span-3 lg:col-span-3 bg-white p-6 rounded-[2rem] border border-slate-100 flex flex-col justify-center items-center text-center gap-2 hover:shadow-md transition-shadow">
                    <div class="text-4xl mb-2">💾</div>
                    <h4 class="font-bold text-slate-900">Auto Save</h4>
                    <p class="text-xs text-slate-500">Aman tiap soal</p>
                </div>

                <div class="col-span-1 md:col-span-3 lg:col-span-3 bg-white p-6 rounded-[2rem] border border-slate-100 flex flex-col justify-center items-center text-center gap-2 hover:shadow-md transition-shadow">
                    <div class="text-4xl mb-2">⚖️</div>
                    <h4 class="font-bold text-slate-900">Jujur</h4>
                    <p class="text-xs text-slate-500">No cheating allowed</p>
                </div>

                <div class="md:col-span-6 lg:col-span-12 bg-[#F1F5F9] rounded-[2rem] p-6 border border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-lg">💡</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Tips Pengerjaan</h4>
                            <p class="text-sm text-slate-500">Kerjakan soal yang mudah terlebih dahulu untuk menghemat waktu.</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>