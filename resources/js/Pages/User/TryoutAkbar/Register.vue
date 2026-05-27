<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object, 
});

const form = useForm({
    proof: [], 
});

const previewUrls = ref([]);
const isDragging = ref(false);
const fileInput = ref(null);

const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const addFiles = (files) => {
    const validFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
    if (validFiles.length === 0) {
        alert('Harap unggah berkas gambar (JPG/PNG).');
        return;
    }
    if (form.proof.length + validFiles.length > 5) {
        alert('Maksimal unggah 5 berkas foto.');
        return;
    }
    validFiles.forEach(file => {
        form.proof.push(file);
        previewUrls.value.push(URL.createObjectURL(file));
    });
};

const handleFileChange = (e) => addFiles(e.target.files);
const handleDrop = (e) => {
    isDragging.value = false;
    addFiles(e.dataTransfer.files);
};
const removeFile = (index) => {
    form.proof.splice(index, 1);
    previewUrls.value.splice(index, 1);
    if (fileInput.value) fileInput.value.value = ''; 
};

const submit = () => {
    if (form.proof.length === 0) return;
    form.post(route('tryout-akbar.store-registration', props.tryout.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pendaftaran Event" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] font-sans selection:bg-indigo-100 selection:text-indigo-700 py-6 md:py-10">
            
            <div class="absolute top-0 left-0 w-full h-[400px] bg-gradient-to-b from-slate-200/40 to-transparent pointer-events-none"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
                
                <div class="mb-6 md:mb-8">
                    <Link :href="route('tryout-akbar.index')" class="inline-flex items-center gap-2 text-[11px] md:text-xs text-slate-500 hover:text-indigo-600 transition-colors uppercase tracking-widest font-normal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Katalog
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="bg-white rounded-[2rem] p-6 md:p-10 shadow-xl shadow-slate-200/40 border border-slate-100 relative overflow-hidden">
                            <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50/50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                            <span class="relative z-10 inline-block px-3.5 py-1.5 bg-indigo-50/50 text-indigo-500 text-[9px] md:text-[10px] uppercase tracking-[0.2em] rounded-full border border-indigo-100/50 mb-6 font-normal">
                                Verifikasi Keikutsertaan
                            </span>
                            
                            <h1 class="relative z-10 text-2xl md:text-4xl text-slate-800 leading-tight md:leading-tight uppercase tracking-wide font-normal mb-8">
                                {{ tryout.title }}
                            </h1>
                            
                            <div class="relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <div class="flex-1 px-6 py-4 bg-emerald-50/50 rounded-2xl border border-emerald-100/50 flex flex-col">
                                    <span class="text-[9px] md:text-[10px] text-emerald-600/70 uppercase tracking-widest font-normal mb-1">Total Biaya Pendaftaran</span>
                                    <span class="text-xl md:text-2xl text-emerald-700 font-normal tracking-wide">
                                        {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'GRATIS' }}
                                    </span>
                                </div>
                                <div class="flex-1 px-6 py-4 bg-slate-50/80 rounded-2xl border border-slate-100 flex flex-col">
                                    <span class="text-[9px] md:text-[10px] text-slate-400 uppercase tracking-widest font-normal mb-1">Durasi Pengerjaan</span>
                                    <span class="text-xl md:text-2xl text-slate-700 font-normal tracking-wide">
                                        {{ tryout.duration }} Menit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="transaction" class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                            <h3 class="text-xs text-slate-400 uppercase tracking-widest font-normal border-b border-slate-100 pb-3 mb-4">Status Pengajuan Anda</h3>
                            <div class="flex items-center gap-4">
                                <span class="px-4 py-1.5 rounded-full text-[10px] md:text-xs uppercase tracking-widest border font-normal"
                                    :class="{
                                        'bg-amber-100/50 text-amber-600 border-amber-200/50': transaction.status === 'pending',
                                        'bg-red-100/50 text-red-600 border-red-200/50': transaction.status === 'failed',
                                        'bg-emerald-100/50 text-emerald-600 border-emerald-200/50': transaction.status === 'paid'
                                    }">
                                    {{ transaction.status }}
                                </span>
                                <p v-if="transaction.status === 'pending'" class="text-xs text-slate-500 font-normal">Berkas sedang ditinjau. Estimasi 1x24 Jam.</p>
                            </div>
                        </div>

                        <div v-if="tryout.requirements" class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100">
                            <h3 class="text-[10px] md:text-xs text-slate-400 uppercase tracking-widest font-normal border-b border-slate-100 pb-3 mb-4">Persyaratan Tambahan</h3>
                            <div class="text-xs md:text-sm text-slate-500 leading-relaxed font-normal p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                {{ tryout.requirements }}
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 relative">
                        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden sticky top-8">
                            
                            <div v-if="transaction && transaction.status === 'pending'" class="p-8 md:p-12 text-center">
                                <div class="w-20 h-20 mx-auto bg-amber-50 rounded-full flex items-center justify-center border border-amber-100/50 mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h2 class="text-lg text-slate-700 uppercase tracking-widest font-normal mb-3">Menunggu Verifikasi</h2>
                                <p class="text-slate-500 text-xs md:text-sm font-normal mb-8 leading-relaxed mx-auto">
                                    Proses peninjauan oleh tim admin sedang berlangsung. Mohon cek secara berkala.
                                </p>
                                <button onclick="window.location.reload()" class="text-indigo-600 font-normal uppercase tracking-widest text-[10px] hover:bg-indigo-50 bg-white border border-indigo-100 px-6 py-3 rounded-full transition-colors shadow-sm w-full">
                                    Muat Ulang Halaman
                                </button>
                            </div>

                            <div v-else class="p-6 md:p-8">
                                
                                <div v-if="transaction && transaction.status === 'failed'" class="mb-6 p-5 bg-red-50/50 rounded-2xl border border-red-100 text-center">
                                    <span class="inline-block text-red-500 uppercase tracking-widest text-[10px] md:text-[11px] font-normal mb-2">Pengajuan Ditolak</span>
                                    <p class="text-red-600 text-[11px] md:text-xs font-normal italic leading-relaxed px-2">
                                        "{{ transaction.rejection_note || 'Berkas yang diunggah tidak valid atau buram.' }}"
                                    </p>
                                    <p class="text-red-400 text-[9px] uppercase tracking-widest mt-4 font-normal">
                                        Silakan unggah ulang berkas di bawah ini.
                                    </p>
                                </div>

                                <div class="mb-6 text-center md:text-left border-b border-slate-50 pb-4">
                                    <h4 class="text-[13px] md:text-sm text-slate-800 font-normal uppercase tracking-widest mb-1.5">Lampirkan Bukti Tangkapan Layar</h4>
                                    <p class="text-[10px] md:text-[11px] text-slate-400 font-normal leading-relaxed">
                                        Pastikan berkas gambar yang Anda unggah dapat terbaca dengan jelas dan tidak terpotong.
                                    </p>
                                </div>

                                <form @submit.prevent="submit" class="space-y-6">
                                    
                                    <div 
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop"
                                        class="relative group border border-dashed rounded-[1.5rem] p-8 md:p-10 text-center transition-all duration-300 cursor-pointer flex flex-col items-center justify-center min-h-[180px]"
                                        :class="isDragging ? 'border-indigo-400 bg-indigo-50/50' : 'border-slate-200 hover:border-indigo-300 bg-slate-50/50 hover:bg-slate-50'"
                                    >
                                        <input ref="fileInput" type="file" @change="handleFileChange" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        
                                        <div class="pointer-events-none flex flex-col items-center">
                                            <div class="w-14 h-14 md:w-16 md:h-16 mb-4 bg-white text-slate-300 rounded-full shadow-sm border border-slate-100 flex items-center justify-center group-hover:text-indigo-500 group-hover:scale-105 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                                </svg>
                                            </div>
                                            <p class="text-[11px] md:text-xs text-slate-600 uppercase tracking-widest font-normal group-hover:text-indigo-600 transition-colors mb-1.5">
                                                Pilih atau Tarik Berkas
                                            </p>
                                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-normal">
                                                Maks. 5 Berkas (JPG/PNG)
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="previewUrls.length > 0">
                                        <div class="flex justify-between items-center text-[9px] md:text-[10px] text-slate-400 font-normal uppercase tracking-widest mb-3 px-1">
                                            <span>Berkas Dipilih: {{ form.proof.length }} dari 5</span>
                                            <span v-if="form.errors.proof" class="text-red-500 normal-case">{{ form.errors.proof }}</span>
                                        </div>

                                        <div class="grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-4 xl:grid-cols-5 gap-2 md:gap-3">
                                            <div v-for="(url, index) in previewUrls" :key="url" class="relative group aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
                                                <img :src="url" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity">
                                                <button @click.prevent="removeFile(index)" class="absolute top-1 right-1 bg-white/95 hover:bg-red-50 text-red-500 rounded-lg w-5 h-5 flex items-center justify-center opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all shadow-sm font-normal backdrop-blur-md border border-slate-100/50">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <button 
                                        type="submit" 
                                        class="w-full py-4 bg-slate-900 text-white font-normal uppercase tracking-widest text-[10px] md:text-xs rounded-2xl hover:bg-slate-800 transition-all active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-4 shadow-xl shadow-slate-900/10"
                                        :disabled="form.processing || form.proof.length === 0"
                                    >
                                        <span v-if="form.processing">Memproses...</span>
                                        <span v-else>Kirim Bukti Tangkapan Layar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>