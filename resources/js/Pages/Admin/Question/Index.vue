<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3'; // Pastikan router diimport
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tryout: Object,
    questions: Array
});

// --- STATE MANAGEMENT ---
const localQuestions = ref([...props.questions]);
const expandedId = ref(null);
const isModalOpen = ref(false);
const fileInput = ref(null);

// Sinkronisasi data lokal
watch(() => props.questions, (newVal) => {
    localQuestions.value = [...newVal];
}, { deep: true });

// --- LOGIKA IMPORT ---
const triggerImport = () => {
    fileInput.value.click();
};

const handleImport = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!confirm('Apakah Anda yakin ingin mengimpor soal dari file ini?')) {
        event.target.value = null;
        return;
    }

    const formData = new FormData();
    formData.append('file', file);

    router.post(route('admin.tryouts.questions.import', props.tryout.id), formData, {
        onSuccess: () => {
            alert('Import berhasil! Halaman akan direfresh.');
            event.target.value = null;
        },
        onError: (errors) => {
            alert('Gagal import: ' + (errors.file || 'Terjadi kesalahan.'));
            event.target.value = null;
        }
    });
};

// --- LOGIKA DRAG & DROP ---
const handleDragEnd = () => {
    const ids = localQuestions.value.map(q => q.id);
    router.patch(route('admin.questions.reorder'), { ids }, { 
        preserveScroll: true,
        onSuccess: () => console.log('Urutan diperbarui')
    });
};

// --- FORM MANAGEMENT ---
const form = useForm({
    id: null,
    type: 'TWK',
    content: '',
    image: null,
    options: { a: '', b: '', c: '', d: '', e: '' },
    correct_answer: '',
    tkp_scores: { a: '', b: '', c: '', d: '', e: '' },
    explanation: ''
});

const openCreateModal = () => {
    form.reset();
    form.id = null;
    isModalOpen.value = true;
};

const openEditModal = (q) => {
    form.id = q.id;
    form.type = q.type;
    form.content = q.content;
    form.options = { ...q.options };
    form.correct_answer = q.correct_answer;
    form.tkp_scores = q.tkp_scores ? { ...q.tkp_scores } : { a: '', b: '', c: '', d: '', e: '' };
    form.explanation = q.explanation;
    isModalOpen.value = true;
};

const submit = () => {
    const url = form.id 
        ? route('admin.questions.update', form.id) 
        : route('admin.questions.store', props.tryout.id);

    form.post(url, {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
        forceFormData: true,
        preserveScroll: true
    });
};

// --- PERBAIKAN FUNGSI DELETE ---
const deleteQuestion = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('admin.questions.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                // Opsional: Hapus manual dari local state agar UI langsung update tanpa nunggu reload
                localQuestions.value = localQuestions.value.filter(q => q.id !== id);
            },
            onError: () => alert('Gagal menghapus soal.')
        });
    }
};

const toggleAccordion = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
};
</script>

<template>
    <Head :title="'Bank Soal - ' + tryout.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <Link :href="route('admin.tryouts.index')" class="bg-white p-2.5 rounded-2xl border border-gray-100 shadow-sm hover:bg-gray-50 transition shrink-0">⬅️</Link>
                    <div class="truncate">
                        <h2 class="font-black text-xl text-gray-900 uppercase tracking-tighter truncate">{{ tryout.title }}</h2>
                        <p class="text-[9px] text-indigo-600 font-bold uppercase tracking-widest">Manajemen {{ questions.length }} Butir Soal</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <input type="file" ref="fileInput" class="hidden" accept=".csv, .xlsx" @change="handleImport" />
                    
                    <button @click="triggerImport" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl font-black text-[10px] transition uppercase tracking-widest shadow-lg shadow-emerald-100 active:scale-95 flex items-center gap-2">
                        <span>📥</span> Import Excel
                    </button>

                    <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-black text-[10px] transition uppercase tracking-widest shadow-lg shadow-indigo-100 active:scale-95">
                        + Tambah Soal
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-3 pb-24 mt-6">
            <draggable 
                v-model="localQuestions" 
                item-key="id" 
                handle=".drag-handle" 
                @end="handleDragEnd" 
                ghost-class="opacity-30"
            >
                <template #item="{ element: q, index }">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-3 transition-all duration-300">
                        <div class="px-6 py-4 flex items-center justify-between group">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-indigo-600 transition p-1 text-lg">⠿</div>
                                <span class="w-8 h-8 shrink-0 bg-gray-100 rounded-lg flex items-center justify-center text-[10px] font-black text-gray-400">#{{ index + 1 }}</span>
                                <div @click="toggleAccordion(q.id)" class="cursor-pointer truncate">
                                    <span class="text-[8px] font-black px-2 py-0.5 bg-indigo-50 text-indigo-500 rounded uppercase mr-2">{{ q.type }}</span>
                                    <span class="text-sm font-bold text-gray-700 truncate inline-block align-middle max-w-xs md:max-w-md">{{ q.content }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 ml-4">
                                <button @click.stop="openEditModal(q)" class="text-gray-300 hover:text-indigo-600 transition text-xs">✏️</button>
                                <button type="button" @click.stop="deleteQuestion(q.id)" class="text-gray-300 hover:text-red-500 transition text-xs">🗑️</button>
                                <span @click="toggleAccordion(q.id)" :class="{'rotate-180': expandedId === q.id}" class="text-gray-300 transition-transform duration-300 text-[10px] cursor-pointer">▼</span>
                            </div>
                        </div>

                        <div v-if="expandedId === q.id" class="px-16 pb-6 pt-2 border-t border-gray-50 bg-gray-50/30 animate-in slide-in-from-top-2 duration-300">
                            <img v-if="q.image" :src="'/storage/' + q.image" class="max-h-48 rounded-xl border border-gray-100 mb-4 bg-white p-2" />
                            <p class="text-sm text-gray-800 leading-relaxed mb-4 whitespace-pre-wrap">{{ q.content }}</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
                                <div v-for="(val, key) in q.options" :key="key" 
                                    :class="[q.correct_answer === key ? 'bg-green-100 border-green-200 text-green-700' : 'bg-white border-gray-100 text-gray-400']"
                                    class="p-3 rounded-xl border text-[11px] flex justify-between">
                                    <span><span class="uppercase font-black mr-2">{{ key }}.</span> {{ val }}</span>
                                    <span v-if="q.type === 'TKP'" class="font-black text-indigo-400">Skor: {{ q.tkp_scores[key] }}</span>
                                </div>
                            </div>

                            <div v-if="q.explanation" class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                                <p class="text-[9px] font-black text-amber-600 uppercase mb-1">💡 Pembahasan</p>
                                <p class="text-xs text-amber-700 italic leading-relaxed">{{ q.explanation }}</p>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>

        <Teleport to="body">
            <div v-if="isModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md" @click="isModalOpen = false"></div>
                
                <div class="relative bg-white w-full max-w-xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
                    <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-white shrink-0">
                        <h3 class="font-black text-lg text-gray-900 uppercase tracking-tighter">
                            {{ form.id ? 'Edit Data Soal' : 'Input Soal Baru' }}
                        </h3>
                        <div class="flex p-1 bg-gray-100 rounded-xl gap-0.5">
                            <button v-for="t in ['TWK', 'TIU', 'TKP']" :key="t" type="button" @click="form.type = t" :class="[form.type === t ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-400']" class="px-4 py-1.5 rounded-lg font-black text-[9px] uppercase transition">{{ t }}</button>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-8 space-y-5 custom-scrollbar">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Narasi Pertanyaan</label>
                                <textarea v-model="form.content" rows="4" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm focus:ring-indigo-500 transition" required placeholder="Tulis soal..."></textarea>
                            </div>

                            <div v-if="form.type === 'TIU'" class="p-4 border-2 border-dashed border-indigo-100 bg-indigo-50/30 rounded-2xl animate-in fade-in duration-300">
                                <label class="block text-[9px] font-black text-indigo-400 uppercase mb-2">Upload Media Gambar</label>
                                <input type="file" @input="form.image = $event.target.files[0]" id="image-input" class="text-[10px] w-full file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:bg-indigo-600 file:text-white font-black" />
                            </div>

                            <div class="space-y-2 pt-2">
                                <div class="flex justify-between px-2 mb-1">
                                    <label class="text-[9px] font-black text-gray-400 uppercase">Pilihan Jawaban</label>
                                    <label class="text-[9px] font-black text-indigo-400 uppercase">{{ form.type === 'TKP' ? 'Bobot' : 'Kunci' }}</label>
                                </div>
                                <div v-for="opt in ['a', 'b', 'c', 'd', 'e']" :key="opt" class="flex gap-2 items-center">
                                    <div class="w-8 h-8 shrink-0 bg-gray-100 rounded-lg flex items-center justify-center font-black text-[10px] uppercase text-gray-400">{{ opt }}</div>
                                    <input v-model="form.options[opt]" type="text" class="flex-1 border-gray-100 bg-gray-50 rounded-xl p-2.5 text-xs focus:ring-indigo-500" required placeholder="Teks jawaban..." />
                                    
                                    <input v-if="form.type === 'TKP'" 
                                        v-model="form.tkp_scores[opt]" 
                                        type="number" 
                                        placeholder="0" 
                                        class="w-12 border-gray-100 bg-indigo-50 rounded-xl p-2.5 text-center text-xs font-black text-indigo-600 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                    
                                    <input v-else type="radio" :value="opt" v-model="form.correct_answer" class="w-5 h-5 text-indigo-600 border-gray-200" required />
                                </div>
                            </div>

                            <div class="space-y-1 pt-2">
                                <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Pembahasan (Explanation)</label>
                                <textarea v-model="form.explanation" rows="3" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-xs italic text-gray-500 focus:ring-indigo-500" placeholder="Jelaskan alasan jawaban benar..."></textarea>
                            </div>
                        </div>
                    </form>

                    <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex gap-3 shrink-0">
                        <button type="button" @click="isModalOpen = false" class="flex-1 py-3 text-gray-400 font-black text-[10px] uppercase tracking-widest">Batal</button>
                        <button @click="submit" :disabled="form.processing" class="flex-[2] py-3 bg-gray-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-xl transition active:scale-95">
                            {{ form.id ? 'Perbarui Data Soal' : 'Simpan ke Bank Soal' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>