<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object
});

// Utility untuk format angka
const formatNum = (num) => new Intl.NumberFormat('id-ID').format(num || 0);
</script>

<template>
    <Head :title="'Persiapan: ' + tryout.title" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50/50 pb-20">
            
            <div class="bg-slate-900 pt-12 pb-24 md:pt-20 md:pb-40 px-6 rounded-b-[3.5rem] md:rounded-b-[5rem] -mt-2 relative overflow-hidden text-center text-white shadow-2xl">
                <div class="absolute top-0 left-0 w-96 h-96 bg-indigo-600 rounded-full blur-[120px] opacity-20 -ml-20 -mt-20"></div>
                <div class="absolute bottom-0 right-0 w-64 h-64 bg-indigo-500 rounded-full blur-[100px] opacity-10 -mr-10 -mb-10"></div>
                
                <div class="max-w-7xl mx-auto relative z-10">
                    <Link :href="route('tryout.index')" class="inline-flex items-center gap-2 text-indigo-400 text-[10px] font-black uppercase tracking-[0.3em] mb-6 hover:text-white transition-colors">
                        <span class="text-lg">←</span> Kembali ke Katalog
                    </Link>
                    <h2 class="text-3xl md:text-5xl font-black uppercase italic tracking-tighter leading-none">{{ tryout.title }}</h2>
                    <p class="text-[10px] md:text-xs text-slate-400 font-bold uppercase tracking-[0.4em] mt-3">Konfirmasi Keikutsertaan Ujian</p>
                </div>
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 -mt-16 md:-mt-24 relative z-20">
                <div class="bg-white rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl shadow-slate-200/60 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-500">
                    
                    <div class="flex flex-col md:flex-row">
                        
                        <div class="flex-1 p-8 md:p-12 bg-slate-50/50 border-b md:border-b-0 md:border-r border-slate-100">
                            <h3 class="font-black text-slate-900 uppercase tracking-tight text-lg mb-8 flex items-center gap-3">
                                <span class="w-8 h-8 bg-indigo-600 text-white rounded-xl flex items-center justify-center text-sm shadow-lg shadow-indigo-100">i</span>
                                Ringkasan Ujian
                            </h3>

                            <div class="grid grid-cols-1 gap-4">
                                <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center gap-5 group hover:border-indigo-500 transition-all">
                                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl">⏱️</div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Durasi Pengerjaan</p>
                                        <p class="text-xl font-black text-slate-900 leading-none">{{ tryout.duration_minutes }} <span class="text-xs font-bold text-slate-400">Menit</span></p>
                                    </div>
                                </div>

                                <div class="p-6 bg-white rounded-3xl border border-slate-100 shadow-sm flex items-center gap-5 group hover:border-indigo-500 transition-all">
                                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl">📚</div>
                                    <div>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Soal</p>
                                        <p class="text-xl font-black text-slate-900 leading-none">{{ formatNum(tryout.questions_count) }} <span class="text-xs font-bold text-slate-400">Butir</span></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 p-6 bg-amber-50 rounded-3xl border border-amber-100 flex gap-4">
                                <span class="text-xl">⚠️</span>
                                <p class="text-[10px] text-amber-800 font-bold leading-relaxed uppercase tracking-wide">
                                    Pilih mode pengerjaan yang sesuai dengan kenyamanan Anda sebelum memulai.
                                </p>
                            </div>
                        </div>

                        <div class="flex-1 p-8 md:p-12 flex flex-col justify-between">
                            <div>
                                <h3 class="font-black text-slate-900 uppercase tracking-tight text-lg mb-8">Pilih Mode Mulai</h3>
                                
                                <ul class="space-y-6 mb-10">
                                    <li class="flex gap-4 items-start">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex-shrink-0 flex items-center justify-center text-[10px] font-black shadow-lg">1</div>
                                        <p class="text-[11px] md:text-xs text-slate-600 leading-relaxed font-bold uppercase tracking-wide">
                                            **Mode CAT BKN**: Tampilan simulasi resmi yang mirip dengan ujian aslinya.
                                        </p>
                                    </li>
                                    <li class="flex gap-4 items-start">
                                        <div class="w-6 h-6 rounded-lg bg-slate-400 text-white flex-shrink-0 flex items-center justify-center text-[10px] font-black shadow-lg">2</div>
                                        <p class="text-[11px] md:text-xs text-slate-600 leading-relaxed font-bold uppercase tracking-wide">
                                            **Mode Standar**: Tampilan modern yang lebih ringan dan fokus pada teks soal.
                                        </p>
                                    </li>
                                </ul>
                            </div>

                            <div class="space-y-3">
                                <Link 
                                    :href="route('tryout.exam.bkn', tryout.id)"
                                    class="flex items-center justify-center w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] shadow-xl hover:bg-indigo-700 transition-all active:scale-95 gap-3"
                                >
                                    <span>🖥️</span> Mulai Mode CAT BKN
                                </Link>

                                <Link 
                                    :href="route('tryout.exam', tryout.id)"
                                    class="flex items-center justify-center w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] shadow-xl hover:bg-slate-800 transition-all active:scale-95 gap-3"
                                >
                                    <span>📱</span> Mulai Mode Standar
                                </Link>

                                <Link 
                                    :href="route('tryout.index')"
                                    class="flex items-center justify-center w-full py-4 text-slate-400 font-black text-[10px] uppercase tracking-widest"
                                >
                                    Batal
                                </Link>
                                
                                <p class="text-[8px] text-center text-slate-400 font-black uppercase tracking-[0.2em]">
                                    Waktu akan otomatis berjalan setelah tombol ditekan.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-all { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
</style>