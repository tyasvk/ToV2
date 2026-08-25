<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryout: Object,
});
</script>

<template>
    <Head :title="`Persiapan Ujian - ${tryout.title}`" />

    <AuthenticatedLayout>
        <!-- Background menyatu dengan layout utama -->
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden">
            
            <!-- Padding dan margin ini DISAMAKAN PERSIS dengan Index.vue (Katalog) -->
            <div class="max-w-[1400px] mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4">

                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
                    <div>
                        <Link :href="route('tryout.index')" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali ke Katalog
                        </Link>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Persiapan Ujian</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1">
                            Harap baca tata tertib dan pastikan koneksi internet stabil sebelum memulai.
                        </p>
                    </div>
                </div>

                <!-- CARD INFO UJIAN -->
                <div class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col gap-4 relative overflow-hidden max-w-3xl">
                    <!-- Ornamen Dekoratif di Pojok -->
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-50 rounded-full blur-2xl opacity-60 pointer-events-none"></div>

                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4 relative z-10">
                        <div class="w-12 h-12 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[16px] md:text-[18px] font-semibold text-slate-900 leading-snug tracking-tight">{{ tryout.title }}</h2>
                            <p class="text-[12px] text-slate-500 font-medium mt-0.5">Sistem CAT (Computer Assisted Test)</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 relative z-10">
                        <div class="bg-[#F5F5F7]/80 rounded-[16px] p-3 md:p-4 flex flex-col items-center text-center border border-slate-50">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Waktu Pengerjaan</span>
                            <span class="text-[20px] md:text-[24px] font-bold text-slate-800 tracking-tight">{{ tryout.duration }} <span class="text-[12px] font-semibold text-slate-500">Mnt</span></span>
                        </div>
                        <div class="bg-[#F5F5F7]/80 rounded-[16px] p-3 md:p-4 flex flex-col items-center text-center border border-slate-50">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Target Soal</span>
                            <span class="text-[20px] md:text-[24px] font-bold text-slate-800 tracking-tight">{{ tryout.questions_count || 110 }} <span class="text-[12px] font-semibold text-slate-500">Soal</span></span>
                        </div>
                    </div>
                </div>

                <!-- CARD TATA TERTIB -->
                <div class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col gap-3 max-w-3xl">
                    <div class="flex items-center gap-2.5 mb-1">
                        <div class="w-7 h-7 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-[15px] font-semibold text-slate-900 tracking-tight">Tata Tertib & Ketentuan</h3>
                    </div>
                    
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-[#007AFF] mt-1.5 shrink-0"></div>
                            <p class="text-[13px] text-slate-600 leading-relaxed font-medium">Waktu ujian akan otomatis berjalan sejak Anda menekan tombol <strong>Mulai Ujian</strong>.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-[#007AFF] mt-1.5 shrink-0"></div>
                            <p class="text-[13px] text-slate-600 leading-relaxed font-medium">Usahakan <strong>jangan me-refresh (F5)</strong> browser atau menekan tombol <em>Back</em> selama ujian berlangsung untuk mencegah masalah pengiriman data.</p>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                            <p class="text-[13px] text-rose-600 leading-relaxed font-medium">Apabila Anda keluar, waktu habis, atau Anda terputus dari server, jawaban akan tersimpan apa adanya dan <strong class="text-rose-700">jatah pengerjaan akan berkurang</strong>.</p>
                        </li>
                    </ul>
                </div>

                <!-- TOMBOL AKSI -->
                <div class="pt-2 pb-6 flex flex-col sm:flex-row gap-3 max-w-3xl">
                    <Link :href="route('tryout.index')" class="flex-1 text-center py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        Batal
                    </Link>

                    <!-- Rute dikembalikan menggunakan ujian standar (ExamSheet biasa) -->
                    <Link :href="route('tryout.exam', tryout.id)" class="flex-[2] text-center py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        Saya Mengerti, Mulai Ujian
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>