<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    attempt: Object
});

// --- 1. STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({});
// Sisa waktu dalam detik
const timeLeft = ref(props.tryout.duration_minutes * 60);
const isSubmitting = ref(false);
let timer = null;

// Proteksi: Pastikan pertanyaan tidak undefined saat di-render
const currentQuestion = computed(() => {
    return props.questions && props.questions[currentIndex.value] 
        ? props.questions[currentIndex.value] 
        : null;
});

// --- 2. LOGIKA TIMER & AUTO-SUBMIT ---
const startTimer = () => {
    timer = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            // WAKTU HABIS: Jalankan Auto-Submit
            stopTimer();
            autoSubmitExam();
        }
    }, 1000);
};

const stopTimer = () => {
    if (timer) clearInterval(timer);
};

const autoSubmitExam = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    
    // Kirim tanpa confirm karena waktu sudah habis
    router.post(route('tryout.finish', props.tryout.id), {
        answers: answers.value,
        auto_submit: true
    }, {
        onFinish: () => isSubmitting.value = false
    });
};

onMounted(() => {
    // Load jawaban yang sudah ada (jika user refresh halaman)
    props.questions.forEach((q) => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });
    startTimer();
});

onUnmounted(() => stopTimer());

// --- 3. NAVIGASI & ACTION ---
const selectAnswer = (questionId, optionKey) => {
    if (isSubmitting.value) return;
    answers.value[questionId] = optionKey;
};

const nextQuestion = () => {
    if (currentIndex.value < props.questions.length - 1) currentIndex.value++;
};

const prevQuestion = () => {
    if (currentIndex.value > 0) currentIndex.value--;
};

const manualFinish = () => {
    if (confirm('Apakah Anda yakin ingin mengakhiri ujian sekarang?')) {
        autoSubmitExam();
    }
};

// Formatter Waktu (HH:MM:SS)
const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};
</script>

<template>
    <Head :title="'Ujian: ' + tryout.title" />

    <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
        
        <header class="fixed top-0 inset-x-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200 px-5 py-4 md:px-10">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Sisa Waktu</span>
                    <div :class="timeLeft < 300 ? 'text-red-600 animate-pulse' : 'text-slate-900'" class="text-xl md:text-2xl font-black tabular-nums leading-none">
                        {{ formatTime(timeLeft) }}
                    </div>
                </div>

                <div class="flex flex-col items-end text-right">
                    <span class="text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Nomor Soal</span>
                    <div class="text-lg md:text-xl font-black text-indigo-600 leading-none">
                        {{ currentIndex + 1 }}<span class="text-slate-300 mx-1">/</span><span class="text-slate-400">{{ questions.length }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 mt-20 mb-28 px-4 md:px-6">
            <div class="max-w-3xl mx-auto py-8">
                
                <div v-if="currentQuestion" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div class="bg-white rounded-[2.5rem] p-7 md:p-12 shadow-sm border border-slate-100 mb-6 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600"></div>
                        <div class="text-slate-800 text-sm md:text-base leading-relaxed font-medium" v-html="currentQuestion.question_text"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <button 
                            v-for="(option, key) in currentQuestion.options" 
                            :key="key"
                            @click="selectAnswer(currentQuestion.id, key)"
                            :class="[
                                answers[currentQuestion.id] === key 
                                ? 'border-indigo-600 bg-indigo-50 ring-4 ring-indigo-50' 
                                : 'border-white bg-white hover:border-slate-200 shadow-sm'
                            ]"
                            class="group w-full text-left p-4 md:p-6 rounded-[1.8rem] border-2 transition-all flex items-start gap-4 active:scale-[0.98]"
                        >
                            <div :class="[answers[currentQuestion.id] === key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-400']"
                                class="w-10 h-10 md:w-12 md:h-12 rounded-2xl flex-shrink-0 flex items-center justify-center font-black text-sm uppercase transition-colors">
                                {{ key }}
                            </div>
                            <div class="text-xs md:text-sm font-bold text-slate-700 pt-2.5 md:pt-3 flex-1">{{ option }}</div>
                        </button>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-20 text-slate-300">
                    <div class="w-12 h-12 border-4 border-slate-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
                    <p class="text-[10px] font-black uppercase tracking-widest">Menyiapkan Soal...</p>
                </div>

            </div>
        </main>

        <footer class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 p-4 md:p-6 shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
            <div class="max-w-3xl mx-auto flex items-center gap-3 md:gap-4">
                
                <button 
                    @click="prevQuestion"
                    :disabled="currentIndex === 0 || isSubmitting"
                    class="flex-1 py-4 md:py-5 bg-slate-100 text-slate-500 rounded-2xl md:rounded-3xl font-black text-[10px] md:text-[11px] uppercase tracking-widest disabled:opacity-30 transition-all active:scale-95"
                >
                    Kembali
                </button>

                <button 
                    v-if="currentIndex < questions.length - 1"
                    @click="nextQuestion"
                    :disabled="isSubmitting"
                    class="flex-[2] py-4 md:py-5 bg-slate-900 text-white rounded-2xl md:rounded-3xl font-black text-[10px] md:text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 transition-all active:scale-95"
                >
                    Selanjutnya
                </button>

                <button 
                    v-else
                    @click="manualFinish"
                    :disabled="isSubmitting"
                    class="flex-[2] py-4 md:py-5 bg-emerald-600 text-white rounded-2xl md:rounded-3xl font-black text-[10px] md:text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95"
                >
                    {{ isSubmitting ? 'Mengirim...' : 'Selesai Ujian' }}
                </button>

            </div>
        </footer>
    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.transition-all { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }

/* Animasi sederhana saat ganti soal */
.animate-in {
    animation: slideUp 0.4s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>