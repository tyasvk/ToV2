<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ tryout: Object, questions: Array });

const form = useForm({
    type: 'TWK',
    content: '',
    options: { a: '', b: '', c: '', d: '', e: '' },
    correct_answer: '', // Untuk TWK/TIU
    tkp_scores: { a: 1, b: 2, c: 3, d: 4, e: 5 } // Untuk TKP
});

const submit = () => {
    form.post(route('admin.questions.store', props.tryout.id), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <h3 class="font-black text-gray-800 mb-6 uppercase tracking-tighter">Tambah Soal Baru</h3>
        
        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Materi Soal</label>
                <select v-model="form.type" class="w-full border-gray-100 rounded-xl bg-gray-50 font-bold text-sm focus:ring-indigo-500">
                    <option value="TWK">TWK (Wawasan Kebangsaan)</option>
                    <option value="TIU">TIU (Intelegensia Umum)</option>
                    <option value="TKP">TKP (Karakteristik Pribadi)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Pertanyaan</label>
                <textarea v-model="form.content" rows="4" class="w-full border-gray-100 rounded-xl bg-gray-50 text-sm focus:ring-indigo-500" placeholder="Tulis soal di sini..."></textarea>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <div v-for="opt in ['a', 'b', 'c', 'd', 'e']" :key="opt" class="flex gap-4 items-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center font-black uppercase text-xs">{{ opt }}</div>
                    <input v-model="form.options[opt]" type="text" class="flex-1 border-gray-100 rounded-xl bg-gray-50 text-sm" :placeholder="`Teks pilihan ${opt.toUpperCase()}`">
                    
                    <input v-if="form.type === 'TKP'" v-model="form.tkp_scores[opt]" type="number" min="1" max="5" class="w-20 border-gray-100 rounded-xl bg-indigo-50 text-center font-bold text-indigo-600">
                    
                    <input v-if="form.type !== 'TKP'" type="radio" :value="opt" v-model="form.correct_answer" class="w-5 h-5 text-indigo-600 border-gray-300">
                </div>
            </div>

            <button type="submit" :disabled="form.processing" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition uppercase tracking-widest text-xs">
                Simpan Soal Ke Database
            </button>
        </form>
    </div>
</template>