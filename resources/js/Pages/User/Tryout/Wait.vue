<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue'; // Tambahkan import computed

const props = defineProps({
    tryout: Object
});

// Logic untuk menentukan rute kembali berdasarkan tipe tryout
const backUrl = computed(() => {
    if (props.tryout.type === 'adidaya') {
        return route('tryout.adidaya');
    } else if (props.tryout.type === 'akbar') {
        return route('tryout-akbar.index');
    }
    return route('tryout.index'); // Default untuk tryout umum
});

// Utility untuk format angka
const formatNum = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <Head :title="'Persiapan: ' + tryout.title" />

    <AuthenticatedLayout>
        
        <div class="relative bg-[#0F172A] text-white overflow-hidden py-10 px-4 sm:px-6 lg:px-8 border-b border-gray-800 font-sans">
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-amber-500/5 rounded-full blur-[80px]"></div>
            
            <div class="relative z-10 max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight mb-1 tracking-tight">
                        Persiapan <span class="text-amber-400">Ujian</span>
                    </h1>
                    <p class="text-slate-400 text-xs md:text-sm max-w-lg font-normal">
                        Pastikan koneksi internet stabil sebelum memulai.
                    </p>
                </div>

                <Link :href="backUrl" 
                    class="group flex items-center gap-2 px-5 py-2.5 rounded-lg border border-white/10 bg-white/5 hover:bg-white/10 hover:border-white/20 transition-all text-xs font-bold uppercase tracking-widest text-slate-300 hover:text-white"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali
                </Link>
            </div>
        </div>

        <div class="min-h-screen bg-[#F8F9FA] relative z-20 py-8 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                            <div class="h-0.5 w-full bg-gradient-to-r from-[#0F172A] via-amber-500 to-[#0F172A] absolute top-0 left-0"></div>
                            
                            <h2 class="text-xl font-bold text-[#0F172A] leading-snug mb-2 tracking-tight">{{ tryout.title }}</h2>
                            <p class="text-slate-500 text-xs mb-6">Simulasi Computer Assisted Test (CAT) BKN</p>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-[#F8F9FA] p-4 rounded-xl border border-gray-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Durasi</p>
                                    <p class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ tryout.duration }} Menit
                                    </p>
                                </div>
                                <div class="bg-[#F8F9FA] p-4 rounded-xl border border-gray-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Total Soal</p>
                                    <p class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ formatNum(tryout.questions_count) }} Butir
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50 rounded-xl border border-amber-100 p-6 flex gap-4 items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <h3 class="text-sm font-bold text-amber-800 uppercase tracking-wide mb-1">Perhatian</h3>
                                <p class="text-xs text-amber-700 leading-relaxed font-medium">
                                    Waktu ujian akan otomatis berjalan segera setelah Anda menekan tombol mulai di sebelah kanan. Pastikan Anda sudah siap dan tidak ada gangguan selama pengerjaan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-6">
                        
                        <div class="bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 overflow-hidden">
                            <div class="bg-[#0F172A] p-6 relative overflow-hidden">
                                <div class="absolute right-0 top-0 w-20 h-20 bg-white/5 rounded-bl-full"></div>
                                <div class="relative z-10">
                                    <h2 class="text-sm font-bold text-white uppercase tracking-widest">Pilih Tampilan</h2>
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mt-1 opacity-80">Sesuaikan dengan preferensi Anda</p>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
                                
                                <Link :href="route('tryout.exam.bkn', tryout.id)"
                                    class="group relative w-full overflow-hidden bg-[#0F172A] text-white py-4 rounded-xl text-xs font-bold uppercase tracking-[0.15em] transition-all shadow-lg hover:shadow-xl active:scale-[0.98] flex items-center justify-center gap-3"
                                >
                                    <span class="text-lg">🖥️</span>
                                    <span class="relative z-10">Mode CAT BKN</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-900 to-[#0F172A] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                </Link>

                                <Link :href="route('tryout.exam', tryout.id)"
                                    class="w-full py-4 bg-white text-[#0F172A] border border-gray-200 hover:border-[#0F172A] rounded-xl text-xs font-bold uppercase tracking-[0.15em] transition-all flex items-center justify-center gap-3 hover:bg-gray-50"
                                >
                                    <span class="text-lg">📱</span>
                                    <span>Mode Modern (Lite)</span>
                                </Link>

                                <div class="pt-4 border-t border-dashed border-gray-200">
                                    <p class="text-[10px] text-center text-slate-400 font-medium">
                                        Mode CAT BKN direkomendasikan untuk pengalaman simulasi yang lebih nyata.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>