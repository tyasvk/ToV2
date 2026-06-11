<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object,
    attempt: Object,
    mode: { type: String, default: null }
});

const page = usePage();

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
    
    if (question.user_answer !== undefined && question.user_answer !== null) {
        return question.user_answer;
    }
    
    if (question.pivot && question.pivot.answer) {
        return question.pivot.answer;
    }

    if (question.answer !== undefined && question.answer !== null) {
        return question.answer;
    }

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
    
    const kLow = String(key).toLowerCase(); // contoh: 'a'
    const kUp = String(key).toUpperCase();  // contoh: 'A'
    
    // 1. TAMBAHAN: Cek dari properti tkp_scores (sesuai backend TryoutController)
    let tkpScoresObj = q.tkp_scores;
    if (typeof tkpScoresObj === 'string') {
        try { tkpScoresObj = JSON.parse(tkpScoresObj); } catch(e) { tkpScoresObj = {}; }
    }
    if (tkpScoresObj && typeof tkpScoresObj === 'object') {
        if (tkpScoresObj[kLow] !== undefined) return Number(tkpScoresObj[kLow]);
        if (tkpScoresObj[kUp] !== undefined) return Number(tkpScoresObj[kUp]);
    }

    // 2. Antisipasi jika Admin menyimpan poin dalam JSON string
    let pointObj = q.points;
    if (typeof pointObj === 'string') {
        try { pointObj = JSON.parse(pointObj); } catch(e) { pointObj = {}; }
    }
    let nilaiObj = q.nilai;
    if (typeof nilaiObj === 'string') {
        try { nilaiObj = JSON.parse(nilaiObj); } catch(e) { nilaiObj = {}; }
    }

    // 3. Cek jika poin berbentuk Object/Array (Kolom 'points' atau 'nilai')
    if (pointObj && typeof pointObj === 'object') {
        if (pointObj[kLow] !== undefined) return Number(pointObj[kLow]);
        if (pointObj[kUp] !== undefined) return Number(pointObj[kUp]);
    }
    if (nilaiObj && typeof nilaiObj === 'object') {
        if (nilaiObj[kLow] !== undefined) return Number(nilaiObj[kLow]);
        if (nilaiObj[kUp] !== undefined) return Number(nilaiObj[kUp]);
    }

    // 4. Cek jika poin disimpan dalam kolom terpisah di database
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

    // Default jika benar-benar tidak ada di database
    return 0; 
};


// --- COMPUTED ---
const currentMode = computed(() => {
    if (props.mode) return props.mode;
    const type = props.tryout?.type?.toLowerCase() || '';
    const category = props.tryout?.category?.toLowerCase() || '';
    const title = props.tryout?.title?.toLowerCase() || '';
    if (type === 'akbar' || category.includes('skd') || category.includes('cpns') || title.includes('skd')) {
        return 'bkn';
    }
    return 'modern';
});

const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

const subtestTopic = computed(() => {
    const type = currentQuestion.value?.type?.toUpperCase();
    if (type === 'TWK') return 'Nasionalisme & Bela Negara';
    if (type === 'TIU') return 'Kemampuan Verbal & Logika';
    if (type === 'TKP') return 'Pelayanan Publik & Jejaring Kerja';
    return type || 'Tes Kompetensi';
});

// Mengecek jika di header perlu label BENAR atau SALAH
const isUserCorrectHeader = computed(() => {
    const q = currentQuestion.value;
    if (!q) return false;
    
    if (isTKP.value) {
        const ans = getUserAnswer(q);
        return ans && getTkpPoint(q, ans) == 5;
    }
    return checkAnswer(q);
});

const isUnansweredHeader = computed(() => {
    const q = currentQuestion.value;
    return !q || !hasAnswered(q);
});

// --- METHODS ---
const goTo = (index) => { currentIndex.value = index; isSidebarOpen.value = false; };
const next = () => { if (currentIndex.value < (props.questions?.length - 1)) currentIndex.value++; };
const prev = () => { if (currentIndex.value > 0) currentIndex.value--; };

// --- LOGIKA STYLING BERSAMA (LINGKARAN A/B/C/D/E) ---
const getCircleClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);
    
    if (isTKP.value) {
        const point = getTkpPoint(q, key);
        if (point == 5) return 'bg-emerald-500 border-emerald-500 text-white'; // Jawaban poin 5 (Ideal)
        if (isUserKey && point < 5) return 'bg-rose-500 border-rose-500 text-white'; // Dipilih user tapi bukan 5 poin
        return 'bg-slate-50 border-slate-200 text-slate-500';
    }

    if (isCorrectKey) return 'bg-emerald-500 border-emerald-500 text-white';
    if (isUserKey) return 'bg-rose-500 border-rose-500 text-white';
    return 'bg-slate-50 border-slate-200 text-slate-500';
};


// --- STYLING HELPERS (MODERN) ---
const getModernOptionClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);

    if (isTKP.value) {
        const point = getTkpPoint(q, key);
        if (isUserKey && point == 5) return 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-200'; 
        if (isUserKey && point < 5) return 'border-rose-300 bg-rose-50'; 
        if (!isUserKey && point == 5) return 'border-emerald-300 bg-emerald-50/50 border-dashed'; 
        return 'border-slate-200 hover:border-slate-300 bg-white opacity-80';
    }

    if (isCorrectKey) return 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-200'; 
    if (isUserKey && !isCorrectKey) return 'border-rose-300 bg-rose-50'; 
    return 'border-slate-200 hover:border-slate-300 bg-white opacity-80'; 
};

const getModernSidebarClass = (q, index) => {
    let base = "aspect-square w-full rounded border flex items-center justify-center text-xs font-medium transition-all shrink-0 ";
    
    if (currentIndex.value === index) {
        return base + "bg-blue-50 text-blue-700 border-blue-400 ring-2 ring-blue-100 shadow-sm z-20";
    }
    
    const isTkpQ = q?.type?.toUpperCase() === 'TKP' || q?.category?.toUpperCase() === 'TKP';
    
    if (isTkpQ) {
        if (hasAnswered(q)) {
             const ans = getUserAnswer(q);
             // Hijau jika poin = 5, Merah jika < 5
             if (getTkpPoint(q, ans) == 5) return base + "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100";
             else return base + "bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100";
        }
    } else {
        if (checkAnswer(q)) return base + "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100";
        if (hasAnswered(q)) return base + "bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100";
    }
    
    return base + "bg-white text-slate-500 border-slate-200 hover:bg-slate-50";
};

// --- STYLING HELPERS (BKN) ---
const getBknSidebarClass = (q, index) => {
    if (currentIndex.value === index) {
        return 'bg-blue-50 text-blue-700 ring-1 ring-blue-400 rounded border border-blue-400 z-10 shadow-sm';
    }
    
    const isTkpQ = q?.type?.toUpperCase() === 'TKP' || q?.category?.toUpperCase() === 'TKP';
    
    if (isTkpQ) {
        if (hasAnswered(q)) {
            const ans = getUserAnswer(q);
            if (getTkpPoint(q, ans) == 5) return 'bg-emerald-50 text-emerald-600 border border-emerald-200 rounded';
            else return 'bg-rose-50 text-rose-600 border border-rose-200 rounded';
        }
    } else {
        if (checkAnswer(q)) return 'bg-emerald-50 text-emerald-600 border border-emerald-200 rounded';
        if (hasAnswered(q)) return 'bg-rose-50 text-rose-600 border border-rose-200 rounded';
    }
    
    return 'bg-white text-slate-500 border border-slate-200 rounded hover:bg-slate-50';
};

const getBknOptionClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);

    if (isTKP.value) {
        const point = getTkpPoint(q, key);
        if (isUserKey && point == 5) return 'bg-emerald-50/50 border-emerald-300 ring-1 ring-emerald-100';
        if (isUserKey && point < 5) return 'bg-rose-50/50 border-rose-300 ring-1 ring-rose-100';
        if (!isUserKey && point == 5) return 'bg-emerald-50/20 border-emerald-200 border-dashed';
        return 'bg-white border-slate-200';
    }

    if (isCorrectKey) return 'bg-emerald-50/50 border-emerald-300 ring-1 ring-emerald-100';
    if (isUserKey && !isCorrectKey) return 'bg-rose-50/50 border-rose-300 ring-1 ring-rose-100';
    return 'bg-white border-slate-200';
};
</script>

<template>
    <Head :title="'Pembahasan: ' + (tryout?.title || 'Loading...')" />

    <div v-if="currentMode === 'modern'" class="min-h-screen bg-slate-50 font-sans text-slate-700 flex flex-col">
        
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm/30 backdrop-blur-md bg-white/90">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="bg-slate-50 p-1.5 rounded-lg border border-slate-200 shadow-sm hover:bg-slate-100 transition-colors">
                        <img src="/images/logo.png" alt="Logo" class="h-6 w-6 object-contain">
                    </Link>
                    <div>
                        <h1 class="font-medium text-slate-800 text-[13px] leading-tight">Review Hasil Ujian</h1>
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wide truncate max-w-[200px] sm:max-w-xs">{{ tryout?.title }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <Link :href="route('tryout.result', attempt?.id)" class="hidden sm:flex items-center px-4 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-md text-[11px] font-medium uppercase tracking-wider hover:bg-slate-50 transition-colors shadow-sm">
                        Kembali
                    </Link>
                    <button @click="isSidebarOpen = !isSidebarOpen" class="lg:hidden p-1.5 text-slate-500 hover:bg-slate-100 border border-slate-200 rounded-md bg-white shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                    </button>
                </div>
            </div>
        </header>

        <div class="flex-1 max-w-7xl mx-auto w-full p-3 sm:p-6 lg:p-8 flex gap-5 relative items-start">
            
            <main class="flex-1 w-full min-w-0 space-y-4">
                <div v-if="currentQuestion" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden transition-colors duration-300">
                    
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-medium px-2 py-0.5 rounded tracking-wide">SOAL {{ currentIndex + 1 }}</span>
                            <span class="text-[10px] font-medium text-slate-500 uppercase tracking-widest">{{ currentQuestion.type }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button @click="openReportModal(currentQuestion.id)" type="button" class="inline-flex items-center gap-1 px-2 py-0.5 bg-rose-50 text-rose-600 border border-rose-100 rounded text-[10px] font-bold transition hover:bg-rose-100 active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                                LAPORKAN
                            </button>

                            <div v-if="isUserCorrectHeader" class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                <span class="text-[10px] font-medium uppercase">Benar</span>
                            </div>
                            <div v-else-if="!isUnansweredHeader" class="flex items-center gap-1.5 text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded border border-rose-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                <span class="text-[10px] font-medium uppercase">Salah</span>
                            </div>
                            <div v-else class="flex items-center gap-1.5 text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded border border-slate-200">
                                <span class="text-[10px] font-medium uppercase">Kosong</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 sm:p-6 lg:p-8">
                        <div v-if="currentQuestion.image" class="mb-5">
                            <img :src="'/storage/' + currentQuestion.image" class="max-h-60 w-auto rounded-lg border border-slate-200 shadow-sm bg-slate-50 p-1">
                        </div>
                        
                        <div class="prose prose-sm max-w-none font-normal text-slate-700 leading-relaxed mb-6 whitespace-pre-wrap" v-html="currentQuestion.content"></div>
                        <div class="space-y-2">
                            <div v-for="(option, key) in currentQuestion.options" :key="key" 
                                 class="relative flex items-start gap-3 p-3 rounded-lg border transition-colors" 
                                 :class="getModernOptionClass(key)">
                                
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-medium shrink-0 border mt-0.5" 
                                     :class="getCircleClass(key)">
                                    {{ key.toUpperCase() }}
                                </div>
                                
                                <div class="flex-1 text-xs sm:text-sm font-normal text-slate-600 leading-relaxed pt-1">
                                    {{ option }}
                                </div>
                                
                                <div v-if="isTKP" class="shrink-0 mt-0.5 ml-3 flex items-center gap-2">
                                    <span v-if="checkAnswer(currentQuestion, key).isUserKey" 
                                          class="text-[9px] font-bold px-2 py-1 rounded text-white shadow-sm"
                                          :class="getTkpPoint(currentQuestion, key) == 5 ? 'bg-emerald-500' : 'bg-rose-500'">
                                        {{ getTkpPoint(currentQuestion, key) == 5 ? 'BENAR' : 'SALAH' }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded border"
                                          :class="getTkpPoint(currentQuestion, key) == 5 ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                        {{ getTkpPoint(currentQuestion, key) }} Poin
                                    </span>
                                </div>
                                <template v-else>
                                    <div v-if="checkAnswer(currentQuestion, key).isCorrectKey" class="text-emerald-500 shrink-0 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    </div>
                                    <div v-else-if="checkAnswer(currentQuestion, key).isUserKey" class="text-rose-500 shrink-0 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/50 rounded-xl border border-blue-100 p-5 md:p-6 relative overflow-hidden">
                    <h3 class="flex items-center gap-2 text-blue-800 font-medium text-sm mb-3 relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Pembahasan
                    </h3>
                    <div class="prose prose-sm max-w-none font-normal text-slate-700 leading-relaxed whitespace-pre-wrap relative z-10">
                        <div v-if="currentQuestion?.explanation" v-html="currentQuestion.explanation"></div>
                        <p v-else class="text-slate-500 italic text-xs">Tidak ada pembahasan khusus untuk soal ini.</p>
                    </div>
                </div>

                <div class="flex justify-between pt-2">
                    <button @click="prev" :disabled="currentIndex === 0" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-[11px] font-medium uppercase tracking-wide hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm text-slate-600">
                        Sebelumnya
                    </button>
                    <button @click="next" :disabled="currentIndex === (questions.length - 1)" class="px-4 py-2 bg-blue-600 border border-blue-600 text-white rounded-lg text-[11px] font-medium uppercase tracking-wide hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                        Selanjutnya
                    </button>
                </div>
            </main>

            <aside :class="[
                'fixed lg:sticky lg:top-[5.5rem] inset-y-0 right-0 z-50 w-72 md:w-80 bg-white lg:bg-transparent lg:border-0 border-l shadow-2xl lg:shadow-none transition-transform duration-300 transform lg:transform-none lg:block flex flex-col', 
                isSidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'
            ]">
                <div class="bg-white rounded-xl lg:border border-slate-200 shadow-sm flex flex-col h-full lg:h-auto lg:max-h-[calc(100vh-8rem)]">
                    
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-slate-800 text-xs">Navigasi Soal</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pilih nomor untuk review</p>
                        </div>
                        <button @click="isSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto px-4 pb-4 pt-3 custom-scrollbar">
                        <div class="grid grid-cols-5 gap-1.5 sm:gap-2 place-content-start">
                            <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" 
                                    :class="getModernSidebarClass(q, i)">
                                {{ i + 1 }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-[10px] font-normal space-y-2 shrink-0">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-emerald-100 border border-emerald-200"></div> <span class="text-slate-600">Benar (TKP 5 Poin)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-rose-100 border border-rose-200"></div> <span class="text-slate-600">Salah (TKP &lt; 5 Poin)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-white border border-slate-200"></div> <span class="text-slate-600">Kosong</span>
                        </div>
                    </div>

                </div>
            </aside>
            
            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-40 lg:hidden backdrop-blur-sm transition-opacity"></div>
        </div>
    </div>

    <div v-else class="h-screen flex flex-col font-sans text-slate-700 bg-slate-50 overflow-hidden">
        
        <header class="bg-[#1e60aa] text-white shadow-sm z-40 shrink-0 h-14 flex items-center px-3 sm:px-4 justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-white p-1 rounded shrink-0">
                    <img src="/images/logo.png" alt="Logo" class="h-6 w-6 sm:h-7 sm:w-7 object-contain">
                </div>
                <div class="leading-tight hidden sm:block">
                    <h1 class="font-medium text-sm tracking-wide">REVIEW PEMBAHASAN</h1>
                    <p class="text-[9px] opacity-80 uppercase font-medium mt-0.5 truncate max-w-[200px]">{{ tryout?.title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <Link :href="route('tryout.result', attempt?.id)" class="hidden sm:flex bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded text-[11px] font-medium uppercase tracking-wider transition-colors border border-white/20">
                    Kembali
                </Link>
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-medium">{{ page.props.auth?.user?.name }}</p>
                </div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-1.5 bg-white/10 hover:bg-white/20 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            
            <aside :class="[
                'absolute md:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 transform shadow-xl md:shadow-none', 
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]">
                <div class="p-3 border-b border-slate-100 flex justify-between items-center md:hidden">
                    <span class="text-xs font-medium text-slate-600 uppercase tracking-wider">Navigasi Review</span>
                    <button @click="isSidebarOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-4 pb-4 pt-3 custom-scrollbar">
                    <div class="grid grid-cols-5 gap-1.5 place-content-start">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" 
                                :class="getBknSidebarClass(q, i)" 
                                class="aspect-square w-full text-[11px] font-medium flex items-center justify-center transition-all shadow-sm">
                            {{ i + 1 }}
                        </button>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 text-[10px] space-y-2 shrink-0">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded bg-emerald-50 border border-emerald-200"></div> <span class="text-slate-600">Benar (TKP 5 Poin)</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded bg-rose-50 border border-rose-200"></div> <span class="text-slate-600">Salah (TKP &lt; 5 Poin)</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded bg-white border border-slate-200"></div> <span class="text-slate-600">Kosong</span></div>
                </div>
            </aside>

            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-40 md:hidden backdrop-blur-sm transition-opacity"></div>

            <main class="flex-1 flex flex-col relative overflow-hidden bg-slate-50/50">
                
                <div class="flex flex-col md:flex-row items-stretch px-3 sm:px-4 py-3 shrink-0 gap-3">
                    
                    <div class="flex-1 flex items-stretch bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                        <div class="w-12 sm:w-16 bg-slate-100 shrink-0 relative border-r border-slate-100 flex items-center justify-center">
                            <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover absolute inset-0">
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="min-w-0 flex-1 flex flex-col justify-center p-2.5">
                            <div class="flex justify-between items-start">
                                <div class="truncate pr-2">
                                    <p class="text-[11px] sm:text-xs font-medium text-slate-800 uppercase leading-none truncate">
                                        {{ page.props.auth?.user?.name }}
                                    </p>
                                    <p class="text-[9px] text-slate-500 mt-1 truncate">{{ page.props.auth?.user?.email }}</p>
                                </div>
                                <span class="text-[9px] text-slate-400 border border-slate-200 px-1.5 py-0.5 rounded shrink-0 hidden sm:block">{{ page.props.auth?.user?.username || page.props.auth?.user?.id }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col shrink-0 w-full md:w-auto sm:min-w-[140px] h-auto">
                        <div class="bg-white border border-slate-200 rounded-lg shadow-sm px-4 py-2 flex flex-col items-center justify-center h-full">
                            <span class="text-[9px] font-medium uppercase text-slate-400 tracking-wider">Status Jawaban</span>
                            <div v-if="currentQuestion" class="mt-0.5">
                                <span v-if="isUserCorrectHeader" class="text-[11px] sm:text-xs font-medium text-emerald-600 uppercase">BENAR</span>
                                <span v-else-if="!isUnansweredHeader" class="text-[11px] sm:text-xs font-medium text-rose-500 uppercase">SALAH</span>
                                <span v-else class="text-[11px] sm:text-xs font-medium text-slate-400 uppercase">KOSONG</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-3 sm:px-4 pb-4 custom-scrollbar">
                    <div class="w-full bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col min-h-full">
                        
                        <div class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                            <span class="font-medium text-[11px] sm:text-xs text-slate-600 tracking-wide uppercase">{{ subtestTopic }}</span>
                            
                            <button @click="openReportModal(currentQuestion.id)" type="button" class="inline-flex items-center gap-1 px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded text-[10px] font-bold tracking-wide transition hover:bg-rose-100 active:scale-95">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                                LAPORKAN
                            </button>
                        </div>

                        <div v-if="currentQuestion" class="p-4 sm:p-6 lg:p-8 flex-1">
                            <div v-if="currentQuestion?.image" class="mb-5 max-w-sm mx-auto">
                                <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded-lg border border-slate-200 p-1 bg-white shadow-sm">
                            </div>
                            
                            <div class="flex items-start gap-3 mb-6">
                                <div class="bg-slate-100 text-slate-500 font-medium text-[10px] sm:text-xs px-2 py-1 rounded shrink-0 mt-0.5">
                                    No. {{ currentIndex + 1 }}
                                </div>
                                <div class="text-[11px] sm:text-sm text-slate-700 font-normal leading-relaxed whitespace-pre-wrap">
                                    <span v-html="currentQuestion.content"></span>
                                </div>
                            </div>

                            <div class="space-y-2 max-w-3xl">
                                <div v-for="(option, key) in currentQuestion?.options" :key="key" 
                                     class="flex items-start gap-3 p-3 rounded-lg border transition-all" 
                                     :class="getBknOptionClass(key)">
                                    
                                    <div class="w-6 h-6 rounded-full border flex items-center justify-center shrink-0 mt-0.5 font-medium text-[10px] sm:text-xs" 
                                         :class="getCircleClass(key)">
                                        {{ key.toUpperCase() }}
                                    </div>
                                    
                                    <div class="flex-1">
                                        <div class="flex justify-between gap-3 items-start">
                                            <span class="text-[11px] sm:text-xs text-slate-600 font-normal leading-relaxed pt-1 block">{{ option }}</span>
                                            
                                            <div v-if="isTKP" class="shrink-0 mt-0.5 ml-2 flex items-center gap-2">
                                                <span v-if="checkAnswer(currentQuestion, key).isUserKey" 
                                                      class="text-[9px] font-bold px-2 py-0.5 rounded text-white shadow-sm"
                                                      :class="getTkpPoint(currentQuestion, key) == 5 ? 'bg-emerald-500' : 'bg-rose-500'">
                                                    {{ getTkpPoint(currentQuestion, key) == 5 ? 'BENAR' : 'SALAH' }}
                                                </span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border"
                                                      :class="getTkpPoint(currentQuestion, key) == 5 ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-slate-100 text-slate-600 border-slate-200'">
                                                    {{ getTkpPoint(currentQuestion, key) }} Poin
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2 flex gap-1.5 flex-wrap items-center">
                                            <template v-if="isTKP">
                                                <span v-if="getTkpPoint(currentQuestion, key) == 5" class="text-[9px] font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wide">Poin Maksimal</span>
                                                <span v-if="checkAnswer(currentQuestion, key).isUserKey" class="text-[9px] font-medium px-2 py-0.5 rounded uppercase tracking-wide" :class="getTkpPoint(currentQuestion, key) == 5 ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-rose-500 bg-rose-50 border-rose-200'">Jawaban Anda</span>
                                            </template>
                                            <template v-else>
                                                <span v-if="checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-wide">Kunci Jawaban</span>
                                                <span v-if="checkAnswer(currentQuestion, key).isUserKey && !checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-medium text-rose-500 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded uppercase tracking-wide">Jawaban Anda</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 pt-6 border-t border-slate-200 max-w-3xl">
                                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 sm:p-5 relative">
                                    <h3 class="font-medium text-blue-800 text-[11px] uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Pembahasan
                                    </h3>
                                    <div class="text-[11px] sm:text-xs text-slate-600 leading-relaxed font-normal whitespace-pre-wrap" v-html="currentQuestion.explanation || 'Pembahasan belum tersedia untuk soal ini.'"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-slate-200 p-3 sm:p-4 shrink-0 shadow-sm z-10">
                    <div class="flex justify-between items-center gap-2 sm:gap-3 max-w-4xl mx-auto">
                        <button @click="prev" :disabled="currentIndex === 0" class="flex-[1.2] sm:flex-1 py-2.5 sm:py-3 bg-white border border-slate-200 text-slate-500 rounded-lg text-[10px] sm:text-[11px] font-medium uppercase tracking-wide hover:bg-slate-50 disabled:opacity-50 transition-colors">
                            &laquo; <span class="hidden sm:inline">Sebelumnya</span><span class="sm:hidden">Back</span>
                        </button>
                        <button @click="next" :disabled="currentIndex === (questions.length - 1)" class="flex-[2] sm:flex-1 py-2.5 sm:py-3 bg-[#1e60aa] text-white rounded-lg text-[10px] sm:text-[11px] font-medium uppercase tracking-wide hover:bg-blue-800 disabled:opacity-50 shadow-sm transition-colors flex justify-center items-center gap-1">
                            <span class="hidden sm:inline">Selanjutnya</span><span class="sm:hidden">Next</span> &raquo;
                        </button>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <Teleport to="body">
        <div v-if="showReportModal" class="fixed inset-0 z-[999] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="absolute inset-0" @click="closeReportModal"></div>
            
            <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden animate-in slide-in-from-bottom-10 sm:zoom-in-95 duration-200 z-10">
                
                <div class="p-4 sm:p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Laporkan Kesalahan Soal
                    </h3>
                    <button @click="closeReportModal" class="text-slate-400 hover:text-slate-600 bg-slate-100 p-1.5 rounded-full transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitReport" class="p-4 sm:p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Masalah <span class="text-rose-500">*</span></label>
                        <select v-model="reportForm.reason" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-slate-800" required>
                            <option value="Kunci Jawaban Salah">Kunci Jawaban Salah</option>
                            <option value="Pembahasan Kurang Jelas / Salah">Pembahasan Kurang Jelas / Salah</option>
                            <option value="Soal Tidak Lengkap / Typo">Soal Tidak Lengkap / Typo</option>
                            <option value="Pilihan Ganda Error / Jawaban Ganda">Pilihan Ganda Error / Jawaban Ganda</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jelaskan Letak Kesalahannya</label>
                        <textarea v-model="reportForm.description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none placeholder:text-slate-400 font-medium text-slate-800" placeholder="Contoh: Kunci jawaban tertulis A, namun berdasarkan pembahasan di buku panduan harusnya B..."></textarea>
                    </div>

                    <div class="pt-1">
                        <button type="submit" :disabled="reportForm.processing" class="w-full py-2.5 bg-rose-500 hover:bg-rose-600 disabled:opacity-50 text-white rounded-xl font-bold text-xs sm:text-sm shadow-sm active:scale-95 transition-all flex justify-center items-center gap-2 uppercase tracking-wider">
                            <span v-if="reportForm.processing">Mengirim...</span>
                            <span v-else>Kirim Laporan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>