<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ tryouts: Array });
const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    title: '',
    description: '',
    duration_minutes: 110,
    is_paid: false,
    price: 0,
    // Field Baru
    is_published: false,
    published_at: '',
    started_at: '',
});

const openModal = (data = null) => {
    if (data) {
        isEditing.value = true;
        form.id = data.id;
        form.title = data.title;
        form.description = data.description;
        form.duration_minutes = data.duration_minutes;
        form.is_paid = !!data.is_paid;
        form.price = data.price;
        // Load data baru
        form.is_published = !!data.is_published;
        form.published_at = data.published_at ? data.published_at.substring(0, 16) : '';
        form.started_at = data.started_at ? data.started_at.substring(0, 16) : '';
    } else {
        isEditing.value = false;
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => { isModalOpen.value = false; form.reset(); };

const submit = () => {
    const routeName = isEditing.value ? 'admin.tryouts.update' : 'admin.tryouts.store';
    const method = isEditing.value ? 'put' : 'post';
    form[method](route(routeName, form.id), { onSuccess: () => closeModal() });
};

const formatPrice = (p) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p);
</script>

<template>
    <Head title="Kelola Tryout" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter">Manajemen Tryout</h2>
                <button @click="openModal()" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-widest shadow-lg">
                    + Paket Baru
                </button>
            </div>
        </template>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-xl rounded-[2rem] shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">{{ isEditing ? 'Edit' : 'Tambah' }} Paket</h3>
                    <button @click="closeModal" class="text-slate-300 hover:text-slate-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Nama Tryout</label>
                        <input v-model="form.title" type="text" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold focus:ring-2 focus:ring-indigo-500/20" placeholder="SKD CPNS...">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Durasi (Menit)</label>
                            <input v-model="form.duration_minutes" type="number" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold">
                        </div>
                        <div>
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Akses</label>
                            <button type="button" @click="form.is_paid = !form.is_paid" :class="form.is_paid ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400'" class="w-full h-10 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all">
                                {{ form.is_paid ? 'Premium' : 'Gratis' }}
                            </button>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]">Status Publikasi</label>
                            <button type="button" @click="form.is_published = !form.is_published" :class="form.is_published ? 'text-emerald-500' : 'text-slate-300'" class="flex items-center gap-2 transition-all">
                                <span class="text-[8px] font-black uppercase">{{ form.is_published ? 'Published' : 'Draft' }}</span>
                                <div :class="form.is_published ? 'bg-emerald-500' : 'bg-slate-300'" class="w-8 h-4 rounded-full relative">
                                    <div :class="form.is_published ? 'translate-x-4' : 'translate-x-0'" class="absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full transition-transform"></div>
                                </div>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 text-center">Tgl Publish (Muncul di Katalog)</label>
                                <input v-model="form.published_at" type="datetime-local" class="w-full px-3 py-2 bg-white border border-slate-100 rounded-lg text-[9px] font-bold">
                            </div>
                            <div>
                                <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 text-center">Tgl Mulai (Bisa Dikerjakan)</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full px-3 py-2 bg-white border border-slate-100 rounded-lg text-[9px] font-bold">
                            </div>
                        </div>
                    </div>

                    <div v-if="form.is_paid" class="animate-in slide-in-from-top-2">
                        <label class="block text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Harga (IDR)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] font-black">Rp</span>
                            <input v-model="form.price" type="number" class="w-full pl-10 pr-4 py-3 bg-indigo-50/50 border-none rounded-xl text-[10px] font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-50">
                        <button type="button" @click="closeModal" class="px-6 py-3 text-[8px] font-black uppercase tracking-widest text-slate-400">Batal</button>
                        <button type="submit" class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[8px] font-black uppercase tracking-widest hover:bg-indigo-600 shadow-lg active:scale-95 transition-all">
                            {{ isEditing ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>