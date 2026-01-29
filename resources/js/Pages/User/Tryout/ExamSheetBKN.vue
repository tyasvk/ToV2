<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object
});

const page = usePage();

// --- 1. STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({});
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 0);
const isSubmitting = ref(false);
let timer = null;

// PROTEKSI: Pastikan mengambil soal dengan aman
const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

// Statistik Jawaban
const answeredCount = computed(() => Object.keys(answers.value).length);
const unansweredCount = computed(() => (props.questions?.length || 0) - answeredCount.value);

// --- 2. LOGIKA TIMER ---
onMounted(() => {
    // Muat jawaban yang sudah ada
    props.questions?.forEach(q => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });
    
    timer = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else autoSubmit();
    }, 1000);
});

onUnmounted(() => clearInterval(timer));

const autoSubmit = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    router.post(route('tryout.finish', props.tryout?.id), { 
        answers: answers.value,
        mode: 'BKN' 
    });
};

const finishExam = () => {
    if (confirm('Apakah Anda yakin ingin menyelesaikan ujian?')) autoSubmit();
};

// --- 3. NAVIGASI ---
const goTo = (index) => { currentIndex.value = index; };
const next = () => { if (currentIndex.value < (props.questions?.length - 1)) currentIndex.value++; };
const prev = () => { if (currentIndex.value > 0) currentIndex.value--; };

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};
</script>

<template>
    <Head :title="'CAT BKN: ' + (tryout?.title || 'Ujian')" />

    <div class="min-h-screen bg-[#f4f8fb] flex flex-col font-sans text-slate-700">
        
        <header class="bg-[#f0f7ff] text-slate-900 p-3 md:p-5 shadow-sm border-b border-blue-100 sticky top-0 z-50">
            <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="bg-white p-2 rounded-xl shadow-sm border border-blue-50">
                        <img src="/images/logo.png" alt="Logo" class="h-12 md:h-20 w-auto object-contain">
                    </div>
                    <div class="text-left border-l border-blue-200 pl-4">
                        <h1 class="text-[11px] md:text-sm font-black uppercase tracking-tighter leading-none text-[#004a87]">Simulasi CAT</h1>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tight">CPNS Nusantara</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto justify-end">
                    <div class="grid grid-cols-2 md:flex items-center gap-4 md:gap-8 w-full md:w-auto overflow-x-auto no-scrollbar justify-end text-right">
                        <div class="flex flex-col min-w-[80px]">
                            <span class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Batas Waktu</span>
                            <span class="text-xs font-black text-slate-900">{{ tryout?.duration_minutes }} Menit</span>
                        </div>
                        <div class="flex flex-col border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[9px] uppercase font-bold text-slate-400 tracking-wider">Jumlah Soal</span>
                            <span class="text-xs font-black text-slate-900">{{ questions?.length || 0 }} Soal</span>
                        </div>
                        <div class="flex flex-col border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[9px] uppercase font-bold text-emerald-500 tracking-wider">Dijawab</span>
                            <span class="text-xs font-black text-emerald-600">{{ answeredCount }}</span>
                        </div>
                        <div class="flex flex-col border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[9px] uppercase font-bold text-rose-500 tracking-wider">Belum Dijawab</span>
                            <span class="text-xs font-black text-rose-600">{{ unansweredCount }}</span>
                        </div>
                    </div>

                    <button @click="finishExam" class="w-full md:w-auto px-8 py-3 bg-[#004a87] hover:bg-blue-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg transition-all active:scale-95">
                        Selesai Ujian
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-4xl mx-auto w-full p-3 md:p-4 space-y-4">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex items-center gap-4">
                <div class="w-14 h-14 md:w-20 md:h-20 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0 relative">
                    <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover">
                    <span v-else class="text-[9px] font-black text-slate-300">PHOTO</span>
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Identitas Peserta</p>
                    <h4 class="text-sm md:text-base font-black text-slate-900 uppercase truncate">{{ page.props.auth?.user?.name || 'Peserta' }}</h4>
                    <p class="text-[10px] font-bold text-blue-600 uppercase mt-1 tracking-tight italic">{{ tryout?.title }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden min-h-[450px] flex flex-col">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <span class="text-xs font-black text-[#004a87] uppercase tracking-widest">Lembar Soal #{{ currentIndex + 1 }}</span>
                </div>

                <div v-if="currentQuestion" class="p-6 md:p-12 flex-1 overflow-y-auto text-left animate-in fade-in">
                    <div v-if="currentQuestion?.image" class="mb-8 rounded-2xl overflow-hidden border border-slate-100 max-w-md mx-auto shadow-sm">
                        <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto object-contain" alt="Gambar Soal">
                    </div>

                    <div class="text-sm md:text-lg leading-relaxed text-slate-800 mb-10 font-medium" v-html="currentQuestion?.question_text"></div>
                    
                    <div class="space-y-4">
                        <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                            class="flex items-start gap-4 p-5 rounded-2xl border border-slate-100 cursor-pointer transition-all hover:bg-slate-50 group"
                            :class="{'bg-blue-50 border-blue-300 ring-1 ring-blue-50': answers[currentQuestion?.id] === key}">
                            <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="mt-1 w-5 h-5 text-blue-600 border-slate-300">
                            <span class="text-sm font-black text-[#004a87] w-5">{{ key }}.</span>
                            <span class="text-sm md:text-base font-bold text-slate-600 group-hover:text-slate-900 flex-1">{{ option }}</span>
                        </label>
                    </div>
                </div>

                <div v-else class="p-20 text-center text-slate-400 italic">
                    Memuat pertanyaan... (Pastikan data soal sudah ada di Database)
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between gap-4 sticky bottom-0">
                    <button @click="prev" :disabled="currentIndex === 0" class="flex-1 md:flex-none px-8 py-3 bg-white border border-slate-300 rounded-xl text-[10px] font-black uppercase text-slate-500 disabled:opacity-30 transition-colors">Sebelumnya</button>
                    <button v-if="currentIndex < (questions?.length - 1)" @click="next" class="flex-1 md:flex-none px-10 py-3 bg-[#f37021] text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md">Selanjutnya</button>
                    <button v-else @click="finishExam" class="flex-1 md:flex-none px-10 py-3 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md">Selesai</button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 mb-28">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6 text-center italic">Navigasi Pengerjaan Soal</p>
                <div v-if="questions?.length > 0" class="flex flex-wrap justify-center gap-3">
                    <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                        :class="[
                            currentIndex === i ? 'ring-2 ring-blue-600 ring-offset-2 scale-110 z-10 shadow-lg' : '',
                            answers[q.id] ? 'bg-emerald-500 text-white border-emerald-600 shadow-sm' : 'bg-white text-slate-400 border-slate-200 shadow-sm'
                        ]"
                        class="w-12 h-12 border rounded-xl text-[11px] font-black flex items-center justify-center transition-all hover:border-blue-400 active:scale-90">
                        {{ i + 1 }}
                    </button>
                </div>
            </div>
        </main>

        <div class="fixed bottom-6 right-6 z-[100] animate-in slide-in-from-bottom-10 duration-700">
            <div class="bg-yellow-400 text-blue-900 px-6 py-4 rounded-2xl font-black text-sm md:text-2xl shadow-2xl border-4 border-white flex items-center gap-3 tabular-nums">
                <span class="text-lg md:text-2xl">⏳</span> {{ formatTime(timeLeft) }}
            </div>
        </div>

    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.transition-all { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.slide-in-from-bottom-10 { animation: slideUp 0.5s ease-out; }
</style>