<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: {
        type: Array,
        default: () => []
    }
});

// --- STATE MANAGEMENT ---
const currentNumber = ref(0);
const selectedAnswers = ref({}); 
const doubtfulQuestions = ref(new Set()); 
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 0);
const isProcessing = ref(false);

// --- LOGIKA TIMER ---
let timerInterval = null;

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h, m, s].map(v => v.toString().padStart(2, '0')).join(':');
};

const startTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval);
            autoFinishExam();
        }
    }, 1000);
};

// --- LOGIKA NAVIGASI & JAWABAN ---
const currentQuestion = computed(() => {
    return props.questions.length > 0 ? props.questions[currentNumber.value] : null;
});

const selectAnswer = (key) => {
    if (currentQuestion.value) {
        selectedAnswers.value[currentQuestion.value.id] = key;
    }
};

const toggleDoubtful = () => {
    if (!currentQuestion.value) return;
    const id = currentQuestion.value.id;
    if (doubtfulQuestions.value.has(id)) {
        doubtfulQuestions.value.delete(id);
    } else {
        doubtfulQuestions.value.add(id);
    }
};

// --- LOGIKA FINISH (SUBMIT) ---
const finishExam = () => {
    if (confirm('Apakah Anda yakin ingin mengakhiri ujian? Pastikan semua soal telah diperiksa.')) {
        processSubmit();
    }
};

const autoFinishExam = () => {
    alert('Waktu habis! Sistem akan menyimpan jawaban Anda secara otomatis.');
    processSubmit();
};

const processSubmit = () => {
    isProcessing.value = true;
    router.post(route('tryout.finish', props.tryout.id), {
        answers: selectedAnswers.value
    }, {
        onBefore: () => clearInterval(timerInterval),
        onFinish: () => isProcessing.value = false,
        onError: () => alert('Gagal mengirim jawaban. Cek koneksi Anda.')
    });
};

// --- KEAMANAN UJIAN ---
const preventCheating = (e) => {
    // Blokir F12, Ctrl+U, dll (Opsional)
    if (e.ctrlKey && (e.key === 'u' || e.key === 's')) e.preventDefault();
};

// --- LIFECYCLE ---
onMounted(() => {
    if (props.questions.length > 0) {
        startTimer();
    }
    window.addEventListener('keydown', preventCheating);
    // Blokir Klik Kanan
    document.addEventListener('contextmenu', e => e.preventDefault());
});

onUnmounted(() => {
    clearInterval(timerInterval);
    window.removeEventListener('keydown', preventCheating);
    document.removeEventListener('contextmenu', e => e.preventDefault());
});
</script>

<template>
    <Head :title="'Ujian: ' + (tryout?.title || 'Loading...')" />

    <div v-if="questions.length > 0" class="min-h-screen bg-gray-50 flex flex-col font-sans select-none">
        
        <header class="h-20 bg-white border-b border-gray-100 px-8 flex justify-between items-center sticky top-0 z-50 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100">CBT</div>
                <div class="hidden md:block">
                    <h1 class="font-black text-gray-900 uppercase tracking-tighter text-sm truncate max-w-xs">{{ tryout.title }}</h1>
                    <p class="text-[9px] text-indigo-500 font-bold uppercase tracking-widest">Swoole Engine Active</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div :class="timeLeft < 300 ? 'bg-red-50 border-red-100 text-red-600 animate-pulse' : 'bg-indigo-50 border-indigo-100 text-indigo-600'" 
                     class="px-6 py-2.5 rounded-2xl border flex items-center gap-3 transition-all duration-500 shadow-sm">
                    <span class="text-[9px] font-black uppercase tracking-widest opacity-60">Sisa Waktu:</span>
                    <span class="font-black text-xl tabular-nums tracking-tighter">{{ formatTime(timeLeft) }}</span>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-[1600px] mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 p-6 md:p-10">
            
            <div class="lg:col-span-8 flex flex-col gap-6">
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-gray-100 min-h-[500px]">
                    <div class="flex items-center gap-3 mb-10">
                        <span class="bg-gray-900 text-white px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest">Soal {{ currentNumber + 1 }}</span>
                        <span class="bg-indigo-50 text-indigo-500 px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest">{{ currentQuestion?.type }}</span>
                    </div>

                    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
                        <div v-if="currentQuestion?.image" class="bg-gray-50 rounded-[2rem] p-4 border border-gray-100 inline-block">
                            <img :src="'/storage/' + currentQuestion.image" class="max-h-64 object-contain rounded-xl" />
                        </div>
                        
                        <p class="text-xl font-bold text-gray-800 leading-relaxed">{{ currentQuestion?.content }}</p>

                        <div class="grid grid-cols-1 gap-4 pt-6">
                            <button v-for="(val, key) in currentQuestion?.options" :key="key"
                                @click="selectAnswer(key)"
                                :class="[selectedAnswers[currentQuestion?.id] === key ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-50' : 'border-gray-100 bg-white hover:border-indigo-200']"
                                class="flex items-center gap-5 p-5 rounded-[1.5rem] border-2 text-left transition-all active:scale-[0.98]">
                                <span :class="[selectedAnswers[currentQuestion?.id] === key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400']" 
                                      class="w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center font-black uppercase border border-gray-100 text-sm transition-all">{{ key }}</span>
                                <span class="text-base font-bold text-gray-700">{{ val }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pb-12">
                    <div class="flex gap-4">
                        <button @click="currentNumber--" :disabled="currentNumber === 0" class="px-8 py-4 bg-white border border-gray-200 rounded-2xl font-black text-[10px] uppercase text-gray-400 disabled:opacity-20 transition">Kembali</button>
                        <button @click="currentNumber++" :disabled="currentNumber === questions.length - 1" class="px-8 py-4 bg-white border border-gray-200 rounded-2xl font-black text-[10px] uppercase text-gray-400 disabled:opacity-20 transition">Lanjut</button>
                    </div>
                    <button @click="toggleDoubtful" 
                        :class="doubtfulQuestions.has(currentQuestion?.id) ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-amber-50 text-amber-600 border border-amber-200'"
                        class="px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest transition shadow-lg">
                        {{ doubtfulQuestions.has(currentQuestion?.id) ? '✓ Sudah Yakin' : '⚠️ Ragu-Ragu' }}
                    </button>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 sticky top-28">
                    <h3 class="font-black text-[10px] text-gray-400 uppercase tracking-[0.4em] mb-8 text-center border-b border-gray-50 pb-5">Navigasi Soal</h3>
                    <div class="grid grid-cols-5 gap-3 max-h-[40vh] overflow-y-auto pr-2 custom-scrollbar">
                        <button v-for="(q, index) in questions" :key="q.id" @click="currentNumber = index"
                            :class="[currentNumber === index ? 'ring-4 ring-indigo-100 border-indigo-600 scale-105' : 'border-gray-50', doubtfulQuestions.has(q.id) ? 'bg-amber-500 text-white' : (selectedAnswers[q.id] ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-400')]"
                            class="w-full aspect-square rounded-2xl flex items-center justify-center border-2 transition-all">
                            <span class="text-[11px] font-black">{{ index + 1 }}</span>
                        </button>
                    </div>
                    <div class="pt-8 border-t border-gray-50 mt-6">
                        <button @click="finishExam" :disabled="isProcessing" class="w-full py-5 bg-gray-900 text-white rounded-[2rem] font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl hover:bg-red-600 transition duration-500">
                            {{ isProcessing ? 'Menyimpan...' : 'Akhiri Ujian' }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div v-else class="h-screen w-full flex flex-col items-center justify-center bg-white p-10">
        <div v-if="tryout" class="flex flex-col items-center animate-in fade-in duration-700">
            <div class="w-14 h-14 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-6"></div>
            <h2 class="text-[11px] font-black text-gray-500 uppercase tracking-[0.4em] mb-2">Sinkronisasi Data...</h2>
            
            <div class="mt-8 p-8 bg-amber-50 rounded-[2rem] border border-amber-100 max-w-sm text-center">
                <p class="text-[10px] font-bold text-amber-700 uppercase leading-relaxed mb-4 italic">
                    Jika layar ini tidak berubah, kemungkinan paket "{{ tryout.title }}" belum memiliki soal di database.
                </p>
                <Link :href="route('dashboard')" class="text-[10px] font-black uppercase text-indigo-600 underline">Kembali ke Dashboard</Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.select-none { user-select: none; -webkit-user-select: none; }
</style>