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
        <div class="max-w-7xl mx-auto px-3 sm:px-4 py-4 md:py-8 space-y-6 animate-in fade-in duration-700">
            
            <div class="relative overflow-hidden rounded-[1.5rem] bg-slate-900 text-white p-5 sm:p-8 md:p-10 shadow-lg shadow-slate-900/5">
                <div class="absolute -right-20 -top-20 w-48 sm:w-72 h-48 sm:h-72 bg-purple-600/15 rounded-full blur-[60px] pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-48 sm:w-72 h-48 sm:h-72 bg-blue-600/15 rounded-full blur-[60px] pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-500/10 border border-purple-500/20 rounded-lg text-[9px] sm:text-[10px] font-medium uppercase tracking-[0.15em] text-purple-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                        Ruang Belajar Eksklusif
                    </span>
                    
                    <h1 class="text-xl sm:text-2xl md:text-3xl font-medium tracking-tight text-white leading-tight">
                        Katalog Nusantara Adidaya
                    </h1>
                    
                    <p class="text-xs sm:text-sm text-slate-400 font-normal leading-relaxed">
                        Akses seluruh materi simulasi, bank soal tingkat tinggi (HOTS), serta perangkingan waktu nyata nasional yang dirancang khusus untuk mempercepat kelulusan Anda.
                    </p>

                    <div class="pt-1 flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full">
                        <div v-if="isUserMember" class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-lg w-full sm:w-auto justify-center sm:justify-start">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shrink-0"></span>
                            <span class="text-[9px] sm:text-[10px] font-medium text-emerald-400 uppercase tracking-wider text-center">
                                Akses Premium Aktif (Hingga {{ formatDate(user.membership_expires_at) }})
                            </span>
                        </div>
                        <div v-else class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500/10 border border-amber-500/20 rounded-lg justify-center sm:justify-start">
                                <span class="w-1.5 h-1.5 bg-amber-500 rounded-full shrink-0"></span>
                                <span class="text-[9px] sm:text-[10px] font-medium text-amber-400 uppercase tracking-wider">Akses Terkunci</span>
                            </div>
                            <Link :href="route('membership.index')" class="text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors underline underline-offset-4 text-center py-1">
                                Berlangganan Sekarang &rarr;
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
                    <div>
                        <h2 class="text-[10px] sm:text-xs font-medium text-slate-400 uppercase tracking-[0.2em]">Daftar Paket Premium</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-normal mt-0.5">Pilih simulasi ujian premium Anda hari ini</p>
                    </div>
                    <span class="text-[10px] sm:text-xs font-medium text-slate-500 bg-white border border-slate-200 px-3 py-1 rounded-xl shadow-sm self-start sm:self-auto">
                        Total: {{ tryouts?.length ?? 0 }} Tryout
                    </span>
                </div>

                <div v-if="tryouts && tryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                    <div v-for="tryout in tryouts" :key="tryout.id" 
                         class="bg-white overflow-hidden rounded-[1.5rem] border border-slate-200 shadow-sm hover:shadow-md hover:border-purple-200/80 transition-all duration-300 flex flex-col group relative">
                        
                        <div v-if="!isUserMember" class="absolute inset-0 bg-slate-50/40 backdrop-blur-[0.5px] rounded-[1.5rem] z-10 pointer-events-none"></div>

                        <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between space-y-5 relative z-20">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-2 py-0.5 bg-purple-50 border border-purple-100 text-purple-600 rounded-md text-[9px] sm:text-[10px] font-medium uppercase tracking-wider shrink-0">
                                        Adidaya Class
                                    </span>
                                    <div class="flex items-center gap-1 text-[10px] sm:text-xs font-medium text-slate-400 shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        {{ tryout.duration }} Menit
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <h3 class="text-base font-medium text-slate-800 leading-snug group-hover:text-purple-600 transition-colors">
                                        {{ tryout.title }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-normal浏览 leading-relaxed whitespace-pre-line">
                                        {{ tryout.description || 'Simulasi integrasi sistem CAT nasional dengan passing grade terbaru.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex flex-row flex-wrap gap-2 w-full">
                                <div class="flex-1 min-w-[100px] bg-slate-50 border border-slate-100 p-2 rounded-xl flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[8px] sm:text-[9px] text-slate-400 font-medium uppercase tracking-wider leading-none mb-0.5 truncate">Butir Soal</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-slate-600 leading-none truncate">{{ tryout.questions_count ?? 110 }} Soal</p>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-[100px] bg-slate-50 border border-slate-100 p-2 rounded-xl flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0a5.995 5.995 0 0 0-4.058-3.036m0 0a5.995 5.995 0 0 0-4.058 3.036m0 0a5.97 5.97 0 0 0-.941 3.197m9.411-3.197a5.971 5.971 0 0 0-.941-3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[8px] sm:text-[9px] text-slate-400 font-medium uppercase tracking-wider leading-none mb-0.5 truncate">Peserta</p>
                                        <p class="text-[11px] sm:text-xs font-medium text-slate-600 leading-none truncate">Aktif</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-1">
                                <Link v-if="isUserMember" :href="route('tryout.wait', tryout.id)" 
                                      class="w-full py-2.5 sm:py-3 bg-slate-900 text-white rounded-xl text-[10px] sm:text-xs font-medium uppercase tracking-[0.15em] transition-all duration-300 hover:bg-purple-600 hover:shadow-md hover:shadow-purple-500/10 active:scale-[0.98] flex items-center justify-center gap-1.5">
                                    <span>Mulai Simulasi</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7M21 12H3" /></svg>
                                </Link>
                                <Link v-else :href="route('membership.index')" 
                                      class="w-full py-2.5 sm:py-3 bg-slate-50 text-slate-400 border border-slate-200 rounded-xl text-[10px] sm:text-xs font-medium uppercase tracking-[0.15em] transition-all duration-300 hover:bg-purple-50 hover:text-purple-600 hover:border-purple-200 flex items-center justify-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                    <span>Buka Akses Paket</span>
                                </Link>
                            </div>
                        </div>

                    </div>
                </div>

                <div v-else class="bg-white rounded-[1.5rem] border border-slate-200 p-6 sm:p-10 text-center max-w-xl mx-auto shadow-sm">
                    <div class="w-12 h-12 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-3 border border-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18M2.25 13.5a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 2.25 4.5h19.5A2.25 2.25 0 0 1 24 6.75v4.5a2.25 2.25 0 0 1-2.25 2.25M2.25 13.5v6.75A2.25 2.25 0 0 0 4.5 22.5h15a2.25 2.25 0 0 0 2.25-2.25V13.5" /></svg>
                    </div>
                    <h3 class="text-base font-medium text-slate-800 tracking-tight">Belum Ada Paket Tersedia</h3>
                    <p class="text-xs text-slate-500 font-normal mt-1 max-w-sm mx-auto">
                        Tim admin sedang menyiapkan materi soal HOTS premium terbaru untuk Anda. Silakan periksa kembali beberapa saat lagi.
                    </p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation-name: fadeIn;
}
</style>