<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: false,
    },
    phpVersion: {
        type: String,
        required: false,
    },
});

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Platform CAT Modern" />

    <div class="min-h-screen bg-slate-50 font-sans selection:bg-[#004a87] selection:text-white">
        
        <nav class="absolute top-0 w-full z-50">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-[#004a87] rounded-lg flex items-center justify-center text-white font-black">
                            TO
                        </div>
                        <span class="font-bold text-slate-800 tracking-tight">CAT SYSTEM</span>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-sm font-bold text-slate-600 hover:text-[#004a87] transition"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-sm font-bold text-slate-600 hover:text-[#004a87] transition"
                            >
                                Masuk
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-5 py-2.5 bg-[#004a87] text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-800 transition active:scale-95"
                            >
                                Daftar Sekarang
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 text-center">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-[#004a87] text-[10px] font-black uppercase tracking-wider mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#004a87] animate-pulse"></span>
                    Sistem Ujian Online Terpercaya
                </div>

                <h1 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tight mb-6 leading-tight">
                    Simulasi Ujian <br class="hidden lg:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#004a87] to-blue-500">
                        Cepat, Akurat & Real-time
                    </span>
                </h1>

                <p class="text-lg text-slate-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Platform Computer Assisted Test (CAT) modern untuk latihan SKD, SKB, dan ujian kompetensi lainnya dengan sistem penilaian standar nasional.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('tryout.index')"
                        class="w-full sm:w-auto px-8 py-4 bg-[#004a87] text-white font-bold rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-800 transition-all active:scale-95 flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                        Mulai Ujian Sekarang
                    </Link>
                    <Link
                        v-else
                        :href="route('register')"
                        class="w-full sm:w-auto px-8 py-4 bg-[#004a87] text-white font-bold rounded-2xl shadow-xl shadow-blue-200 hover:bg-blue-800 transition-all active:scale-95"
                    >
                        Buat Akun Gratis
                    </Link>

                    <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-white text-slate-600 font-bold rounded-2xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition-all text-center">
                        Pelajari Fitur
                    </a>
                </div>
            </div>

            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-7xl pointer-events-none">
                <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400/10 rounded-full blur-3xl mix-blend-multiply animate-blob"></div>
                <div class="absolute top-20 right-10 w-72 h-72 bg-purple-400/10 rounded-full blur-3xl mix-blend-multiply animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-32 left-1/2 w-72 h-72 bg-emerald-400/10 rounded-full blur-3xl mix-blend-multiply animate-blob animation-delay-4000"></div>
            </div>
        </div>

        <div id="features" class="py-24 bg-white border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
                    
                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center text-[#004a87] mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Sistem Real-time</h3>
                        <p class="text-slate-500 leading-relaxed text-sm">
                            Pengalaman ujian tanpa lag dengan sinkronisasi jawaban otomatis. Hasil langsung keluar setelah selesai.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Analisis Lengkap</h3>
                        <p class="text-slate-500 leading-relaxed text-sm">
                            Statistik pengerjaan detail, grafik perkembangan nilai, dan perbandingan ranking nasional.
                        </p>
                    </div>

                    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all duration-300 group">
                        <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 mb-6 group-hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Simulasi CAT BKN</h3>
                        <p class="text-slate-500 leading-relaxed text-sm">
                            Tampilan antarmuka (UI) yang dirancang mirip dengan sistem CAT BKN asli agar Anda terbiasa.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        <footer class="bg-white border-t border-slate-200 py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-center md:text-left">
                    <span class="font-black text-slate-800 tracking-tight text-lg">CAT SYSTEM</span>
                    <p class="text-slate-400 text-xs mt-1">© {{ currentYear }} All rights reserved.</p>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-slate-400 hover:text-[#004a87] text-sm font-bold transition">Syarat & Ketentuan</a>
                    <a href="#" class="text-slate-400 hover:text-[#004a87] text-sm font-bold transition">Kebijakan Privasi</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Animasi Background Blob */
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>