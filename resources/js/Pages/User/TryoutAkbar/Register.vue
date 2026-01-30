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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// --- FILE HANDLING LOGIC ---

// 1. Tambah File (Validasi Max 5 & Image Type)
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

// 2. Event Handlers
const handleFileChange = (e) => addFiles(e.target.files);

const handleDrop = (e) => {
    isDragging.value = false;
    addFiles(e.dataTransfer.files);
};

// 3. Hapus File dari List
const removeFile = (index) => {
    form.proof.splice(index, 1);
    previewUrls.value.splice(index, 1);
    
    // Reset input file agar bisa pilih file yang sama lagi jika perlu
    if (fileInput.value) fileInput.value.value = ''; 
};

// 4. Submit
const submit = () => {
    if (form.proof.length === 0) return;
    
    form.post(route('tryout-akbar.store-registration', props.tryout.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Reset form handled by Inertia reload usually
        }
    });
};
</script>

<template>
    <Head title="Registrasi Event" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] font-sans relative selection:bg-indigo-100 selection:text-indigo-700 pb-20">
            
            <div class="absolute top-0 left-0 w-full h-[500px] bg-gradient-to-b from-slate-100 to-transparent pointer-events-none"></div>
            <div class="absolute top-10 right-0 w-96 h-96 bg-indigo-100/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-40 left-10 w-72 h-72 bg-amber-100/40 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                
                <nav class="flex items-center text-sm font-medium text-slate-500 mb-8 animate-fade-in-down">
                    <Link :href="route('tryout-akbar.index')" class="hover:text-indigo-600 transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                        Kembali ke Katalog
                    </Link>
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-800">Verifikasi Pendaftaran</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    
                    <div class="lg:col-span-7 space-y-6 animate-fade-in-up">
                        
                        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-100 to-transparent rounded-bl-full opacity-50"></div>
                            
                            <div class="relative">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-indigo-100 mb-5">
                                    <span class="w-1.5 h-1.5 bg-indigo-600 rounded-full animate-pulse"></span>
                                    Official Event
                                </span>

                                <h1 class="text-3xl md:text-4xl font-black text-slate-800 leading-tight mb-4">
                                    {{ tryout.title }}
                                </h1>

                                <div class="flex items-center gap-4 mb-8">
                                    <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                                        <p class="text-[10px] font-bold text-emerald-600 uppercase">Biaya Pendaftaran</p>
                                        <p class="text-xl font-black text-emerald-700">
                                            {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'GRATIS' }}
                                        </p>
                                    </div>
                                    <div class="px-4 py-2 bg-slate-50 rounded-xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase">Durasi</p>
                                        <p class="text-xl font-black text-slate-700">{{ tryout.duration }} <span class="text-sm font-bold text-slate-400">Menit</span></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-slate-100">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase">Waktu Mulai</p>
                                            <p class="text-sm font-bold text-slate-700 mt-0.5">{{ formatDate(tryout.started_at) }} WIB</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-400 uppercase">Batas Akhir</p>
                                            <p class="text-sm font-bold text-slate-700 mt-0.5">{{ formatDate(tryout.ended_at) }} WIB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-100">
                            <h3 class="font-bold text-lg text-slate-800 mb-6 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Syarat & Ketentuan
                            </h3>
                            <div class="prose prose-sm prose-slate max-w-none bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                                <p v-if="!tryout.requirements" class="text-slate-400 italic">Tidak ada persyaratan khusus untuk event ini.</p>
                                <p v-else class="whitespace-pre-line text-slate-600 font-medium leading-relaxed">
                                    {{ tryout.requirements }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 relative animate-fade-in-up delay-100">
                        <div class="bg-white rounded-[2rem] shadow-xl shadow-indigo-100/50 border border-slate-100 overflow-hidden sticky top-8">
                            
                            <div class="px-8 py-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                                <h3 class="font-bold text-slate-800">Status Pendaftaran</h3>
                                
                                <span v-if="transaction" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm"
                                    :class="{
                                        'bg-amber-100 text-amber-700 border-amber-200': transaction.status === 'pending',
                                        'bg-red-100 text-red-700 border-red-200': transaction.status === 'failed',
                                        'bg-emerald-100 text-emerald-700 border-emerald-200': transaction.status === 'success' || transaction.status === 'paid'
                                    }">
                                    {{ transaction.status === 'paid' ? 'Terdaftar' : transaction.status }}
                                </span>
                                <span v-else class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-500 border border-slate-200">
                                    Belum Daftar
                                </span>
                            </div>

                            <div class="p-8">
                                
                                <div v-if="transaction && transaction.status === 'pending'" class="text-center py-8">
                                    <div class="relative w-24 h-24 mx-auto mb-6">
                                        <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                                        <div class="absolute inset-0 border-4 border-indigo-500 rounded-full border-t-transparent animate-spin"></div>
                                        <div class="absolute inset-0 flex items-center justify-center text-3xl">⏳</div>
                                    </div>
                                    <h2 class="text-xl font-black text-slate-800 mb-2">Menunggu Verifikasi</h2>
                                    <p class="text-slate-500 text-sm leading-relaxed mb-6">
                                        Data Anda sedang ditinjau oleh Admin. <br>
                                        Estimasi verifikasi: 1x24 Jam.
                                    </p>
                                    <button onclick="window.location.reload()" class="text-indigo-600 font-bold text-xs hover:underline bg-indigo-50 px-4 py-2 rounded-lg transition">
                                        Refresh Status
                                    </button>
                                </div>

                                <div v-else>
                                    
                                    <div v-if="transaction && transaction.status === 'failed'" class="mb-6 p-4 bg-red-50 rounded-2xl border border-red-100 flex items-start gap-3">
                                        <div class="text-lg">❌</div>
                                        <div>
                                            <p class="font-bold text-red-700 text-sm">Pendaftaran Ditolak</p>
                                            <p class="text-red-600 text-xs mt-1">Bukti tidak valid. Silakan perbaiki dan upload ulang.</p>
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
                                            <input 
                                                ref="fileInput"
                                                type="file" 
                                                @change="handleFileChange" 
                                                multiple 
                                                accept="image/png, image/jpeg, image/jpg" 
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                            >
                                            
                                            <div class="pointer-events-none space-y-3">
                                                <div class="w-14 h-14 bg-white text-indigo-500 rounded-full shadow-sm border border-slate-100 flex items-center justify-center mx-auto group-hover:scale-110 transition duration-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition">Klik atau Tarik File ke Sini</p>
                                                    <p class="text-xs text-slate-400 mt-1">JPG, PNG (Max 5 File)</p>
                                                </div>
                                            </div>
                                        </div>

                                        <transition-group name="list" tag="div" class="grid grid-cols-3 gap-3" v-if="previewUrls.length > 0">
                                            <div v-for="(url, index) in previewUrls" :key="url" class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                                <img :src="url" class="w-full h-full object-cover">
                                                
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                    <button @click.prevent="removeFile(index)" class="bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 transition transform hover:scale-110" title="Hapus gambar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div v-if="previewUrls.length < 5" class="aspect-square rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 hover:text-indigo-500 hover:border-indigo-300 hover:bg-indigo-50 transition cursor-pointer" @click="$refs.fileInput.click()">
                                                <span class="text-2xl font-bold">+</span>
                                            </div>
                                        </transition-group>

                                        <div class="flex justify-between items-center text-xs text-slate-400 font-bold px-1">
                                            <span>{{ form.proof.length }} / 5 File</span>
                                            <span v-if="form.errors.proof" class="text-red-500">{{ form.errors.proof }}</span>
                                        </div>

                                        <button 
                                            type="submit" 
                                            class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                            :disabled="form.processing || form.proof.length === 0"
                                        >
                                            <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            <span v-else>Kirim Bukti & Daftar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
                                <p class="text-[10px] text-slate-400">
                                    Pastikan gambar bukti jelas dan dapat dibaca.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animasi Masuk */
.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

.animate-fade-in-down {
    animation: fadeInDown 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Animasi List Grid */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>