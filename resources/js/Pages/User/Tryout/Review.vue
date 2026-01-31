<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object,
    attempt: Object,
    mode: {
        type: String,
        default: 'bkn' // 'bkn' atau 'modern'
    }
});

const page = usePage();

// --- STATE ---
const currentIndex = ref(0);
const isSidebarOpen = ref(false);

// --- COMPUTED ---
const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

const subtestTopic = computed(() => {
    const type = currentQuestion.value?.type;
    if (type === 'TWK') return 'Nasionalisme & Bela Negara';
    if (type === 'TIU') return 'Kemampuan Verbal & Logika';
    if (type === 'TKP') return 'Pelayanan Publik & Jejaring Kerja';
    return '';
});

// Helper: Cek status jawaban
const isCorrect = (q) => q.user_answer === q.correct_answer;

// --- METHODS ---
const goTo = (index) => { currentIndex.value = index; isSidebarOpen.value = false; };
const next = () => { 
    if (currentIndex.value < (props.questions?.length - 1)) currentIndex.value++; 
    else currentIndex.value = 0; 
};
const prev = () => { if (currentIndex.value > 0) currentIndex.value--; };

// --- LOGIKA WARNA (BKN MODE) ---
const getBknSidebarClass = (q, index) => {
    if (currentIndex.value === index) return 'bg-[#1e60aa] text-white font-black ring-1 ring-blue-300 rounded-sm z-10 shadow-md';
    if (q.user_answer === q.correct_answer) return 'bg-emerald-600 text-white border-emerald-700 rounded-full font-bold';
    return 'bg-red-600 text-white border-red-700 hover:bg-red-700 rounded-sm font-bold';
};

const getBknOptionClass = (key) => {
    const q = currentQuestion.value;
    if (key === q.correct_answer) return 'bg-emerald-100 border-emerald-500 ring-1 ring-emerald-500';
    if (key === q.user_answer && key !== q.correct_answer) return 'bg-rose-100 border-rose-500 ring-1 ring-rose-500';
    return 'bg-white border-slate-200 opacity-70';
};

// --- LOGIKA WARNA (MODERN MODE) ---
const getModernOptionClass = (key) => {
    const q = currentQuestion.value;
    if (key === q.correct_answer) return 'bg-emerald-50 border-emerald-500 ring-1 ring-emerald-500 shadow-sm z-10';
    if (key === q.user_answer && key !== q.correct_answer) return 'bg-rose-50 border-rose-500 ring-1 ring-rose-500 shadow-sm z-10';
    return 'bg-white border-slate-200 opacity-60 hover:opacity-100';
};

const getModernSidebarClass = (q, index) => {
    let base = "h-8 w-8 text-[10px] font-bold rounded-lg flex items-center justify-center transition-all duration-200 border ";
    if (currentIndex.value === index) return base + "bg-[#1e60aa] text-white border-[#1e60aa] ring-2 ring-blue-200 shadow-md transform scale-110 z-10";
    if (q.user_answer === q.correct_answer) return base + "bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-200";
    return base + "bg-rose-100 text-rose-700 border-rose-200 hover:bg-rose-200";
};
</script>

<template>
    <Head :title="'Pembahasan: ' + (tryout?.title || 'Loading...')" />

    <div v-if="mode === 'modern'" class="min-h-screen bg-slate-50 font-sans text-slate-700 flex flex-col">
        
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm/50 backdrop-blur-md bg-white/90">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="bg-blue-50 p-1.5 rounded-lg border border-blue-100">
                        <img src="/images/logo.png" alt="Logo" class="h-8 w-8 object-contain">
                    </Link>
                    <div class="hidden md:block leading-tight">
                        <h1 class="font-bold text-slate-900 text-sm tracking-tight">PEMBAHASAN SOAL</h1>
                        <p class="text-[10px] font-medium text-slate-500 uppercase tracking-wider">{{ tryout?.title }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('tryout.result', attempt?.id)" class="group flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-full text-xs font-bold uppercase tracking-wide hover:bg-slate-50 transition-all">
                        <span>Kembali</span>
                    </Link>
                    <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-lg">☰</button>
                </div>
            </div>
        </header>

        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8 flex gap-6 lg:gap-8 relative items-start">
            <main class="flex-1 w-full min-w-0 space-y-6">
                <div v-if="currentQuestion" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap justify-between items-center bg-slate-50/50 gap-3">
                        <div class="flex items-center gap-3">
                            <span class="bg-[#1e60aa] text-white text-xs font-black px-2.5 py-1 rounded-md shadow-sm">NO. {{ currentIndex + 1 }}</span>
                            <span class="text-xs font-bold text-slate-500 uppercase">{{ currentQuestion.type }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span v-if="isCorrect(currentQuestion)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase border border-emerald-200">Benar</span>
                            <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold uppercase border border-rose-200">Salah</span>
                        </div>
                    </div>
                    <div class="p-6 md:p-8">
                        <div v-if="currentQuestion.image" class="mb-6 max-w-lg"><img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded-lg border"></div>
                        <div class="prose prose-sm max-w-none text-slate-800 font-medium leading-relaxed mb-8" v-html="currentQuestion.content"></div>
                        <div class="space-y-3">
                            <div v-for="(option, key) in currentQuestion.options" :key="key" class="relative flex items-start gap-4 p-4 rounded-xl border-2 transition-all" :class="getModernOptionClass(key)">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-colors bg-slate-100 text-slate-500">{{ key.toUpperCase() }}</div>
                                <div class="flex-1 text-sm pt-0.5">{{ option }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-blue-50/50 rounded-2xl border-l-4 border-blue-500 shadow-sm p-6 md:p-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">💡 Pembahasan</h3>
                    <div class="prose prose-sm max-w-none text-slate-600 leading-loose" v-html="currentQuestion?.explanation || 'Tidak ada pembahasan.'"></div>
                </div>
                <div class="flex justify-between items-center pt-4">
                    <button @click="prev" :disabled="currentIndex === 0" class="px-6 py-3 rounded-xl font-bold text-xs uppercase border bg-white disabled:opacity-50">Previous</button>
                    <button @click="next" class="px-6 py-3 rounded-xl font-bold text-xs uppercase bg-[#1e60aa] text-white shadow-md">Next</button>
                </div>
            </main>
            <aside :class="['fixed lg:sticky lg:top-24 inset-y-0 right-0 z-50 w-72 bg-white lg:bg-transparent lg:border-0 border-l shadow-2xl lg:shadow-none transition-transform duration-300 transform lg:transform-none lg:block flex flex-col', isSidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0']">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-full lg:h-auto lg:max-h-[calc(100vh-8rem)]">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50"><h3 class="font-bold text-slate-700 text-sm">Navigasi</h3></div>
                    <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                        <div class="grid grid-cols-5 gap-2">
                            <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)" :class="getModernSidebarClass(q, i)">{{ i + 1 }}</button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div v-else class="h-screen flex flex-col font-sans text-slate-800 bg-[#eef2f7] overflow-hidden">
        
        <header class="bg-[#1e60aa] text-white shadow-md z-50 shrink-0 h-16 flex items-center px-4 justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white p-1 rounded-md shrink-0"><img src="/images/logo.png" alt="Logo" class="h-9 w-9 object-contain"></div>
                <div class="leading-tight hidden md:block">
                    <h1 class="font-bold text-lg tracking-wide">PEMBAHASAN SOAL</h1>
                    <p class="text-[10px] opacity-80 uppercase font-medium">{{ tryout?.title }}</p>
                </div>
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
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-sm bg-red-600"></div> <span>Salah / Kosong</span></div>
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-sm bg-[#1e60aa] ring-1 ring-blue-300"></div> <span>Aktif</span></div>
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
                                <span v-if="isCorrect(currentQuestion)" class="text-sm font-black text-emerald-600 uppercase">BENAR</span>
                                <span v-else class="text-sm font-black text-rose-600 uppercase">SALAH</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 pb-4 custom-scrollbar">
                    <div class="w-full bg-white rounded-lg shadow-sm border border-slate-300 overflow-hidden">
                        <div class="bg-[#1e60aa] text-white px-5 py-2.5 flex items-center justify-between">
                            <span class="font-bold text-xs tracking-wide uppercase">{{ subtestTopic }}</span>
                        </div>
                        <div v-if="currentQuestion" class="p-5 md:p-8">
                            <div v-if="currentQuestion?.image" class="mb-5 max-w-md mx-auto"><img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded border p-1 bg-slate-50 shadow-sm"></div>
                            
                            <div class="flex items-start gap-2 mb-6">
                                <span class="font-bold text-slate-900 text-[10px] md:text-xs shrink-0 mt-0.5">No. {{ currentIndex + 1 }}</span>
                                <div class="text-[10px] md:text-xs text-slate-800 font-medium leading-relaxed whitespace-pre-wrap" v-html="currentQuestion.content"></div>
                            </div>

                            <div class="space-y-2.5">
                                <div v-for="(option, key) in currentQuestion?.options" :key="key" class="flex items-start gap-3 p-2.5 rounded border-2 transition-all relative overflow-hidden" :class="getBknOptionClass(key)">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]" 
                                         :class="key === currentQuestion.correct_answer ? 'bg-emerald-500 border-emerald-500 text-white' : (key === currentQuestion.user_answer ? 'bg-rose-500 border-rose-500 text-white' : 'border-slate-300 text-slate-400')">
                                        {{ key.toUpperCase() }}
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-[10px] md:text-xs text-slate-700 pt-0.5 block">{{ option }}</span>
                                        <div class="mt-1.5 flex gap-2">
                                            <span v-if="key === currentQuestion.correct_answer" class="text-[9px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-200 px-1.5 py-0.5 rounded">Kunci Jawaban</span>
                                            <span v-if="key === currentQuestion.user_answer && key !== currentQuestion.correct_answer" class="text-[9px] font-bold text-rose-700 bg-rose-100 border border-rose-200 px-1.5 py-0.5 rounded">Jawaban Anda</span>
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