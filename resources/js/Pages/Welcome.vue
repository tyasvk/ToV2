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

    <div class="min-h-screen bg-white text-slate-900 selection:bg-amber-100 selection:text-amber-900">
        <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <div class="flex items-center">
                        <img src="/images/logo.png" alt="Logo" class="h-12 w-auto" />
                        <div class="ml-3 flex flex-col justify-center">
                            <span class="text-xl font-black tracking-tighter leading-none text-slate-900">
                                CPNS <span class="text-amber-500">NUSANTARA</span>
                            </span>
                        </div>
                    </div>

                    <div v-if="canLogin" class="flex items-center space-x-6">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-sm font-semibold text-slate-600 hover:text-amber-500 transition"
                        >
                            Ke Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-sm font-bold text-slate-600 hover:text-slate-900 transition"
                            >
                                Masuk
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-full transition shadow-lg shadow-slate-200"
                            >
                                Daftar Gratis
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header class="relative pt-16 pb-24 lg:pt-32 lg:pb-40">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-full -z-10">
                <div class="absolute top-10 left-10 w-72 h-72 bg-amber-100 rounded-full blur-3xl opacity-50"></div>
                <div class="absolute bottom-10 right-10 w-72 h-72 bg-blue-100 rounded-full blur-3xl opacity-50"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold uppercase tracking-widest mb-8">
                    🚀 Persiapan CPNS 2026 Terlengkap
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black text-slate-900 leading-tight mb-8">
                    Lolos Seleksi CPNS <br />
                    <span class="relative">
                        <span class="relative z-10 text-amber-500 italic">Lebih Mudah & Terukur</span>
                        <svg class="absolute -bottom-2 left-0 w-full h-3 text-amber-200 -z-10" viewBox="0 0 100 10" preserveAspectRatio="none">
                            <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="8" fill="none" />
                        </svg>
                    </span>
                </h1>

                <p class="text-xl text-slate-500 max-w-3xl mx-auto mb-12 leading-relaxed">
                    Gabung bersama ribuan pejuang NIP lainnya. Akses simulasi CAT tercanggih dengan sistem penilaian akurat sesuai standar BKN.
                </p>

                <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                    <Link :href="route('register')" class="w-full sm:w-auto px-10 py-5 bg-amber-500 hover:bg-amber-600 text-white font-black text-lg rounded-2xl transition-all shadow-xl shadow-amber-200 hover:-translate-y-1">
                        Mulai Belajar Sekarang
                    </Link>
                    <a href="#features" class="w-full sm:w-auto px-10 py-5 bg-white border-2 border-slate-100 text-slate-600 font-bold text-lg rounded-2xl hover:bg-slate-50 transition">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </header>

        <section id="features" class="py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-4">
                    <div class="max-w-xl">
                        <h2 class="text-3xl font-black text-slate-900 mb-4">Fitur Unggulan Untuk Kelulusan Anda</h2>
                        <p class="text-slate-500">Kami merancang setiap fitur untuk memastikan Anda siap menghadapi ujian yang sesungguhnya.</p>
                    </div>
                    <div class="hidden md:block">
                        <div class="h-1 w-24 bg-amber-500 rounded-full"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div class="group p-10 bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl hover:border-amber-500/20 transition-all duration-300">
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black mb-4">Materi Terintegrasi</h3>
                        <p class="text-slate-500 leading-relaxed">Modul pembelajaran lengkap mencakup TWK, TIU, dan TKP yang disusun oleh tim ahli.</p>
                    </div>

                    <div class="group p-10 bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl hover:border-amber-500/20 transition-all duration-300">
                        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black mb-4">Tryout CAT Akurat</h3>
                        <p class="text-slate-500 leading-relaxed">Sistem ujian yang 99% mirip dengan aplikasi CAT BKN. Latih mental dan kecepatan Anda.</p>
                    </div>

                    <div class="group p-10 bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-2xl hover:border-amber-500/20 transition-all duration-300">
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black mb-4">Grafik Analisis</h3>
                        <p class="text-slate-500 leading-relaxed">Dapatkan hasil analisis mendalam setelah tryout untuk mengetahui kelemahan Anda secara spesifik.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-white py-16 border-t border-slate-100">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <div class="flex justify-center items-center mb-8 opacity-50">
                    <img src="/images/logo.png" alt="Logo" class="h-8 w-auto grayscale" />
                    <span class="ml-2 font-bold text-slate-400">CPNS NUSANTARA</span>
                </div>
                <p class="text-slate-400 text-sm">
                    &copy; 2026 CPNS Nusantara. <br class="sm:hidden" /> Bagian dari transformasi digital ASN Indonesia.
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