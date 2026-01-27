<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps(['questions', 'session', 'duration']);
const currentIdx = ref(0);
const answers = ref({});
const timeLeft = ref(props.duration * 60);

// Timer Logic
onMounted(() => {
    const timer = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
        else {
            clearInterval(timer);
            submitExam();
        }
    }, 1000);
});

const submitExam = () => {
    if(confirm('Yakin ingin mengakhiri ujian?')) {
        router.post(route('tryout.submit', props.session.id), { answers: answers.value });
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <div class="bg-indigo-600 p-4 text-white flex justify-between items-center shadow-lg">
            <span class="font-bold">SIMULASI SKD CPNS</span>
            <div class="bg-red-500 px-4 py-1 rounded font-mono text-xl">
                {{ Math.floor(timeLeft / 60) }}:{{ (timeLeft % 60).toString().padStart(2, '0') }}
            </div>
        </div>

        <div class="flex flex-1 p-6 gap-6 overflow-hidden">
            <div class="flex-1 bg-white rounded-2xl p-8 shadow-sm overflow-y-auto">
                <div class="mb-4 text-sm font-bold text-indigo-500 uppercase tracking-widest">
                    {{ questions[currentIdx].type }} - Soal {{ currentIdx + 1 }}
                </div>
                <p class="text-xl text-gray-800 mb-8">{{ questions[currentIdx].content }}</p>

                <div class="space-y-4">
                    <button v-for="(val, key) in JSON.parse(questions[currentIdx].options)" 
                        @click="answers[questions[currentIdx].id] = key"
                        :class="['w-full text-left p-4 rounded-xl border-2 transition', 
                        answers[questions[currentIdx].id] === key ? 'border-indigo-600 bg-indigo-50' : 'border-gray-100 hover:bg-gray-50']">
                        <span class="font-bold mr-4">{{ key.toUpperCase() }}.</span> {{ val }}
                    </button>
                </div>
            </div>

            <div class="w-80 bg-white rounded-2xl p-6 shadow-sm flex flex-col">
                <h3 class="font-bold mb-4 text-gray-700">Nomor Soal</h3>
                <div class="grid grid-cols-5 gap-2 flex-1 overflow-y-auto">
                    <button v-for="(q, i) in questions" @click="currentIdx = i"
                        :class="['h-10 rounded font-bold text-xs transition', 
                        currentIdx === i ? 'ring-2 ring-indigo-600' : '',
                        answers[q.id] ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-400']">
                        {{ i + 1 }}
                    </button>
                </div>
                <button @click="submitExam" class="mt-6 w-full bg-red-600 text-white py-3 rounded-xl font-bold shadow-lg shadow-red-100">
                    Selesai Ujian
                </button>
            </div>
        </div>
    </div>
</template>