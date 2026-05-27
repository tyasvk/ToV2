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

// State tambahan HANYA untuk toggle menu di layar HP
const isSidebarOpen = ref(false); 

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
        mode: 'BKN',
        time_left: timeLeft.value // <--- Tambahkan baris ini untuk mengirim sisa waktu
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

const goTo = (index) => { 
    currentIndex.value = index; 
    isSidebarOpen.value = false; 
};

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

    <div class="h-screen bg-slate-50 flex flex-col font-sans text-slate-700 overflow-hidden">
        
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 h-14 shrink-0 flex items-center shadow-sm px-3 md:px-6">
            <div class="w-full flex justify-between items-center gap-2">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Logo" class="h-7 md:h-8 w-auto">
                    <div class="hidden md:block border-l border-slate-200 pl-3">
                        <h1 class="text-xs font-medium text-[#004a87] uppercase tracking-wide leading-none">Simulasi CAT Nusantara</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    <div class="bg-slate-50 border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg font-mono text-sm font-medium flex items-center gap-1.5 tabular-nums">
                        <span class="text-xs">⏱️</span> {{ formatTime(timeLeft) }}
                    </div>
                    
                    <button @click="finishExam" class="bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 px-3 md:px-5 py-1.5 rounded-lg text-xs font-medium uppercase tracking-wider transition-colors active:scale-95 hidden sm:block">
                        Selesai Ujian
                    </button>

                    <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-1.5 text-slate-500 hover:bg-slate-100 rounded-lg bg-white border border-slate-200 active:scale-95 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden relative">
            
            <aside :class="[
                'absolute md:static inset-y-0 left-0 z-50 w-72 md:w-80 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 transform shadow-2xl md:shadow-none h-full',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]">
                <div class="p-4 border-b border-slate-100">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-slate-100 rounded-full overflow-hidden border border-slate-200 flex-shrink-0">
                                <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover">
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300 m-auto mt-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div class="overflow-hidden text-left leading-tight">
                                <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Peserta</p>
                                <h4 class="text-xs font-medium text-slate-800 truncate">{{ page.props.auth?.user?.name }}</h4>
                            </div>
                        </div>
                        <button @click="isSidebarOpen = false" class="md:hidden text-slate-400 hover:text-slate-600 p-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between font-normal">
                            <span class="text-slate-400">Email</span>
                            <span class="text-slate-600 truncate ml-2">{{ page.props.auth?.user?.email }}</span>
                        </div>
                        <div class="pt-2">
                            <div class="w-full text-center bg-blue-50/50 text-[#004a87] py-2 rounded-lg text-[10px] font-medium border border-blue-100/50 uppercase tracking-wide">
                                {{ subtestLabel }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-4 shrink-0">
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="bg-emerald-50/50 p-2 rounded-lg border border-emerald-100">
                                <p class="text-[9px] font-medium text-emerald-600 uppercase tracking-wider">Dijawab</p>
                                <p class="text-sm font-medium text-emerald-700 mt-0.5">{{ answeredCount }}</p>
                            </div>
                            <div class="bg-amber-50/50 p-2 rounded-lg border border-amber-100">
                                <p class="text-[9px] font-medium text-amber-600 uppercase tracking-wider">Sisa</p>
                                <p class="text-sm font-medium text-amber-700 mt-0.5">{{ unansweredCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-4 pb-4 pt-2 custom-scrollbar">
                        <div class="flex flex-wrap content-start gap-1.5 sm:gap-2">
                            <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                                :class="[
                                    currentIndex === i ? 'ring-2 ring-blue-400 ring-offset-1 z-10' : '',
                                    answers[q.id] ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white text-slate-500 border-slate-200'
                                ]"
                                class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 border rounded text-[10px] sm:text-[11px] font-medium flex items-center justify-center transition-all hover:border-blue-300">
                                {{ i + 1 }}
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-40 md:hidden backdrop-blur-sm"></div>

            <section class="flex-1 flex flex-col relative overflow-hidden bg-slate-50/50">
                
                <div class="flex-1 overflow-y-auto p-3 md:p-6 custom-scrollbar">
                    <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col min-h-full">
                        
                        <div class="bg-slate-50/50 px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                            <span class="text-[11px] font-medium text-[#004a87] uppercase tracking-wider">Soal Nomor {{ currentIndex + 1 }}</span>
                        </div>

                        <div v-if="currentQuestion" class="p-4 md:p-6 text-left flex-1">
                            <div v-if="currentQuestion?.image" class="mb-5 rounded-lg overflow-hidden border border-slate-200 max-w-sm mx-auto shadow-sm">
                                <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto bg-slate-50 p-1" alt="Gambar Soal">
                            </div>

                            <div class="text-[13px] md:text-sm leading-relaxed text-slate-700 mb-5 font-normal whitespace-pre-wrap" v-html="currentQuestion?.content"></div>
                            
                            <div class="space-y-2">
                                <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                                    class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-all active:scale-[0.99]"
                                    :class="answers[currentQuestion?.id] === key ? 'bg-blue-50/50 border-blue-300 ring-1 ring-blue-100' : 'border-slate-200 hover:bg-slate-50'">
                                    
                                    <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="hidden">
                                    
                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center shrink-0 transition-colors mt-0.5 text-[10px]"
                                        :class="answers[currentQuestion?.id] === key ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-300 text-slate-400'">
                                        <span class="font-medium">{{ key.toUpperCase() }}</span>
                                    </div>

                                    <span class="text-xs md:text-sm font-normal text-slate-600 flex-1 leading-relaxed">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-slate-200 p-3 sm:p-4 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] z-10">
                    <div class="max-w-3xl mx-auto flex justify-between items-center gap-2 sm:gap-3">
                        <button @click="next" class="flex-1 py-2.5 sm:py-3 bg-white border border-slate-200 rounded-lg text-[11px] sm:text-xs font-medium uppercase text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors">
                            Lewatkan
                        </button>
                        
                        <button @click="next" class="flex-[1.5] sm:flex-1 py-2.5 sm:py-3 bg-[#f37021] border border-[#f37021] text-white rounded-lg text-[11px] sm:text-xs font-medium uppercase tracking-wider hover:bg-orange-600 transition-colors shadow-sm flex justify-center items-center gap-1.5">
                            Simpan & Lanjut
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </div>
                    
                    <div class="max-w-3xl mx-auto mt-2 sm:hidden">
                        <button @click="finishExam" class="w-full py-2 bg-rose-50 text-rose-600 rounded-lg text-[11px] font-medium uppercase tracking-wider">
                            Selesai Ujian
                        </button>
                    </div>
                </div>

            </section>
        </main>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full overflow-hidden animate-in">
                <div class="bg-rose-50 p-4 border-b border-rose-100 flex items-center justify-center gap-2 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <h3 class="text-sm font-medium uppercase tracking-wider">Akhiri Ujian</h3>
                </div>
                <div class="p-6 text-slate-600 text-center">
                    <div class="mb-4">
                        <p class="text-sm font-medium mb-1">Soal Belum Dijawab:</p>
                        <p class="text-2xl font-medium text-rose-500">{{ unansweredCount }}</p>
                    </div>
                    <p class="text-xs font-normal leading-relaxed text-slate-500">
                        Apakah Anda yakin ingin mengakhiri simulasi ini? Sisa waktu Anda akan hangus.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-2">
                    <button @click="showConfirmModal = false" class="flex-1 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-lg text-xs font-medium uppercase tracking-wider hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="autoSubmit" class="flex-1 py-2.5 bg-emerald-600 text-white border border-emerald-600 rounded-lg text-xs font-medium uppercase tracking-wider hover:bg-emerald-700 transition-colors">Ya, Selesai</button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.tabular-nums { font-variant-numeric: tabular-nums; }

@keyframes zoomIn { 
    from { transform: scale(0.95); opacity: 0; } 
    to { transform: scale(1); opacity: 1; } 
}
.animate-in { animation: zoomIn 0.2s ease-out forwards; }
</style>