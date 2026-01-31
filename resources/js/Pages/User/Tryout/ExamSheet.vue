<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object
});

const page = usePage();

// --- 1. STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({});

// PERBAIKAN DISINI: Ganti duration_minutes jadi duration
const timeLeft = ref((props.tryout?.duration || 110) * 60);

const isSubmitting = ref(false);
const showConfirmModal = ref(false);
let timer = null;

const answersKey = computed(() => `cat_bkn_answers_${props.tryout?.id}_${page.props.auth.user.id}`);
const indexKey = computed(() => `cat_bkn_index_${props.tryout?.id}_${page.props.auth.user.id}`);

const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

const subtestLabel = computed(() => {
    const type = currentQuestion.value?.type;
    if (type === 'TWK') return 'Tes Wawasan Kebangsaan - TWK';
    if (type === 'TIU') return 'Tes Intelegensia Umum - TIU';
    if (type === 'TKP') return 'Tes Karakteristik Pribadi - TKP';
    return type || '-';
});

const answeredCount = computed(() => Object.keys(answers.value).length);
const unansweredCount = computed(() => (props.questions?.length || 0) - answeredCount.value);

// --- 2. LOGIKA PERSISTENSI & TIMER ---
onMounted(() => {
    props.questions?.forEach(q => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });

    const savedAnswers = localStorage.getItem(answersKey.value);
    if (savedAnswers) {
        const parsed = JSON.parse(savedAnswers);
        answers.value = { ...answers.value, ...parsed };
    }

    const savedIndex = localStorage.getItem(indexKey.value);
    if (savedIndex) {
        currentIndex.value = parseInt(savedIndex);
    }
    
    timer = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else autoSubmit();
    }, 1000);
});

watch(answers, (newVal) => {
    localStorage.setItem(answersKey.value, JSON.stringify(newVal));
}, { deep: true });

watch(currentIndex, (newVal) => {
    localStorage.setItem(indexKey.value, newVal);
});

onUnmounted(() => clearInterval(timer));

const autoSubmit = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    router.post(route('tryout.finish', props.tryout?.id), { 
        answers: answers.value,
        mode: 'BKN' 
    }, {
        onSuccess: () => {
            localStorage.removeItem(answersKey.value);
            localStorage.removeItem(indexKey.value);
        }
    });
};

const finishExam = () => {
    showConfirmModal.value = true;
};

const goTo = (index) => { currentIndex.value = index; };
const next = () => { 
    if (currentIndex.value < (props.questions?.length - 1)) {
        currentIndex.value++; 
    } else {
        currentIndex.value = 0; 
    }
};

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};
</script>

<template>
    <Head :title="'CAT BKN - ' + tryout?.title" />

    <div class="min-h-screen bg-[#F0F2F5] flex flex-col font-sans text-slate-700">
        
        <header class="bg-white border-b border-slate-200 sticky top-0 z-50 h-16 flex items-center shadow-sm">
            <div class="w-full px-6 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <img src="/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <div class="hidden md:block border-l border-slate-200 pl-4">
                        <h1 class="text-sm font-black text-[#004a87] uppercase leading-none">Simulasi CAT Nusantara</h1>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <div class="bg-slate-900 text-white px-4 py-2 rounded-lg font-mono text-lg font-bold flex items-center gap-2 tabular-nums">
                        <span class="text-yellow-400 text-sm">⏳</span> {{ formatTime(timeLeft) }}
                    </div>
                    <button @click="finishExam" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all shadow-md active:scale-95">
                        Selesai Ujian
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 flex flex-col md:flex-row h-[calc(100vh-64px)] overflow-hidden">
            
            <aside class="w-full md:w-[320px] bg-white border-r border-slate-200 flex flex-col overflow-y-auto custom-scrollbar">
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 flex-shrink-0">
                            <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover">
                            <span v-else class="text-[9px] font-black text-slate-300 flex items-center justify-center h-full uppercase">USER</span>
                        </div>
                        <div class="overflow-hidden text-left leading-tight">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Peserta</p>
                            <h4 class="text-[13px] font-black text-slate-900 truncate uppercase">{{ page.props.auth?.user?.name }}</h4>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-[9px] font-bold uppercase">
                            <span class="text-slate-400 italic">Email</span>
                            <span class="text-slate-700 truncate ml-2 lowercase">{{ page.props.auth?.user?.email }}</span>
                        </div>
                        <div class="pt-2">
                            <span class="block w-full text-center bg-blue-50 text-[#004a87] py-1.5 rounded-lg text-[9px] font-black border border-blue-100 uppercase tracking-tighter">
                                {{ subtestLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-2 gap-3 mb-5 text-center">
                        <div class="bg-emerald-50 p-2 rounded-xl border border-emerald-100">
                            <p class="text-[8px] font-bold text-emerald-600 uppercase">Dijawab</p>
                            <p class="text-base font-black text-emerald-700 leading-none mt-1">{{ answeredCount }}</p>
                        </div>
                        <div class="bg-rose-50 p-2 rounded-xl border border-rose-100">
                            <p class="text-[8px] font-bold text-rose-600 uppercase">Sisa</p>
                            <p class="text-base font-black text-rose-700 leading-none mt-1">{{ unansweredCount }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-1">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                            :class="[
                                currentIndex === i ? 'ring-2 ring-blue-600 ring-offset-1 scale-105 z-10 shadow-sm' : '',
                                answers[q.id] ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white text-slate-400 border-slate-200'
                            ]"
                            class="w-6 h-6 border rounded-md text-[8px] font-black flex items-center justify-center transition-all hover:border-blue-400 active:scale-90">
                            {{ i + 1 }}
                        </button>
                    </div>
                </div>
            </aside>

            <section class="flex-1 flex flex-col bg-slate-50 relative overflow-hidden">
                <div class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar">
                    <div class="max-w-[1000px] mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-6 py-2 border-b border-slate-100">
                            <span class="text-[11px] font-black text-[#004a87] uppercase tracking-[0.2em]">Soal Nomor {{ currentIndex + 1 }}</span>
                        </div>

                        <div v-if="currentQuestion" class="p-6 md:p-8 text-left">
                            <div v-if="currentQuestion?.image" class="mb-5 rounded-xl overflow-hidden border border-slate-200 max-w-md mx-auto shadow-sm">
                                <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto" alt="Gambar Soal">
                            </div>

                            <div class="text-[13px] md:text-base leading-relaxed text-slate-800 mb-3 font-medium" v-html="currentQuestion?.content"></div>
                            
                            <div class="space-y-1">
                                <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                                    class="flex items-start gap-3 p-1.5 rounded-lg border border-slate-100 cursor-pointer transition-all hover:bg-blue-50/50 group"
                                    :class="{'bg-blue-50 border-blue-200 ring-1 ring-blue-50': answers[currentQuestion?.id] === key}">
                                    <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="mt-1 w-4 h-4 text-blue-600 border-slate-300">
                                    <span class="text-xs font-black text-[#004a87] w-4 uppercase">{{ key }}.</span>
                                    <span class="text-[12px] md:text-sm font-bold text-slate-600 group-hover:text-slate-900 flex-1 leading-snug">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-slate-200 p-4 shadow-sm">
                    <div class="max-w-[1000px] mx-auto flex justify-start items-center gap-3">
                        <button @click="next" class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-[10px] font-black uppercase text-slate-500 hover:bg-slate-100 transition-all shadow-sm">
                            Lewatkan Soal Ini
                        </button>
                        <button @click="next" class="px-8 py-2.5 bg-[#f37021] text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg hover:bg-orange-600 active:scale-95 transition-all">
                            Simpan dan Lanjutkan
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-[2rem] shadow-2xl max-w-xl w-full overflow-hidden animate-in zoom-in duration-300">
                <div class="bg-rose-600 p-4 text-white text-center">
                    <h3 class="text-xl font-black uppercase tracking-widest">Konfirmasi Selesai</h3>
                </div>
                <div class="p-8 text-slate-700">
                    <p class="text-lg font-bold mb-6 text-center italic">SOAL BELUM DIJAWAB : <span class="text-rose-600 underline underline-offset-4">{{ unansweredCount }}</span></p>
                    <div class="space-y-4 text-sm leading-relaxed text-center">
                        <p class="font-black text-slate-900 text-base">Apakah Anda yakin ingin mengakhiri simulasi?</p>
                        <p class="text-slate-500">Jika "Ya", jawaban Anda akan dikirim dan hasil ujian akan segera diproses. Anda tidak bisa kembali setelah ini.</p>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center gap-3">
                    <button @click="autoSubmit" class="flex-1 py-3 bg-emerald-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-emerald-700 shadow-xl transition-all active:scale-95">Ya, Selesai</button>
                    <button @click="showConfirmModal = false" class="flex-1 py-3 bg-slate-200 text-slate-600 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-300 transition-all active:scale-95">Tidak</button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.tabular-nums { font-variant-numeric: tabular-nums; }

@keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.animate-in { animation: zoomIn 0.2s ease-out forwards; }
</style>