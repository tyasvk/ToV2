<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object
});

// --- STATE MANAGEMENT ---
const currentNumber = ref(0);
const selectedAnswers = ref({});
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 6600); 
const showConfirmModal = ref(false); // State untuk Pop-up

// --- MAPPING SUBTES ---
const subtestName = computed(() => {
    const type = currentQuestion.value?.type?.toUpperCase();
    if (type === 'TWK') return 'Tes Wawasan Kebangsaan - TWK';
    if (type === 'TIU') return 'Tes Intelegensia Umum - TIU';
    if (type === 'TKP') return 'Tes Karakteristik Pribadi - TKP';
    return type || 'Umum';
});

// --- INFO STATISTIK ---
const answeredCount = computed(() => Object.keys(selectedAnswers.value).length);
const unansweredCount = computed(() => props.questions.length - answeredCount.value);
const currentQuestion = computed(() => props.questions[currentNumber.value]);

// --- TIMER LOGIC ---
let timerInterval = null;
const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
};

const startTimer = () => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else processSubmit(true);
    }, 1000);
};

// --- ACTION LOGIC ---
const selectAnswer = (option) => {
    selectedAnswers.value[currentQuestion.value.id] = option;
};

const nextQuestion = () => {
    if (currentNumber.value < props.questions.length - 1) {
        currentNumber.value++;
    }
};

const finishExam = () => {
    showConfirmModal.value = true;
};

const processSubmit = (isAuto = false) => {
    router.post(route('tryout.finish', props.tryout.id), {
        answers: selectedAnswers.value
    }, {
        onBefore: () => {
            clearInterval(timerInterval);
        }
    });
};

onMounted(() => {
    startTimer();
    document.body.style.overflow = 'hidden';
});

onUnmounted(() => {
    clearInterval(timerInterval);
});
</script>

<template>
    <Head :title="'CAT BKN - ' + tryout.title" />

    <div class="fixed inset-0 bg-[#f8fafc] flex flex-col font-sans text-[#334155] select-none overflow-hidden">
        
        <header class="h-16 bg-[#334155] border-b-4 border-[#d4ae28] px-6 flex justify-between items-center shrink-0 shadow-md z-50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-contain bg-no-repeat bg-center" 
                     style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Garuda_Pancasila_Coat_of_Arms_of_Indonesia.svg/200px-Garuda_Pancasila_Coat_of_Arms_of_Indonesia.svg.png');">
                </div>
                <div class="text-white leading-none">
                    <h1 class="font-black text-lg tracking-tighter uppercase">CAT BKN</h1>
                    <p class="text-[7px] font-black tracking-[0.2em] opacity-60 mt-1 uppercase">Computer Assisted Test</p>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <div class="hidden md:flex items-center gap-6 text-white border-r border-white/20 pr-6">
                    <div class="text-center">
                        <p class="text-[7px] font-black opacity-50 uppercase tracking-widest">Batas Waktu</p>
                        <p class="text-[10px] font-black uppercase">{{ tryout.duration_minutes }} Menit</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[7px] font-black opacity-50 uppercase tracking-widest">Jumlah Soal</p>
                        <p class="text-[10px] font-black uppercase">{{ questions.length }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[7px] font-black text-emerald-300 uppercase tracking-widest">Soal Dijawab</p>
                        <p class="text-[10px] font-black uppercase">{{ answeredCount }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-[7px] font-black text-rose-300 uppercase tracking-widest">Belum Dijawab</p>
                        <p class="text-[10px] font-black uppercase">{{ unansweredCount }}</p>
                    </div>
                </div>

                <button @click="finishExam" 
                    class="bg-[#d4ae28] hover:bg-yellow-600 text-white px-5 py-2.5 rounded-lg font-black text-[10px] uppercase tracking-widest transition-all shadow-sm active:scale-95">
                    SELESAI UJIAN 🏁
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-3">
            
            <div class="max-w-4xl mx-auto space-y-3">
                
                <div class="bg-white border border-slate-200 rounded-xl p-3 flex gap-5 items-center shadow-sm">
                    <div class="w-12 h-16 bg-slate-50 border border-slate-200 rounded-md overflow-hidden shrink-0 shadow-inner">
                        <img v-if="user.profile_photo_url" :src="user.profile_photo_url" class="w-full h-full object-cover grayscale-[20%]">
                        <div v-else class="w-full h-full flex items-center justify-center text-[6px] font-black text-slate-300 uppercase">Foto</div>
                    </div>
                    <div class="flex-1 space-y-1">
                        <div class="flex gap-2 text-[9px] font-black uppercase leading-none">
                            <span class="text-slate-400 min-w-[80px]">Nama Peserta</span>
                            <span class="text-slate-400">:</span>
                            <span class="text-slate-700">{{ user.name }}</span>
                        </div>
                        <div class="flex gap-2 text-[9px] font-black uppercase leading-none">
                            <span class="text-slate-400 min-w-[80px]">Email</span>
                            <span class="text-slate-400">:</span>
                            <span class="text-slate-600 font-bold lowercase">{{ user.email }}</span>
                        </div>
                        <div class="text-[9px] font-black uppercase leading-none pt-1">
                            <span class="text-[#475569] tracking-wider">{{ subtestName }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t-4 border-[#334155] rounded-xl shadow-sm px-5 md:px-8 pt-2 pb-6 md:pb-8 space-y-3">
                    <div class="flex items-center gap-2">
                        <h2 class="font-black text-slate-400 text-[10px] uppercase tracking-tight">
                            Soal No. {{ currentNumber + 1 }}
                        </h2>
                    </div>

                    <div class="space-y-3">
                        <div v-if="currentQuestion.image" class="inline-block p-1 bg-slate-50 rounded-lg border border-slate-100">
                            <img :src="'/storage/' + currentQuestion.image" class="max-h-32 object-contain rounded-md" />
                        </div>
                        <p class="text-[9px] md:text-[10px] font-bold text-slate-800 leading-relaxed uppercase tracking-tight text-justify">
                            {{ currentQuestion.question_text || currentQuestion.content }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-1.5 max-w-xl">
                        <button v-for="(val, key) in (currentQuestion.options || {
                            'a': currentQuestion.option_a, 'b': currentQuestion.option_b, 'c': currentQuestion.option_c, 'd': currentQuestion.option_d, 'e': currentQuestion.option_e
                        })" :key="key"
                            @click="selectAnswer(key)"
                            :class="[selectedAnswers[currentQuestion.id] === key ? 'border-[#475569] bg-slate-50' : 'border-slate-100 bg-white hover:bg-slate-50']"
                            class="flex items-center gap-3 p-1.5 rounded-lg border-2 text-left transition-all active:scale-[0.99] group">
                            <span :class="[selectedAnswers[currentQuestion.id] === key ? 'bg-[#334155] text-white shadow-sm' : 'bg-slate-100 text-slate-400']" 
                                  class="w-6 h-6 shrink-0 rounded-md flex items-center justify-center font-black uppercase text-[9px]">{{ key }}</span>
                            <span class="text-[9px] font-black uppercase tracking-wide leading-tight" :class="selectedAnswers[currentQuestion.id] === key ? 'text-slate-900' : 'text-gray-600'">
                                {{ val }}
                            </span>
                        </button>
                    </div>

                    <div class="flex gap-3 pt-3 border-t border-slate-50">
                        <button @click="nextQuestion" 
                            class="flex-1 py-2.5 bg-[#475569] text-white rounded-lg font-black text-[9px] uppercase tracking-widest hover:bg-[#334155] transition-all shadow-md">
                            Simpan dan Lanjutkan
                        </button>
                        <button @click="nextQuestion" 
                            class="px-6 py-2.5 bg-white border border-slate-200 rounded-lg font-black text-[9px] uppercase tracking-widest text-slate-400 hover:bg-slate-50 hover:text-gray-600 transition-all">
                            Lewatkan Soal Ini
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-3 shadow-sm">
                    <p class="text-[7px] font-black text-slate-400 uppercase tracking-[0.3em] text-center">Navigasi Nomor Soal</p>
                    <div class="flex flex-wrap justify-center gap-1">
                        <button v-for="(q, index) in questions" :key="q.id" 
                            @click="currentNumber = index"
                            :class="[
                                currentNumber === index ? 'ring-2 ring-orange-400 z-10 scale-105 shadow-md' : '',
                                selectedAnswers[q.id] ? 'bg-[#27ae60] border-green-700' : 'bg-[#f43f5e] border-rose-700'
                            ]"
                            class="w-6 h-6 rounded-md flex items-center justify-center text-[8px] font-black border transition-all text-white">
                            {{ index + 1 }}
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <div class="fixed bottom-3 right-3 z-[60]">
            <div class="bg-[#1e293b] text-white px-5 py-2.5 rounded-xl shadow-lg border border-white/10 text-center backdrop-blur-sm">
                <p class="text-[5px] font-black text-slate-400 uppercase tracking-[0.3em] mb-0.5">SISA WAKTU</p>
                <p class="text-xl font-black tabular-nums tracking-widest leading-none">
                    {{ formatTime(timeLeft) }}
                </p>
            </div>
        </div>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-all">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-gray-100 animate-in zoom-in duration-300">
                <div class="bg-rose-600 px-6 py-4 flex items-center gap-3">
                    <span class="text-2xl">⚠️</span>
                    <h2 class="font-black text-white uppercase tracking-widest text-sm">PERHATIAN!!!</h2>
                </div>
                
                <div class="p-8 space-y-6">
                    <div class="bg-rose-50 border border-rose-100 p-4 rounded-xl text-center">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Status Pengerjaan</p>
                        <p class="text-lg font-black text-rose-600 uppercase">SOAL BELUM DIJAWAB : {{ unansweredCount }}</p>
                    </div>

                    <div class="space-y-4 text-[11px] font-bold text-gray-600 text-center leading-relaxed">
                        <p class="text-gray-900 text-xs font-black">Apakah Anda ingin mengakhiri simulasi ujian ini?</p>
                        <p>Jika <span class="text-rose-600 font-black">"Ya"</span> maka Anda sudah dinyatakan selesai mengikuti simulasi ujian, dan Anda tidak bisa memperbaiki lembar kerja Anda.</p>
                        <p>Jika <span class="text-gray-900 font-black">"Tidak"</span> maka anda akan kembali ke lembar kerja dan silahkan untuk melanjutkan menjawab atau memperbaiki jawaban anda.</p>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <button @click="showConfirmModal = false" 
                            class="flex-1 py-3 bg-gray-100 text-gray-500 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all">
                            Tidak
                        </button>
                        <button @click="processSubmit" 
                            class="flex-1 py-3 bg-rose-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-rose-200 hover:bg-rose-700 transition-all">
                            Ya
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
* { font-style: normal !important; }
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
:deep(body) { overflow: hidden !important; }
</style>