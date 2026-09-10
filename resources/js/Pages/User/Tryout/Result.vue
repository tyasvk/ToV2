<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import html2canvas from 'html2canvas-pro';

const props = defineProps({
    attempt: Object,
    tryout: Object,
    totalScore: Number,
    scoreDetails: Array,
    materialScores: Object, 
    ranking: Object,
    timeStats: Object, 
    backUrl: String,
    hasFullAccess: Boolean, 
});

const showUpgradeModal = ref(false);

const dynamicBackUrl = computed(() => {
    const type = props.tryout?.type || 'general';
    if (type === 'adidaya') return route('tryout.adidaya');
    if (type === 'akbar') return route('tryout-akbar.index');
    return route('tryout.index');
});

const formatTime = (seconds) => {
    if (!seconds || seconds <= 0) return '0d';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    if (h > 0) return `${h}j ${m}m ${s}d`;
    if (m > 0) return `${m}m ${s}d`;
    return `${s} detik`;
};

const activeMaterialTab = ref('TWK');

// ==========================================
// FITUR SHARE IG STORY
// ==========================================
const scoreCardRef = ref(null);
const isGenerating = ref(false);

const shareToIGStory = async () => {
    if (!scoreCardRef.value) return;
    
    isGenerating.value = true;
    
    try {
        const canvas = await html2canvas(scoreCardRef.value, {
            scale: 3, 
            useCORS: true,
            backgroundColor: '#0B0F19', 
        });

        canvas.toBlob(async (blob) => {
            const file = new File([blob], 'skor-cpns-nusantara.png', { type: 'image/png' });

            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({
                    title: 'Skor Tryout CPNS Nusantara',
                    text: 'Berani adu skor? Buktikan di CPNS Nusantara!',
                    files: [file],
                });
            } else {
                const link = document.createElement('a');
                link.download = 'skor-cpns-nusantara.png';
                link.href = URL.createObjectURL(blob);
                link.click();
                alert('Gambar berhasil diunduh! Silakan upload manual ke IG Story Anda.');
            }
            isGenerating.value = false;
        }, 'image/png');

    } catch (error) {
        console.error('Error generating image:', error);
        alert('Terjadi kesalahan saat memproses gambar.');
        isGenerating.value = false;
    }
};
</script>

<template>
    <Head title="Hasil Tryout" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden relative">

            <!-- ========================================== -->
            <!-- KARTU IG STORY TERSEMBUNYI (HIDDEN CANVAS) -->
            <!-- CLASS FIXED & RELATIVE SUDAH DIPERBAIKI -->
            <!-- ========================================== -->
            <div class="absolute top-[-9999px] left-[-9999px] pointer-events-none opacity-0">
                <div ref="scoreCardRef" class="w-[360px] h-[640px] bg-[#0B0F19] overflow-hidden flex flex-col pt-10 pb-8 px-6 font-sans relative">
                    
                    <div class="absolute top-[-10%] left-[-20%] w-[300px] h-[300px] bg-indigo-600/40 rounded-full blur-[80px]"></div>
                    <div class="absolute bottom-[10%] right-[-20%] w-[250px] h-[250px] bg-fuchsia-600/30 rounded-full blur-[80px]"></div>

                    <div class="flex items-center justify-between z-10 w-full mb-6">
                        <div class="flex items-center gap-2.5">
                            <div class="bg-white p-1.5 rounded-lg shadow-sm">
                                <img src="/images/logo.png" alt="Logo" class="w-6 h-6 object-contain" />
                            </div>
                            <span class="text-[12px] font-black text-white tracking-wider uppercase">CPNS Nusantara</span>
                        </div>
                        <div class="px-2.5 py-1 rounded-md bg-white/10 border border-white/20 text-[9px] font-bold text-white uppercase tracking-widest">
                            SKD 2026
                        </div>
                    </div>

                    <div class="text-center z-10 w-full mb-5 mt-2">
                        <h1 class="text-[32px] font-black italic text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-orange-400 to-rose-500 uppercase tracking-tighter leading-none transform -skew-x-6">
                            SKOR SAYA!
                        </h1>
                        <p class="text-[12px] text-slate-300 font-medium mt-2 truncate px-2">{{ tryout?.title || 'Tryout CPNS' }}</p>
                    </div>

                    <div class="w-full bg-white/10 backdrop-blur-xl rounded-[24px] p-6 mb-5 border border-white/20 z-10 flex flex-col items-center relative shadow-2xl">
                        <div class="absolute inset-0 opacity-20 rounded-[24px] pointer-events-none" :class="attempt?.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                        
                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-[0.2em] relative z-10 mb-1">Total Skor Akhir</p>
                        
                        <div class="text-[76px] leading-[0.9] font-black tabular-nums tracking-tighter relative z-10 drop-shadow-[0_0_15px_rgba(255,255,255,0.2)]" :class="attempt?.is_passed ? 'text-emerald-400' : 'text-rose-400'">
                            {{ totalScore || 0 }}
                        </div>

                        <div class="mt-4 px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider relative z-10 border shadow-lg" 
                             :class="attempt?.is_passed ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50' : 'bg-rose-500/20 text-rose-300 border-rose-500/50'">
                            {{ attempt?.is_passed ? '🎉 LULUS PASSING GRADE' : '💪 TETAP SEMANGAT' }}
                        </div>
                    </div>

                    <div class="w-full grid grid-cols-3 gap-2.5 z-10 mb-auto">
                        <div v-for="detail in scoreDetails" :key="detail.category" class="bg-[#0B0F19]/80 backdrop-blur-md rounded-[16px] p-3.5 border border-white/10 text-center relative overflow-hidden shadow-inner">
                            <div class="absolute top-0 inset-x-0 h-1.5" :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mb-1 mt-1">{{ detail.category }}</p>
                            <p class="text-[22px] font-bold text-white tabular-nums leading-none">{{ detail.score }}</p>
                        </div>
                    </div>

                    <!-- MENGGUNAKAN FALLBACK (OPTIONAL CHAINING) AGAR TIDAK CRASH -->
                    <div class="w-full z-10 mt-6 flex flex-col items-center">
                        <div class="flex items-center justify-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-slate-800 rounded-full overflow-hidden border-2 border-white/20">
                                <img :src="`https://ui-avatars.com/api/?name=${$page.props.auth?.user?.name || 'Peserta'}&background=random`" alt="Avatar" class="w-full h-full" />
                            </div>
                            <p class="text-[13px] font-bold text-white truncate max-w-[180px]">{{ $page.props.auth?.user?.name || 'Peserta' }}</p>
                        </div>

                        <div class="w-full bg-gradient-to-r from-indigo-600 to-blue-500 rounded-2xl p-4 flex flex-col items-center justify-center shadow-[0_8px_30px_rgba(79,70,229,0.4)] border border-white/20 transform rotate-[-1deg]">
                            <p class="text-[11px] text-blue-100 font-medium mb-1">Berani adu skor dengan saya?</p>
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                                <p class="text-[15px] font-black text-white uppercase tracking-wider">
                                    cpnsnusantara.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ========================================== -->


            <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4 relative z-10">

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
                    <div>
                        <Link :href="dynamicBackUrl" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali
                        </Link>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Rapor Kelulusan</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1 uppercase tracking-wide">
                            {{ tryout?.title }}
                        </p>
                    </div>

                    <button 
                        @click="shareToIGStory"
                        :disabled="isGenerating"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-fuchsia-600 to-orange-500 hover:from-fuchsia-700 hover:to-orange-600 text-white text-[13px] font-bold rounded-full shadow-lg shadow-orange-500/20 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!isGenerating" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                        <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ isGenerating ? 'Memproses...' : 'Pamer ke IG Story' }}</span>
                    </button>
                </div>

                <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 overflow-hidden relative">
                    <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full blur-[60px] opacity-20 pointer-events-none" :class="attempt?.is_passed ? 'bg-emerald-500' : 'bg-rose-500'"></div>

                    <div class="p-5 md:p-6 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                        <div class="text-center md:text-left flex-1">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest mb-3 border shadow-sm" 
                                 :class="attempt?.is_passed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'">
                                <svg v-if="attempt?.is_passed" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                {{ attempt?.is_passed ? 'Selamat, Memenuhi Passing Grade!' : 'Maaf, Belum Memenuhi Passing Grade' }}
                            </div>
                            <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ tryout?.title }}</h2>
                            <p class="text-[12px] text-slate-500 mt-1 font-medium">Sistem CAT (TWK, TIU, TKP)</p>
                        </div>

                        <div class="shrink-0 flex flex-col items-center justify-center w-28 h-28 rounded-full border-4 shadow-sm bg-white" :class="attempt?.is_passed ? 'border-emerald-400' : 'border-rose-400'">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Total Skor</span>
                            <span class="text-3xl font-black tabular-nums tracking-tighter mt-0.5" :class="attempt?.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                {{ totalScore }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 border-t border-slate-100 bg-[#F5F5F7]/50 divide-y md:divide-y-0 md:divide-x divide-slate-100/80">
                        <div class="p-3 text-center border-r border-slate-100 md:border-r-0">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Peringkat Anda</p>
                            <p v-if="hasFullAccess" class="text-[17px] font-bold text-slate-800 tabular-nums">#{{ ranking?.rank || '-' }} <span class="text-[11px] font-medium text-slate-400">/ {{ ranking?.total_participants || '-' }}</span></p>
                            <button v-else @click="showUpgradeModal = true" class="text-[17px] font-bold text-slate-300 w-full text-center hover:text-slate-400 transition-colors">🔒</button>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Soal Dikerjakan</p>
                            <p class="text-[17px] font-bold text-slate-800 tabular-nums">
                                {{ Object.keys(attempt?.answers || {}).length }} 
                                <span class="text-[11px] font-medium text-slate-400">/ {{ timeStats?.total_questions || 0 }}</span>
                            </p>
                        </div>
                        <div class="p-3 text-center border-r border-slate-100 md:border-r-0">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Total Waktu</p>
                            <p class="text-[17px] font-bold text-[#007AFF] tabular-nums">{{ formatTime(timeStats?.total_seconds) }}</p>
                        </div>
                        <div class="p-3 text-center">
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Rata-rata / Soal</p>
                            <p v-if="hasFullAccess" class="text-[17px] font-bold text-amber-500 tabular-nums">{{ formatTime(timeStats?.average_seconds) }}</p>
                            <button v-else @click="showUpgradeModal = true" class="text-[17px] font-bold text-slate-300 w-full text-center hover:text-slate-400 transition-colors">🔒</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 p-5 md:p-6">
                    <h3 class="text-[12px] font-bold text-slate-900 uppercase tracking-widest border-b border-slate-100 pb-2.5 mb-4">Rincian Ambang Batas</h3>

                    <div class="space-y-4">
                        <div v-for="(detail, index) in scoreDetails" :key="index" class="bg-[#F5F5F7]/80 rounded-[16px] p-3.5 border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-4">

                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1.5">
                                    <h4 class="font-semibold text-slate-900 text-[13px] md:text-[14px]">{{ detail.category }}</h4>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-sm" :class="detail.is_passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'">
                                        {{ detail.is_passed ? 'Lulus' : 'Gagal' }}
                                    </span>
                                </div>

                                <div class="w-full bg-[#E3E3E8] rounded-full h-2.5 mb-1.5 relative overflow-hidden">
                                    <div class="h-2.5 rounded-full transition-all duration-1000" 
                                         :class="detail.is_passed ? 'bg-emerald-500' : 'bg-rose-500'" 
                                         :style="`width: ${Math.min(100, (detail.score / (detail.passing_grade * 1.5)) * 100)}%`">
                                    </div>
                                    <div class="absolute top-0 bottom-0 border-l-[3px] border-slate-900 z-10" :style="`left: ${(detail.passing_grade / (detail.passing_grade * 1.5)) * 100}%`"></div>
                                </div>
                                <p class="text-[10px] text-slate-500 font-medium">Garis hitam menunjukkan Passing Grade ({{ detail.passing_grade }})</p>
                            </div>

                            <div class="shrink-0 text-right md:text-center md:w-24 flex flex-row md:flex-col justify-between items-center md:border-l border-slate-200/60 md:pl-4">
                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Skor Anda</span>
                                <span class="text-2xl font-black tabular-nums tracking-tight" :class="detail.is_passed ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ detail.score }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 p-5 md:p-6">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-4">
                        <h3 class="text-[12px] font-bold text-slate-900 uppercase tracking-widest">Analisis Materi</h3>
                    </div>

                    <div class="flex bg-[#F2F2F7] p-1 rounded-[12px] mb-4">
                        <button @click="activeMaterialTab = 'TWK'" :class="activeMaterialTab === 'TWK' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 rounded-[8px] text-[12px] font-bold transition-all">TWK</button>
                        <button @click="activeMaterialTab = 'TIU'" :class="activeMaterialTab === 'TIU' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 rounded-[8px] text-[12px] font-bold transition-all">TIU</button>
                        <button @click="activeMaterialTab = 'TKP'" :class="activeMaterialTab === 'TKP' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="flex-1 py-1.5 rounded-[8px] text-[12px] font-bold transition-all">TKP</button>
                    </div>

                    <div class="space-y-3">
                        <div v-for="(data, topic) in materialScores[activeMaterialTab]" :key="topic" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-[13px] font-semibold text-slate-800 truncate">{{ topic }}</h4>
                                <div class="w-full bg-[#E3E3E8] rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-[#007AFF]" :style="`width: ${Math.min(100, (data.score / data.max_score) * 100)}%`"></div>
                                </div>
                            </div>
                            <div class="ml-4 text-right shrink-0">
                                <span class="text-[15px] font-black text-slate-900">{{ data.score }}</span>
                                <span class="text-[11px] font-medium text-slate-500 block">/ {{ data.max_score }}</span>
                            </div>
                        </div>

                        <div v-if="Object.keys(materialScores[activeMaterialTab] || {}).length === 0" class="text-center py-6">
                            <p class="text-[12px] text-slate-400 font-medium">Belum ada analisis materi untuk subtes ini.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2.5 pt-2 pb-6">

                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Pembahasan
                    </button>
                    <Link v-else :href="route('tryout.review', attempt?.id)" class="flex-1 flex justify-center items-center py-3 bg-slate-900 hover:bg-slate-800 text-white text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        Lihat Pembahasan
                    </Link>

                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Papan Peringkat
                    </button>
                    <Link v-else :href="route('tryout.leaderboard', tryout?.id)" class="flex-1 flex justify-center items-center py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        Lihat Peringkat
                    </Link>

                    <button v-if="!hasFullAccess" @click="showUpgradeModal = true" class="flex-1 flex justify-center items-center gap-1.5 py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-400 text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Sertifikat
                    </button>
                    <a v-else :href="route('tryout.certificate', attempt?.id)" target="_blank" class="flex-1 flex justify-center items-center py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[13px] font-semibold rounded-full shadow-sm transition-colors active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Unduh Sertifikat
                    </a>
                </div>

            </div>
        </div>

        <Teleport to="body">
            <div v-if="showUpgradeModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="showUpgradeModal = false"></div>

                <div class="relative bg-white rounded-[24px] p-6 md:p-8 max-w-sm w-full text-center shadow-2xl animate-in zoom-in-95 duration-200 z-10">
                    <div class="w-14 h-14 bg-[#F5F5F7] rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                        🔒
                    </div>
                    <h3 class="text-[17px] font-bold text-slate-900 mb-2 tracking-tight">Fitur Terkunci</h3>
                    <p class="text-[13px] text-slate-500 mb-6 leading-relaxed font-medium">
                        Anda mengerjakan paket ini secara gratis. Beli akses premium untuk membuka <strong class="text-slate-700">Pembahasan, Peringkat Nasional, dan Sertifikat</strong>.
                    </p>
                    <div class="flex flex-col gap-2.5">
                        <Link :href="route('tryout.show', tryout?.id)" class="w-full py-3 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[13px] font-semibold transition-all active:scale-95">
                            Beli Akses Premium
                        </Link>
                        <button @click="showUpgradeModal = false" class="w-full py-3 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 rounded-full text-[13px] font-semibold transition-colors active:scale-95">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>