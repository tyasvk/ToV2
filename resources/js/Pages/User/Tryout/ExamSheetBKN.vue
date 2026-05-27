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

    <div class="h-screen flex flex-col font-sans text-slate-800 bg-slate-50 overflow-hidden">
        
        <header class="bg-[#1e60aa] text-white shadow-sm z-50 shrink-0 h-14 flex items-center px-3 sm:px-4 justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-white p-1 rounded shrink-0">
                    <img src="/images/logo.png" alt="Logo" class="h-6 w-6 sm:h-7 sm:w-7 object-contain">
                </div>
                <div class="leading-tight hidden sm:block">
                    <h1 class="font-medium text-sm tracking-wide">CAT BKN SIMULATION</h1>
                    <p class="text-[9px] opacity-80 uppercase font-medium mt-0.5">{{ tryout?.title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-medium">{{ page.props.auth?.user?.name }}</p>
                </div>
                <button @click="isSidebarOpen = !isSidebarOpen" class="md:hidden p-1.5 bg-white/10 hover:bg-white/20 rounded transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden relative">
            
            <aside :class="[
                'absolute md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 transform shadow-xl md:shadow-none',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]">
                <div class="p-3 border-b border-slate-100 flex justify-between items-center md:hidden">
                    <span class="text-xs font-medium text-slate-600 uppercase tracking-wider">Navigasi Soal</span>
                    <button @click="isSidebarOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-4 custom-scrollbar">
                    <div class="grid grid-cols-5 gap-1.5 place-content-start">
                        <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                            :class="[
                                currentIndex === i 
                                    ? 'bg-[#1e60aa] text-white ring-2 ring-blue-200 rounded-md font-medium' 
                                    : (answers[q.id] 
                                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-medium' 
                                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 rounded-md font-medium'
                                      )
                            ]"
                            class="h-8 w-full text-[11px] flex items-center justify-center transition-all shadow-sm">
                            {{ i + 1 }}
                        </button>
                    </div>
                </div>
            </aside>

            <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-30 md:hidden backdrop-blur-sm transition-opacity"></div>

            <main class="flex-1 flex flex-col relative overflow-hidden bg-slate-50">
                
                <div class="flex flex-col sm:flex-row items-stretch px-3 sm:px-4 py-3 shrink-0 gap-3">
                    
                    <div class="flex-1 flex items-stretch bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                        <div class="w-12 sm:w-16 bg-slate-100 shrink-0 relative border-r border-slate-100 flex items-center justify-center">
                            <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover absolute inset-0">
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="min-w-0 flex-1 flex flex-col justify-center p-2.5">
                            <div class="flex justify-between items-start">
                                <div class="truncate pr-2">
                                    <p class="text-[11px] sm:text-xs font-medium text-slate-800 uppercase leading-none truncate">
                                        {{ page.props.auth?.user?.name }}
                                    </p>
                                    <p class="text-[9px] sm:text-[10px] text-slate-500 mt-1 truncate">{{ page.props.auth?.user?.email }}</p>
                                </div>
                                <span class="text-[9px] text-slate-400 border border-slate-200 px-1.5 py-0.5 rounded shrink-0">{{ page.props.auth?.user?.username || page.props.auth?.user?.id }}</span>
                            </div>
                            
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 transition-all duration-500 ease-out" :style="{ width: progressPercentage + '%' }"></div>
                                </div>
                                <span class="text-[9px] font-medium text-slate-500 tabular-nums leading-none">{{ progressPercentage }}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex sm:flex-col gap-2 shrink-0 w-full sm:w-auto">
                        <div class="bg-white border border-slate-200 rounded-lg shadow-sm px-4 flex items-center justify-center gap-2 flex-1 sm:flex-none h-10 sm:h-auto sm:py-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#1e60aa]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-base sm:text-lg font-medium text-[#1e60aa] tabular-nums leading-none mt-0.5">{{ formatTime(timeLeft) }}</span>
                        </div>
                        <button @click="finishExam" class="flex items-center justify-center gap-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 px-4 rounded-lg text-[10px] sm:text-xs font-medium uppercase tracking-wider transition-colors h-10 sm:h-auto sm:py-2 flex-1 sm:flex-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Selesai
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-3 sm:px-4 pb-4 custom-scrollbar">
                    <div class="w-full bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden min-h-full flex flex-col">
                        
                        <div class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex items-center justify-between">
                            <span class="font-medium text-[11px] sm:text-xs text-slate-600 tracking-wide uppercase">{{ subtestTopic || subtestLabel }}</span>
                            <span class="text-[10px] text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-200">Soal {{ currentIndex + 1 }} dari {{ questions.length }}</span>
                        </div>

                        <div v-if="currentQuestion" class="p-4 sm:p-6 lg:p-8 flex-1">
                            
                            <div v-if="currentQuestion?.image" class="mb-5 max-w-sm mx-auto">
                                <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto rounded-lg border border-slate-200 p-1 bg-white shadow-sm">
                            </div>

                            <div class="flex items-start gap-3 mb-6">
                                <div class="bg-slate-100 text-slate-500 font-medium text-[10px] sm:text-xs px-2 py-1 rounded shrink-0 mt-0.5">
                                    {{ currentIndex + 1 }}.
                                </div>
                                <div class="text-[11px] sm:text-sm text-slate-700 font-normal leading-relaxed whitespace-pre-wrap">
                                    <span v-html="currentQuestion?.content"></span>
                                </div>
                            </div>

                            <div class="space-y-2 max-w-3xl">
                                <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                                    class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-all hover:bg-slate-50 active:scale-[0.99] group"
                                    :class="answers[currentQuestion?.id] === key ? 'border-[#1e60aa] bg-blue-50/50' : 'border-slate-200 bg-white'">
                                    
                                    <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="hidden">
                                    
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 font-medium text-[10px] sm:text-xs transition-colors mt-0.5"
                                        :class="answers[currentQuestion?.id] === key 
                                            ? 'bg-[#1e60aa] text-white' 
                                            : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">
                                        {{ key.toUpperCase() }}
                                    </div>
                                    
                                    <span class="text-[11px] sm:text-xs text-slate-600 pt-1 group-hover:text-slate-800 font-normal leading-relaxed">{{ option }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border-t border-slate-200 p-3 sm:p-4 shrink-0 shadow-sm z-10">
                    <div class="flex flex-row items-center gap-2 sm:gap-3 max-w-4xl mx-auto">
                        
                        <button @click="next" 
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 sm:py-3 bg-[#1e60aa] text-white rounded-lg font-medium text-[10px] sm:text-xs uppercase hover:bg-blue-800 transition-colors">
                            <span class="hidden sm:inline">Simpan dan Lanjutkan</span>
                            <span class="sm:hidden">Simpan & Lanjut</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <button @click="skip" 
                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 sm:py-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg font-medium text-[10px] sm:text-xs uppercase hover:bg-amber-100 transition-colors">
                            Lewatkan
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                        </button>

                    </div>
                </div>

            </main>
        </div>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full overflow-hidden animate-in zoom-in duration-200 border border-slate-100">
                <div class="p-6 text-center text-slate-700">
                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <h3 class="font-medium text-base mb-2">Konfirmasi Akhiri Ujian</h3>
                    <p class="font-normal text-sm text-slate-500 mb-1">Anda masih memiliki <span class="font-medium text-rose-500">{{ unansweredCount }} soal</span> yang belum dijawab.</p>
                    <p class="text-xs text-slate-400 mt-3">Apakah Anda yakin ingin menyelesaikan ujian sekarang? Sisa waktu Anda akan hangus.</p>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                    <button @click="showConfirmModal = false" class="flex-1 py-2.5 bg-white border border-slate-300 text-slate-600 font-medium rounded-lg text-xs hover:bg-slate-50 transition-colors">Batal</button>
                    <button @click="autoSubmit" class="flex-1 py-2.5 bg-[#1e60aa] text-white font-medium rounded-lg text-xs hover:bg-blue-800 transition-colors shadow-sm">Ya, Selesai</button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Animasi sederhana untuk modal */
.animate-in {
    animation: zoomIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>