<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object,
    is_registration_closed: Boolean,
});

const formatCurrency = (price) => {
    if (!price || price === 0) return 'Gratis';
    return new Intl.NumberFormat('id-ID', { 
        style: 'currency', 
        currency: 'IDR', 
        minimumFractionDigits: 0 
    }).format(price);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Tidak ditentukan';
    return new Date(dateString).toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    }) + ' WIB';
};
</script>

<template>
    <Head :title="tryout.title" />

    <AuthenticatedLayout>
        <!-- Container Minimalis -->
        <div class="max-w-4xl mx-auto px-4 py-8 sm:py-12 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <!-- Tombol Kembali -->
            <Link :href="route('tryout.index')" class="text-sm text-slate-500 hover:text-slate-900 mb-8 inline-flex items-center gap-2 transition-colors font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Katalog
            </Link>

            <!-- Card Utama -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm flex flex-col md:flex-row gap-10 lg:gap-16">
                
                <!-- Kiri: Detail Informasi -->
                <div class="flex-1 space-y-8">
                    <div>
                        <div class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider mb-4 border"
                            :class="tryout.is_paid ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
                            {{ tryout.is_paid ? 'Paket Premium' : 'Paket Gratis' }}
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ tryout.title }}
                        </h1>
                        <p class="text-slate-500 mt-4 text-sm sm:text-base leading-relaxed">
                            {{ tryout.description || 'Tidak ada deskripsi tambahan untuk paket tryout ini.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-y-6 gap-x-4 pt-6 border-t border-slate-100">
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mb-1">Durasi Pengerjaan</p>
                            <p class="font-medium text-slate-800 text-lg">{{ tryout.duration }} <span class="text-sm text-slate-500">Menit</span></p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mb-1">Total Soal</p>
                            <p class="font-medium text-slate-800 text-lg">{{ tryout.questions_count || '-' }} <span class="text-sm text-slate-500">Soal</span></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mb-1">Masa Pendaftaran</p>
                            <p class="font-medium text-slate-800 text-sm">
                                {{ formatDate(tryout.registration_start_at) }} <span class="text-slate-400 mx-2 font-normal">s/d</span> {{ formatDate(tryout.registration_end_at) }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold mb-1">Mulai Ujian</p>
                            <p class="font-medium text-blue-600 text-sm">
                                {{ formatDate(tryout.started_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Panel Aksi (Harga & Tombol) -->
                <div class="w-full md:w-72 shrink-0 flex flex-col justify-center">
                    <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-100 text-center flex flex-col h-full justify-center">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-widest mb-2">Investasi</p>
                        <p class="text-3xl font-extrabold text-slate-900 tracking-tight mb-8">
                            {{ formatCurrency(tryout.price) }}
                        </p>

                        <div v-if="is_registration_closed" class="bg-rose-50 text-rose-600 text-sm font-bold py-3.5 rounded-xl border border-rose-100">
                            Pendaftaran Ditutup
                        </div>
                        <Link v-else :href="route('tryout.register', tryout.id)" class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-4 rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 block w-full">
                            Daftar Sekarang
                        </Link>
                        
                        <p v-if="!is_registration_closed" class="text-[10px] text-slate-400 mt-4 font-medium">
                            Akses ujian akan otomatis terbuka pada jadwal yang ditentukan.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>