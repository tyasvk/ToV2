<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import draggable from 'vuedraggable';
import axios from 'axios'; // <-- IMPORT AXIOS DITAMBAHKAN

import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    tryout: Object,
    questions: Array
});

// --- DAFTAR MATERI SUBTES BKN ---
const materiOptions = {
    TWK: ['Pilar Negara', 'Nasionalisme', 'Bela Negara', 'Integritas', 'Bahasa Indonesia'],
    TIU: ['Analogi', 'Silogisme', 'Analitis', 'Berhitung', 'Deret Angka', 'Perbandingan Kuantitatif', 'Soal Cerita', 'Analogi Gambar', 'Ketidaksamaan Gambar', 'Serial Gambar'],
    TKP: ['Pelayanan Publik', 'Profesionalisme', 'Jejaring Kerja', 'Sosial Budaya', 'Teknologi Informasi dan Komunikasi', 'Anti Radikalisme']
};

const parseData = (data) => {
    if (typeof data === 'string') {
        try { return JSON.parse(data); } catch (e) { return null; }
    }
    return data;
};

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

// --- STATE UNTUK GENERATOR & KELOLA BOT ---
const isDummyModalOpen = ref(false);
const isManageDummyModalOpen = ref(false);
const dummyList = ref([]);
const isFetchingDummies = ref(false);

const dummyForm = useForm({
    amount: 50,
});

const submitDummies = () => {
    const requestedAmount = dummyForm.amount; // SIMPAN NILAI SEBELUM RESET
    
    dummyForm.post(route('tryouts.dummies', props.tryout.id), {
        onSuccess: () => {
            isDummyModalOpen.value = false;
            dummyForm.reset();
            alert(`Berhasil membuat ${requestedAmount} peserta fiktif!`);
        }
    });
};

const openManageDummies = async () => {
    isManageDummyModalOpen.value = true;
    isFetchingDummies.value = true;
    try {
        // Sesuaikan dengan URL admin Anda
        const res = await axios.get(`/admin/tryouts/${props.tryout.id}/dummies`);
        dummyList.value = res.data;
    } catch (error) {
        console.error('Gagal mengambil data bot', error);
    } finally {
        isFetchingDummies.value = false;
    }
};

const clearAllDummies = () => {
    if (confirm('Yakin ingin menghapus SEMUA peserta bot di Tryout ini? Aksi ini tidak dapat dibatalkan.')) {
        router.delete(`/admin/tryouts/${props.tryout.id}/dummies`, {
            preserveScroll: true,
            onSuccess: () => {
                isManageDummyModalOpen.value = false;
                dummyList.value = [];
                alert('Semua bot berhasil dihapus!');
            }
        });
    }
};

const existingImages = ref({
    question: null,
    options: { a: null, b: null, c: null, d: null, e: null }
});

const imagePreviews = ref({
    question: null,
    options: { a: null, b: null, c: null, d: null, e: null }
});

watch(() => props.questions, (newVal) => {
    localQuestions.value = formatQuestions(newVal);
}, { deep: true });

const backUrl = computed(() => {
    if (props.tryout?.type === 'akbar') return '/admin/tryout-akbar';
    if (props.tryout?.type === 'adidaya') return '/admin/adidaya-manage';
    return '/admin/tryouts';
});

const triggerImport = () => { fileInput.value.click(); };

const handleImport = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!confirm('Impor soal dari file ini?')) { 
        event.target.value = null; 
        return; 
    }

    const formData = new FormData();
    formData.append('file', file);

    router.post(route('admin.tryouts.questions.import', props.tryout.id), formData, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { 
            event.target.value = null; 
            alert('Berhasil! Soal telah diimpor ke database.'); 
        },
        onError: (errors) => {
            event.target.value = null;
            alert('Gagal mengimpor file: \n' + Object.values(errors).join('\n'));
        }
    });
};

const handleDragEnd = () => {
    const ids = localQuestions.value.map(q => q.id);
    router.patch(route('admin.questions.reorder'), { ids }, { preserveScroll: true });
};

const form = useForm({
    id: null,
    type: 'TWK',
    sub_category: 'Pilar Negara',
    content: '',
    image: null,
    options: { a: '', b: '', c: '', d: '', e: '' },
    option_images: { a: null, b: null, c: null, d: null, e: null },
    correct_answer: '',
    tkp_scores: { a: '', b: '', c: '', d: '', e: '' },
    explanation: ''
});

const currentMateriList = computed(() => materiOptions[form.type] || []);

watch(() => form.type, (newType) => {
    if (newType && materiOptions[newType] && !materiOptions[newType].includes(form.sub_category)) {
        form.sub_category = materiOptions[newType][0];
    }
});

const handleImagePreview = (event, type, opt = null) => {
    const file = event.target.files[0];
    if (type === 'question') {
        form.image = file || null;
        if (imagePreviews.value.question) URL.revokeObjectURL(imagePreviews.value.question);
        imagePreviews.value.question = file ? URL.createObjectURL(file) : null;
    } else if (type === 'option') {
        form.option_images[opt] = file || null;
        if (imagePreviews.value.options[opt]) URL.revokeObjectURL(imagePreviews.value.options[opt]);
        imagePreviews.value.options[opt] = file ? URL.createObjectURL(file) : null;
    }
};

const openCreateModal = () => {
    form.reset();
    if (form.clearErrors) form.clearErrors(); 
    
    form.id = null;
    form.type = 'TWK';
    form.sub_category = 'Pilar Negara';
    form.content = '';
    form.image = null;
    form.options = { a: '', b: '', c: '', d: '', e: '' };
    form.option_images = { a: null, b: null, c: null, d: null, e: null };
    form.correct_answer = '';
    form.tkp_scores = { a: '', b: '', c: '', d: '', e: '' };
    form.explanation = '';
    
    existingImages.value = { question: null, options: { a: null, b: null, c: null, d: null, e: null } };
    imagePreviews.value = { question: null, options: { a: null, b: null, c: null, d: null, e: null } };
    
    isModalOpen.value = true;
};

const openEditModal = (q) => {
    if (form.clearErrors) form.clearErrors(); 
    
    form.id = q.id;
    form.type = q.type || 'TWK';
    form.sub_category = q.sub_category || q.materi || materiOptions[form.type][0];
    form.content = q.content || '';
    form.image = null; 
    form.options = { ...q.options };
    form.option_images = { a: null, b: null, c: null, d: null, e: null }; 
    form.correct_answer = q.correct_answer || '';
    form.tkp_scores = { ...q.tkp_scores };
    form.explanation = q.explanation || '';

    existingImages.value = { question: q.image || null, options: { ...q.option_images } };
    imagePreviews.value = { question: null, options: { a: null, b: null, c: null, d: null, e: null } };
    
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
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6">
            <!-- Header Halaman -->
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 mb-8 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>
                
                <div class="relative z-10">
                    <Link :href="backUrl" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 mb-2 block">&larr; Kembali ke List</Link>
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">{{ tryout.title }}</h1>
                    <p class="text-sm text-slate-500 font-medium mt-1">Mengelola {{ questions.length }} butir soal</p>
                </div>

                <div class="relative z-10 flex flex-wrap gap-3">
                    <input type="file" ref="fileInput" class="hidden" accept=".csv, .xlsx" @change="handleImport" />
                    
                    <!-- TOMBOL GENERATE & KELOLA BOT -->
                    <div class="flex bg-white rounded-xl shadow-sm border border-purple-300 overflow-hidden">
                        <button @click="isDummyModalOpen = true" class="px-4 py-2.5 text-purple-700 hover:bg-purple-50 font-semibold text-sm transition active:scale-95 flex items-center gap-1.5">
                            🤖 Bot Peserta
                        </button>
                        <div class="w-px bg-purple-200"></div>
                        <button @click="openManageDummies" class="px-3 py-2.5 bg-purple-50 text-purple-700 hover:bg-purple-100 font-semibold text-sm transition" title="Lihat & Hapus Bot">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <button @click="triggerImport" class="px-5 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-sm hover:bg-slate-50 transition shadow-sm">
                        Import
                    </button>
                    <button @click="openCreateModal" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold text-sm shadow-sm transition active:scale-95">
                        + Tambah Soal
                    </button>
                </div>
            </div>

            <!-- List Soal -->
            <div v-if="localQuestions.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                <p class="text-slate-400 font-medium">Belum ada soal tersedia.</p>
            </div>

            <draggable v-model="localQuestions" item-key="id" handle=".drag-handle" @end="handleDragEnd" ghost-class="opacity-30" class="space-y-4">
                <template #item="{ element: q, index }">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden transition-all">
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50/50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" /></svg>
                                </div>
                                <span class="w-8 h-8 shrink-0 bg-slate-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-slate-500">#{{ index + 1 }}</span>
                                <div @click="toggleAccordion(q.id)" class="cursor-pointer truncate flex items-center min-w-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-md uppercase mr-2 shrink-0">{{ q.type }}</span>
                                    <span class="text-[9px] font-semibold px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 rounded-md mr-2 shrink-0">{{ q.sub_category || 'Umum' }}</span>
                                    <div class="text-sm font-semibold text-slate-800 line-clamp-1 inline-block align-middle max-w-sm html-content" v-html="q.content || '[Gambar Soal]'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
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
                                    class="p-3 rounded-xl border text-xs flex justify-between min-w-0">
                                    <div class="flex flex-col min-w-0">
                                        <div class="font-medium flex items-start">
                                            <span class="uppercase font-bold mr-2 mt-0.5 shrink-0">{{ key }}.</span> 
                                            <div class="html-content min-w-0 break-words" v-html="val"></div>
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
            <!-- MODAL TAMBAH/EDIT SOAL -->
            <div v-if="isModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 lg:pl-[17rem]">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isModalOpen = false"></div>
                
                <div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl flex flex-col max-h-[95vh] sm:max-h-[90vh] animate-in zoom-in-95 duration-200">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/80 rounded-t-2xl">
                        <h3 class="font-bold text-slate-900">{{ form.id ? 'Edit Soal' : 'Tambah Soal Baru' }}</h3>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submit" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 custom-scrollbar">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tipe</label>
                                <select v-model="form.type" class="w-full border-slate-200 rounded-xl bg-slate-50 text-sm focus:ring-indigo-500">
                                    <option>TWK</option><option>TIU</option><option>TKP</option>
                                </select>
                            </div>
                            
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Materi Soal</label>
                                <select v-model="form.sub_category" class="w-full border-slate-200 rounded-xl bg-slate-50 text-sm focus:ring-indigo-500">
                                    <option v-for="materi in currentMateriList" :key="materi" :value="materi">{{ materi }}</option>
                                </select>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Gambar Soal (Opsional)</label>
                                
                                <div v-if="imagePreviews.question" class="mb-3">
                                    <img :src="imagePreviews.question" class="h-20 rounded-lg border border-indigo-200 object-contain shadow-sm bg-indigo-50" />
                                </div>
                                <div v-else-if="form.id && existingImages.question" class="mb-3">
                                    <img :src="`/storage/${existingImages.question}`" class="h-20 rounded-lg border border-slate-200 object-contain shadow-sm bg-white" />
                                </div>

                                <input type="file" @change="e => handleImagePreview(e, 'question')" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pertanyaan</label>
                            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                                <QuillEditor 
                                    theme="snow" 
                                    :toolbar="[
                                        ['bold', 'italic', 'underline'],
                                        [{ 'script': 'sub'}, { 'script': 'super' }],
                                        ['formula'],
                                        ['clean']
                                    ]"
                                    v-model:content="form.content" 
                                    contentType="html"
                                    class="min-h-[120px] text-sm"
                                    placeholder="Ketik soal atau rumus di sini..."
                                />
                            </div>
                            <div v-if="form.errors.content" class="text-red-500 text-[10px] mt-1">{{ form.errors.content }}</div>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pilihan Jawaban</label>
                            
                            <div v-for="opt in ['a', 'b', 'c', 'd', 'e']" :key="opt" class="flex flex-col lg:flex-row gap-5 p-4 bg-slate-50 border border-slate-100 rounded-xl shadow-sm">
                                
                                <div class="flex-1 flex flex-col gap-2 min-w-0">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 shrink-0 bg-white rounded-lg flex items-center justify-center font-bold text-xs text-slate-500 shadow-sm">{{ opt.toUpperCase() }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Teks / Rumus Pilihan {{ opt.toUpperCase() }}</div>
                                    </div>
                                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden w-full shadow-sm">
                                        <QuillEditor 
                                            theme="snow" 
                                            :toolbar="[
                                                ['bold', 'italic'],
                                                [{ 'script': 'sub'}, { 'script': 'super' }],
                                                ['formula'],
                                                ['clean']
                                            ]"
                                            v-model:content="form.options[opt]" 
                                            contentType="html"
                                            class="min-h-[60px] text-sm"
                                            :placeholder="`Isi pilihan ${opt.toUpperCase()}...`"
                                        />
                                    </div>
                                </div>

                                <div class="w-full lg:w-48 shrink-0 flex flex-col gap-3 justify-start pt-3 lg:pt-0 border-t lg:border-t-0 lg:border-l border-slate-200 lg:pl-5 mt-2 lg:mt-0">
                                    <div v-if="form.type === 'TKP'">
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Poin TKP</label>
                                        <input v-model="form.tkp_scores[opt]" type="number" placeholder="1 - 5" class="w-full border-slate-200 rounded-lg text-sm text-center focus:ring-indigo-500" />
                                    </div>
                                    <div v-else>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Kunci Jawaban?</label>
                                        <label class="flex items-center gap-2 cursor-pointer bg-white border border-slate-200 px-3 py-2 rounded-lg hover:bg-indigo-50 transition w-full shadow-sm">
                                            <input type="radio" :value="opt" v-model="form.correct_answer" class="text-indigo-600 focus:ring-indigo-500 shrink-0" required />
                                            <span class="text-xs font-semibold text-slate-600">Jawaban Benar</span>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Gambar (Opsional)</label>
                                        
                                        <div v-if="imagePreviews.options[opt]" class="mb-2">
                                            <img :src="imagePreviews.options[opt]" class="h-12 rounded border border-indigo-200 object-contain shadow-sm bg-indigo-50" />
                                        </div>
                                        <div v-else-if="form.id && existingImages.options && existingImages.options[opt]" class="mb-2">
                                            <img :src="`/storage/${existingImages.options[opt]}`" class="h-12 rounded border border-slate-200 object-contain shadow-sm bg-white" />
                                        </div>

                                        <input type="file" @change="e => handleImagePreview(e, 'option', opt)" accept="image/*" class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors" />
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pembahasan</label>
                            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                                <QuillEditor 
                                    theme="snow" 
                                    :toolbar="[
                                        ['bold', 'italic', 'underline'],
                                        [{ 'script': 'sub'}, { 'script': 'super' }],
                                        ['formula'],
                                        ['clean']
                                    ]"
                                    v-model:content="form.explanation" 
                                    contentType="html"
                                    class="min-h-[120px] text-sm"
                                    placeholder="Ketik pembahasan soal di sini..."
                                />
                            </div>
                        </div>
                    </form>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/80 rounded-b-2xl flex justify-end gap-3 shrink-0">
                        <button type="button" @click="isModalOpen = false" class="px-5 py-2 text-slate-600 font-semibold text-sm hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
                        <button @click="submit" :disabled="form.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-semibold text-sm hover:bg-indigo-700 transition shadow-sm disabled:opacity-50">
                            {{ form.id ? 'Simpan Perubahan' : 'Tambah Soal' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL KELOLA PESERTA BOT -->
            <div v-if="isManageDummyModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 lg:pl-[17rem]">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isManageDummyModalOpen = false"></div>
                
                <div class="relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl flex flex-col max-h-[85vh] p-6 animate-in zoom-in-95 duration-200 z-10">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Daftar Peserta Bot</h3>
                            <p class="text-xs text-slate-500">Terdapat <strong>{{ dummyList.length }}</strong> peserta fiktif di Tryout ini.</p>
                        </div>
                        <button @click="isManageDummyModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-full p-1.5 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isFetchingDummies" class="flex-1 flex justify-center items-center py-10">
                        <span class="animate-spin w-8 h-8 border-4 border-purple-500 border-t-transparent rounded-full"></span>
                    </div>

                    <!-- Tabel Data Bot -->
                    <div v-else class="flex-1 overflow-y-auto custom-scrollbar border border-slate-200 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 sticky top-0 z-10">
                                <tr>
                                    <th class="p-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Nama Bot</th>
                                    <th class="p-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">Instansi</th>
                                    <th class="p-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 text-center">Skor Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="bot in dummyList" :key="bot.id" class="hover:bg-slate-50 transition">
                                    <td class="p-3">
                                        <p class="text-xs font-bold text-slate-800">{{ bot.name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ bot.email }}</p>
                                    </td>
                                    <td class="p-3 text-xs text-slate-600 truncate max-w-[200px]">{{ bot.agency_name }}</td>
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-1 bg-emerald-50 text-emerald-600 font-bold rounded-lg text-xs">{{ bot.total_score }}</span>
                                    </td>
                                </tr>
                                <tr v-if="dummyList.length === 0">
                                    <td colspan="3" class="p-8 text-center text-slate-400 text-sm font-medium">Belum ada peserta bot di Tryout ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                        <button v-if="dummyList.length > 0" @click="clearAllDummies" class="px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 font-bold text-xs rounded-xl transition">
                            🗑️ Bersihkan Semua Bot
                        </button>
                        <div v-else></div>
                    </div>
                </div>
            </div>

            <!-- MODAL GENERATE BOT/DUMMIES -->
            <div v-if="isDummyModalOpen" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 lg:pl-[17rem]">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isDummyModalOpen = false"></div>
                
                <div class="relative bg-white w-full max-w-sm rounded-3xl shadow-2xl flex flex-col p-6 animate-in zoom-in-95 duration-200 z-10">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-4 border-4 border-white shadow-sm">
                        🤖
                    </div>
                    <h3 class="text-[17px] font-bold text-slate-900 text-center mb-1">Generate Peserta Bot</h3>
                    <p class="text-[12px] text-slate-500 text-center mb-6 px-2">
                        Penuhi papan peringkat nasional dan instansi dengan peserta fiktif agar terlihat ramai.
                    </p>
                    
                    <form @submit.prevent="submitDummies">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-widest text-center mb-2">Jumlah Bot</label>
                        <input 
                            v-model="dummyForm.amount" 
                            type="number" 
                            min="1" 
                            max="500" 
                            class="w-3/4 mx-auto block border-slate-200 rounded-xl text-center text-xl font-black text-slate-800 focus:ring-purple-500 mb-2 py-3 shadow-inner bg-slate-50"
                        />
                        <p v-if="dummyForm.errors.amount" class="text-[10px] text-red-500 mb-4 text-center">{{ dummyForm.errors.amount }}</p>
                        
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="isDummyModalOpen = false" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[13px] font-bold transition-colors active:scale-95">Batal</button>
                            <button type="submit" :disabled="dummyForm.processing" class="flex-[1.5] py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[13px] font-bold transition-colors active:scale-95 flex justify-center items-center shadow-md shadow-purple-500/30">
                                <span v-if="dummyForm.processing" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                                <span v-else>🚀 Eksekusi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.html-content :deep(p) { margin: 0; display: inline; }
.html-content :deep(img) {
    max-height: 120px; width: auto; border-radius: 6px;
    display: inline-block; vertical-align: middle;
    margin-top: 4px; margin-bottom: 4px;
}

:deep(.ql-toolbar) {
    border: none !important; border-bottom: 1px solid #e2e8f0 !important;
    background-color: #f8fafc; flex-wrap: wrap; display: flex;
}
:deep(.ql-container) { border: none !important; font-family: inherit; }
:deep(.ql-editor) { min-height: 120px; word-break: break-word; }
.flex-col > .bg-white > :deep(.ql-editor) { min-height: 60px !important; }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>