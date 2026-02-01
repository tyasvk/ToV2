<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

// --- 1. SUPER ROBUST DATA GETTER ---
// Fungsi ini mencari jawaban user dimanapun ia berada (root object, pivot, atau attempt_answers)
const getUserAnswer = (question) => {
    if (!question) return null;
    
    // Cek 1: Langsung di properti question (paling umum)
    if (question.user_answer !== undefined && question.user_answer !== null) {
        return question.user_answer;
    }
    
    // Cek 2: Jika menggunakan relasi many-to-many (pivot)
    if (question.pivot && question.pivot.answer) {
        return question.pivot.answer;
    }

    // Cek 3: Kadang fieldnya bernama 'answer' saja
    if (question.answer !== undefined && question.answer !== null) {
        return question.answer;
    }

    return null;
};

// --- 2. LOGIC MODIFIED ---
const checkAnswer = (q, key = null) => {
    // Ambil jawaban user menggunakan helper di atas
    const userAns = getUserAnswer(q);
    const correctAns = q.correct_answer;

    // Jika sedang mengecek opsi spesifik (untuk pewarnaan tombol A,B,C,D)
    if (key !== null) {
        const isCorrectKey = String(key).trim().toLowerCase() === String(correctAns).trim().toLowerCase();
        const isUserKey = userAns && String(key).trim().toLowerCase() === String(userAns).trim().toLowerCase();
        return { isCorrectKey, isUserKey };
    }

    // Jika mengecek status soal secara umum (Benar/Salah)
    if (!userAns) return false;
    return String(userAns).trim().toLowerCase() === String(correctAns).trim().toLowerCase();
};

const hasAnswered = (q) => {
    const ans = getUserAnswer(q);
    return ans !== null && ans !== undefined && String(ans).trim() !== '';
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
    const type = currentQuestion.value?.type;
    if (type === 'TWK') return 'Nasionalisme & Bela Negara';
    if (type === 'TIU') return 'Kemampuan Verbal & Logika';
    if (type === 'TKP') return 'Pelayanan Publik & Jejaring Kerja';
    return type || 'Tes Kompetensi';
});

const isUserCorrectHeader = computed(() => {
    const q = currentQuestion.value;
    return q && checkAnswer(q);
});

const isUnansweredHeader = computed(() => {
    const q = currentQuestion.value;
    return !q || !hasAnswered(q);
});

// --- METHODS ---
const goTo = (index) => { currentIndex.value = index; isSidebarOpen.value = false; };
const next = () => { if (currentIndex.value < (props.questions?.length - 1)) currentIndex.value++; };
const prev = () => { if (currentIndex.value > 0) currentIndex.value--; };

// --- STYLING HELPERS (MODERN) ---
const getModernOptionClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);

    if (isCorrectKey) return 'border-emerald-500 bg-emerald-50 ring-1 ring-emerald-500/50'; 
    if (isUserKey && !isCorrectKey) return 'border-rose-500 bg-rose-50 ring-1 ring-rose-500/50'; 
    return 'border-slate-200 hover:border-slate-300 bg-white opacity-80'; 
};

const getModernSidebarClass = (q, index) => {
    let base = "w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold transition-all border ";
    
    if (currentIndex.value === index) {
        return base + "bg-slate-800 text-white border-slate-800 shadow-md transform scale-110 z-20";
    }
    if (checkAnswer(q)) {
        return base + "bg-emerald-100 text-emerald-700 border-emerald-200";
    }
    if (hasAnswered(q)) {
        return base + "bg-rose-100 text-rose-700 border-rose-200";
    }
    return base + "bg-white text-slate-400 border-slate-200";
};

// --- STYLING HELPERS (BKN) ---
const getBknSidebarClass = (q, index) => {
    if (currentIndex.value === index) {
        return 'bg-[#1e60aa] text-white font-black ring-1 ring-blue-300 rounded-sm z-10 shadow-md scale-110';
    }
    if (checkAnswer(q)) {
        return 'bg-emerald-600 text-white border-emerald-700 rounded-full font-bold';
    }
    if (hasAnswered(q)) {
        return 'bg-red-600 text-white border-red-700 rounded-sm font-bold';
    }
    return 'bg-slate-200 text-slate-500 border-slate-300 rounded-sm font-bold opacity-80';
};

const getBknOptionClass = (key) => {
    const q = currentQuestion.value;
    const { isCorrectKey, isUserKey } = checkAnswer(q, key);

    if (isCorrectKey) return 'bg-emerald-100 border-emerald-500 ring-1 ring-emerald-500';
    if (isUserKey && !isCorrectKey) return 'bg-rose-100 border-rose-500 ring-1 ring-rose-500';
    return 'bg-white border-slate-200 opacity-70';
};
</script>

<template>
    <Head :title="'Pembahasan: ' + (tryout?.title || 'Loading...')" />

    <div v-if="currentMode === 'modern'" class="min-h-screen bg-[#F8FAFC] font-sans text-slate-700 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm/30 backdrop-blur-md bg-white/90">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="bg-slate-900 p-1.5 rounded-lg border border-slate-800 shadow-sm">
                        <img src="/images/logo.png" alt="Logo" class="h-7 w-7 object-contain brightness-0 invert">
                    </Link>
                    <div>
                        <h1 class="font-bold text-slate-900 text-sm leading-tight">Review Hasil</h1>
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wider">{{ tryout?.title }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('tryout.result', attempt?.id)" class="group hidden sm:flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-full text-xs font-bold uppercase tracking-wide hover:bg-slate-50 hover:border-slate-300 transition-all">
                        <span>Kembali</span>
                    </Link>
                    <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg></button>
                </div>
            </div>
        </header>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 flex gap-8 relative items-start">
            <main class="flex-1 w-full min-w-0 space-y-6">
                <div v-if="currentQuestion" class="bg-white rounded-2xl border-2 shadow-sm overflow-hidden transition-colors duration-300" :class="isUserCorrectHeader ? 'border-emerald-400' : (isUnansweredHeader ? 'border-slate-200' : 'border-rose-400')">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <span class="bg-slate-800 text-white text-xs font-black px-3 py-1 rounded-lg shadow-md shadow-slate-200">SOAL {{ currentIndex + 1 }}</span>
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">{{ currentQuestion.type }}</span>
                        </div>
                        <div v-if="isUserCorrectHeader" class="flex items-center gap-2 text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            <span class="text-[10px] font-bold uppercase">Jawaban Benar</span>
                        </div>
                        <div v-else-if="!isUnansweredHeader" class="flex items-center gap-2 text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            <span class="text-[10px] font-bold uppercase">Jawaban Salah</span>
                        </div>
                        <div v-else class="flex items-center gap-2 text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200"><span class="text-[10px] font-bold uppercase">Tidak Dijawab</span></div>
                    </div>
                    <div class="p-6 md:p-8">
                        <div v-if="currentQuestion.image" class="mb-6"><img :src="'/storage/' + currentQuestion.image" class="max-h-80 w-auto rounded-lg border border-slate-200 shadow-sm"></div>
                        <div class="prose prose-slate prose-sm max-w-none font-medium text-slate-800 leading-8 mb-8" v-html="currentQuestion.content"></div>
                        <div class="space-y-3">
                            <div v-for="(option, key) in currentQuestion.options" :key="key" class="relative flex items-start gap-4 p-4 rounded-xl border-2 transition-all" :class="getModernOptionClass(key)">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors" :class="checkAnswer(currentQuestion, key).isCorrectKey ? 'bg-emerald-500 text-white' : (checkAnswer(currentQuestion, key).isUserKey ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-500')">{{ key.toUpperCase() }}</div>
                                <div class="flex-1 text-sm pt-1 leading-relaxed">{{ option }}</div>
                                <div v-if="checkAnswer(currentQuestion, key).isCorrectKey" class="text-emerald-500 shrink-0 self-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg></div>
                                <div v-else-if="checkAnswer(currentQuestion, key).isUserKey" class="text-rose-500 shrink-0 self-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-indigo-50/50 rounded-2xl border border-indigo-100 p-6 md:p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-100 rounded-full blur-2xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
                    <h3 class="flex items-center gap-2 text-indigo-900 font-bold text-lg mb-4 relative z-10"><span class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-lg">💡</span> Pembahasan</h3>
                    <div class="prose prose-sm max-w-none text-slate-700 leading-loose relative z-10">
                        <div v-if="currentQuestion?.explanation" v-html="currentQuestion.explanation"></div>
                        <p v-else class="text-slate-400 italic">Tidak ada pembahasan khusus untuk soal ini.</p>
                    </div>
                </div>
                <div class="flex justify-between pt-4">
                    <button @click="prev" :disabled="currentIndex === 0" class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">&laquo; Sebelumnya</button>
                    <button @click="next" :disabled="currentIndex === (questions.length - 1)" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-wide hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-slate-200">Selanjutnya &raquo;</button>
                </div>
            </main>
            <aside :class="['fixed lg:sticky lg:top-24 inset-y-0 right-0 z-50 w-80 bg-white lg:bg-transparent lg:border-0 border-l shadow-2xl lg:shadow-none transition-transform duration-300 transform lg:transform-none lg:block flex flex-col', isSidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0']">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col h-full lg:h-auto lg:max-h-[calc(100vh-8rem)]">
                    <div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-800">Navigasi Soal</h3><p class="text-xs text-slate-400 mt-1">Lompat ke nomor soal</p></div>
                    <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
                        <div class="grid grid-cols-5 gap-3">
                            <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" :class="getModernSidebarClass(q, i)">{{ i + 1 }}</button>
                        </div>
                    </div>
                    <div class="p-5 bg-slate-50 border-t border-slate-100 text-xs font-medium space-y-3">
                        <div class="flex items-center gap-3"><div class="w-3 h-3 rounded-full bg-emerald-500"></div> <span class="text-slate-600">Jawaban Benar</span></div>
                        <div class="flex items-center gap-3"><div class="w-3 h-3 rounded-full bg-rose-500"></div> <span class="text-slate-600">Jawaban Salah</span></div>
                        <div class="flex items-center gap-3"><div class="w-3 h-3 rounded-full bg-white border border-slate-300"></div> <span class="text-slate-600">Tidak Dijawab</span></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div v-else class="h-screen flex flex-col font-sans text-slate-800 bg-[#eef2f7] overflow-hidden">
        <header class="bg-[#1e60aa] text-white shadow-md z-50 shrink-0 h-16 flex items-center px-4 justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white p-1 rounded-md shrink-0"><img src="/images/logo.png" alt="Logo" class="h-9 w-9 object-contain"></div>
                <div class="leading-tight hidden md:block"><h1 class="font-bold text-lg tracking-wide">PEMBAHASAN SOAL</h1><p class="text-[10px] opacity-80 uppercase font-medium">{{ tryout?.title }}</p></div>
            </div>
            <div class="flex items-center gap-4">
                <Link :href="route('tryout.result', attempt?.id)" class="hidden sm:flex bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition border border-white/10">Kembali</Link>
                <div class="text-right hidden sm:block"><p class="text-sm font-bold">{{ page.props.auth?.user?.name }}</p></div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-2 bg-white/20 rounded">☰</button>
            </div>
        </header>
        <div class="flex flex-1 overflow-hidden relative">
            <aside :class="['absolute md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-300 flex flex-col transition-transform duration-300 transform shadow-lg md:shadow-none', isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">
                <div class="flex-1 overflow-y-auto px-7 py-4 custom-scrollbar">
                    <div class="grid grid-cols-5 gap-0.5 place-content-start">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" :class="getBknSidebarClass(q, i)" class="h-6 w-full text-[9px] flex items-center justify-center transition-all shadow-sm border border-transparent">{{ i + 1 }}</button>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 text-[10px] space-y-2">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-emerald-600"></div> <span>Benar</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-sm bg-red-600"></div> <span>Salah</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-sm bg-slate-200 border-slate-300"></div> <span>Tidak Dijawab</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-sm bg-[#1e60aa] ring-1 ring-blue-300"></div> <span>Sedang Dilihat</span></div>
                </div>
            </aside>
            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 md:hidden"></div>
            <main class="flex-1 flex flex-col relative overflow-hidden bg-[#eef2f7]">
                <div class="flex flex-col md:flex-row items-stretch px-4 py-3 shrink-0 gap-3">
                    <div class="flex-1 flex items-stretch bg-white rounded-r-lg border border-slate-300 shadow-sm overflow-hidden">
                        <div class="w-16 bg-slate-200 shrink-0 relative border-r border-slate-100">
                            <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover absolute inset-0">
                            <div v-else class="w-full h-full flex items-center justify-center text-[8px] font-bold text-slate-400 absolute inset-0">IMG</div>
                        </div>
                        <div class="min-w-0 flex-1 flex flex-col justify-center text-center p-1.5">
                            <p class="text-[10px] font-bold text-slate-900 uppercase leading-none truncate">{{ page.props.auth?.user?.name }} <span class="font-normal opacity-70 ml-1">({{ page.props.auth?.user?.username || page.props.auth?.user?.id }})</span></p>
                            <p class="text-[9px] text-slate-500 leading-none mt-1 truncate">{{ page.props.auth?.user?.email }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 shrink-0 w-full md:w-auto min-w-[140px]">
                        <div class="bg-white border border-slate-300 rounded-lg shadow-sm px-4 py-1.5 flex flex-col items-center justify-center h-full">
                            <span class="text-[9px] font-bold uppercase text-slate-400 tracking-wider">Status Jawaban</span>
                            <div v-if="currentQuestion" class="mt-0.5">
                                <span v-if="isUserCorrectHeader" class="text-sm font-black text-emerald-600 uppercase">BENAR</span>
                                <span v-else-if="!isUnansweredHeader" class="text-sm font-black text-rose-600 uppercase">SALAH</span>
                                <span v-else class="text-sm font-black text-slate-400 uppercase">KOSONG</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto px-4 pb-4 custom-scrollbar">
                    <div class="w-full bg-white rounded-lg shadow-sm border border-slate-300 overflow-hidden">
                        <div class="bg-[#1e60aa] text-white px-5 py-2.5 flex items-center justify-between"><span class="font-bold text-xs tracking-wide uppercase">{{ subtestTopic }}</span></div>
                        <div v-if="currentQuestion" class="p-5 md:p-8">
                            <div v-if="currentQuestion?.image" class="mb-5 max-w-md mx-auto"><img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded border p-1 bg-slate-50 shadow-sm"></div>
                            <div class="flex items-start gap-2 mb-6"><span class="font-bold text-slate-900 text-[10px] md:text-xs shrink-0 mt-0.5">No. {{ currentIndex + 1 }}</span><div class="text-[10px] md:text-xs text-slate-800 font-medium leading-relaxed whitespace-pre-wrap" v-html="currentQuestion.content"></div></div>
                            <div class="space-y-2.5">
                                <div v-for="(option, key) in currentQuestion?.options" :key="key" class="flex items-start gap-3 p-2.5 rounded border-2 transition-all relative overflow-hidden" :class="getBknOptionClass(key)">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]" :class="checkAnswer(currentQuestion, key).isCorrectKey ? 'bg-emerald-500 border-emerald-500 text-white' : (checkAnswer(currentQuestion, key).isUserKey ? 'bg-rose-500 border-rose-500 text-white' : 'border-slate-300 text-slate-400')">{{ key.toUpperCase() }}</div>
                                    <div class="flex-1">
                                        <span class="text-[10px] md:text-xs text-slate-700 pt-0.5 block">{{ option }}</span>
                                        <div class="mt-1.5 flex gap-2">
                                            <span v-if="checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded">Kunci Jawaban</span>
                                            <span v-if="checkAnswer(currentQuestion, key).isUserKey && !checkAnswer(currentQuestion, key).isCorrectKey" class="text-[9px] font-bold text-rose-700 bg-rose-100 border border-rose-200 px-1.5 py-0.5 rounded">Jawaban Anda</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8 pt-6 border-t border-slate-200">
                                <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-5 relative">
                                    <h3 class="font-bold text-indigo-900 text-xs uppercase tracking-wider mb-2">Pembahasan Soal</h3>
                                    <div class="text-[10px] md:text-xs text-slate-700 leading-relaxed whitespace-pre-wrap" v-html="currentQuestion.explanation || 'Pembahasan belum tersedia.'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border-t border-slate-300 py-3 px-4 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                    <div class="flex justify-between items-center gap-3">
                        <button @click="prev" :disabled="currentIndex === 0" class="flex items-center gap-2 px-5 py-2 bg-white border border-slate-300 text-slate-600 rounded font-bold text-[10px] uppercase hover:bg-slate-50 disabled:opacity-50 transition">&laquo; Sebelumnya</button>
                        <button @click="next" class="flex items-center gap-2 px-5 py-2 bg-[#1e60aa] text-white rounded font-bold text-[10px] uppercase hover:bg-blue-800 shadow-md transition">Selanjutnya &raquo;</button>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>