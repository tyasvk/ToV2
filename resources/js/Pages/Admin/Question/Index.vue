<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tryout: Object,
    questions: Array
});

// ==========================================
// HELPER: Mengubah String JSON dari database menjadi Object Vue
// ==========================================
const parseData = (data) => {
    if (typeof data === 'string') {
        try { return JSON.parse(data); } catch (e) { return null; }
    }
    return data;
};

// Format seluruh soal di awal agar format opsinya pasti menjadi Object
const formatQuestions = (questions) => {
    return questions.map(q => ({
        ...q,
        options: parseData(q.options) || { a: '', b: '', c: '', d: '', e: '' },
        option_images: parseData(q.option_images) || { a: null, b: null, c: null, d: null, e: null },
        tkp_scores: parseData(q.tkp_scores) || { a: '', b: '', c: '', d: '', e: '' }
    }));
};

const localQuestions = ref(formatQuestions(props.questions));
const expandedId = ref(null);
const isModalOpen = ref(false);
const fileInput = ref(null);

const existingImages = ref({
    question: null,
    options: { a: null, b: null, c: null, d: null, e: null }
});

// Update list secara dinamis namun tetap melewati filter pemformatan
watch(() => props.questions, (newVal) => {
    localQuestions.value = formatQuestions(newVal);
}, { deep: true });

const backUrl = computed(() => {
    if (props.tryout?.type === 'akbar') {
        return '/admin/tryout-akbar';
    } else if (props.tryout?.type === 'adidaya') {
        return '/admin/adidaya-manage';
    } else {
        return '/admin/tryouts';
    }
});

const triggerImport = () => { fileInput.value.click(); };

const handleImport = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!confirm('Impor soal dari file ini?')) { event.target.value = null; return; }

    const formData = new FormData();
    formData.append('file', file);

    router.post(route('admin.tryouts.questions.import', props.tryout.id), formData, {
        onSuccess: () => { event.target.value = null; },
    });
};

const handleDragEnd = () => {
    const ids = localQuestions.value.map(q => q.id);
    router.patch(route('admin.questions.reorder'), { ids }, { preserveScroll: true });
};

const form = useForm({
    id: null,
    type: 'TWK',
    content: '',
    image: null,
    options: { a: '', b: '', c: '', d: '', e: '' },
    option_images: { a: null, b: null, c: null, d: null, e: null },
    correct_answer: '',
    tkp_scores: { a: '', b: '', c: '', d: '', e: '' },
    explanation: ''
});

const openCreateModal = () => {
    form.reset();
    if (form.clearErrors) form.clearErrors(); 
    
    form.id = null;
    form.type = 'TWK';
    form.content = '';
    form.image = null;
    form.options = { a: '', b: '', c: '', d: '', e: '' };
    form.option_images = { a: null, b: null, c: null, d: null, e: null };
    form.correct_answer = '';
    form.tkp_scores = { a: '', b: '', c: '', d: '', e: '' };
    form.explanation = '';
    
    existingImages.value = {
        question: null,
        options: { a: null, b: null, c: null, d: null, e: null }
    };
    
    isModalOpen.value = true;
};

const openEditModal = (q) => {
    if (form.clearErrors) form.clearErrors(); 
    
    form.id = q.id;
    form.type = q.type;
    form.content = q.content || '';
    form.image = null; 
    
    // Karena sudah diformat oleh formatQuestions di awal, kita bisa langsung copy aman
    form.options = { ...q.options };
    form.option_images = { a: null, b: null, c: null, d: null, e: null }; 
    
    form.correct_answer = q.correct_answer || '';
    form.tkp_scores = { ...q.tkp_scores };
    form.explanation = q.explanation || '';

    existingImages.value = {
        question: q.image || null,
        options: { ...q.option_images }
    };
    
    isModalOpen.value = true;
};

const submit = () => {
    if (form.id) {
        form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('admin.tryouts.questions.update', [props.tryout.id, form.id]), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); },
            forceFormData: true,
            preserveScroll: true
        });
    } else {
        form.post(route('admin.tryouts.questions.store', props.tryout.id), {
            onSuccess: () => { isModalOpen.value = false; form.reset(); },
            forceFormData: true,
            preserveScroll: true
        });
    }
};

const deleteQuestion = (id) => {
    if (confirm('Hapus soal ini?')) {
        router.delete(route('admin.tryouts.questions.destroy', [props.tryout.id, id]), { preserveScroll: true });
    }
};

const toggleAccordion = (id) => { expandedId.value = expandedId.value === id ? null : id; };
</script>

<template>
    <Head :title="'Bank Soal - ' + tryout.title" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto py-8">
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 mb-8 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>
                
                <div class="relative z-10">
                    <Link :href="backUrl" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 mb-2 block">&larr; Kembali ke List</Link>
                    
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">{{ tryout.title }}</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Mengelola {{ questions.length }} butir soal</p>
                </div>

                <div class="relative z-10 flex gap-3">
                    <input type="file" ref="fileInput" class="hidden" accept=".csv, .xlsx" @change="handleImport" />
                    <button @click="triggerImport" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-50 transition shadow-sm">
                        Import
                    </button>
                    <button @click="openCreateModal" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm shadow-sm transition active:scale-95">
                        + Tambah Soal
                    </button>
                </div>
            </div>

            <div v-if="localQuestions.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                <p class="text-slate-400 font-medium">Belum ada soal tersedia.</p>
            </div>

            <draggable v-model="localQuestions" item-key="id" handle=".drag-handle" @end="handleDragEnd" ghost-class="opacity-30" class="space-y-4">
                <template #item="{ element: q, index }">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all">
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" /></svg>
                                </div>
                                <span class="w-8 h-8 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-slate-500">#{{ index + 1 }}</span>
                                <div @click="toggleAccordion(q.id)" class="cursor-pointer truncate flex items-center">
                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-md uppercase mr-2">{{ q.type }}</span>
                                    
                                    <div class="text-sm font-semibold text-slate-800 line-clamp-1 inline-block align-middle max-w-sm html-content" v-html="q.content || '[Gambar Soal]'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click.stop="openEditModal(q)" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M15.2 3a2.8 2.8 0 10-4 4L4 14.8V18h3.2l7.2-7.2a2.8 2.8 0 000-4z"/></svg></button>
                                <button @click.stop="deleteQuestion(q.id)" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
                            </div>
                        </div>
                        
                        <div v-if="expandedId === q.id" class="px-6 pb-6 pt-2 bg-slate-50/50 border-t border-slate-100">
                            <div v-if="q.image" class="mb-4">
                                <img :src="'/storage/' + q.image" class="h-32 rounded-lg border border-slate-200 shadow-sm" />
                            </div>

                            <div class="text-sm text-slate-700 mb-4 whitespace-pre-wrap html-content" v-html="q.content"></div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="(val, key) in (q.options || {})" :key="key" 
                                    :class="[q.correct_answer === key ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-white border-slate-200']" 
                                    class="p-3 rounded-xl border text-xs flex justify-between">
                                    <div class="flex flex-col">
                                        
                                        <div class="font-medium flex items-start">
                                            <span class="uppercase font-bold mr-2 mt-0.5 shrink-0">{{ key }}.</span> 
                                            <div class="html-content" v-html="val"></div>
                                        </div>

                                        <img v-if="q.option_images && q.option_images[key]" :src="'/storage/' + q.option_images[key]" class="h-16 mt-2 rounded border border-slate-200 object-contain shadow-sm bg-white" />
                                    </div>
                                    <span v-if="q.type === 'TKP' && q.tkp_scores" class="font-bold text-slate-400 shrink-0 ml-2">Poin: {{ q.tkp_scores[key] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>

        <Teleport to="body">
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="isModalOpen = false"></div>
                <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-xl flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-2xl">
                        <h3 class="font-bold text-slate-900">{{ form.id ? 'Edit Soal' : 'Tambah Soal Baru' }}</h3>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                    </div>
                    
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tipe</label>
                                <select v-model="form.type" class="w-full border-slate-200 rounded-xl bg-slate-50 text-sm">
                                    <option>TWK</option><option>TIU</option><option>TKP</option>
                                </select>
                            </div>
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Gambar Soal (Opsional jika isi teks)</label>
                                
                                <div v-if="form.id && existingImages.question" class="mb-3">
                                    <p class="text-[10px] text-slate-500 font-bold mb-1">GAMBAR SAAT INI:</p>
                                    <img :src="`/storage/${existingImages.question}`" class="h-24 rounded-lg border border-slate-200 object-contain shadow-sm bg-white" alt="Gambar Soal" />
                                </div>

                                <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p v-if="form.id" class="text-[10px] text-slate-400 mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                                
                                <div v-if="form.errors.image" class="text-red-500 text-[10px] mt-1">{{ form.errors.image }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pertanyaan (Opsional jika isi gambar)</label>
                            <textarea v-model="form.content" rows="3" class="w-full border-slate-200 rounded-xl bg-slate-50 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            
                            <div v-if="form.errors.content" class="text-red-500 text-[10px] mt-1">{{ form.errors.content }}</div>
                        </div>

                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pilihan Jawaban</label>
                            <div v-for="opt in ['a', 'b', 'c', 'd', 'e']" :key="opt" class="flex flex-col gap-2 p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                                <div class="flex gap-3 items-center">
                                    <div class="w-8 h-8 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center font-bold text-xs text-slate-500">{{ opt.toUpperCase() }}</div>
                                    <input v-model="form.options[opt]" type="text" class="flex-1 border-slate-200 rounded-xl text-sm" :required="!form.option_images[opt] && !existingImages.options[opt]" />
                                    <input v-if="form.type === 'TKP'" v-model="form.tkp_scores[opt]" type="number" placeholder="Poin" class="w-20 border-slate-200 rounded-xl text-sm text-center" />
                                    <input v-else type="radio" :value="opt" v-model="form.correct_answer" class="text-indigo-600 focus:ring-indigo-500" required />
                                </div>
                                
                                <div v-if="form.type === 'TIU'" class="pl-11 mt-1 border-t border-slate-50 pt-2">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Gambar Opsi (Opsional)</label>
                                    
                                    <div v-if="form.id && existingImages.options[opt]" class="mb-2">
                                        <p class="text-[10px] text-slate-400 mb-1">Gambar saat ini:</p>
                                        <img :src="`/storage/${existingImages.options[opt]}`" class="h-16 rounded border border-slate-200 object-contain shadow-sm bg-white" alt="Gambar Opsi" />
                                    </div>

                                    <input type="file" @input="form.option_images[opt] = $event.target.files[0]" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    <p v-if="form.id" class="text-[10px] text-slate-400 mt-1">Biarkan kosong jika tidak diubah.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pembahasan</label>
                            <textarea v-model="form.explanation" rows="2" class="w-full border-slate-200 rounded-xl bg-slate-50 text-sm"></textarea>
                        </div>
                    </form>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex justify-end gap-3">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2 text-slate-600 font-semibold text-sm">Batal</button>
                        <button @click="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-semibold text-sm hover:bg-indigo-700 transition">
                            {{ form.id ? 'Simpan Perubahan' : 'Tambah Soal' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
/* PERUBAHAN 4: Styling tambahan agar format HTML / Gambar tidak bocor ukurannya */
.html-content :deep(img) {
    max-height: 120px;
    width: auto;
    border-radius: 6px;
    display: inline-block;
    vertical-align: middle;
    margin-top: 4px;
    margin-bottom: 4px;
}
.html-content :deep(p) {
    margin: 0;
    display: inline;
}
</style>