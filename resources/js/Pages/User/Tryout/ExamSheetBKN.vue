<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    tryout: Object,
    questions: Array,
    attempt: Object
});

const page = usePage();

// --- 1. STATE MANAGEMENT ---
const currentIndex = ref(0);
const answers = ref({});
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 0);
const showNav = ref(false);
const isSubmitting = ref(false);
let timer = null;

// PROTEKSI: Computed property yang aman untuk mengambil soal saat ini
const currentQuestion = computed(() => {
    if (!props.questions || props.questions.length === 0) return null;
    return props.questions[currentIndex.value] || null;
});

// --- 2. LOGIKA TIMER ---
onMounted(() => {
    // Load jawaban yang tersimpan
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
    router.post(route('tryout.finish', props.tryout.id), { 
        answers: answers.value,
        mode: 'BKN' 
    });
};

// --- 3. NAVIGASI ---
const goTo = (index) => {
    currentIndex.value = index;
    showNav.value = false;
};

const next = () => { if (currentIndex.value < props.questions.length - 1) currentIndex.value++; };
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

    <div class="min-h-screen bg-[#f0f2f5] flex flex-col font-sans text-slate-700">
        
        <header class="bg-[#004a87] text-white p-3 md:p-4 shadow-lg z-50">
            <div class="max-w-[1440px] mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/Logo_BKN.png/600px-Logo_BKN.png" 
                         alt="BKN" class="w-8 h-8 md:w-10 md:h-10 object-contain bg-white rounded-full p-1">
                    <div class="hidden sm:block">
                        <h1 class="text-xs md:text-sm font-black uppercase tracking-tighter leading-none">Computer Assisted Test</h1>
                        <p class="text-[9px] font-bold text-blue-200 uppercase mt-1">Simulator Ujian Resmi</p>
                    </div>
                </div>
                <div class="bg-[#ffcc00] text-[#004a87] px-4 py-2 rounded-md font-black text-sm md:text-xl shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)] tabular-nums">
                    {{ formatTime(timeLeft) }}
                </div>
            </div>
        </header>

        <div class="flex-1 flex flex-col md:flex-row max-w-[1440px] mx-auto w-full p-2 md:p-4 gap-4 overflow-hidden">
            
            <aside class="hidden lg:flex flex-col w-64 gap-4">
                <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5 text-center">
                    <div class="w-24 h-32 bg-slate-100 mx-auto rounded-md mb-4 border-2 border-slate-200 flex items-center justify-center text-slate-300 overflow-hidden relative">
                        <img v-if="page.props.auth?.user?.image" :src="page.props.auth.user.image" class="w-full h-full object-cover">
                        <span v-else class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pas Foto</span>
                    </div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Peserta</p>
                    <p class="text-[11px] font-black text-slate-900 uppercase leading-tight truncate mb-4">{{ page.props.auth?.user?.name || 'Peserta' }}</p>
                    <div class="h-px bg-slate-100 mb-4"></div>
                    <p class="text-[9px] font-bold text-indigo-600 uppercase italic">{{ tryout?.title }}</p>
                </div>
            </aside>

            <main class="flex-1 flex flex-col bg-white rounded-lg shadow-md border border-slate-200 overflow-hidden relative">
                <div class="bg-slate-50 border-b border-slate-200 p-3 flex justify-between items-center">
                    <span class="bg-[#004a87] text-white px-3 py-1 rounded text-[10px] font-black uppercase">
                        Soal Nomor {{ currentIndex + 1 }}
                    </span>
                    <button @click="showNav = true" class="lg:hidden bg-white text-[#004a87] border border-blue-200 px-3 py-1 rounded text-[9px] font-black uppercase shadow-sm active:scale-95 transition-transform">
                        Daftar Soal
                    </button>
                </div>

                <div v-if="currentQuestion" class="flex-1 p-5 md:p-10 overflow-y-auto bg-white">
                    <div v-if="currentQuestion?.image" class="mb-6 max-w-full overflow-hidden rounded-lg border border-slate-100">
                        <img :src="currentQuestion.image" class="max-h-64 mx-auto object-contain" alt="Gambar Soal">
                    </div>

                    <div class="text-sm md:text-base leading-relaxed text-slate-800 mb-8" v-html="currentQuestion.question_text"></div>
                    
                    <div class="space-y-3">
                        <label v-for="(option, key) in currentQuestion.options" :key="key" 
                            class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 cursor-pointer transition-all hover:bg-blue-50/50 group"
                            :class="{'bg-blue-50 border-blue-300 ring-1 ring-blue-100': answers[currentQuestion.id] === key}">
                            <input type="radio" :name="'q-'+currentQuestion.id" :value="key" v-model="answers[currentQuestion.id]" class="mt-1 w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span class="text-xs font-black text-blue-900 w-4">{{ key }}.</span>
                            <span class="text-xs md:text-sm font-bold text-slate-600 group-hover:text-slate-900 flex-1">{{ option }}</span>
                        </label>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center">
                    <button @click="prev" :disabled="currentIndex === 0" class="px-5 py-2.5 bg-white border border-slate-300 rounded-md text-[10px] font-black uppercase tracking-widest text-slate-500 shadow-sm disabled:opacity-30">Sebelumnya</button>
                    <div class="flex gap-2">
                        <button v-if="currentIndex < (questions?.length - 1)" @click="next" class="px-5 py-2.5 bg-[#f37021] text-white rounded-md text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-[#d9611a] transition-colors">Selanjutnya</button>
                        <button v-else @click="autoSubmit" class="px-5 py-2.5 bg-red-600 text-white rounded-md text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-red-700 transition-colors">Selesai Ujian</button>
                    </div>
                </div>
            </main>

            <aside class="hidden lg:block w-72 bg-white rounded-xl shadow-sm border border-slate-200 p-5 overflow-y-auto max-h-[calc(100vh-140px)]">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 text-center">Peta Soal</p>
                <div class="grid grid-cols-5 gap-2">
                    <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                        :class="[
                            currentIndex === i ? 'ring-2 ring-blue-600 ring-offset-2 scale-105 z-10' : '',
                            answers[q.id] ? 'bg-green-500 text-white border-green-600 shadow-sm' : 'bg-white text-slate-400 border-slate-200'
                        ]"
                        class="h-9 border rounded-md text-[10px] font-black flex items-center justify-center transition-all hover:border-blue-400">
                        {{ i + 1 }}
                    </button>
                </div>
            </aside>
        </div>

        <div v-if="showNav" class="fixed inset-0 z-[100] lg:hidden">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showNav = false"></div>
            <div class="absolute right-0 top-0 bottom-0 w-72 bg-white p-6 shadow-2xl animate-in slide-in-from-right duration-300 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-black text-xs uppercase text-slate-900">Navigasi Soal</h3>
                    <button @click="showNav = false" class="text-slate-400 text-xl">✕</button>
                </div>
                <div class="grid grid-cols-4 gap-2 overflow-y-auto flex-1 p-1">
                    <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                        :class="[
                            currentIndex === i ? 'ring-2 ring-blue-600 ring-offset-1' : '',
                            answers[q.id] ? 'bg-green-500 text-white border-green-600 shadow-md' : 'bg-white text-slate-400 border-slate-200'
                        ]"
                        class="h-10 border rounded-lg text-[10px] font-black flex items-center justify-center">
                        {{ i + 1 }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.transition-all { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.animate-in { animation: fadeIn 0.3s ease-out; }
</style>