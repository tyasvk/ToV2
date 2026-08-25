<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object,
    is_registration_closed: Boolean,
    packages: Array,
    registrationStatus: String, // Status dari TryoutRegistration (pending, approved, rejected, null)
    hasPaid: Boolean            // Apakah user sudah membeli paket Premium
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
        month: 'short', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    }) + ' WIB';
};

const getFeatures = (isPremium) => {
    return [
        { name: 'Akses sistem CAT & Timer', included: true },
        { name: isPremium ? 'Bisa dikerjakan 3 kali' : 'Hanya 1 kali pengerjaan', included: true },
        { name: 'Perangkingan Nasional, Provinsi, Instansi', included: isPremium },
        { name: 'Pembahasan Soal Lengkap', included: isPremium },
        { name: 'Sertifikat Digital', included: isPremium },
        { name: 'Tidak perlu upload persyaratan', included: isPremium },
    ];
};
</script>

<template>
    <Head :title="tryout.title" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent font-sans selection:bg-[#0071e3] selection:text-white animate-in fade-in duration-700 py-5 sm:py-12">
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                
                <!-- Navigasi -->
                <div class="mb-5 sm:mb-6">
                    <Link :href="route('tryout.index')" class="inline-flex items-center gap-1 text-[#0071e3] hover:opacity-80 text-[14px] sm:text-[15px] font-medium transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Katalog
                    </Link>
                </div>

                <!-- Header -->
                <div class="text-center mb-6 sm:mb-10">
                    <h1 class="text-[24px] sm:text-[34px] font-semibold text-[#1d1d1f] tracking-tight mb-2">
                        {{ tryout.title }}
                    </h1>
                    <p class="text-[13px] sm:text-[15px] text-[#86868b] leading-relaxed max-w-lg mx-auto px-2">
                        Pilih paket akses yang sesuai untuk memulai simulasi ujian Anda.
                    </p>
                </div>

                <!-- Info Widget -->
                <div class="mb-6 sm:mb-10">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4">
                        
                        <div class="bg-white rounded-[16px] sm:rounded-[18px] p-3 sm:p-4 flex flex-col items-center justify-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 text-[#0071e3] mb-1.5 sm:mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] sm:text-[11px] text-[#86868b] font-semibold uppercase tracking-wider mb-0.5">Durasi</span>
                            <span class="text-[#1d1d1f] font-semibold text-[13px] sm:text-[15px]">{{ tryout.duration }} Mnt</span>
                        </div>

                        <div class="bg-white rounded-[16px] sm:rounded-[18px] p-3 sm:p-4 flex flex-col items-center justify-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 text-[#0071e3] mb-1.5 sm:mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-[10px] sm:text-[11px] text-[#86868b] font-semibold uppercase tracking-wider mb-0.5">Total Soal</span>
                            <span class="text-[#1d1d1f] font-semibold text-[13px] sm:text-[15px]">110 Butir</span>
                        </div>

                        <div class="bg-white rounded-[16px] sm:rounded-[18px] p-3 sm:p-4 flex flex-col items-center justify-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 text-[#0071e3] mb-1.5 sm:mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-[10px] sm:text-[11px] text-[#86868b] font-semibold uppercase tracking-wider mb-0.5">Daftar</span>
                            <span v-if="tryout.registration_start_at" class="text-[#1d1d1f] font-semibold text-[11px] sm:text-[13px] leading-tight mt-0.5">
                                {{ formatDate(tryout.registration_start_at).split(' ')[0] }}<br>s/d<br>{{ formatDate(tryout.registration_end_at).split(' ')[0] }}
                            </span>
                            <span v-else class="text-[#1d1d1f] font-semibold text-[13px] sm:text-[14px]">Terbuka</span>
                        </div>

                        <div class="bg-white rounded-[16px] sm:rounded-[18px] p-3 sm:p-4 flex flex-col items-center justify-center text-center shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-7 sm:h-7 text-[#0071e3] mb-1.5 sm:mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-[10px] sm:text-[11px] text-[#86868b] font-semibold uppercase tracking-wider mb-0.5">Mulai Ujian</span>
                            <span v-if="tryout.started_at" class="text-[#1d1d1f] font-semibold text-[11px] sm:text-[13px] leading-tight mt-0.5">
                                {{ formatDate(tryout.started_at).split(' ')[0] }}<br>{{ formatDate(tryout.started_at).split(' ')[1] }}
                            </span>
                            <span v-else class="text-[#1d1d1f] font-semibold text-[13px] sm:text-[14px]">Kapan Saja</span>
                        </div>

                    </div>
                </div>

                <!-- Bagian Opsi Akses -->
                <div class="pb-12">
                    <h2 class="text-[17px] sm:text-[19px] font-semibold text-center text-[#1d1d1f] mb-4 sm:mb-5 tracking-tight">Opsi Akses</h2>
                    
                    <div v-if="is_registration_closed && tryout.registration_start_at && tryout.registration_end_at" 
                         class="max-w-sm mx-auto bg-white rounded-[20px] p-6 text-center shadow-[0_2px_8px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                        <div class="w-10 h-10 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <h3 class="text-[17px] font-semibold text-[#1d1d1f] mb-1">Pendaftaran Ditutup</h3>
                        <p class="text-[#86868b] text-[13px]">Sesi untuk tryout ini sudah tidak menerima pendaftaran baru.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-5 max-w-3xl mx-auto">
                        
                        <!-- Card Paket -->
                        <div v-for="pkg in packages" :key="pkg.id"
                             class="bg-white rounded-[20px] sm:rounded-[22px] p-5 sm:p-7 flex flex-col justify-between transition-all duration-300 border shadow-[0_2px_12px_rgba(0,0,0,0.03)]"
                             :class="pkg.is_premium ? 'border-[#0071e3]' : 'border-[#d2d2d7]/50 hover:border-[#d2d2d7]'">
                            
                            <!-- Header & Harga -->
                            <div class="text-center relative">
                                <!-- Badge Rekomendasi khusus Premium -->
                                <div v-if="pkg.is_premium" class="mb-1.5">
                                    <span class="inline-block bg-[#0071e3]/10 text-[#0071e3] text-[10px] sm:text-[11px] font-bold px-2.5 py-0.5 rounded-full tracking-wide">
                                        REKOMENDASI
                                    </span>
                                </div>
                                <h3 class="text-[17px] sm:text-[19px] font-semibold text-[#1d1d1f] mb-0.5">{{ pkg.name }}</h3>
                                <p class="text-[28px] sm:text-[36px] font-bold text-[#1d1d1f] tracking-tight my-1 sm:my-2">
                                    {{ formatCurrency(pkg.price).replace(',00', '') }}
                                </p>
                            </div>

                            <!-- Daftar Fitur -->
                            <div class="mt-4 sm:mt-5 mb-5 sm:mb-8 space-y-2.5 sm:space-y-3.5 border-t border-[#d2d2d7]/40 pt-4 sm:pt-6">
                                <div v-for="(feature, index) in getFeatures(pkg.is_premium)" :key="index" class="flex items-start gap-2.5 sm:gap-3">
                                    
                                    <svg v-if="feature.included" xmlns="http://www.w3.org/2000/svg" class="w-[16px] h-[16px] sm:w-[18px] sm:h-[18px] text-[#0071e3] shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-[16px] h-[16px] sm:w-[18px] sm:h-[18px] text-[#d2d2d7] shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    
                                    <span class="text-[12px] sm:text-[13px] leading-snug" :class="feature.included ? 'text-[#1d1d1f]' : 'text-[#86868b] line-through decoration-[#d2d2d7]'">
                                        {{ feature.name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Tombol Aksi Dinamis -->
                            <div class="mt-auto">
                                
                                <!-- JIKA PAKET PREMIUM -->
                                <template v-if="pkg.is_premium">
                                    <Link v-if="hasPaid" :href="route('tryout.history.detail', tryout.id)" class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium transition-colors duration-200 bg-[#0071e3] text-white hover:bg-[#005bb5]">
                                        Mulai Ujian Premium
                                    </Link>
                                    <Link v-else :href="route('tryout.register', tryout.id)" class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium transition-colors duration-200 bg-[#0071e3] text-white hover:bg-[#005bb5]">
                                        Beli Premium
                                    </Link>
                                </template>

                                <!-- JIKA PAKET GRATIS -->
                                <template v-else>
                                    <button v-if="hasPaid" disabled class="w-full py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium bg-[#e8e8ed]/60 text-slate-400 cursor-not-allowed">
                                        Telah Memiliki Premium
                                    </button>
                                    <button v-else-if="registrationStatus === 'pending'" disabled class="w-full py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium bg-[#e8e8ed] text-slate-500 cursor-not-allowed flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Sedang Diverifikasi
                                    </button>
                                    <Link v-else-if="registrationStatus === 'approved'" :href="route('tryout.history.detail', tryout.id)" class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium transition-colors duration-200 bg-emerald-600 text-white hover:bg-emerald-700">
                                        Mulai Ujian Gratis
                                    </Link>
                                    <Link v-else :href="route('tryout.upload-syarat', tryout.id)" class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-full text-[13px] sm:text-[14px] font-medium transition-colors duration-200 bg-[#e8e8ed]/60 text-[#1d1d1f] hover:bg-[#d2d2d7]/50">
                                        {{ registrationStatus === 'rejected' ? 'Upload Ulang Syarat' : 'Lanjutkan Gratis' }}
                                    </Link>
                                </template>

                            </div>
                        </div>

                    </div>
                    
                    <p class="text-center text-[#86868b] text-[11px] sm:text-[12px] mt-6 sm:mt-8 px-4">
                        Akses ujian dan fitur lainnya mengikuti syarat dan ketentuan yang berlaku.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>