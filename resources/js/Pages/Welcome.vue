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

    <!-- Latar Belakang Abu-abu Sistem (iCloud Style) -->
    <div class="min-h-screen bg-[#F5F5F7] text-[#1D1D1F] font-sans selection:bg-[#007AFF] selection:text-white pb-20">

        <!-- NAVBAR (Apple Glassmorphism Floating Bar) -->
        <nav class="fixed w-full z-50 top-0 inset-x-0 bg-white/70 backdrop-blur-xl border-b border-black/5 transition-all">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-[60px] items-center">

                    <!-- Logo & Brand -->
                    <Link href="/" class="flex items-center gap-2.5 group">
                        <!-- Kotak Logo Putih ala Favicon/App Icon iOS -->
                        <div class="w-8 h-8 flex items-center justify-center bg-white rounded-[8px] shadow-sm border border-black/5 group-hover:scale-105 transition-transform overflow-hidden">
                            <!-- Hapus efek invert agar warna asli logo tampil -->
                            <img src="/images/logo.png" alt="Logo" class="h-5 w-auto object-contain" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[15px] font-bold tracking-tight text-[#1D1D1F] leading-none">
                                CPNS Nusantara
                            </span>
                        </div>
                    </Link>

                    <!-- Nav Buttons -->
                    <div v-if="canLogin" class="flex items-center gap-1 sm:gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-5 py-2 bg-[#1D1D1F] hover:bg-[#333336] text-white text-[13px] font-semibold rounded-full transition-all shadow-sm active:scale-[0.98]"
                        >
                            Buka Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-[13px] font-medium text-[#1D1D1F] hover:text-[#007AFF] transition-colors"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="hidden sm:inline-flex px-5 py-2 bg-[#1D1D1F] hover:bg-[#333336] text-white text-[13px] font-semibold rounded-full transition-all shadow-sm active:scale-[0.98]"
                            >
                                Daftar Gratis
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- HERO SECTION (Apple Product Page Style) -->
        <header class="relative pt-32 pb-16 md:pt-44 md:pb-24 overflow-hidden px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center space-y-6">

                <!-- Badge Atas -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-black/5 text-[#86868B] text-[11px] font-bold uppercase tracking-widest shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                    Simulasi Berstandar CAT BKN
                </div>

                <!-- Judul Raksasa ala Apple -->
                <h1 class="text-[40px] sm:text-[56px] md:text-[72px] font-bold text-[#1D1D1F] tracking-tight leading-[1.05]">
                    Tingkatkan peluang lolos seleksi Anda.
                </h1>

                <!-- Sub-judul -->
                <p class="text-[16px] sm:text-[18px] md:text-[20px] text-[#86868B] max-w-2xl mx-auto font-medium leading-relaxed">
                    Sistem evaluasi mandiri dengan bank soal terverifikasi, analitik kelemahan, dan perankingan nasional serentak.
                </p>

                <!-- Action Buttons (Pill & Chevron Link) -->
                <div class="pt-6 flex flex-col sm:flex-row justify-center items-center gap-5 sm:gap-8 max-w-sm sm:max-w-none mx-auto">
                    <Link 
                        :href="canRegister ? route('register') : route('login')" 
                        class="w-full sm:w-auto px-8 py-3.5 bg-[#007AFF] hover:bg-[#0062CC] text-white font-semibold text-[15px] rounded-full transition-all shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98] text-center"
                    >
                        Mulai Belajar Sekarang
                    </Link>
                    
                    <!-- Link dengan Chevron khas Apple -->
                    <a 
                        href="#features" 
                        class="group flex items-center justify-center gap-1.5 text-[15px] font-semibold text-[#007AFF] hover:text-[#0062CC] transition-colors"
                    >
                        Pelajari fitur lebih lanjut
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- FEATURES SECTION (iOS Widget Grid Style) -->
        <section id="features" class="py-12 md:py-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">

                <!-- Card 1 -->
                <div class="p-8 sm:p-10 bg-white rounded-[32px] border border-black/5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_16px_40px_rgba(0,0,0,0.08)] transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-[#F0F4FF] text-[#007AFF] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Materi Terstruktur
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed max-w-xs mx-auto">
                        Akses bank soal HOTS TWK, TIU, dan TKP yang dikurasi langsung sesuai kisi-kisi resmi BKN terbaru.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="p-8 sm:p-10 bg-white rounded-[32px] border border-black/5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_16px_40px_rgba(0,0,0,0.08)] transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-[#E5F5EA] text-[#34C759] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Simulasi CAT Real-Time
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed max-w-xs mx-auto">
                        Pengalaman tes interaktif dengan batas waktu, navigasi nomor, dan sistem penskoran yang akurat.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="p-8 sm:p-10 bg-white rounded-[32px] border border-black/5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] hover:shadow-[0_16px_40px_rgba(0,0,0,0.08)] transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-[#FFF9E6] text-[#FF9500] rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Analisis & Peringkat
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed max-w-xs mx-auto">
                        Pantau posisi skor Anda di papan peringkat nasional serta dapatkan evaluasi pembahasan per butir soal.
                    </p>
                </div>

            </div>
        </section>

        <!-- FOOTER (Minimalist Apple Style) -->
        <footer class="mt-12 py-8">
            <div class="max-w-6xl mx-auto px-4 text-center flex flex-col items-center space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 flex items-center justify-center bg-white rounded-[6px] shadow-sm border border-black/5 overflow-hidden">
                        <img src="/images/logo.png" alt="Logo" class="h-3.5 w-auto object-contain" />
                    </div>
                    <span class="font-bold text-[#1D1D1F] text-[13px] tracking-wider uppercase">CPNS NUSANTARA</span>
                </div>
                <p class="text-[12px] text-[#86868B] font-medium">
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
</style>