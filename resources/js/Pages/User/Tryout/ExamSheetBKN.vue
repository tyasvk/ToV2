<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    user: Object
});

const page = usePage();

// --- STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({});
const timeLeft = ref((props.tryout?.duration || 110) * 60);
const isSubmitting = ref(false);
const showConfirmModal = ref(false);
const isSidebarOpen = ref(false);
let timer = null;

const storageKey = computed(() => `cat_bkn_answers_${props.tryout?.id}_${page.props.auth.user.id}`);

const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

const subtestLabel = computed(() => {
    const type = currentQuestion.value?.type;
    if (type === 'TWK') return 'Tes Wawasan Kebangsaan (TWK)';
    if (type === 'TIU') return 'Tes Intelegensia Umum (TIU)';
    if (type === 'TKP') return 'Tes Karakteristik Pribadi (TKP)';
    return type || 'Tes Kompetensi Dasar';
});

const subtestTopic = computed(() => {
    const type = currentQuestion.value?.type;
    if (type === 'TWK') return 'Nasionalisme & Bela Negara';
    if (type === 'TIU') return 'Kemampuan Verbal & Logika';
    if (type === 'TKP') return 'Pelayanan Publik & Jejaring Kerja';
    return '';
});

const answeredCount = computed(() => Object.keys(answers.value).length);
const unansweredCount = computed(() => (props.questions?.length || 0) - Object.keys(answers.value).length);

// HITUNG PERSENTASE PROGRESS
const progressPercentage = computed(() => {
    const total = props.questions?.length || 0;
    if (total === 0) return 0;
    return Math.round((answeredCount.value / total) * 100);
});

// --- LOGIC ---
onMounted(() => {
    props.questions?.forEach(q => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });

    const savedAnswers = localStorage.getItem(storageKey.value);
    if (savedAnswers) {
        const parsed = JSON.parse(savedAnswers);
        answers.value = { ...answers.value, ...parsed };
    }
    
    timer = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else autoSubmit();
    }, 1000);
});

watch(answers, (newVal) => {
    localStorage.setItem(storageKey.value, JSON.stringify(newVal));
}, { deep: true });

onUnmounted(() => clearInterval(timer));

const autoSubmit = () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;
    router.post(route('tryout.finish', props.tryout?.id), { 
        answers: answers.value,
        mode: 'BKN' 
    }, {
        onSuccess: () => localStorage.removeItem(storageKey.value)
    });
};

const finishExam = () => showConfirmModal.value = true;
const goTo = (index) => { currentIndex.value = index; isSidebarOpen.value = false; };

// Fungsi Next (Hanya Pindah)
const next = () => { 
    if (currentIndex.value < (props.questions?.length - 1)) {
        currentIndex.value++; 
    } else {
        currentIndex.value = 0; // LOOPING
    }
};

// Fungsi Skip (Hapus Jawaban Lalu Pindah)
const skip = () => {
    if (currentQuestion.value) {
        delete answers.value[currentQuestion.value.id];
    }
    next();
};

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
};
</script>

<template>
    <Head :title="'CAT BKN: ' + (tryout?.title || 'Ujian')" />

    <div class="h-screen flex flex-col font-sans text-slate-800 bg-[#eef2f7] overflow-hidden">
        
        <header class="bg-[#1e60aa] text-white shadow-md z-50 shrink-0 h-16 flex items-center px-4 justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white p-1 rounded-md shrink-0">
                    <img src="/images/logo.png" alt="Logo" class="h-9 w-9 object-contain">
                </div>
                <div class="leading-tight hidden md:block">
                    <h1 class="font-bold text-lg tracking-wide">CAT BKN SIMULATION</h1>
                    <p class="text-[10px] opacity-80 uppercase font-medium">{{ tryout?.title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ page.props.auth?.user?.name }}</p>
                </div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-2 bg-white/20 rounded">
                    <span class="text-xl">☰</span>
                </button>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            
            <aside :class="[
                'absolute md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-300 flex flex-col transition-transform duration-300 transform shadow-lg md:shadow-none',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]">
                <div class="flex-1 overflow-y-auto px-7 py-4 custom-scrollbar">
                    <div class="grid grid-cols-5 gap-0.5 place-content-start">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                            :class="[
                                currentIndex === i 
                                    ? 'bg-[#1e60aa] text-white font-black ring-1 ring-blue-300 rounded-sm' 
                                    : (answers[q.id] 
                                        ? 'bg-emerald-600 text-white border-emerald-700 rounded-full font-bold' 
                                        : 'bg-red-600 text-white border-red-700 hover:bg-red-700 rounded-sm font-bold'
                                      )
                            ]"
                            class="h-6 w-full text-[9px] flex items-center justify-center transition-all shadow-sm border border-transparent">
                            {{ i + 1 }}
                        </button>
                    </div>
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
                            <p class="text-[10px] font-bold text-slate-900 uppercase leading-none truncate">
                                {{ page.props.auth?.user?.name }}
                                <span class="font-normal opacity-70 ml-1">({{ page.props.auth?.user?.username || page.props.auth?.user?.id }})</span>
                            </p>
                            <p class="text-[9px] text-slate-500 leading-none mt-1 truncate">{{ page.props.auth?.user?.email }}</p>
                            
                            <div class="mt-1 flex items-center gap-2">
                                <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                                    <div class="h-full bg-emerald-500 transition-all duration-500 ease-out" 
                                         :style="{ width: progressPercentage + '%' }"></div>
                                </div>
                                <span class="text-[8px] font-bold text-slate-600 tabular-nums">{{ progressPercentage }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 shrink-0 w-full md:w-auto">
                        <div class="bg-white border border-slate-300 rounded-lg shadow-sm px-4 py-1.5 flex items-center justify-center gap-2 flex-1">
                            <span class="text-sm">⏱️</span>
                            <span class="text-xl font-mono font-bold text-[#1e60aa] tabular-nums leading-none">{{ formatTime(timeLeft) }}</span>
                        </div>

                        <button @click="finishExam" class="flex items-center justify-center gap-2 w-full bg-rose-600 hover:bg-rose-700 text-white py-2 px-6 rounded-lg shadow-sm text-[10px] font-bold uppercase tracking-widest transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Selesai Ujian
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 pb-4 custom-scrollbar">
                    <div class="w-full bg-white rounded-lg shadow-sm border border-slate-300 overflow-hidden">
                        
                        <div class="bg-[#1e60aa] text-white px-5 py-2.5 flex items-center justify-between">
                            <span class="font-bold text-xs tracking-wide uppercase">{{ subtestTopic }}</span>
                        </div>

                        <div v-if="currentQuestion" class="p-5 md:p-8">
                            
                            <div v-if="currentQuestion?.image" class="mb-5 max-w-md mx-auto">
                                <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded border p-1 bg-slate-50 shadow-sm">
                            </div>

                            <div class="flex items-start gap-2 mb-6">
                                <span class="font-bold text-slate-900 text-[10px] md:text-xs shrink-0 mt-0.5">
                                    No. {{ currentIndex + 1 }}
                                </span>
                                <div class="text-[10px] md:text-xs text-slate-800 font-medium leading-relaxed whitespace-pre-wrap">
                                    <span v-html="currentQuestion?.content"></span>
                                </div>
                            </div>

                            <div class="space-y-2.5">
                                <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                                    class="flex items-start gap-3 p-2.5 rounded border-2 cursor-pointer transition-all hover:bg-blue-50 group"
                                    :class="answers[currentQuestion?.id] === key ? 'border-[#1e60aa] bg-blue-50' : 'border-slate-100 bg-white'">
                                    
                                    <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="hidden">
                                    
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px] transition-colors"
                                        :class="answers[currentQuestion?.id] === key 
                                            ? 'bg-[#1e60aa] border-[#1e60aa] text-white' 
                                            : 'border-slate-300 text-slate-400 group-hover:border-[#1e60aa] group-hover:text-[#1e60aa]'">
                                        {{ key.toUpperCase() }}
                                    </div>
                                    
                                    <span class="text-[10px] md:text-xs text-slate-700 pt-0.5 group-hover:text-slate-900">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-slate-300 py-3 px-4 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                    <div class="flex justify-start items-center gap-3">
                        
                        <button @click="next" 
                            class="flex items-center gap-2 px-5 py-2 bg-[#1e60aa] text-white rounded font-bold text-[10px] uppercase hover:bg-blue-800 shadow-md transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V7.24162C21 6.70327 20.7831 6.18763 20.3983 5.8122L17.5686 3.03123C17.1953 2.66442 16.6853 2.45838 16.1557 2.45838H5ZM5 5H15V8H5V5ZM5 10H19V19H5V10ZM14 17V13H10V17H14Z"/></svg>
                            Simpan dan Lanjutkan
                        </button>

                        <button @click="skip" 
                            class="flex items-center gap-2 px-5 py-2 bg-orange-500 text-white rounded font-bold text-[10px] uppercase hover:bg-orange-600 shadow-md transition">
                            Lewatkan
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>

                    </div>
                </div>

            </main>
        </div>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full overflow-hidden animate-in zoom-in duration-200">
                <div class="bg-rose-600 p-3 text-white text-center">
                    <h3 class="font-bold text-sm uppercase tracking-wide">Konfirmasi Selesai</h3>
                </div>
                <div class="p-5 text-center text-slate-700">
                    <p class="font-medium mb-1 text-sm">Anda memiliki <span class="font-black text-rose-600">{{ unansweredCount }}</span> soal belum dijawab.</p>
                    <p class="text-xs text-slate-500">Yakin ingin mengakhiri ujian sekarang?</p>
                </div>
                <div class="p-3 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button @click="showConfirmModal = false" class="flex-1 py-2 bg-white border border-slate-300 text-slate-700 font-bold rounded text-xs uppercase hover:bg-slate-100 transition">Batal</button>
                    <button @click="autoSubmit" class="flex-1 py-2 bg-emerald-600 text-white font-bold rounded text-xs uppercase hover:bg-emerald-700 shadow transition">Ya, Selesai</button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>