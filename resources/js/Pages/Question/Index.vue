<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tryout: { type: Object, default: () => ({}) }, // Tambahkan default
    questions: { type: Array, default: () => [] }   // Tambahkan default
});

// Gunakan fallback array kosong agar spread operator tidak error
const localQuestions = ref([...(props.questions || [])]);
const expandedId = ref(null);
const isModalOpen = ref(false);
const fileInput = ref(null);

watch(() => props.questions, (newVal) => {
    localQuestions.value = [...(newVal || [])];
}, { deep: true });

// ... (logika lainnya tetap sama) ...

const submit = () => {
    // Pastikan parameter ID ada sebelum memanggil route
    if (!props.tryout?.id) return alert('Data Tryout tidak ditemukan.');

    const url = form.id 
        ? route('admin.tryouts.questions.update', [props.tryout.id, form.id]) 
        : route('admin.tryouts.questions.store', props.tryout.id);

    form.post(url, {
        onSuccess: () => { isModalOpen.value = false; form.reset(); },
        forceFormData: true,
        preserveScroll: true
    });
};
</script>

<template>
    <Head :title="'Bank Soal - ' + (tryout?.title || 'Loading...')" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto pb-24 mt-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4 min-w-0">
                    <Link :href="route('admin.tryouts.index')" class="bg-gray-50 p-2.5 rounded-2xl hover:bg-gray-100 transition shrink-0">⬅️</Link>
                    <div class="truncate">
                        <h2 class="font-black text-xl text-gray-900 uppercase tracking-tighter truncate">{{ tryout?.title }}</h2>
                        <p class="text-[9px] text-indigo-600 font-bold uppercase tracking-widest">Manajemen {{ questions?.length || 0 }} Butir Soal</p>
                    </div>
                </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>