<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    tryout: {
        type: Object,
        required: true
    },
    alreadyRegistered: {
        type: Boolean,
        default: false
    },
    isPremiumMember: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <Head :title="`${props.tryout.title} - CPNS Nusantara`" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-12">
            
            <div class="flex items-center">
                <Link 
                    :href="route('tryout.index')" 
                    class="inline-flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-slate-500 hover:text-blue-600 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Katalog
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <div class="lg:col-span-2 space-y-5">
                    
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                        <div class="relative h-44 md:h-60 bg-slate-50 border-b border-slate-100 flex items-center justify-center overflow-hidden">
                            <img 
                                :src="props.tryout.image_url || '/images/logo.png'" 
                                class="w-full h-full transition-transform duration-700 hover:scale-101"
                                :class="props.tryout.image_url ? 'object-cover' : 'object-contain p-6 object-center max-h-40 md:max-h-48'"
                                @error="(e) => { e.target.src = '/images/logo.png'; e.target.className = 'w-full h-full object-contain p-6 object-center max-h-40 md:max-h-48'; }"
                            >
                            <div class="absolute top-4 left-4">
                                <span :class="props.tryout.price > 0 ? 'bg-amber-500' : 'bg-emerald-500'" class="px-3 py-1 rounded-lg text-[10px] font-medium text-white uppercase tracking-widest shadow-sm">
                                    {{ props.tryout.price > 0 ? 'PREMIUM ACCESS' : 'FREE ACCESS' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-5 md:p-6 space-y-3">
                            <h1 class="text-xl md:text-2xl text-slate-900 uppercase tracking-tight leading-snug font-medium">
                                {{ props.tryout.title }}
                            </h1>
                            <div class="h-px bg-slate-100 w-full"></div>
                            <p class="text-xs md:text-sm text-slate-600 font-normal leading-relaxed italic pt-1">
                                {{ props.tryout.description || 'Wujudkan impianmu menjadi Abdi Negara! Terus berlatih, pantang menyerah, dan raih NIP tahun ini bersama simulasi CAT modern kami.' }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm space-y-4">
                        <h3 class="text-xs font-normal text-slate-400 uppercase tracking-widest">Komponen Kisi-Kisi Ujian</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                                <span class="text-[11px] font-medium text-blue-600 uppercase tracking-wider">TWK</span>
                                <span class="text-xs text-slate-500 mt-1 leading-normal">Tes Wawasan Kebangsaan untuk menguji nasionalisme dan integritas.</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                                <span class="text-[11px] font-medium text-orange-600 uppercase tracking-wider">TIU</span>
                                <span class="text-xs text-slate-500 mt-1 leading-normal">Tes Inteligensia Umum untuk menguji kemampuan logis, numerik, dan verbal.</span>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between">
                                <span class="text-[11px] font-medium text-purple-600 uppercase tracking-wider">TKP</span>
                                <span class="text-xs text-slate-500 mt-1 leading-normal">Tes Karakteristik Pribadi untuk menilai kompetensi perilaku & pelayanan publik.</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm space-y-4">
                        <h3 class="text-xs font-normal text-slate-400 uppercase tracking-widest">Aturan & Mekanisme CAT</h3>
                        <ul class="space-y-2.5 text-xs md:text-sm text-slate-600 font-normal">
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span>
                                <span>Simulasi berjalan secara realtime menggunakan pembatasan waktu otomatis sesuai parameter paket.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span>
                                <span>Jawaban tersimpan otomatis secara berkala pada cloud system setiap kali Anda menekan tombol navigasi soal.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span>
                                <span>Ujian akan berakhir otomatis jika waktu habis meskipun Anda belum menekan tombol selesai secara manual.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span>
                                <span>Gunakan koneksi internet yang stabil selama ujian berlangsung untuk kelancaran sinkronisasi data skor.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:sticky lg:top-6 space-y-4">
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-5">
                        <h3 class="text-xs font-normal text-slate-400 uppercase tracking-widest">Ringkasan Simulasi</h3>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2.5">
                            <div class="border border-slate-100 bg-slate-50/50 rounded-xl p-2.5 flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-normal leading-tight">Jumlah</span>
                                    <span class="text-xs md:text-sm text-slate-800 mt-0.5 leading-tight break-words">{{ props.tryout.questions_count || 110 }} Soal</span>
                                </div>
                            </div>

                            <div class="border border-slate-100 bg-slate-50/50 rounded-xl p-2.5 flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-600 shrink-0">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-normal leading-tight">Waktu</span>
                                    <span class="text-xs md:text-sm text-slate-800 mt-0.5 leading-tight break-words">{{ props.tryout.duration || 100 }} Menit</span>
                                </div>
                            </div>

                            <div class="border border-slate-100 bg-slate-50/50 rounded-xl p-2.5 flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-normal leading-tight">Metode</span>
                                    <span class="text-xs md:text-sm text-slate-800 mt-0.5 leading-tight break-words">Sistem CAT</span>
                                </div>
                            </div>

                            <div class="border border-slate-100 bg-slate-50/50 rounded-xl p-2.5 flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0V10.5m-2.25 0h13.5m-13.5 0a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H4.5Z" /></svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-[9px] uppercase tracking-wider text-slate-400 font-normal leading-tight">Validasi</span>
                                    <span class="text-xs md:text-sm text-slate-800 mt-0.5 leading-tight break-words">Kunci & Pembahasan</span>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-slate-100 w-full"></div>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-normal uppercase tracking-wider">Harga Akses</span>
                            <span class="text-base md:text-lg text-slate-900 tracking-tight font-medium">
                                <template v-if="props.alreadyRegistered || props.isPremiumMember">
                                    <span class="text-emerald-600 uppercase text-xs md:text-sm font-medium">Siap Diakses</span>
                                </template>
                                <template v-else>
                                    {{ props.tryout.price > 0 ? `Rp ${Number(props.tryout.price).toLocaleString('id-ID')}` : 'GRATIS' }}
                                </template>
                            </span>
                        </div>

                        <div>
                            <template v-if="props.alreadyRegistered || props.isPremiumMember">
                                <Link 
                                    :href="route('tryout.wait', props.tryout.id)"
                                    class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs uppercase tracking-widest py-3 rounded-xl transition-all shadow-sm hover:shadow-emerald-100 active:scale-98"
                                >
                                    Mulai Simulasi Sekarang
                                </Link>
                            </template>

                            <template v-else>
                                <Link 
                                    :href="route('tryout.register', props.tryout.id)"
                                    class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs uppercase tracking-widest py-3 rounded-xl transition-all shadow-sm hover:shadow-blue-100 active:scale-98"
                                >
                                    {{ props.tryout.price > 0 ? 'Beli & Daftar Paket' : 'Daftar Paket Gratis' }}
                                </Link>
                            </template>
                        </div>
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