<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object,
    questions: Array
});

// --- STATE MANAGEMENT ---
const currentNumber = ref(0);
const selectedAnswers = ref({});
const doubtfulQuestions = ref(new Set());
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 5400); 
const isFullScreen = ref(false);

// --- FULLSCREEN LOGIC ---
const toggleFullScreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().then(() => isFullScreen.value = true);
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            isFullScreen.value = false;
        }
    }
};

// --- TIMER LOGIC ---
let timerInterval = null;
const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

const startTimer = () => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else processSubmit(true);
    }, 1000);
};

// --- EXAM LOGIC ---
const currentQuestion = computed(() => props.questions[currentNumber.value]);

const selectAnswer = (option) => {
    selectedAnswers.value[currentQuestion.value.id] = option;
};

const toggleDoubtful = () => {
    const id = currentQuestion.value.id;
    if (doubtfulQuestions.value.has(id)) doubtfulQuestions.value.delete(id);
    else doubtfulQuestions.value.add(id);
};

const finishExam = () => {
    if (confirm('YAKIN INGIN MENGAKHIRI UJIAN? PASTIKAN SEMUA SOAL TELAH DIPERIKSA.')) {
        processSubmit();
    }
};

const processSubmit = (isAuto = false) => {
    if (isAuto) alert('WAKTU HABIS! SISTEM AKAN MENYIMPAN JAWABAN ANDA SECARA OTOMATIS.');
    
    router.post(route('tryout.finish', props.tryout.id), {
        answers: selectedAnswers.value
    }, {
        onBefore: () => {
            clearInterval(timerInterval);
            if (document.fullscreenElement) document.exitFullscreen();
        }
    });
};

onMounted(() => {
    startTimer();
    document.addEventListener('contextmenu', e => e.preventDefault());
});

onUnmounted(() => {
    clearInterval(timerInterval);
    document.removeEventListener('contextmenu', e => e.preventDefault());
});
</script>

<template>
    <Head :title="'UJIAN: ' + tryout.title" />

    <div class="fixed inset-0 bg-[#f8fafc] flex flex-col z-[100] overflow-hidden select-none font-sans text-gray-900">
        
        <header class="h-12 bg-gray-900 px-6 flex justify-between items-center shrink-0 shadow-2xl z-50">
            <div class="flex items-center gap-4">
                <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center font-black text-[9px] text-white">CAT</div>
                <h1 class="font-black text-white uppercase text-[9px] tracking-widest leading-none">{{ tryout.title }}</h1>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3 bg-white/5 px-4 py-1 rounded-full border border-white/10">
                    <span class="text-[7px] font-black text-gray-400 uppercase tracking-widest">SISA WAKTU</span>
                    <span :class="timeLeft < 300 ? 'text-red-400 animate-pulse' : 'text-white'" class="font-black text-base tabular-nums tracking-tighter">
                        {{ formatTime(timeLeft) }}
                    </span>
                </div>
                <button @click="toggleFullScreen" class="text-white opacity-40 hover:opacity-100 transition-opacity">
                    <svg v-if="!isFullScreen" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                </button>
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">
            
            <div class="flex-1 flex flex-col px-6 pt-2 pb-4 overflow-hidden bg-white border-r border-gray-100">
                
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3">
                    
                    <div class="flex items-center justify-between bg-white p-2 rounded-xl border border-gray-100 shadow-sm mt-1">
                        <div class="flex items-center gap-3">
                            <span class="bg-gray-900 text-white w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shadow-md shadow-gray-200">
                                {{ currentNumber + 1 }}
                            </span>
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[9px] font-black uppercase tracking-widest border border-indigo-100">
                                {{ currentQuestion.type }}
                            </span>
                        </div>
                        <button @click="toggleDoubtful" 
                                :class="doubtfulQuestions.has(currentQuestion.id) ? 'bg-amber-500 text-white shadow-amber-100' : 'bg-amber-50 text-amber-600 border border-amber-200'"
                                class="px-4 py-1.5 rounded-lg text-[8px] font-black uppercase tracking-widest transition-all active:scale-95">
                            {{ doubtfulQuestions.has(currentQuestion.id) ? '✓ YAKIN' : '⚠️ RAGU' }}
                        </button>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
                        <div class="relative z-10 space-y-4">
                            <div v-if="currentQuestion.image" class="inline-block p-1 bg-gray-50 rounded-xl border border-gray-100 mb-1">
                                <img :src="'/storage/' + currentQuestion.image" class="max-h-40 object-contain rounded-lg" />
                            </div>
                            <p class="text-[12px] md:text-[13px] font-bold text-gray-800 leading-relaxed uppercase tracking-tight">
                                {{ currentQuestion.question_text || currentQuestion.content }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-1.5 pt-1">
                        <button v-for="(val, key) in (currentQuestion.options || {
                            'a': currentQuestion.option_a, 'b': currentQuestion.option_b, 'c': currentQuestion.option_c, 'd': currentQuestion.option_d, 'e': currentQuestion.option_e
                        })" :key="key"
                            @click="selectAnswer(key)"
                            :class="[selectedAnswers[currentQuestion.id] === key ? 'border-indigo-600 bg-indigo-50' : 'border-gray-50 bg-gray-50 hover:border-indigo-100']"
                            class="flex items-center gap-3 p-2 rounded-xl border-2 text-left transition-all active:scale-[0.98] group shadow-sm"
                        >
                            <span :class="[selectedAnswers[currentQuestion.id] === key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-400']" 
                                  class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center font-black uppercase border border-gray-100 text-[9px] transition-all">
                                {{ key }}
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-wide leading-tight" :class="selectedAnswers[currentQuestion.id] === key ? 'text-indigo-900' : 'text-gray-600'">
                                {{ val }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="shrink-0 pt-3 flex gap-4 border-t border-gray-50 mt-1">
                    <button @click="currentNumber--" :disabled="currentNumber === 0" 
                        class="flex-1 py-2.5 bg-white border border-gray-200 rounded-xl font-black text-[8px] uppercase tracking-widest text-gray-400 disabled:opacity-20 hover:bg-gray-50 transition-all shadow-sm">
                        ← SEBELUMNYA
                    </button>
                    <button @click="currentNumber++" :disabled="currentNumber === questions.length - 1" 
                        class="flex-1 py-2.5 bg-gray-900 text-white rounded-xl font-black text-[8px] uppercase tracking-widest hover:bg-indigo-600 transition-all disabled:opacity-20 shadow-lg">
                        SELANJUTNYA →
                    </button>
                </div>
            </div>

            <div class="w-60 bg-gray-50 p-4 flex flex-col shrink-0">
                <div class="flex items-center justify-between mb-4 px-1">
                    <p class="text-[7px] font-black text-gray-400 uppercase tracking-[0.3em]">NAVIGASI</p>
                    <span class="px-2 py-0.5 bg-gray-200 rounded text-[7px] font-black text-gray-500 uppercase">{{ questions.length }} BUTIR</span>
                </div>
                
                <div class="grid grid-cols-6 gap-1.5 overflow-y-auto flex-1 pr-1 custom-scrollbar">
                    <button v-for="(q, index) in questions" :key="q.id" 
                        @click="currentNumber = index"
                        :class="[
                            currentNumber === index ? 'border-indigo-600' : 'border-transparent',
                            doubtfulQuestions.has(q.id) ? 'bg-amber-500 text-white shadow-amber-100' : (selectedAnswers[q.id] ? 'bg-indigo-600 text-white shadow-indigo-100' : 'bg-white text-gray-400')
                        ]"
                        class="w-full aspect-square rounded-full flex items-center justify-center border-2 transition-all text-[8px] font-black shadow-sm"
                    >
                        {{ index + 1 }}
                    </button>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-200">
                    <button @click="finishExam" 
                            class="w-full py-3.5 bg-red-600 text-white rounded-xl font-black text-[9px] uppercase tracking-[0.2em] shadow-xl hover:bg-red-700 transition-all active:scale-95">
                        AKHIRI UJIAN 🏁
                    </button>
                </div>
            </div>
        </main>

    </div>
</template>

<style scoped>
/* No Italics */
* { font-style: normal !important; }

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

:deep(body) { overflow: hidden !important; }
</style>