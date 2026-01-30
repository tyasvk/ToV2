<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    started_at: '',
    ended_at: '',
    duration: 90,
    price: 0,
    description: '',
    requirements: '',
    is_published: false, // Default Draft
});

const submit = () => {
    form.post(route('admin.tryout-akbar.store'));
};
</script>

<template>
    <Head title="Buat Tryout Akbar" />
    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                    
                    <div class="mb-6 pb-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Setup Tryout Akbar</h3>
                            <p class="text-sm text-slate-500">Atur jadwal dan persyaratan event.</p>
                        </div>
                        <Link :href="route('admin.tryout-akbar.index')" class="text-sm font-bold text-slate-500 hover:text-slate-800">
                            &larr; Kembali
                        </Link>
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Event</label>
                            <input v-model="form.title" type="text" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500" placeholder="Contoh: Tryout Akbar Nasional SKD 2024">
                            <p v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Mulai</label>
                                <input v-model="form.started_at" type="datetime-local" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500">
                                <p v-if="form.errors.started_at" class="text-red-500 text-xs mt-1">{{ form.errors.started_at }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Waktu Selesai</label>
                                <input v-model="form.ended_at" type="datetime-local" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500">
                                <p v-if="form.errors.ended_at" class="text-red-500 text-xs mt-1">{{ form.errors.ended_at }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Harga (Rp)</label>
                                <input v-model="form.price" type="number" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">Isi 0 jika Gratis</p>
                                <p v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Durasi Pengerjaan (Menit)</label>
                                <input v-model="form.duration" type="number" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500">
                                <p v-if="form.errors.duration" class="text-red-500 text-xs mt-1">{{ form.errors.duration }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Persyaratan / Info Penting</label>
                            <textarea v-model="form.requirements" rows="4" class="w-full rounded-xl border-slate-300 focus:ring-indigo-500" placeholder="Contoh: Wajib follow IG, Share ke grup WA, dsb."></textarea>
                            <p v-if="form.errors.requirements" class="text-red-500 text-xs mt-1">{{ form.errors.requirements }}</p>
                        </div>

                        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-indigo-900 text-sm">Status Publikasi</h4>
                                <p class="text-xs text-indigo-600">Jika aktif, event akan langsung muncul di halaman user.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_published" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                <span class="ml-3 text-sm font-bold text-slate-700">{{ form.is_published ? 'Published' : 'Draft' }}</span>
                            </label>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-95" :disabled="form.processing">
                                {{ form.processing ? 'Menyimpan...' : 'Simpan & Lanjut Atur Soal' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>