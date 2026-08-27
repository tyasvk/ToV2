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

    <div class="min-h-screen bg-[#F5F5F7] text-[#1D1D1F] font-sans selection:bg-[#007AFF] selection:text-white">
        
        <!-- NAVBAR (Apple Glassmorphism Floating Bar) -->
        <nav class="fixed w-full z-50 top-0 inset-x-0 bg-white/75 backdrop-blur-2xl border-b border-black/5 transition-all">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    
                    <!-- Logo & Brand -->
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 flex items-center justify-center bg-[#1D1D1F] rounded-[10px] shadow-sm group-hover:scale-105 transition-transform">
                            <img src="/images/logo.png" alt="Logo" class="h-5 w-auto object-contain brightness-0 invert" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold tracking-wider text-[#1D1D1F] uppercase leading-tight">
                                CPNS <span class="text-[#007AFF]">NUSANTARA</span>
                            </span>
                            <span class="text-[9px] font-semibold text-[#86868B] uppercase tracking-widest leading-none">
                                Platform Seleksi ASN
                            </span>
                        </div>
                    </Link>

                    <!-- Nav Buttons -->
                    <div v-if="canLogin" class="flex items-center gap-2 sm:gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold rounded-full transition-all shadow-[0_4px_12px_rgba(0,122,255,0.25)] active:scale-[0.98]"
                        >
                            Buka Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-[13px] font-semibold text-[#1D1D1F] hover:text-[#007AFF] transition-colors"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold rounded-full transition-all shadow-[0_4px_12px_rgba(0,122,255,0.25)] active:scale-[0.98]"
                            >
                                Mulai Gratis
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- HERO SECTION (Editorial Apple Style) -->
        <header class="relative pt-32 pb-16 md:pt-44 md:pb-28 overflow-hidden px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center space-y-6">
                
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-black/5 text-[#007AFF] text-[11px] font-bold uppercase tracking-widest shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-pulse"></span>
                    Simulasi Berstandar CAT BKN
                </div>
                
                <h1 class="text-[36px] sm:text-[52px] md:text-[64px] font-bold text-[#1D1D1F] tracking-tight leading-[1.08]">
                    Tingkatkan Peluang Lolos <br class="hidden sm:block" />
                    Seleksi CPNS Anda.
                </h1>

                <p class="text-[15px] sm:text-[17px] md:text-[19px] text-[#86868B] max-w-2xl mx-auto font-medium leading-relaxed">
                    Sistem evaluasi mandiri dengan bank soal terverifikasi, analitik kelemahan, dan perankingan nasional serentak.
                </p>

                <!-- Action Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-3.5 max-w-sm sm:max-w-none mx-auto">
                    <Link 
                        :href="canRegister ? route('register') : route('login')" 
                        class="w-full sm:w-auto px-8 py-4 bg-[#007AFF] hover:bg-[#0062CC] text-white font-semibold text-[15px] rounded-full transition-all shadow-[0_6px_20px_rgba(0,122,255,0.3)] active:scale-[0.98] text-center"
                    >
                        Mulai Belajar Sekarang
                    </Link>
                    <a 
                        href="#features" 
                        class="w-full sm:w-auto px-8 py-4 bg-white text-[#1D1D1F] font-semibold text-[15px] rounded-full hover:bg-[#E3E3E8] border border-black/5 transition-all text-center shadow-sm active:scale-[0.98]"
                    >
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </header>

        <!-- FEATURES SECTION (iOS Widget Grid Style) -->
        <section id="features" class="py-16 md:py-24 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 md:mb-16 space-y-2">
                <h2 class="text-[26px] sm:text-[34px] font-bold text-[#1D1D1F] tracking-tight">
                    Fasilitas Pembelajaran Modern
                </h2>
                <p class="text-[15px] sm:text-[16px] text-[#86868B] max-w-xl mx-auto font-medium">
                    Dirancang dengan arsitektur intuitif untuk membantu Anda menguasai setiap materi ujian secara optimal.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                
                <!-- Card 1 -->
                <div class="p-8 bg-white rounded-[32px] border border-black/5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_36px_rgba(0,0,0,0.06)] transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <div class="w-14 h-14 bg-gradient-to-br from-[#007AFF] to-[#33C1FF] text-white rounded-[18px] flex items-center justify-center mb-6 shadow-md shadow-blue-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Materi Terstruktur
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed">
                        Akses bank soal HOTS TWK, TIU, dan TKP yang dikurasi langsung sesuai kisi-kisi resmi BKN.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="p-8 bg-white rounded-[32px] border border-black/5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_36px_rgba(0,0,0,0.06)] transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <div class="w-14 h-14 bg-gradient-to-br from-[#34C759] to-[#30D158] text-white rounded-[18px] flex items-center justify-center mb-6 shadow-md shadow-emerald-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Simulasi CAT Real-Time
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed">
                        Pengalaman tes interaktif dengan batas waktu, navigasi nomor, dan sistem penskoran yang akurat.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="p-8 bg-white rounded-[32px] border border-black/5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_36px_rgba(0,0,0,0.06)] transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                    <div class="w-14 h-14 bg-gradient-to-br from-[#AF52DE] to-[#5E5CE6] text-white rounded-[18px] flex items-center justify-center mb-6 shadow-md shadow-purple-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">
                        Analisis & Peringkat
                    </h3>
                    <p class="text-[14px] text-[#86868B] font-medium leading-relaxed">
                        Pantau posisi skor Anda di papan peringkat nasional serta dapatkan evaluasi pembahasan per butir soal.
                    </p>
                </div>

            </div>
        </section>

        <!-- FOOTER (Minimal Apple Style) -->
        <footer class="py-12 border-t border-black/5 bg-[#F5F5F7]">
            <div class="max-w-6xl mx-auto px-4 text-center flex flex-col items-center space-y-3">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 flex items-center justify-center bg-[#1D1D1F] rounded-[6px]">
                        <img src="/images/logo.png" alt="Logo" class="h-3.5 w-auto object-contain brightness-0 invert" />
                    </div>
                    <span class="font-bold text-[#1D1D1F] text-[13px] tracking-wider uppercase">CPNS NUSANTARA</span>
                </div>
                <p class="text-[12px] text-[#86868B] font-medium leading-relaxed max-w-md">
                    &copy; 2026 CPNS Nusantara. Hak Cipta Dilindungi.<br class="sm:hidden" /> Bagian dari transformasi digital seleksi ASN.
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