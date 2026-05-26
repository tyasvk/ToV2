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
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5 flex flex-col">
                    <Link :href="route('admin.tryout-akbar.index')" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-blue-600 transition-colors mb-2 w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Katalog
                    </Link>
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight flex items-center gap-2">
                        Edit Event Akbar
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">Perbarui informasi, jadwal, instruksi pendaftaran, dan harga event.</p>
                </div>

                <div class="relative z-10 flex items-center gap-3 w-full md:w-auto">
                    <button @click="submit" :disabled="form.processing" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed text-white rounded-xl font-semibold text-sm shadow-sm active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 xl:grid-cols-3 gap-6 md:gap-8">
                
                <div class="xl:col-span-2 space-y-6 md:space-y-8">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </div>
                            <h3 class="font-bold text-lg text-slate-800">Informasi Dasar</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Event <span class="text-rose-500">*</span></label>
                                <input v-model="form.title" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="Contoh: Tryout Akbar Nasional 2026" />
                                <p v-if="form.errors.title" class="text-xs text-rose-500 mt-1.5 font-medium">{{ form.errors.title }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi Singkat</label>
                                <textarea v-model="form.description" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all resize-none" placeholder="Jelaskan detail event secara singkat..."></textarea>
                                <p v-if="form.errors.description" class="text-xs text-rose-500 mt-1.5 font-medium">{{ form.errors.description }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="font-bold text-lg text-slate-800">Instruksi Pendaftaran</h3>
                        </div>
                        
                        <div>
                            <div class="flex items-start gap-2 bg-blue-50/50 text-blue-700 p-3.5 rounded-xl border border-blue-100 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <p class="text-xs font-medium leading-relaxed">
                                    Teks persyaratan ini akan muncul di halaman upload bukti bagi pendaftar. Gunakan untuk menjelaskan syarat khusus seperti <span class="font-bold">"Follow Instagram"</span> atau <span class="font-bold">"Share ke Grup WA"</span>.
                                </p>
                            </div>

                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Persyaratan / Cara Daftar</label>
                            <textarea v-model="form.requirements" rows="6" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all resize-none" placeholder="Contoh:&#10;1. Wajib follow Instagram @cpnsnusantara&#10;2. Share postingan ini ke 3 grup WA&#10;3. Upload bukti screenshot di sini."></textarea>
                            <p v-if="form.errors.requirements" class="text-xs text-rose-500 mt-1.5 font-medium">{{ form.errors.requirements }}</p>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1 space-y-6 md:space-y-8">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-3">Status Visibilitas</h3>
                        
                        <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                            <button type="button" @click="form.is_published = false" :class="[!form.is_published ? 'bg-white text-slate-800 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700']" class="py-2.5 rounded-lg font-semibold text-xs transition-all border border-transparent">
                                Draft (Sembunyi)
                            </button>
                            <button type="button" @click="form.is_published = true" :class="[form.is_published ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700']" class="py-2.5 rounded-lg font-semibold text-xs transition-all">
                                Live (Publikasi)
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-3 text-center font-medium">
                            {{ form.is_published ? 'Peserta sudah bisa melihat event ini.' : 'Hanya admin yang dapat melihat event ini.' }}
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-3">Jadwal Pelaksanaan</h3>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Dimulai</label>
                                <input type="datetime-local" v-model="form.started_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" />
                                <div v-if="form.errors.started_at" class="text-rose-500 text-xs mt-1.5 font-medium">{{ form.errors.started_at }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Berakhir</label>
                                <input type="datetime-local" v-model="form.ended_at" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" />
                                <div v-if="form.errors.ended_at" class="text-rose-500 text-xs mt-1.5 font-medium">{{ form.errors.ended_at }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-100 pb-3">Pengaturan Teknis</h3>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi (Menit) <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <input type="number" v-model="form.duration" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all pr-14" placeholder="120" />
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 text-xs font-bold">Menit</span>
                                    </div>
                                </div>
                                <div v-if="form.errors.duration" class="text-rose-500 text-xs mt-1.5 font-medium">{{ form.errors.duration }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Harga Tiket (IDR)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                        <span class="text-slate-500 text-sm font-semibold">Rp</span>
                                    </div>
                                    <input type="number" v-model="form.price" class="w-full bg-white border border-slate-300 rounded-xl pl-10 pr-4 py-2.5 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="0" />
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1.5 font-medium">Biarkan 0 jika event ini 100% Gratis.</p>
                                <div v-if="form.errors.price" class="text-rose-500 text-xs mt-1.5 font-medium">{{ form.errors.price }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>