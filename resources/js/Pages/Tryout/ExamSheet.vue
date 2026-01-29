<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    attempt: Object
});

// --- 1. STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({}); // Menyimpan jawaban sementara di local
const timeLeft = ref(props.tryout.duration_minutes * 60);
let timer = null;

// Ambil pertanyaan saat ini dengan proteksi agar tidak undefined
const currentQuestion = computed(() => {
    return props.questions && props.questions[currentIndex.value] 
        ? props.questions[currentIndex.value] 
        : null;
});

// --- 2. LOGIKA TIMER ---
onMounted(() => {
    // Load jawaban yang sudah tersimpan sebelumnya jika ada
    props.questions.forEach((q, idx) => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });

    timer = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            finishExam();
        }
    }, 1000);
});

onUnmounted(() => clearInterval(timer));

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h > 0 ? h + ':' : ''}${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};

// --- 3. LOGIKA JAWABAN ---
const selectAnswer = (questionId, optionKey) => {
    answers.value[questionId] = optionKey;
    // Auto-save ke server bisa ditambahkan di sini via axios/router.post
};

const nextQuestion = () => {
    if (currentIndex.value < props.questions.length - 1) currentIndex.value++;
};

const prevQuestion = () => {
    if (currentIndex.value > 0) currentIndex.value--;
};

const finishExam = () => {
    if (confirm('Apakah Anda yakin ingin mengakhiri ujian?')) {
        router.post(route('tryout.finish', props.tryout.id), {
            answers: answers.value
        });
    }
};
</script>

<template>
    <Head :title="'Ujian: ' + tryout.title" />

    <div class="min-h-screen bg-slate-50 flex flex-col">
        <div class="fixed top-0 inset-x-0 z-30 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sisa Waktu</span>
                <span :class="timeLeft < 300 ? 'text-red-600' : 'text-slate-900'" class="text-lg font-black tabular-nums">
                    {{ formatTime(timeLeft) }}
                </span>
            </div>
            
            <div class="flex flex-col items-end">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Progress</span>
                <span class="text-sm font-black text-indigo-600">
                    {{ currentIndex + 1 }} <span class="text-slate-300">/</span> {{ questions.length }}
                </span>
            </div>
        </div>

        <main class="flex-1 pt-24 pb-32 px-6 max-w-2xl mx-auto w-full">
            
            <div v-if="currentQuestion" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100 mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">
                            Pertanyaan #{{ currentIndex + 1 }}
                        </span>
                    </div>
                    <div class="text-slate-800 font-medium leading-relaxed" v-html="currentQuestion.question_text"></div>
                </div>

                <div class="space-y-3">
                    <button 
                        v-for="(option, key) in currentQuestion.options" 
                        :key="key"
                        @click="selectAnswer(currentQuestion.id, key)"
                        :class="[
                            answers[currentQuestion.id] === key 
                            ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-100' 
                            : 'border-white bg-white hover:border-slate-200'
                        ]"
                        class="w-full text-left p-5 rounded-2xl border-2 transition-all flex items-start gap-4 active:scale-[0.98]"
                    >
                        <div :class="[answers[currentQuestion.id] === key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400']"
                            class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center font-black text-xs uppercase">
                            {{ key }}
                        </div>
                        <div class="text-sm font-bold text-slate-700 pt-1">{{ option }}</div>
                    </button>
                </div>
            </div>

            <div v-else class="flex flex-col items-center justify-center py-20 text-slate-400">
                <div class="animate-spin mb-4">⏳</div>
                <p class="text-xs font-black uppercase tracking-widest">Memuat Soal...</p>
            </div>
        </main>

        <div class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-slate-200 p-6 flex items-center gap-3">
            <button 
                @click="prevQuestion"
                :disabled="currentIndex === 0"
                class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest disabled:opacity-30"
            >
                Sebelumnya
            </button>

            <button 
                v-if="currentIndex < questions.length - 1"
                @click="nextQuestion"
                class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg active:bg-indigo-600"
            >
                Selanjutnya
            </button>

            <button 
                v-else
                @click="finishExam"
                class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-emerald-100"
            >
                Selesai Ujian
            </button>
        </div>
    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.transition-all { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
</style>