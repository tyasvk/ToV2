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
const timeLeft = ref(props.tryout?.duration_minutes * 60 || 0);
const isSubmitting = ref(false);
const showConfirmModal = ref(false);
let timer = null;

// Key unik untuk localStorage agar tidak tertukar antar tryout atau user
const storageKey = computed(() => `cat_bkn_answers_${props.tryout?.id}_${page.props.auth.user.id}`);

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
    // A. Muat jawaban dari Database (jika ada)
    props.questions?.forEach(q => {
        if (q.user_answer) answers.value[q.id] = q.user_answer;
    });

    // B. Muat jawaban dari LocalStorage (jika ada data yang belum terkirim ke server)
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

// C. Simpan ke LocalStorage setiap kali ada perubahan pada 'answers'
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
        onSuccess: () => {
            // Hapus cache lokal setelah berhasil submit
            localStorage.removeItem(storageKey.value);
        }
    });
};

const finishExam = () => {
    showConfirmModal.value = true;
};

// --- 3. NAVIGASI ---
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
    <Head :title="'CAT BKN: ' + (tryout?.title || 'Ujian')" />

    <div class="min-h-screen bg-[#f4f8fb] flex flex-col font-sans text-slate-700 relative">
        
        <header class="bg-[#f0f7ff] text-slate-900 p-3 md:p-4 shadow-sm border-b border-blue-100 sticky top-0 z-50">
            <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="bg-white p-2 rounded-xl shadow-sm border border-blue-50">
                        <img src="/images/logo.png" alt="Logo" class="h-10 md:h-14 w-auto object-contain">
                    </div>
                    <div class="text-left border-l border-blue-200 pl-4">
                        <h1 class="text-base md:text-xl font-black uppercase tracking-tighter leading-none text-[#004a87]">Simulasi CAT</h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-1 tracking-tight">CPNS Nusantara</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto justify-end text-center">
                    <div class="grid grid-cols-2 md:flex items-center gap-4 md:gap-8 w-full md:w-auto overflow-x-auto no-scrollbar justify-end">
                        <div class="flex flex-col items-center min-w-[80px]">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Batas Waktu</span>
                            <span class="text-xs md:text-sm font-black text-slate-900">{{ tryout?.duration_minutes }} Menit</span>
                        </div>
                        <div class="flex flex-col items-center border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Jumlah Soal</span>
                            <span class="text-xs md:text-sm font-black text-slate-900">{{ questions?.length || 0 }}</span>
                        </div>
                        <div class="flex flex-col items-center border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[10px] uppercase font-bold text-emerald-500 tracking-wider">Soal Dijawab</span>
                            <span class="text-xs md:text-sm font-black text-emerald-600">{{ answeredCount }}</span>
                        </div>
                        <div class="flex flex-col items-center border-l border-blue-100 pl-4 md:pl-8 min-w-[80px]">
                            <span class="text-[10px] uppercase font-bold text-rose-500 tracking-wider">Belum Dijawab</span>
                            <span class="text-xs md:text-sm font-black text-rose-600">{{ unansweredCount }}</span>
                        </div>
                    </div>

                    <button @click="finishExam" class="w-full md:w-auto px-8 py-2.5 bg-[#004a87] hover:bg-blue-800 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg transition-all active:scale-95">
                        Selesai Ujian
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-[1440px] mx-auto w-full p-3 md:p-4 space-y-4">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col md:flex-row items-center md:items-start gap-5">
                <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center overflow-hidden flex-shrink-0 relative">
                    <img v-if="page.props.auth?.user?.image" :src="page.props.auth?.user?.image" class="w-full h-full object-cover">
                    <span v-else class="text-[9px] font-black text-slate-300">PHOTO</span>
                </div>
                <div class="flex-1 text-center md:text-left w-full">
                    <div class="grid grid-cols-[100px_10px_1fr] md:grid-cols-[120px_10px_1fr] text-xs md:text-sm text-slate-600 font-medium uppercase leading-relaxed">
                        <span>Nama Peserta</span>
                        <span>:</span>
                        <span class="text-slate-900 text-left">{{ page.props.auth?.user?.name }}</span>
                        
                        <span>Email</span>
                        <span>:</span>
                        <span class="text-slate-900 lowercase text-left">{{ page.props.auth?.user?.email }}</span>
                    </div>
                    <div class="mt-3 flex justify-center md:justify-start">
                        <span class="bg-blue-50 text-[#004a87] border border-blue-100 px-3 py-1 rounded-lg text-[10px] md:text-[11px] font-black uppercase tracking-wider shadow-sm">
                            {{ subtestLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="bg-slate-50 px-6 py-3 border-b border-slate-200 flex justify-between items-center flex-none">
                    <span class="text-xs font-black text-[#004a87] uppercase tracking-widest">Soal No. {{ currentIndex + 1 }}</span>
                </div>

                <div v-if="currentQuestion" class="pt-2 px-6 pb-3 md:pt-3 md:px-12 md:pb-3 flex-none text-left animate-in fade-in">
                    <div v-if="currentQuestion?.image" class="mb-4 rounded-2xl overflow-hidden border border-slate-100 max-w-md mx-auto shadow-sm">
                        <img :src="'/storage/' + currentQuestion.image" class="w-full h-auto object-contain" alt="Gambar Soal">
                    </div>

                    <div class="text-[13px] md:text-base leading-relaxed text-slate-800 mb-2 font-medium" v-html="currentQuestion?.content"></div>
                    
                    <div class="space-y-1">
                        <label v-for="(option, key) in currentQuestion?.options" :key="key" 
                            class="flex items-start gap-3 p-1.5 rounded-lg border border-slate-100 cursor-pointer transition-all hover:bg-slate-50 group"
                            :class="{'bg-blue-50 border-blue-300 ring-1 ring-blue-50': answers[currentQuestion?.id] === key}">
                            <input type="radio" :name="'q-'+currentQuestion?.id" :value="key" v-model="answers[currentQuestion?.id]" class="mt-1 w-4 h-4 text-blue-600 border-slate-300">
                            <span class="text-xs font-black text-[#004a87] w-4">{{ key }}.</span>
                            <span class="text-[12px] md:text-sm font-bold text-slate-600 group-hover:text-slate-900 flex-1">{{ option }}</span>
                        </label>
                    </div>
                </div>

                <div class="px-6 py-2 bg-slate-50 border-t border-slate-200 flex justify-start items-center gap-3 flex-none">
                    <button @click="next" class="px-6 py-2.5 bg-white border border-slate-300 rounded-xl text-[10px] font-black uppercase text-slate-500 hover:bg-slate-100 transition-colors shadow-sm">
                        Lewatkan Soal Ini
                    </button>
                    <button @click="next" class="px-8 py-2.5 bg-[#f37021] text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-orange-600 transition-colors">
                        Simpan dan Lanjutkan
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-5 mb-28">
                <div v-if="questions?.length > 0" class="flex flex-wrap justify-center gap-2">
                    <button v-for="(q, i) in questions" :key="q.id" @click="goTo(i)"
                        :class="[
                            currentIndex === i ? 'ring-2 ring-blue-600 ring-offset-2 scale-110 z-10 shadow-lg' : '',
                            answers[q.id] ? 'bg-emerald-500 text-white border-emerald-600 shadow-sm' : 'bg-white text-slate-400 border-slate-200 shadow-sm'
                        ]"
                        class="w-8 h-8 border rounded-lg text-[9px] font-black flex items-center justify-center transition-all hover:border-blue-400 active:scale-90">
                        {{ i + 1 }}
                    </button>
                </div>
            </div>
        </main>

        <div v-if="showConfirmModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden animate-in zoom-in duration-300">
                <div class="bg-rose-600 p-4 text-white text-center">
                    <h3 class="text-xl font-black uppercase tracking-widest">PERHATIAN!!!</h3>
                </div>
                <div class="p-8 text-slate-700">
                    <p class="text-lg font-bold mb-6">SOAL BELUM DIJAWAB : <span class="text-rose-600">{{ unansweredCount }}</span></p>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p class="font-bold text-slate-900">Apakah Anda ingin mengakhiri simulasi ujian ini?</p>
                        <p>Jika <span class="font-bold text-emerald-600">"Ya"</span> maka Anda sudah dinyatakan selesai mengikuti simulasi ujian, dan Anda tidak bisa memperbaiki lembar kerja Anda.</p>
                        <p>Jika <span class="font-bold text-rose-600">"Tidak"</span> maka anda akan kembali ke lembar kerja dan silahkan untuk melanjutkan menjawab atau memperbaiki jawaban anda.</p>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center gap-4">
                    <button @click="autoSubmit" class="min-w-[120px] px-8 py-3 bg-emerald-600 text-white rounded-xl font-black uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg active:scale-95">Ya</button>
                    <button @click="showConfirmModal = false" class="min-w-[120px] px-8 py-3 bg-rose-600 text-white rounded-xl font-black uppercase tracking-widest hover:bg-rose-700 transition-all shadow-lg active:scale-95">Tidak</button>
                </div>
            </div>
        </div>

        <div class="fixed bottom-6 right-6 z-[100]">
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

@keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.animate-in { animation: zoomIn 0.2s ease-out forwards; }
</style>