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

    <div class="min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-blue-100 selection:text-blue-900">
        <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-lg border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo.png" alt="Logo" class="h-8 w-auto" />
                        <div class="hidden sm:flex flex-col justify-center">
                            <span class="text-lg font-bold tracking-tight text-slate-900">
                                CPNS <span class="text-blue-600">NUSANTARA</span>
                            </span>
                        </div>
                    </div>

                    <div v-if="canLogin" class="flex items-center gap-2 sm:gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-sm font-medium text-slate-600 hover:text-blue-600 transition"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-sm font-medium text-slate-600 hover:text-slate-900 transition px-2 py-2"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition shadow-sm"
                            >
                                Daftar
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header class="relative pt-28 pb-16 md:pt-36 md:pb-24 overflow-hidden px-4 sm:px-6 lg:px-8">
            <div class="absolute inset-0 bg-gradient-to-b from-blue-50/50 to-slate-50 -z-10"></div>
            
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold uppercase tracking-wide mb-6 md:mb-8">
                    Platform Persiapan CPNS Terpadu
                </div>
                
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight mb-6 tracking-tight">
                    Tingkatkan Peluang Lolos <br class="hidden sm:block" />
                    Seleksi CPNS Anda
                </h1>

                <p class="text-sm sm:text-base md:text-lg text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed px-2">
                    Sistem pembelajaran terstruktur dengan simulasi CAT BKN yang dirancang khusus untuk mengukur dan meningkatkan kesiapan Anda secara komprehensif.
                </p>

                <div class="flex flex-col sm:flex-row justify-center items-center gap-3 w-full max-w-md mx-auto sm:max-w-none">
                    <Link :href="route('register')" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors shadow-sm text-center">
                        Mulai Belajar Sekarang
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-300 text-slate-700 font-medium text-sm rounded-lg hover:bg-slate-50 transition-colors text-center">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </header>

        <section id="features" class="py-16 md:py-24 bg-white border-y border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12 md:mb-16">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4">Fasilitas Pembelajaran Terbaik</h2>
                    <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base">Kami menyediakan alat bantu komprehensif yang Anda butuhkan untuk persiapan tes yang optimal.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                    <div class="p-6 md:p-8 bg-slate-50 rounded-xl border border-slate-100 hover:shadow-md transition-shadow duration-300">
                        <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center mb-6 shadow-sm">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Materi Terintegrasi</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Akses modul pembelajaran TWK, TIU, dan TKP yang disusun berdasar kisi-kisi terbaru dari BKN.</p>
                    </div>

                    <div class="p-6 md:p-8 bg-slate-50 rounded-xl border border-slate-100 hover:shadow-md transition-shadow duration-300">
                        <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center mb-6 shadow-sm">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Simulasi CAT Akurat</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Uji kesiapan dengan sistem ujian yang dirancang secara khusus untuk menyerupai aplikasi tes sesungguhnya.</p>
                    </div>

                    <div class="p-6 md:p-8 bg-slate-50 rounded-xl border border-slate-100 hover:shadow-md transition-shadow duration-300">
                        <div class="w-12 h-12 bg-white rounded-lg border border-slate-200 flex items-center justify-center mb-6 shadow-sm">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Analitik Performa</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Evaluasi hasil secara instan dengan grafik analitik detail untuk memetakan kekuatan dan kelemahan Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-slate-50 py-10 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 text-center flex flex-col items-center">
                <div class="flex justify-center items-center mb-4">
                    <img src="/images/logo.png" alt="Logo" class="h-6 w-auto grayscale opacity-60" />
                    <span class="ml-2 font-semibold text-slate-500 text-sm tracking-wide">CPNS NUSANTARA</span>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-md">
                    &copy; 2026 CPNS Nusantara. Hak Cipta Dilindungi.<br class="sm:hidden" /> Bagian dari transformasi digital ASN.
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