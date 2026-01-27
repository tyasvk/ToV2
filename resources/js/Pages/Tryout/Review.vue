<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    attempt: Object, // Berisi hasil skor dan jawaban user
    questions: Array // Berisi soal, kunci jawaban, dan pembahasan
});

// --- STATE ---
const currentNumber = ref(0);

// --- DATA BINDING ---
const currentQuestion = computed(() => props.questions[currentNumber.value]);
const userAnswers = computed(() => props.attempt.answers || {}); // Format: { question_id: 'a' }

// Cek apakah jawaban user benar
const isCorrect = (questionId) => {
    return userAnswers.value[questionId] === props.questions.find(q => q.id === questionId)?.correct_answer;
};

// Nama Subtes Dinamis
const subtestName = computed(() => {
    const type = currentQuestion.value?.type?.toUpperCase();
    if (type === 'TWK') return 'Tes Wawasan Kebangsaan - TWK';
    if (type === 'TIU') return 'Tes Intelegensia Umum - TIU';
    if (type === 'TKP') return 'Tes Karakteristik Pribadi - TKP';
    return type || 'Umum';
});
</script>

<template>
    <Head title="Pembahasan Ujian" />

    <div class="min-h-screen bg-[#f8fafc] flex flex-col font-sans text-[#334155] select-none">
        
        <header class="h-20 bg-[#334155] border-b-4 border-[#d4ae28] px-6 flex justify-between items-center shrink-0 shadow-md sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-contain bg-no-repeat bg-center" 
                     style="background-image: url('https://upload.wikimedia.org/wikipedia/commons/thumb/6/66/Garuda_Pancasila_Coat_of_Arms_of_Indonesia.svg/200px-Garuda_Pancasila_Coat_of_Arms_of_Indonesia.svg.png');">
                </div>
                <div class="text-white leading-none">
                    <h1 class="font-black text-lg tracking-tighter uppercase">Review Hasil Ujian</h1>
                    <p class="text-[7px] font-black tracking-[0.2em] opacity-60 mt-1 uppercase">Evaluasi & Pembahasan Mandiri</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col items-end text-white mr-4">
                    <p class="text-[8px] font-black opacity-50 uppercase tracking-widest">Skor Total</p>
                    <p class="text-xl font-black text-[#d4ae28]">{{ attempt.total_score }}</p>
                </div>
                <Link :href="route('tryout.index')" 
                    class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest transition-all border border-white/10">
                    Kembali Ke Dashboard
                </Link>
            </div>
        </header>

        <main class="flex-1 max-w-5xl mx-auto w-full p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-8 space-y-4">
                
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex justify-between items-center">
                    <div>
                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-widest block mb-1">Kategori Soal</span>
                        <span class="text-[10px] font-black text-[#0d7e88] uppercase tracking-tight">{{ subtestName }}</span>
                    </div>
                    <div :class="isCorrect(currentQuestion.id) ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'" 
                         class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase border border-current opacity-80">
                        {{ isCorrect(currentQuestion.id) ? 'Jawaban Benar' : 'Jawaban Salah' }}
                    </div>
                </div>

                <div class="bg-white border-t-4 border-[#334155] rounded-2xl shadow-sm p-6 md:p-10 space-y-6">
                    <div class="flex items-center gap-2">
                        <h2 class="font-black text-slate-400 text-[10px] uppercase tracking-tight">Soal No. {{ currentNumber + 1 }}</h2>
                    </div>

                    <div class="space-y-4">
                        <div v-if="currentQuestion.image" class="inline-block p-1 bg-slate-50 rounded-lg border border-slate-100">
                            <img :src="'/storage/' + currentQuestion.image" class="max-h-48 object-contain rounded-md" />
                        </div>
                        <p class="text-[10px] md:text-[11px] font-bold text-slate-800 leading-relaxed uppercase tracking-tight text-justify">
                            {{ currentQuestion.question_text || currentQuestion.content }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <div v-for="(val, key) in (currentQuestion.options || {
                            'a': currentQuestion.option_a, 'b': currentQuestion.option_b, 'c': currentQuestion.option_c, 'd': currentQuestion.option_d, 'e': currentQuestion.option_e
                        })" :key="key"
                            :class="[
                                currentQuestion.correct_answer === key ? 'border-emerald-500 bg-emerald-50' : 
                                (userAnswers[currentQuestion.id] === key ? 'border-rose-500 bg-rose-50' : 'border-slate-100 bg-white')
                            ]"
                            class="flex items-center gap-3 p-2 rounded-xl border-2 text-left transition-all">
                            
                            <span :class="[
                                currentQuestion.correct_answer === key ? 'bg-emerald-500 text-white' : 
                                (userAnswers[currentQuestion.id] === key ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-400')
                            ]" 
                            class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center font-black uppercase text-[10px]">
                                {{ key }}
                            </span>

                            <div class="flex-1 flex justify-between items-center pr-2">
                                <span class="text-[10px] font-black uppercase tracking-wide leading-tight" 
                                      :class="currentQuestion.correct_answer === key ? 'text-emerald-900' : (userAnswers[currentQuestion.id] === key ? 'text-rose-900' : 'text-slate-600')">
                                    {{ val }}
                                </span>
                                <span v-if="currentQuestion.correct_answer === key" class="text-[7px] font-black text-emerald-600 uppercase">Kunci</span>
                                <span v-else-if="userAnswers[currentQuestion.id] === key" class="text-[7px] font-black text-rose-600 uppercase tracking-tighter">Jawaban Anda</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-slate-50 border-l-4 border-[#d4ae28] p-5 rounded-r-xl">
                        <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span>📖</span> Pembahasan & Analisis
                        </h3>
                        <div class="text-[10px] md:text-[11px] font-bold text-slate-600 leading-relaxed uppercase tracking-tight text-justify">
                            {{ currentQuestion.explanation || 'Pembahasan belum tersedia untuk soal ini.' }}
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button @click="currentNumber--" :disabled="currentNumber === 0" 
                        class="flex-1 py-3 bg-white border border-slate-200 rounded-xl font-black text-[9px] uppercase tracking-widest text-slate-400 disabled:opacity-20 hover:bg-slate-50 transition-all">
                        Sebelumnya
                    </button>
                    <button @click="currentNumber++" :disabled="currentNumber === questions.length - 1" 
                        class="flex-1 py-3 bg-[#334155] text-white rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-[#1e293b] transition-all">
                        Berikutnya
                    </button>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm sticky top-28">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] text-center mb-4">Navigasi Pembahasan</p>
                    <div class="grid grid-cols-5 sm:grid-cols-6 lg:grid-cols-5 gap-1.5">
                        <button v-for="(q, index) in questions" :key="q.id" 
                            @click="currentNumber = index"
                            :class="[
                                currentNumber === index ? 'ring-2 ring-orange-400 z-10 scale-105 shadow-md' : '',
                                isCorrect(q.id) ? 'bg-emerald-500 border-emerald-600' : 'bg-rose-500 border-rose-600'
                            ]"
                            class="aspect-square rounded-lg flex items-center justify-center text-[9px] font-black border transition-all text-white">
                            {{ index + 1 }}
                        </button>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded"></div>
                            <span class="text-[8px] font-black text-slate-400 uppercase">Benar</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-rose-500 rounded"></div>
                            <span class="text-[8px] font-black text-slate-400 uppercase">Salah</span>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
/* No Italics */
* { font-style: normal !important; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>