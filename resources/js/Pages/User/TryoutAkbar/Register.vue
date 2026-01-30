<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tryout: Object,
    transaction: Object, // Status: null (belum daftar), pending, success, failed
});

// --- STATE MANAGEMENT ---
const form = useForm({
    proof: [], // Array untuk multiple files
});

const previewUrls = ref([]);
const isDragging = ref(false);
const fileInput = ref(null);

// --- HELPER FUNCTIONS ---
const formatRupiah = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

// --- FILE HANDLING LOGIC ---
const addFiles = (files) => {
    const validFiles = Array.from(files).filter(file => file.type.startsWith('image/'));
    if (validFiles.length === 0) {
        alert('Harap upload file gambar (JPG/PNG).');
        return;
    }
    if (form.proof.length + validFiles.length > 5) {
        alert('Maksimal upload 5 file bukti.');
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
    <Head title="Registrasi Event" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] font-sans relative selection:bg-indigo-100 selection:text-indigo-700 pb-20">
            
            <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-slate-100 to-transparent pointer-events-none"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                
                <nav class="flex items-center text-sm font-medium text-slate-500 mb-8">
                    <Link :href="route('tryout-akbar.index')" class="hover:text-indigo-600 transition flex items-center gap-1">
                        &larr; Kembali ke Katalog
                    </Link>
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-800">Verifikasi Pendaftaran</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    
                    <div class="lg:col-span-7 space-y-6">
                        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100 mb-5">
                                Official Event
                            </span>
                            <h1 class="text-3xl md:text-4xl font-black text-slate-800 leading-tight mb-4">{{ tryout.title }}</h1>
                            <div class="flex items-center gap-4 mb-8">
                                <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                                    <p class="text-[10px] font-bold text-emerald-600 uppercase">Biaya</p>
                                    <p class="text-xl font-black text-emerald-700">{{ tryout.price > 0 ? formatRupiah(tryout.price) : 'GRATIS' }}</p>
                                </div>
                                <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Durasi</p>
                                    <p class="text-xl font-black text-slate-700">{{ tryout.duration }} Menit</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                            <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2">Syarat & Ketentuan</h3>
                            <div class="prose prose-sm prose-slate max-w-none bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                                <p class="whitespace-pre-line text-slate-600 font-medium">{{ tryout.requirements || 'Tidak ada persyaratan khusus.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 relative">
                        <div class="bg-white rounded-[2rem] shadow-xl shadow-indigo-100/50 border border-slate-100 overflow-hidden sticky top-8">
                            
                            <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800">Status Pendaftaran</h3>
                                <span v-if="transaction" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm"
                                    :class="{
                                        'bg-amber-100 text-amber-700 border-amber-200': transaction.status === 'pending',
                                        'bg-red-100 text-red-700 border-red-200': transaction.status === 'failed',
                                        'bg-emerald-100 text-emerald-700 border-emerald-200': transaction.status === 'paid'
                                    }">
                                    {{ transaction.status }}
                                </span>
                                <span v-else class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200">
                                    Belum Daftar
                                </span>
                            </div>

                            <div class="p-8">
                                
                                <div v-if="transaction && transaction.status === 'pending'" class="text-center py-8">
                                    <div class="text-5xl mb-4">⏳</div>
                                    <h2 class="text-xl font-black text-slate-800 mb-2">Menunggu Verifikasi</h2>
                                    <p class="text-slate-500 text-sm mb-6">Data Anda sedang ditinjau. Estimasi 1x24 Jam.</p>
                                    <button onclick="window.location.reload()" class="text-indigo-600 font-bold text-xs hover:underline bg-indigo-50 px-4 py-2 rounded-lg">Refresh Status</button>
                                </div>

                                <div v-else>
                                    
                                    <div v-if="transaction && transaction.status === 'failed'" class="mb-6 p-4 bg-red-50 rounded-2xl border border-red-100 flex items-start gap-3">
                                        <div class="text-lg">❌</div>
                                        <div>
                                            <p class="font-bold text-red-700 text-sm">Pendaftaran Ditolak</p>
                                            <p class="text-red-600 text-xs mt-1">
                                                Alasan: <span class="font-bold">{{ transaction.rejection_note || 'Bukti tidak valid.' }}</span>
                                            </p>
                                            <p class="text-red-500 text-[10px] mt-2">Silakan perbaiki dan upload ulang bukti baru di bawah ini.</p>
                                        </div>
                                    </div>

                                    <form @submit.prevent="submit" class="space-y-6">
                                        
                                        <div 
                                            @dragover.prevent="isDragging = true"
                                            @dragleave.prevent="isDragging = false"
                                            @drop.prevent="handleDrop"
                                            class="relative group border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-300 cursor-pointer"
                                            :class="isDragging ? 'border-indigo-500 bg-indigo-50 scale-[1.02]' : 'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'"
                                        >
                                            <input ref="fileInput" type="file" @change="handleFileChange" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div class="pointer-events-none space-y-3">
                                                <div class="w-14 h-14 bg-white text-indigo-500 rounded-full shadow-sm border border-slate-100 flex items-center justify-center mx-auto group-hover:scale-110 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                </div>
                                                <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition">Klik atau Tarik File ke Sini</p>
                                                <p class="text-xs text-slate-400 mt-1">JPG, PNG (Max 5 File)</p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-3 gap-3" v-if="previewUrls.length > 0">
                                            <div v-for="(url, index) in previewUrls" :key="url" class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200">
                                                <img :src="url" class="w-full h-full object-cover">
                                                <button @click.prevent="removeFile(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition shadow-md">&times;</button>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center text-xs text-slate-400 font-bold px-1">
                                            <span>{{ form.proof.length }} / 5 File</span>
                                            <span v-if="form.errors.proof" class="text-red-500">{{ form.errors.proof }}</span>
                                        </div>

                                        <button 
                                            type="submit" 
                                            class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                            :disabled="form.processing || form.proof.length === 0"
                                        >
                                            <span v-if="form.processing">Mengirim...</span>
                                            <span v-else>Kirim Bukti & Daftar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>