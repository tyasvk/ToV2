<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object
});

// --- HELPER FORMAT TANGGAL ---
// Mengubah datetime UTC dari database menjadi format datetime-local (YYYY-MM-DDTHH:MM)
const formatDateTimeForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
};

const form = useForm({
    title: props.tryout.title,
    started_at: formatDateTimeForInput(props.tryout.started_at),
    ended_at: formatDateTimeForInput(props.tryout.ended_at),
    duration: props.tryout.duration,
    price: props.tryout.price,
    description: props.tryout.description || '',
    requirements: props.tryout.requirements || '',
    is_published: Boolean(props.tryout.is_published),
});

const submit = () => {
    form.put(route('admin.tryout-akbar.update', props.tryout.id));
};
</script>

<template>
    <Head :title="`Edit ${tryout.title}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 py-8 font-sans text-slate-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <nav class="flex items-center text-sm font-medium text-slate-500 mb-2">
                            <Link :href="route('admin.tryout-akbar.index')" class="hover:text-indigo-600 transition flex items-center gap-1">
                                &larr; Kembali ke Daftar
                            </Link>
                            <span class="mx-2 text-slate-300">/</span>
                            <span class="text-slate-800">Edit Event</span>
                        </nav>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Edit Tryout Akbar</h1>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <Link :href="route('admin.tryout-akbar.index')" class="px-5 py-2.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 transition text-sm">
                            Batal
                        </Link>
                        <button @click="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition text-sm disabled:opacity-50 flex items-center gap-2">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        
                        <div class="lg:col-span-2 space-y-6">
                            
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                <h3 class="font-bold text-lg text-slate-800 mb-6 border-b border-slate-100 pb-4">Informasi Dasar</h3>
                                
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Event</label>
                                        <input 
                                            type="text" 
                                            v-model="form.title" 
                                            class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm text-slate-800 font-medium"
                                            placeholder="Contoh: Tryout Akbar Nasional 2026"
                                        >
                                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
                                        <textarea 
                                            v-model="form.description" 
                                            rows="4" 
                                            class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm"
                                            placeholder="Jelaskan detail event secara singkat..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                <h3 class="font-bold text-lg text-slate-800 mb-6 border-b border-slate-100 pb-4 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Instruksi Pendaftaran
                                </h3>
                                
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Persyaratan / Cara Daftar</label>
                                    <p class="text-xs text-slate-400 mb-3 bg-slate-50 p-2 rounded border border-slate-100">
                                        ℹ️ Teks ini akan muncul di halaman upload bukti. Gunakan untuk menjelaskan syarat seperti "Follow IG", "Share WA", dll.
                                    </p>
                                    <textarea 
                                        v-model="form.requirements" 
                                        rows="5" 
                                        class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-sm font-mono text-sm"
                                        placeholder="Contoh:&#10;1. Wajib follow Instagram @kami&#10;2. Share postingan ke 3 grup WA&#10;3. Upload bukti screenshot di sini."
                                    ></textarea>
                                </div>
                            </div>

                        </div>

                        <div class="lg:col-span-1 space-y-6">
                            
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status Event</h3>
                                
                                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border transition" :class="form.is_published ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200'">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input type="checkbox" v-model="form.is_published" class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <span class="block text-sm font-bold" :class="form.is_published ? 'text-emerald-800' : 'text-slate-600'">
                                            {{ form.is_published ? 'Published (Aktif)' : 'Draft (Sembunyi)' }}
                                        </span>
                                        <span class="block text-[10px] text-slate-500 mt-1">
                                            {{ form.is_published ? 'Event terlihat oleh user.' : 'Hanya admin yang bisa melihat.' }}
                                        </span>
                                    </div>
                                </label>
                            </div>

                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Jadwal Pelaksanaan</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Mulai</label>
                                        <input type="datetime-local" v-model="form.started_at" class="w-full rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <div v-if="form.errors.started_at" class="text-red-500 text-[10px] mt-1">{{ form.errors.started_at }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Selesai</label>
                                        <input type="datetime-local" v-model="form.ended_at" class="w-full rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <div v-if="form.errors.ended_at" class="text-red-500 text-[10px] mt-1">{{ form.errors.ended_at }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pengaturan Teknis</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Durasi Pengerjaan</label>
                                        <div class="relative">
                                            <input type="number" v-model="form.duration" class="w-full rounded-lg border-slate-300 text-sm pr-12 focus:ring-indigo-500 focus:border-indigo-500" placeholder="120">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Menit</span>
                                        </div>
                                        <div v-if="form.errors.duration" class="text-red-500 text-[10px] mt-1">{{ form.errors.duration }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Pendaftaran</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500">Rp</span>
                                            <input type="number" v-model="form.price" class="w-full rounded-lg border-slate-300 text-sm pl-8 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0">
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-1">Isi 0 untuk Gratis.</p>
                                        <div v-if="form.errors.price" class="text-red-500 text-[10px] mt-1">{{ form.errors.price }}</div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </form>

            </div>
        </div>
    </AuthenticatedLayout>
</template>