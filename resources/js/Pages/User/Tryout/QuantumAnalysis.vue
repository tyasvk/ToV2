<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    result: {
        type: Object,
        default: () => ({
            total_score: 415,
            passing_status: true,
            twk: 110,
            tiu: 145,
            tkp: 160,
            duration_used: '98 Menit',
            rank: 12,
            total_participants: 1540
        })
    }
});

// Logic Radar Chart (SVG Points)
// Asumsi nilai max: TWK (150), TIU (175), TKP (225)
const radarPoints = computed(() => {
    const center = 100; // Pusat koordinat
    const scale = 80;   // Jari-jari maksimal
    
    // Hitung persentase per materi
    const pTWK = props.result.twk / 150;
    const pTIU = props.result.tiu / 175;
    const pTKP = props.result.tkp / 225;

    // Titik sudut (Radar Segitiga untuk 3 Materi)
    const x1 = center + 0;
    const y1 = center - (scale * pTWK); // Atas (TWK)

    const x2 = center + (scale * pTIU * Math.cos(30 * Math.PI / 180));
    const y2 = center + (scale * pTIU * Math.sin(30 * Math.PI / 180)); // Kanan Bawah (TIU)

    const x3 = center - (scale * pTKP * Math.cos(30 * Math.PI / 180));
    const y3 = center + (scale * pTKP * Math.sin(30 * Math.PI / 180)); // Kiri Bawah (TKP)

    return `${x1},${y1} ${x2},${y2} ${x3},${y3}`;
});
</script>

<template>
    <Head title="Quantum Intelligence Analysis" />

    <AuthenticatedLayout>
        <div class="py-12 bg-[#F1F5F9] min-h-screen font-sans selection:bg-indigo-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                <div class="bg-white rounded-[3.5rem] p-10 md:p-16 shadow-[0_40px_100px_-20px_rgba(15,23,42,0.1)] border border-white flex flex-col md:flex-row items-center gap-12 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-50 rounded-full blur-[100px] -mr-32 -mt-32"></div>
                    
                    <div class="relative w-48 h-48 md:w-56 md:h-56 flex items-center justify-center shrink-0">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="50%" cy="50%" r="45%" stroke="currentColor" stroke-width="12" fill="transparent" class="text-slate-50" />
                            <circle cx="50%" cy="50%" r="45%" stroke="currentColor" stroke-width="12" fill="transparent" 
                                    stroke-dasharray="700" :stroke-dashoffset="700 - (result.total_score/550 * 700)"
                                    :class="result.passing_status ? 'text-emerald-500' : 'text-rose-500'" 
                                    class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute text-center">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Skor Total</p>
                            <h2 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tighter">{{ result.total_score }}</h2>
                            <p :class="result.passing_status ? 'text-emerald-500' : 'text-rose-500'" class="text-[9px] font-black uppercase tracking-widest mt-1">
                                {{ result.passing_status ? 'Lulus PassGrade' : 'Belum Lulus' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex-1 text-center md:text-left space-y-6">
                        <div class="space-y-2">
                            <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tighter leading-none uppercase">
                                Analysis <br> <span class="italic text-indigo-600">Quantum Intelligence.</span>
                            </h1>
                            <p class="text-slate-400 text-sm font-medium">Berdasarkan performa Anda, simulasi ini menunjukkan potensi keberhasilan yang sangat tinggi pada materi TIU.</p>
                        </div>

                        <div class="flex flex-wrap justify-center md:justify-start gap-8">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Peringkat</p>
                                <p class="text-xl font-black text-slate-900">#{{ result.rank }} <span class="text-[10px] text-slate-400">/ {{ result.total_participants }}</span></p>
                            </div>
                            <div class="w-[1px] h-10 bg-slate-100 hidden sm:block"></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Durasi</p>
                                <p class="text-xl font-black text-slate-900">{{ result.duration_used }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 bg-slate-900 rounded-[3rem] p-10 flex flex-col items-center justify-center text-center shadow-2xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl"></div>
                        <h3 class="text-indigo-400 text-[10px] font-black uppercase tracking-[0.4em] mb-8">Mastery Map</h3>
                        
                        <div class="relative w-64 h-64">
                            <svg viewBox="0 0 200 200" class="w-full h-full drop-shadow-[0_0_15px_rgba(79,70,229,0.4)]">
                                <circle cx="100" cy="100" r="80" fill="none" stroke="white" stroke-opacity="0.05" stroke-dasharray="4" />
                                <circle cx="100" cy="100" r="40" fill="none" stroke="white" stroke-opacity="0.05" stroke-dasharray="4" />
                                
                                <polygon :points="radarPoints" fill="rgba(79, 70, 229, 0.4)" stroke="#818cf8" stroke-width="2" />
                                
                                <text x="100" y="15" text-anchor="middle" fill="#94a3b8" font-size="10" font-weight="900" class="uppercase">TWK</text>
                                <text x="185" y="150" text-anchor="middle" fill="#94a3b8" font-size="10" font-weight="900" class="uppercase">TIU</text>
                                <text x="15" y="150" text-anchor="middle" fill="#94a3b8" font-size="10" font-weight="900" class="uppercase">TKP</text>
                            </svg>
                        </div>
                        
                        <p class="mt-8 text-xs text-slate-400 leading-relaxed italic">
                            Grafik menunjukkan dominasi kekuatan Anda berada pada area logika numerik (TIU).
                        </p>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-[3rem] p-10 md:p-14 shadow-sm border border-white">
                        <h3 class="text-slate-900 text-[10px] font-black uppercase tracking-[0.4em] mb-10">Detailed Score Breakdown</h3>
                        
                        <div class="space-y-10">
                            <div class="space-y-3">
                                <div class="flex justify-between items-end px-2">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Tes Wawasan Kebangsaan</h4>
                                    <p class="text-xl font-black text-slate-900">{{ result.twk }} <span class="text-xs text-slate-400">/ 150</span></p>
                                </div>
                                <div class="w-full h-3 bg-slate-50 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" :style="{ width: (result.twk / 150 * 100) + '%' }"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-end px-2">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Tes Intelegensia Umum</h4>
                                    <p class="text-xl font-black text-slate-900">{{ result.tiu }} <span class="text-xs text-slate-400">/ 175</span></p>
                                </div>
                                <div class="w-full h-3 bg-slate-50 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" :style="{ width: (result.tiu / 175 * 100) + '%' }"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between items-end px-2">
                                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Tes Karakteristik Pribadi</h4>
                                    <p class="text-xl font-black text-slate-900">{{ result.tkp }} <span class="text-xs text-slate-400">/ 225</span></p>
                                </div>
                                <div class="w-full h-3 bg-slate-50 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all duration-1000" :style="{ width: (result.tkp / 225 * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-6 justify-center pb-12">
                    <Link :href="route('tryout.index')" class="px-10 py-5 bg-white text-slate-900 border border-slate-200 rounded-[2rem] text-[10px] font-black uppercase tracking-[0.3em] hover:bg-slate-50 transition-all text-center">
                        Daftar Tryout
                    </Link>
                    <Link href="#" class="px-10 py-5 bg-slate-900 text-white rounded-[2rem] text-[10px] font-black uppercase tracking-[0.3em] shadow-2xl shadow-indigo-200 hover:bg-indigo-600 transition-all text-center">
                        Review Pembahasan
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.max-w-7xl > div {
    animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.max-w-7xl > div:nth-child(2) { animation-delay: 0.1s; }
.max-w-7xl > div:nth-child(3) { animation-delay: 0.2s; }
</style>