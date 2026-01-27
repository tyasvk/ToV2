<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    attempt: Object
});

// Ambang Batas SKD CPNS Resmi
const PASSING_GRADE = { 
    TWK: 65, 
    TIU: 80, 
    TKP: 166 
};

// Logika Pengecekan Kelulusan
const isPassed = (type, score) => score >= PASSING_GRADE[type];
const isTotalPassed = isPassed('TWK', props.attempt.twk_score) && 
                      isPassed('TIU', props.attempt.tiu_score) && 
                      isPassed('TKP', props.attempt.tkp_score);

// Format Tanggal: "27 Januari 2026 pukul 21.40"
const formatDate = (dateString) => {
    const d = new Date(dateString);
    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const date = d.getDate();
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${date} ${month} ${year} pukul ${hours}.${minutes}`;
};
</script>

<template>
    <Head title="Hasil Simulasi Ujian" />

    <AuthenticatedLayout>
        <template #header>
            <div class="pb-14 pt-0 flex justify-between items-start">
                <div class="space-y-0.5">
                    <h2 class="font-black text-3xl text-slate-800 tracking-tighter uppercase leading-none">
                        Hasil Simulasi
                    </h2>
                    <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] max-w-md">
                        Laporan perolehan skor SKD Computer Assisted Test
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Selesai Pada</p>
                    <p class="text-[10px] font-black text-slate-700 uppercase leading-none mt-1.5">
                        {{ formatDate(attempt.created_at) }}
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 -mt-16 pb-12">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <div class="lg:col-span-5">
                    <div class="h-full bg-[#334155] rounded-[2.5rem] p-10 text-center shadow-2xl relative overflow-hidden flex flex-col justify-center items-center border-b-8 border-[#d4ae28]">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                        
                        <div class="relative z-10 space-y-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Total Skor SKD</p>
                            <h3 class="text-8xl font-black text-white tracking-tighter leading-none">
                                {{ attempt.total_score }}
                            </h3>
                            <div :class="isTotalPassed ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border-rose-500/30'"
                                 class="px-5 py-2 rounded-xl border backdrop-blur-sm inline-block mt-4">
                                <span class="text-[9px] font-black uppercase tracking-[0.2em]">
                                    {{ isTotalPassed ? 'Lulus Passing Grade' : 'Tidak Lulus Passing Grade' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 grid grid-cols-2 gap-4">
                    <Link :href="route('tryout.review', attempt.id)" 
                        class="bg-white p-7 rounded-[2rem] flex flex-col items-center justify-center gap-3 shadow-xl shadow-slate-200/50 border-2 border-transparent hover:border-[#334155] transition-all group active:scale-95">
                        <span class="text-3xl group-hover:scale-110 transition-transform">📖</span>
                        <div class="text-center">
                            <p class="font-black text-slate-800 text-[10px] uppercase tracking-widest">Pembahasan</p>
                            <p class="text-[7px] font-bold text-slate-400 uppercase mt-1">Evaluasi Jawaban</p>
                        </div>
                    </Link>

                    <Link :href="route('tryout.leaderboard', attempt.tryout_id)" 
                        class="bg-white p-7 rounded-[2rem] flex flex-col items-center justify-center gap-3 shadow-xl shadow-slate-200/50 border-2 border-transparent hover:border-[#334155] transition-all group active:scale-95">
                        <span class="text-3xl group-hover:scale-110 transition-transform">📊</span>
                        <div class="text-center">
                            <p class="font-black text-slate-800 text-[10px] uppercase tracking-widest">Perangkingan</p>
                            <p class="text-[7px] font-bold text-slate-400 uppercase mt-1">Cek Posisi Anda</p>
                        </div>
                    </Link>

                    <a :href="route('tryout.certificate', attempt.id)" target="_blank"
                        class="bg-white p-7 rounded-[2rem] flex flex-col items-center justify-center gap-3 shadow-xl shadow-slate-200/50 border-2 border-transparent hover:border-[#334155] transition-all group active:scale-95">
                        <span class="text-3xl group-hover:scale-110 transition-transform">📜</span>
                        <div class="text-center">
                            <p class="font-black text-slate-800 text-[10px] uppercase tracking-widest">Sertifikat</p>
                            <p class="text-[7px] font-bold text-slate-400 uppercase mt-1">Download Hasil</p>
                        </div>
                    </a>

                    <Link :href="route('tryout.index')" 
                        class="bg-slate-100 p-7 rounded-[2rem] flex flex-col items-center justify-center gap-3 shadow-inner border-2 border-transparent hover:bg-slate-200 transition-all active:scale-95">
                        <span class="text-3xl hover:scale-110 transition-transform">🏠</span>
                        <div class="text-center">
                            <p class="font-black text-slate-500 text-[10px] uppercase tracking-widest">Dashboard</p>
                            <p class="text-[7px] font-bold text-slate-400 uppercase mt-1">Kembali Ke List</p>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-6">
                <div v-for="type in ['TWK', 'TIU', 'TKP']" :key="type"
                    class="bg-white rounded-3xl border border-slate-100 p-5 shadow-sm flex flex-col relative overflow-hidden group hover:bg-slate-50/50 transition-colors">
                    
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Subtes {{ type }}</span>
                        <div :class="isPassed(type, attempt[type.toLowerCase() + '_score']) ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-rose-500 bg-rose-50 border-rose-100'"
                             class="px-3 h-6 rounded-full flex items-center justify-center text-[7.5px] font-black border uppercase tracking-widest leading-none">
                            {{ isPassed(type, attempt[type.toLowerCase() + '_score']) ? 'Lulus' : 'Gagal' }}
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2 mb-4">
                        <div class="text-5xl font-black text-slate-800 tracking-tighter leading-none group-hover:text-[#334155] transition-colors">
                            {{ attempt[type.toLowerCase() + '_score'] }}
                        </div>
                        <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">Poin</span>
                    </div>

                    <div class="mt-auto pt-3 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[6px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Ambang Batas</span>
                            <span class="text-[10px] font-black text-slate-700 leading-none uppercase">{{ type }}</span>
                        </div>
                        <div class="text-lg font-black text-slate-400 tracking-tighter">
                            {{ PASSING_GRADE[type] }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* PAKSA TEGAK (NO ITALIC) */
* { font-style: normal !important; }

/* Custom Scrollbar for better UI */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>