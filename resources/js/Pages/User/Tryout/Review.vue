<script setup>
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object,
    attempt: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const currentIndex = ref(0);
const isSidebarOpen = ref(false);

// --- FITUR LAPORKAN SOAL ---
const showReportModal = ref(false);

const reportForm = useForm({
    question_id: '',
    reason: 'Kunci Jawaban Salah',
    description: ''
});

const openReportModal = (questionId) => {
    reportForm.reset();
    reportForm.question_id = questionId;
    reportForm.reason = 'Kunci Jawaban Salah';
    showReportModal.value = true;
};

const closeReportModal = () => {
    showReportModal.value = false;
    reportForm.reset();
};

const submitReport = () => {
    reportForm.post(route('tryout.report-question'), {
        preserveScroll: true,
        onSuccess: () => {
            closeReportModal();
            alert('Laporan berhasil dikirim. Tim kami akan segera meninjaunya!');
        }
    });
};

// --- 1. SUPER ROBUST DATA GETTER ---
const getUserAnswer = (question) => {
    if (!question) return null;
    if (question.user_answer !== undefined && question.user_answer !== null) return question.user_answer;
    if (question.pivot && question.pivot.answer) return question.pivot.answer;
    if (question.answer !== undefined && question.answer !== null) return question.answer;
    return null;
};

// --- 2. LOGIC MODIFIED UNTUK PENGECEKAN JAWABAN ---
const checkAnswer = (q, key = null) => {
    const userAns = getUserAnswer(q);
    const correctAns = q.correct_answer;

    if (key !== null) {
        const isCorrectKey = String(key).trim().toLowerCase() === String(correctAns).trim().toLowerCase();
        const isUserKey = userAns && String(key).trim().toLowerCase() === String(userAns).trim().toLowerCase();
        return { isCorrectKey, isUserKey };
    }

    if (!userAns) return false;
    return String(userAns).trim().toLowerCase() === String(correctAns).trim().toLowerCase();
};

const hasAnswered = (q) => {
    const ans = getUserAnswer(q);
    return ans !== null && ans !== undefined && String(ans).trim() !== '';
};

// --- 3. HELPER KHUSUS TKP ---
const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

const isTKP = computed(() => {
    const q = currentQuestion.value;
    if (!q) return false;
    const type = q.type?.toUpperCase();
    const category = q.category?.toUpperCase();
    return type === 'TKP' || category === 'TKP';
});

// SUPER ROBUST: Ambil poin otomatis dari berbagai macam struktur database Admin
const getTkpPoint = (q, key) => {
    if (!q || !key) return 0;
    
    const kLow = String(key).toLowerCase();
    const kUp = String(key).toUpperCase();
    
    let tkpScoresObj = q.tkp_scores;
    if (typeof tkpScoresObj === 'string') {
        try { tkpScoresObj = JSON.parse(tkpScoresObj); } catch(e) { tkpScoresObj = {}; }
    }
    if (tkpScoresObj && typeof tkpScoresObj === 'object') {
        if (tkpScoresObj[kLow] !== undefined) return Number(tkpScoresObj[kLow]);
        if (tkpScoresObj[kUp] !== undefined) return Number(tkpScoresObj[kUp]);
    }

    let pointObj = q.points;
    if (typeof pointObj === 'string') {
        try { pointObj = JSON.parse(pointObj); } catch(e) { pointObj = {}; }
    }
    let nilaiObj = q.nilai;
    if (typeof nilaiObj === 'string') {
        try { nilaiObj = JSON.parse(nilaiObj); } catch(e) { nilaiObj = {}; }
    }

    if (pointObj && typeof pointObj === 'object') {
        if (pointObj[kLow] !== undefined) return Number(pointObj[kLow]);
        if (pointObj[kUp] !== undefined) return Number(pointObj[kUp]);
    }
    if (nilaiObj && typeof nilaiObj === 'object') {
        if (nilaiObj[kLow] !== undefined) return Number(nilaiObj[kLow]);
        if (nilaiObj[kUp] !== undefined) return Number(nilaiObj[kUp]);
    }

    const possibleColumns = [
        `point_${kLow}`, `points_${kLow}`, `nilai_${kLow}`, `score_${kLow}`, 
        `option_${kLow}_point`, `option_${kLow}_nilai`,
        `point_${kUp}`, `nilai_${kUp}`
    ];

    for (const col of possibleColumns) {
        if (q[col] !== undefined && q[col] !== null) {
            return Number(q[col]);
        }
    }

    return 0; 
};

// --- COMPUTED ---
const subtestTopic = computed(() => {
    const type = currentQuestion.value?.type?.toUpperCase();
    if (type === 'TWK') return 'Nasionalisme & Bela Negara';
    if (type === 'TIU') return 'Kemampuan Verbal & Logika';
    if (type === 'TKP') return 'Pelayanan Publik & Jejaring Kerja';
    return type || 'Tes Kompetensi';
});

const answeredCount = computed(() => props.questions.filter(q => hasAnswered(q)).length);

// --- METHODS NAVIGASI ---
const goTo = (index) => { 
    currentIndex.value = index; 
    if (window.innerWidth < 1024) isSidebarOpen.value = false;
    document.getElementById('scroll-area')?.scrollTo({ top: 0, behavior: 'smooth' });
};
const next = () => { if (currentIndex.value < (props.questions?.length - 1)) goTo(currentIndex.value + 1); };
const prev = () => { if (currentIndex.value > 0) goTo(currentIndex.value - 1); };

// --- STYLING iCLOUD / MODERN ---
const getOptionClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);
    
    if (isTKP.value) {
        const point = getTkpPoint(q, key);
        if (isUserKey) {
            return point == 5 ? 'bg-emerald-50 border-emerald-400 shadow-[0_2px_8px_rgba(16,185,129,0.15)]' : 'bg-[#F0F4FF] border-[#007AFF] shadow-[0_2px_8px_rgba(0,122,255,0.15)]';
        }
        return 'bg-white border-slate-200';
    }

    if (isCorrectKey) return 'bg-emerald-50 border-emerald-400 shadow-[0_2px_8px_rgba(16,185,129,0.15)]';
    if (isUserKey && !isCorrectKey) return 'bg-rose-50 border-rose-400 shadow-[0_2px_8px_rgba(244,63,94,0.15)]';
    return 'bg-white border-slate-200';
};

const getCircleClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);
    
    if (isTKP.value) {
        const point = getTkpPoint(q, key);
        if (point == 5 && isUserKey) return 'bg-emerald-500 border-emerald-500 text-white';
        if (isUserKey) return 'bg-[#007AFF] border-[#007AFF] text-white';
        return 'bg-[#F5F5F7] border-transparent text-slate-500';
    }

    if (isCorrectKey) return 'bg-emerald-500 border-emerald-500 text-white shadow-sm';
    if (isUserKey && !isCorrectKey) return 'bg-rose-500 border-rose-500 text-white shadow-sm';
    return 'bg-[#F5F5F7] border-transparent text-slate-500';
};

const getSidebarClass = (index) => {
    const q = props.questions[index];
    const isActive = currentIndex.value === index;
    
    let base = 'aspect-square rounded-[10px] flex items-center justify-center text-[12px] font-bold transition-all border ';
    
    if (isActive) {
        base += 'ring-2 ring-offset-2 ring-[#007AFF] z-10 ';
    }
    
    if (!hasAnswered(q)) {
        return base + 'bg-white border-slate-200 text-slate-500 hover:bg-[#F5F5F7]';
    }
    
    const isTkpQ = q?.type?.toUpperCase() === 'TKP' || q?.category?.toUpperCase() === 'TKP';
    if (isTkpQ) {
        const ans = getUserAnswer(q);
        if (getTkpPoint(q, ans) == 5) return base + 'bg-emerald-100 border-emerald-200 text-emerald-700';
        return base + 'bg-blue-100 border-blue-200 text-[#007AFF]';
    } else {
        if (checkAnswer(q)) return base + 'bg-emerald-100 border-emerald-200 text-emerald-700';
        return base + 'bg-rose-100 border-rose-200 text-rose-700';
    }
};
</script>

<template>
    <Head :title="`Pembahasan: ${tryout?.title}`" />

    <!-- LAYOUT FULLSCREEN iCLOUD -->
    <div class="h-[100dvh] flex flex-col font-sans text-slate-800 bg-[#F2F2F7] overflow-hidden">
        
        <!-- ============================================== -->
        <!-- HEADER BARS                                    -->
        <!-- ============================================== -->
        <header class="h-14 sm:h-16 bg-white/80 backdrop-blur-md border-b border-slate-200/60 flex items-center justify-between px-4 sm:px-6 shrink-0 z-30">
            <div class="flex items-center gap-3">
                <Link :href="route('tryout.result', attempt?.id)" class="text-[#007AFF] hover:bg-[#007AFF]/10 p-1.5 rounded-lg transition-colors flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    <span class="text-[13px] font-semibold hidden sm:block">Kembali</span>
                </Link>
                <div class="w-px h-6 bg-slate-200 mx-1 hidden sm:block"></div>
                <div class="leading-tight">
                    <h1 class="font-bold text-[14px] sm:text-[15px] tracking-tight">Review Pembahasan</h1>
                    <p class="text-[10px] text-slate-500 font-medium uppercase tracking-widest truncate max-w-[150px] sm:max-w-xs">{{ tryout?.title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Tally Rings (Desktop) -->
                <div class="hidden lg:flex items-center gap-3 bg-[#F5F5F7] px-3 py-1.5 rounded-full border border-slate-200/60">
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div><span class="text-[10px] font-bold text-slate-600">Benar</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-rose-400"></div><span class="text-[10px] font-bold text-slate-600">Salah</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></div><span class="text-[10px] font-bold text-slate-600">TKP</span></div>
                </div>

                <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg border border-slate-200 bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </header>

        <!-- ============================================== -->
        <!-- MAIN AREA (SOAL KIRI, SIDEBAR KANAN)           -->
        <!-- ============================================== -->
        <div class="flex-1 flex overflow-hidden relative">
            
            <!-- KOLOM KIRI (KONTEN SOAL & PEMBAHASAN) -->
            <main id="scroll-area" class="flex-1 overflow-y-auto custom-scrollbar relative">
                <div class="max-w-4xl mx-auto p-3 sm:p-5 lg:p-6 space-y-4 pb-28">
                    
                    <!-- KARTU SOAL -->
                    <div class="bg-white rounded-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-slate-100/80 p-5 sm:p-8">
                        <div class="flex flex-wrap justify-between items-center gap-3 border-b border-slate-100 pb-4 mb-5">
                            <div class="flex items-center gap-2.5">
                                <span class="bg-[#F5F5F7] text-slate-600 text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-widest border border-slate-200/60">
                                    Soal {{ currentIndex + 1 }}
                                </span>
                                <span class="bg-[#F0F4FF] text-[#007AFF] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest">
                                    {{ currentQuestion?.type || 'UMUM' }}
                                </span>
                            </div>
                            
                            <button @click="openReportModal(currentQuestion.id)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 rounded-full text-[10px] font-bold transition-colors active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                                LAPORKAN
                            </button>
                        </div>

                        <!-- Gambar Soal -->
                        <div v-if="currentQuestion?.image" class="mb-5 max-w-sm mx-auto">
                            <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded-[16px] border border-slate-200 p-1 shadow-sm">
                        </div>

                        <!-- Teks Soal -->
                        <div class="prose prose-slate max-w-none text-[15px] sm:text-[16px] text-slate-800 font-medium leading-relaxed mb-6 whitespace-pre-wrap" v-html="currentQuestion?.content"></div>

                        <!-- Opsi Jawaban -->
                        <div class="space-y-3">
                            <div v-for="(option, key) in currentQuestion?.options" :key="key" 
                                 class="flex items-start gap-3 p-3 sm:p-4 rounded-[16px] border-2 transition-all relative overflow-hidden" 
                                 :class="getOptionClass(key)">
                                
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-slate-200/50 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[12px] sm:text-[13px]" 
                                     :class="getCircleClass(key)">
                                    {{ key.toUpperCase() }}
                                </div>
                                
                                <div class="flex-1">
                                    <div class="text-[13px] sm:text-[14px] text-slate-700 leading-relaxed font-medium pt-1 pr-16" v-html="option"></div>
                                    
                                    <!-- Badge Keterangan di Bawah Opsi -->
                                    <div class="mt-2.5 flex flex-wrap gap-1.5 items-center">
                                        <template v-if="isTKP">
                                            <span v-if="getTkpPoint(currentQuestion, key) == 5" class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 uppercase">Poin Maksimal</span>
                                            <span v-if="checkAnswer(currentQuestion, key).isUserKey" class="text-[9px] font-bold px-2 py-0.5 rounded border uppercase" :class="getTkpPoint(currentQuestion, key) == 5 ? 'text-emerald-700 bg-emerald-100 border-emerald-300' : 'text-[#007AFF] bg-[#F0F4FF] border-blue-200'">Jawaban Anda</span>
                                        </template>
                                        <template v-else>
                                            <span v-if="checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 uppercase">Kunci Jawaban</span>
                                            <span v-if="checkAnswer(currentQuestion, key).isUserKey && !checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 uppercase">Jawaban Anda</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Skor TKP di Kanan -->
                                <div v-if="isTKP" class="absolute right-3 top-3">
                                    <div class="px-2 py-1 rounded-[8px] border text-[11px] font-black shadow-sm" :class="getTkpPoint(currentQuestion, key) == 5 ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200'">
                                        +{{ getTkpPoint(currentQuestion, key) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU PEMBAHASAN -->
                    <div class="bg-blue-50/60 rounded-[24px] shadow-[0_2px_8px_rgba(0,0,0,0.02)] border border-blue-100/80 p-5 sm:p-8 relative overflow-hidden">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-full bg-[#007AFF] text-white flex items-center justify-center shadow-md shadow-blue-500/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="font-bold text-slate-900 text-[16px] tracking-tight">Pembahasan Detail</h3>
                        </div>
                        
                        <div v-if="currentQuestion?.explanation" class="prose prose-sm max-w-none text-[13px] sm:text-[14px] text-slate-700 leading-relaxed font-medium whitespace-pre-wrap" v-html="currentQuestion.explanation"></div>
                        <div v-else class="text-[13px] text-slate-500 italic font-medium">Pembahasan belum tersedia untuk soal ini.</div>
                    </div>
                </div>
            </main>

            <!-- ============================================== -->
            <!-- SIDEBAR NAVIGASI (KANAN)                       -->
            <!-- ============================================== -->
            <aside :class="[
                'fixed lg:static inset-y-0 right-0 z-40 w-[280px] bg-white border-l border-slate-200/60 flex flex-col transform transition-transform duration-300 lg:translate-x-0 shadow-2xl lg:shadow-none', 
                isSidebarOpen ? 'translate-x-0' : 'translate-x-full'
            ]">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-900 text-[13px]">Navigasi Soal</h3>
                        <p class="text-[10px] text-slate-500 font-medium mt-0.5">Pilih nomor untuk review</p>
                    </div>
                    <button @click="isSidebarOpen = false" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <div class="grid grid-cols-5 gap-2 place-content-start">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" :class="getSidebarClass(i)">
                            {{ i + 1 }}
                        </button>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 bg-[#F5F5F7]/50 space-y-2.5 shrink-0">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                        <span>Soal Terjawab</span>
                        <span>{{ answeredCount }} / {{ questions.length }}</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-1.5">
                        <div class="bg-[#007AFF] h-1.5 rounded-full" :style="`width: ${(answeredCount / questions.length) * 100}%`"></div>
                    </div>
                </div>
            </aside>
            
            <!-- OVERLAY MOBILE SIDEBAR -->
            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden backdrop-blur-sm transition-opacity"></div>
        </div>

        <!-- ============================================== -->
        <!-- FLOATING BOTTOM NAVIGATION                     -->
        <!-- ============================================== -->
        <div class="fixed bottom-4 sm:bottom-6 left-0 lg:right-[280px] right-0 flex justify-center pointer-events-none z-20 px-4">
            <div class="bg-white/90 backdrop-blur-xl border border-slate-200/80 shadow-[0_8px_30px_rgba(0,0,0,0.12)] rounded-full p-1.5 flex items-center gap-2 pointer-events-auto w-full max-w-sm">
                <button @click="prev" :disabled="currentIndex === 0" class="flex-1 py-3 px-4 rounded-full text-[13px] font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed bg-[#F2F2F7] hover:bg-[#E3E3E8] text-slate-700 active:scale-95">
                    &larr; Prev
                </button>
                <div class="w-px h-8 bg-slate-200 mx-1"></div>
                <button @click="next" :disabled="currentIndex === (questions.length - 1)" class="flex-1 py-3 px-4 rounded-full text-[13px] font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed bg-[#007AFF] hover:bg-[#0062CC] text-white active:scale-95 shadow-sm">
                    Next &rarr;
                </button>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL LAPORKAN SOAL                            -->
        <!-- ============================================== -->
        <Teleport to="body">
            <div v-if="showReportModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
                <div class="absolute inset-0" @click="closeReportModal"></div>
                
                <div class="relative bg-white w-full max-w-md rounded-[24px] shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 z-10">
                    <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-900 text-[15px] flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Laporkan Kesalahan Soal
                        </h3>
                        <button @click="closeReportModal" class="text-slate-400 hover:text-slate-600 bg-slate-100 w-8 h-8 flex items-center justify-center rounded-full transition active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitReport" class="p-5 space-y-4">
                        <div>
                            <label class="block text-[12px] font-bold text-slate-700 mb-1.5">Jenis Masalah <span class="text-rose-500">*</span></label>
                            <select v-model="reportForm.reason" class="w-full bg-[#F5F5F7] border border-transparent rounded-[12px] px-3.5 py-3 text-[13px] focus:ring-2 focus:ring-[#007AFF]/20 focus:bg-white focus:border-[#007AFF] outline-none transition-all font-semibold text-slate-800" required>
                                <option value="Kunci Jawaban Salah">Kunci Jawaban Salah</option>
                                <option value="Pembahasan Kurang Jelas / Salah">Pembahasan Kurang Jelas / Salah</option>
                                <option value="Soal Tidak Lengkap / Typo">Soal Tidak Lengkap / Typo</option>
                                <option value="Pilihan Ganda Error / Jawaban Ganda">Pilihan Ganda Error / Jawaban Ganda</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-slate-700 mb-1.5">Jelaskan Letak Kesalahannya</label>
                            <textarea v-model="reportForm.description" rows="3" class="w-full bg-[#F5F5F7] border border-transparent rounded-[12px] px-3.5 py-3 text-[13px] focus:ring-2 focus:ring-[#007AFF]/20 focus:bg-white focus:border-[#007AFF] outline-none transition-all resize-none placeholder:text-slate-400 font-medium text-slate-800" placeholder="Contoh: Kunci jawaban tertulis A, namun berdasarkan pembahasan di buku panduan harusnya B..."></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" :disabled="reportForm.processing" class="w-full py-3.5 bg-rose-500 hover:bg-rose-600 disabled:opacity-50 text-white rounded-full font-bold text-[13px] shadow-sm active:scale-95 transition-all flex justify-center items-center gap-2 tracking-wide">
                                <span v-if="reportForm.processing">MENGIRIM...</span>
                                <span v-else>KIRIM LAPORAN</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

    </div>
</template>

<style>
/* Reset Prose untuk HTML dari Backend */
.prose p { margin-bottom: 0.75em; }
.prose p:last-child { margin-bottom: 0; }
.prose img { max-width: 100%; height: auto; border-radius: 12px; margin: 10px 0; }
.prose ul { list-style-type: disc; padding-left: 1.5em; margin-bottom: 0.75em; }
.prose ol { list-style-type: decimal; padding-left: 1.5em; margin-bottom: 0.75em; }

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>