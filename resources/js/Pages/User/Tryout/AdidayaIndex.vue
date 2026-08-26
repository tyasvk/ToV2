<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const props = defineProps({
    tryouts: Array,
});

// Cek status keanggotaan premium user secara reaktif
const user = computed(() => page.props.auth?.user ?? null);
const isUserMember = computed(() => {
    if (!user.value || !user.value.membership_expires_at) return false;
    return new Date(user.value.membership_expires_at) > new Date();
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Nusantara Adidaya Premium" />

    <AuthenticatedLayout>
        <!-- Background transparan agar menyatu dengan base layout, khas Apple -->
        <div class="w-full bg-transparent pb-16 md:pb-24 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-4 md:pt-6 space-y-5 md:space-y-6">
                
                <!-- HERO BANNER (Kompak & Padat) -->
                <div class="relative overflow-hidden rounded-[24px] sm:rounded-[32px] bg-[#1D1D1F] p-6 sm:p-8 md:p-10 shadow-[0_12px_30px_rgba(0,0,0,0.12)]">
                    
                    <!-- Apple Intelligence / Siri Glow Effect -->
                    <div class="absolute -right-10 -top-10 w-48 sm:w-72 h-48 sm:h-72 bg-gradient-to-br from-[#AF52DE] to-[#5E5CE6] rounded-full blur-[60px] opacity-30 pointer-events-none"></div>
                    <div class="absolute -left-10 -bottom-10 w-48 sm:w-72 h-48 sm:h-72 bg-gradient-to-tr from-[#007AFF] to-[#33C1FF] rounded-full blur-[60px] opacity-20 pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl space-y-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 backdrop-blur-md border border-white/10 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-[#E5E5EA]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                            Ruang Belajar Eksklusif
                        </span>
                        
                        <h1 class="text-[26px] sm:text-[34px] md:text-[40px] font-bold tracking-tight text-white leading-tight">
                            Katalog Nusantara Adidaya
                        </h1>
                        
                        <p class="text-[13px] sm:text-[15px] text-[#AEAEB2] font-medium leading-relaxed max-w-2xl m-0">
                            Akses seluruh simulasi & bank soal tingkat tinggi (HOTS) yang dirancang khusus untuk mempercepat kelulusan Anda.
                        </p>

                        <div class="pt-2 flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                            <!-- Status Member Aktif -->
                            <div v-if="isUserMember" class="inline-flex items-center gap-2.5 px-4 py-2.5 bg-[#E5F5EA]/10 border border-[#34C759]/30 rounded-full w-full sm:w-auto justify-center sm:justify-start backdrop-blur-sm">
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#34C759] opacity-60"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#34C759]"></span>
                                </span>
                                <span class="text-[11px] sm:text-[12px] font-bold text-[#34C759] uppercase tracking-wider text-center">
                                    Akses Premium Aktif hingga {{ formatDate(user.membership_expires_at) }}
                                </span>
                            </div>
                            
                            <!-- Status Member Terkunci -->
                            <div v-else class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                                <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#FFF9E6]/10 border border-[#FF9500]/30 rounded-full w-full sm:w-auto justify-center backdrop-blur-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#FF9500]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                    <span class="text-[11px] sm:text-[12px] font-bold text-[#FF9500] uppercase tracking-wider">Akses Terkunci</span>
                                </div>
                                <Link :href="route('membership.index')" class="text-[13px] font-semibold text-[#007AFF] hover:text-[#33C1FF] transition-colors text-center w-full sm:w-auto group flex items-center justify-center gap-1.5">
                                    Berlangganan Sekarang 
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION DAFTAR PAKET -->
                <div class="space-y-4">
                    <div class="flex flex-row items-end justify-between gap-4 px-1">
                        <div>
                            <h2 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] tracking-tight">Daftar Paket Premium</h2>
                        </div>
                        <span class="text-[12px] font-bold text-[#86868B] bg-white border border-black/5 px-3 py-1.5 rounded-full shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                            {{ tryouts?.length ?? 0 }} Tersedia
                        </span>
                    </div>

                    <div v-if="tryouts && tryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                        <div v-for="tryout in tryouts" :key="tryout.id" 
                             class="bg-white rounded-[24px] border border-black/5 shadow-[0_4px_16px_rgba(0,0,0,0.03)] hover:shadow-[0_12px_24px_rgba(0,0,0,0.06)] transition-all duration-300 flex flex-col group relative overflow-hidden transform hover:-translate-y-1">
                            
                            <!-- Glassmorphism Blur jika belum berlangganan (Terkunci) -->
                            <div v-if="!isUserMember" class="absolute inset-0 bg-white/40 backdrop-blur-[2px] z-10 pointer-events-none transition-all duration-300"></div>

                            <div class="p-5 flex-1 flex flex-col justify-between space-y-4 relative z-20">
                                
                                <div class="space-y-3">
                                    <!-- Badge Kategori & Waktu -->
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2.5 py-1 bg-[#F5F5F7] text-[#1D1D1F] rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-widest shrink-0">
                                            Adidaya Class
                                        </span>
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-[#86868B] shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            {{ tryout.duration }} Menit
                                        </div>
                                    </div>

                                    <!-- Judul Saja (Keterangan Dihapus agar bersih) -->
                                    <h3 class="text-[16px] sm:text-[17px] font-bold text-[#1D1D1F] leading-snug group-hover:text-[#007AFF] transition-colors break-words">
                                        {{ tryout.title }}
                                    </h3>
                                </div>

                                <!-- Meta Info Box (Kompak & Padat) -->
                                <div class="bg-[#F5F5F7] rounded-[16px] p-3 flex items-center justify-around gap-3 w-full border border-black/5 mt-auto">
                                    <div class="flex flex-col items-center justify-center w-full">
                                        <div class="w-7 h-7 rounded-full bg-white text-[#007AFF] flex items-center justify-center mb-1 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        </div>
                                        <p class="text-[9px] text-[#86868B] font-bold uppercase tracking-wider mb-0.5">Butir Soal</p>
                                        <p class="text-[12px] font-bold text-[#1D1D1F]">{{ tryout.questions_count ?? 110 }} Soal</p>
                                    </div>
                                    
                                    <div class="w-px h-8 bg-black/5"></div> <!-- Divider -->

                                    <div class="flex flex-col items-center justify-center w-full">
                                        <div class="w-7 h-7 rounded-full bg-white text-[#34C759] flex items-center justify-center mb-1 shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0a5.995 5.995 0 0 0-4.058-3.036m0 0a5.995 5.995 0 0 0-4.058 3.036m0 0a5.97 5.97 0 0 0-.941 3.197m9.411-3.197a5.971 5.971 0 0 0-.941-3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                        </div>
                                        <p class="text-[9px] text-[#86868B] font-bold uppercase tracking-wider mb-0.5">Status</p>
                                        <p class="text-[12px] font-bold text-[#1D1D1F]">Aktif</p>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-1">
                                    <Link v-if="isUserMember" :href="route('tryout.wait', tryout.id)" 
                                          class="w-full py-3 bg-[#007AFF] text-white rounded-full text-[13px] font-semibold transition-all duration-300 hover:bg-[#0062CC] shadow-[0_4px_10px_rgba(0,122,255,0.3)] active:scale-[0.98] flex items-center justify-center gap-2">
                                        <span>Mulai Simulasi</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7M21 12H3" /></svg>
                                    </Link>
                                    <Link v-else :href="route('membership.index')" 
                                          class="w-full py-3 bg-[#F5F5F7] text-[#86868B] border border-black/5 rounded-full text-[13px] font-semibold transition-all duration-300 hover:bg-[#E3E3E8] hover:text-[#1D1D1F] flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                        <span>Buka Akses Paket</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EMPTY STATE -->
                    <div v-else class="bg-white rounded-[24px] border border-black/5 p-10 sm:p-16 text-center max-w-2xl mx-auto shadow-[0_4px_20px_rgba(0,0,0,0.03)] mt-4">
                        <div class="w-16 h-16 bg-[#F5F5F7] text-[#86868B] rounded-full flex items-center justify-center mx-auto mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18M2.25 13.5a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 2.25 4.5h19.5A2.25 2.25 0 0 1 24 6.75v4.5a2.25 2.25 0 0 1-2.25 2.25M2.25 13.5v6.75A2.25 2.25 0 0 0 4.5 22.5h15a2.25 2.25 0 0 0 2.25-2.25V13.5" /></svg>
                        </div>
                        <h3 class="text-[18px] sm:text-[20px] font-bold text-[#1D1D1F] tracking-tight mb-2">Belum Ada Paket Tersedia</h3>
                        <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium leading-relaxed max-w-sm mx-auto">
                            Tim kurikulum kami sedang menyiapkan materi soal HOTS premium terbaru untuk Anda.
                        </p>
                    </div>
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