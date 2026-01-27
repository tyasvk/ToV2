<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    attempt: Object,
    questions: Array
});

const currentIdx = ref(0);
const currentQ = computed(() => props.questions[currentIdx.value]);

const jump = (i) => {
    currentIdx.value = i;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Navigasi Peta Soal (Dibuat Lebih Kecil)
const getNavClass = (index, q) => {
    const isCurrent = currentIdx.value === index;
    const base = "h-7 w-7 md:h-8 md:w-8 flex items-center justify-center rounded text-[9px] font-black transition-all border ";
    
    if (isCurrent) return base + "border-black bg-black text-white ring-1 ring-offset-1 ring-black z-10";
    if (q.is_correct) return base + "bg-emerald-500 text-white border-emerald-600 shadow-sm";
    return base + "bg-rose-500 text-white border-rose-600 shadow-sm";
};

// Card Pilihan Jawaban (Dibuat Super Slim)
const getOptionClass = (key, q) => {
    const isUser = q.user_selected_answer === key;
    const isCorrect = q.correct_answer === key;

    if (isCorrect) return 'bg-emerald-50 border-emerald-500 ring-1 ring-emerald-500 z-10';
    if (isUser && !isCorrect) return 'bg-rose-50 border-rose-500 ring-1 ring-rose-500 z-10';
    return 'bg-white border-gray-100 opacity-70';
};
</script>

<template>
    <Head title="Review Compact" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center py-0">
                <div class="flex items-center gap-2">
                    <Link :href="route('user.history')" class="p-1 hover:bg-gray-100 rounded transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <h2 class="text-xs font-black uppercase tracking-tighter">Review Sesi #{{ attempt.id }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right border-r pr-3 border-gray-200">
                        <span class="block text-[7px] font-bold text-gray-400 uppercase">Skor</span>
                        <span class="text-base font-black leading-none">{{ attempt.total_score }}</span>
                    </div>
                    <span class="text-[8px] font-black bg-gray-100 px-2 py-1 rounded">
                        {{ currentIdx + 1 }} / {{ questions.length }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-3 bg-white min-h-screen">
            <div class="max-w-7xl mx-auto px-3 md:px-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    
                    <div class="flex-1 space-y-3">
                        <div class="border border-gray-200 p-4 md:p-6 bg-white relative">
                            <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-50">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Pertanyaan {{ currentIdx + 1 }}</span>
                                <div class="flex gap-2">
                                    <span v-if="currentQ.is_correct" class="text-[8px] font-black text-emerald-600 uppercase italic">CORRECT_RESULT</span>
                                    <span v-else class="text-[8px] font-black text-rose-600 uppercase italic">INCORRECT_RESULT</span>
                                </div>
                            </div>

                            <div class="mb-6">
                                <p class="text-sm md:text-base font-bold text-slate-900 leading-snug" v-html="currentQ.question_text || currentQ.content"></p>
                            </div>

                            <div class="grid grid-cols-1 gap-1.5">
                                <div v-for="(val, key) in (currentQ.options || {'a': currentQ.option_a, 'b': currentQ.option_b, 'c': currentQ.option_c, 'd': currentQ.option_d, 'e': currentQ.option_e})" 
                                    :key="key"
                                    :class="['group flex items-center p-2.5 border transition-all', getOptionClass(key, currentQ)]">
                                    
                                    <div :class="[currentQ.correct_answer === key ? 'bg-black text-white' : 'bg-gray-100 text-gray-400']" 
                                         class="w-6 h-6 shrink-0 flex items-center justify-center text-[10px] font-black mr-3">
                                        {{ key.toUpperCase() }}
                                    </div>

                                    <div class="flex-1 flex justify-between items-center pr-1">
                                        <span class="text-[11px] font-semibold text-slate-700 leading-tight uppercase tracking-tight">{{ val }}</span>
                                        <div v-if="currentQ.correct_answer === key" class="flex items-center gap-1">
                                            <div class="w-1 h-1 bg-emerald-500 rounded-full"></div>
                                            <span class="text-[7px] font-black text-emerald-600 uppercase">Kunci</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 border-x border-b border-gray-200 p-4">
                            <h3 class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em] mb-3">Analisis_Pembahasan</h3>
                            <div class="text-[11px] leading-relaxed text-slate-600 font-medium prose-tight" v-html="currentQ.explanation || 'N/A: No Data'"></div>
                        </div>

                        <div class="flex gap-1 pt-2">
                            <button @click="jump(currentIdx - 1)" :disabled="currentIdx === 0" 
                                class="flex-1 py-3 bg-white border border-gray-200 text-[10px] font-black uppercase tracking-widest disabled:opacity-20 transition-all">
                                Previous
                            </button>
                            <button @click="jump(currentIdx + 1)" :disabled="currentIdx === questions.length - 1" 
                                class="flex-1 py-3 bg-black text-white text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 disabled:opacity-20 transition-all shadow-md">
                                Next Soal
                            </button>
                        </div>
                    </div>

                    <div class="w-full lg:w-60 shrink-0 lg:sticky lg:top-4 h-fit">
                        <div class="border border-gray-200 p-3 bg-white">
                            <div class="text-[9px] font-black text-gray-400 uppercase mb-3 text-center tracking-widest">Navigator_Grid</div>
                            
                            <div class="grid grid-cols-8 lg:grid-cols-5 gap-1">
                                <button v-for="(q, index) in questions" :key="q.id" 
                                    @click="jump(index)"
                                    :class="getNavClass(index, q)"
                                >
                                    {{ index + 1 }}
                                </button>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-50 flex flex-wrap justify-center gap-3">
                                <div class="flex items-center gap-1.5 text-[8px] font-black text-gray-300 uppercase">
                                    <div class="w-2 h-2 bg-emerald-500"></div> Benar
                                </div>
                                <div class="flex items-center gap-1.5 text-[8px] font-black text-gray-300 uppercase">
                                    <div class="w-2 h-2 bg-rose-500"></div> Salah
                                </div>
                                <div class="flex items-center gap-1.5 text-[8px] font-black text-gray-300 uppercase">
                                    <div class="w-2 h-2 bg-black"></div> Aktif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Menghilangkan margin berlebih pada elemen prose jika ada */
.prose-tight :deep(p) { margin-bottom: 0.4rem; }
.transition-all { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
</style>