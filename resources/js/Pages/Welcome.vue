<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Selamat Datang - CPNS Nusantara" />

    <!-- Latar Belakang Modern dengan Mesh Gradient -->
    <div class="min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-indigo-500 selection:text-white pb-20 relative overflow-hidden">
        
        <!-- Animated Background Blobs -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-purple-300/40 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
            <div class="absolute top-[20%] right-[-10%] w-96 h-96 bg-sky-300/40 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-96 h-96 bg-teal-300/40 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <!-- NAVBAR (Glassmorphism dengan Aksen Warna) -->
        <nav class="fixed w-full z-50 top-0 inset-x-0 bg-white/70 backdrop-blur-xl border-b border-slate-200/60 transition-all">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-[70px] items-center">

                    <!-- Logo & Brand -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.06)] border border-slate-100 group-hover:shadow-[0_4px_15px_rgba(99,102,241,0.2)] group-hover:-translate-y-0.5 transition-all overflow-hidden p-1.5">
                            <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[17px] font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-blue-600 leading-none">
                                CPNS Nusantara
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 mt-0.5">Platform Tryout No. 1</span>
                        </div>
                    </Link>

                    <!-- Nav Buttons -->
                    <div v-if="canLogin" class="flex items-center gap-2 sm:gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 text-white text-[14px] font-semibold rounded-full transition-all shadow-md shadow-indigo-500/20 active:scale-[0.98]"
                        >
                            Buka Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-[14px] font-bold text-slate-600 hover:text-indigo-600 transition-colors"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="hidden sm:inline-flex px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 text-white text-[14px] font-bold rounded-full transition-all shadow-md shadow-indigo-500/20 hover:shadow-lg hover:shadow-indigo-500/30 active:scale-[0.98]"
                            >
                                Daftar Gratis
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- HERO SECTION (Vibrant Edutech Style) -->
        <header class="relative pt-36 pb-20 md:pt-48 md:pb-28 overflow-hidden px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center space-y-8 relative z-10">

                <!-- Badge Atas -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-indigo-100 text-indigo-700 text-[12px] font-bold uppercase tracking-wider shadow-[0_4px_14px_rgba(99,102,241,0.1)]">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Simulasi Berstandar CAT BKN
                </div>

                <!-- Judul Raksasa dengan Gradient -->
                <h1 class="text-[42px] sm:text-[60px] md:text-[76px] font-extrabold text-slate-800 tracking-tight leading-[1.1]">
                    Tingkatkan peluang <br class="hidden sm:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-blue-500 to-teal-400">
                        lolos seleksi
                    </span> Anda.
                </h1>

                <!-- Sub-judul -->
                <p class="text-[16px] sm:text-[20px] text-slate-600 max-w-2xl mx-auto font-medium leading-relaxed">
                    Sistem evaluasi mandiri dengan bank soal terverifikasi, analitik kelemahan mendalam, dan perankingan nasional serentak.
                </p>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4 sm:gap-6 max-w-sm sm:max-w-none mx-auto">
                    <Link 
                        :href="canRegister ? route('register') : route('login')" 
                        class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-600 to-blue-500 hover:from-indigo-700 hover:to-blue-600 text-white font-bold text-[16px] rounded-full transition-all shadow-[0_8px_25px_rgba(99,102,241,0.35)] hover:shadow-[0_12px_30px_rgba(99,102,241,0.45)] hover:-translate-y-1 active:translate-y-0 text-center flex items-center justify-center gap-2"
                    >
                        Mulai Belajar Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </Link>
                    
                    <a 
                        href="#features" 
                        class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 text-slate-700 font-bold text-[16px] rounded-full transition-all shadow-sm border border-slate-200 hover:border-slate-300 text-center flex items-center justify-center gap-2 group"
                    >
                        Pelajari Fitur
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 group-hover:translate-y-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- FEATURES SECTION (Colorful & Engaging) -->
        <section id="features" class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Mengapa Memilih Kami?</h2>
                <p class="text-slate-500 font-medium">Fitur premium yang didesain khusus untuk kelulusan Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="group p-8 bg-white/80 backdrop-blur-sm rounded-[32px] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(99,102,241,0.08)] hover:-translate-y-2 hover:border-indigo-100 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-blue-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-sm border border-indigo-50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-slate-800 tracking-tight mb-3">
                        Materi Terstruktur
                    </h3>
                    <p class="text-[15px] text-slate-500 font-medium leading-relaxed">
                        Akses bank soal HOTS TWK, TIU, dan TKP yang dikurasi langsung sesuai kisi-kisi resmi BKN terbaru.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="group p-8 bg-white/80 backdrop-blur-sm rounded-[32px] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(16,185,129,0.08)] hover:-translate-y-2 hover:border-emerald-100 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-teal-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-sm border border-emerald-50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-slate-800 tracking-tight mb-3">
                        Simulasi CAT Real-Time
                    </h3>
                    <p class="text-[15px] text-slate-500 font-medium leading-relaxed">
                        Pengalaman tes interaktif dengan batas waktu, navigasi nomor, dan sistem penskoran yang 100% akurat.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="group p-8 bg-white/80 backdrop-blur-sm rounded-[32px] border border-slate-100 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgba(245,158,11,0.08)] hover:-translate-y-2 hover:border-amber-100 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-sm border border-amber-50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-slate-800 tracking-tight mb-3">
                        Analisis & Peringkat
                    </h3>
                    <p class="text-[15px] text-slate-500 font-medium leading-relaxed">
                        Pantau posisi skor Anda di papan peringkat nasional serta dapatkan evaluasi pembahasan per butir soal.
                    </p>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="mt-16 py-8 relative z-10 border-t border-slate-200/60 bg-white/50">
            <div class="max-w-6xl mx-auto px-4 text-center flex flex-col items-center space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 flex items-center justify-center bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden p-1">
                        <img src="/images/logo.png" alt="Logo" class="w-full h-full object-contain" />
                    </div>
                    <span class="font-extrabold text-slate-700 text-[14px] tracking-wide uppercase">CPNS NUSANTARA</span>
                </div>
                <p class="text-[13px] text-slate-500 font-medium">
                    Hak Cipta &copy; 2026 CPNS Nusantara. Seluruh hak cipta dilindungi undang-undang.
                </p>
            </div>
        </footer>

    </div>
</template>

<style scoped>
html {
    scroll-behavior: smooth;
}

/* Custom Keyframes for Background Blobs */
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
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