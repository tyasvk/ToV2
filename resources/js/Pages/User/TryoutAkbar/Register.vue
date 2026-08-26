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
    <Head title="Verifikasi Event - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background Apple Default (#F2F2F7 atau Transparent agar menyatu) -->
        <div class="w-full bg-transparent pb-24 md:pb-16 animate-in fade-in duration-500 font-sans">
            
            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 relative z-10">
                
                <!-- Navigasi Kembali -->
                <div class="mb-6 md:mb-8">
                    <Link :href="route('tryout-akbar.index')" class="inline-flex items-center gap-1.5 text-[14px] font-semibold text-[#007AFF] hover:opacity-80 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali ke Event
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 items-start">
                    
                    <!-- BAGIAN KIRI (Informasi Event) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="bg-white rounded-[32px] p-6 sm:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 relative overflow-hidden">
                            <span class="inline-block px-3 py-1.5 bg-[#F0F4FF] text-[#007AFF] text-[12px] font-bold uppercase tracking-wider rounded-full mb-5">
                                Verifikasi Keikutsertaan
                            </span>
                            
                            <h1 class="text-[26px] sm:text-[34px] font-bold text-[#1D1D1F] leading-tight tracking-tight mb-8">
                                {{ tryout.title }}
                            </h1>
                            
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                                <div class="flex-1 px-5 sm:px-6 py-4 sm:py-5 bg-[#F5F5F7] rounded-[24px] flex flex-col justify-center">
                                    <span class="text-[12px] text-[#86868B] font-semibold mb-1">Total Biaya Pendaftaran</span>
                                    <span class="text-[20px] sm:text-[24px] text-[#1D1D1F] font-bold tracking-tight">
                                        {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'Gratis' }}
                                    </span>
                                </div>
                                <div class="flex-1 px-5 sm:px-6 py-4 sm:py-5 bg-[#F5F5F7] rounded-[24px] flex flex-col justify-center">
                                    <span class="text-[12px] text-[#86868B] font-semibold mb-1">Durasi Pengerjaan</span>
                                    <span class="text-[20px] sm:text-[24px] text-[#1D1D1F] font-bold tracking-tight">
                                        {{ tryout.duration }} Menit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Transaksi (Jika Ada) -->
                        <div v-if="transaction" class="bg-white rounded-[32px] p-6 sm:p-8 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5">
                            <h3 class="text-[15px] text-[#1D1D1F] font-bold border-b border-black/5 pb-4 mb-4">Status Pengajuan Anda</h3>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <span class="px-5 py-2 rounded-full text-[13px] font-bold w-fit"
                                    :class="{
                                        'bg-amber-100/50 text-amber-600': transaction.status === 'pending',
                                        'bg-red-100/50 text-red-600': transaction.status === 'failed',
                                        'bg-emerald-100/50 text-emerald-600': transaction.status === 'paid'
                                    }">
                                    {{ transaction.status === 'pending' ? 'Menunggu Verifikasi' : (transaction.status === 'failed' ? 'Ditolak' : 'Disetujui') }}
                                </span>
                                <p v-if="transaction.status === 'pending'" class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                                    Berkas Anda sedang ditinjau oleh tim kami. Estimasi proses 1x24 jam kerja.
                                </p>
                            </div>
                        </div>

                        <!-- Syarat & Ketentuan -->
                        <div v-if="tryout.requirements" class="bg-white rounded-[32px] p-6 sm:p-8 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-black/5">
                            <h3 class="text-[15px] text-[#1D1D1F] font-bold border-b border-black/5 pb-4 mb-4">Persyaratan Tambahan</h3>
                            <div class="text-[14px] text-[#86868B] leading-relaxed font-medium p-5 bg-[#F5F5F7] rounded-[20px] whitespace-pre-wrap">
                                {{ tryout.requirements }}
                            </div>
                        </div>
                    </div>

                    <!-- BAGIAN KANAN (Form Upload / Status) -->
                    <div class="lg:col-span-5 relative">
                        <div class="bg-white rounded-[32px] shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-black/5 overflow-hidden sticky top-8">
                            
                            <!-- State: Menunggu Verifikasi -->
                            <div v-if="transaction && transaction.status === 'pending'" class="p-8 sm:p-12 text-center">
                                <div class="w-20 h-20 mx-auto bg-amber-50 rounded-full flex items-center justify-center mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h2 class="text-[20px] text-[#1D1D1F] font-bold mb-2">Peninjauan Berkas</h2>
                                <p class="text-[#86868B] text-[14px] font-medium mb-8 leading-relaxed max-w-xs mx-auto">
                                    Berkas pengajuan Anda sedang dalam antrean verifikasi. Silakan periksa kembali secara berkala.
                                </p>
                                <button onclick="window.location.reload()" class="w-full text-[#007AFF] font-semibold text-[15px] bg-[#F0F4FF] hover:bg-[#E0EBF5] py-3.5 rounded-full transition-colors">
                                    Muat Ulang Halaman
                                </button>
                            </div>

                            <!-- State: Upload Form (Baru atau Ditolak) -->
                            <div v-else class="p-6 sm:p-8">
                                
                                <!-- Notifikasi Ditolak -->
                                <div v-if="transaction && transaction.status === 'failed'" class="mb-6 p-5 bg-red-50 rounded-[20px] text-center border border-red-100">
                                    <span class="block text-red-600 font-bold text-[14px] mb-1">Pengajuan Ditolak</span>
                                    <p class="text-red-500 text-[13px] font-medium leading-relaxed mb-3">
                                        "{{ transaction.rejection_note || 'Berkas yang Anda unggah tidak valid atau buram.' }}"
                                    </p>
                                    <p class="text-red-400 text-[12px] font-medium">
                                        Silakan unggah ulang bukti yang benar di bawah ini.
                                    </p>
                                </div>

                                <div class="mb-6 text-center sm:text-left border-b border-black/5 pb-5">
                                    <h4 class="text-[18px] text-[#1D1D1F] font-bold mb-1.5">Unggah Bukti</h4>
                                    <p class="text-[13px] text-[#86868B] font-medium leading-relaxed">
                                        Lampirkan tangkapan layar pembayaran atau persyaratan lainnya. Maksimal 5 foto (JPG/PNG).
                                    </p>
                                </div>

                                <form @submit.prevent="submit" class="space-y-6">
                                    
                                    <!-- Area Drag & Drop -->
                                    <div 
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop"
                                        class="relative group border-2 border-dashed rounded-[24px] p-8 sm:p-10 text-center transition-all duration-300 cursor-pointer flex flex-col items-center justify-center min-h-[200px]"
                                        :class="isDragging ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-[#C7C7CC] hover:border-[#007AFF] bg-[#F5F5F7] hover:bg-[#F0F4FF]/50'"
                                    >
                                        <input ref="fileInput" type="file" @change="handleFileChange" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                        
                                        <div class="pointer-events-none flex flex-col items-center">
                                            <div class="w-14 h-14 mb-4 bg-white text-[#86868B] rounded-full shadow-sm flex items-center justify-center group-hover:text-[#007AFF] group-hover:scale-105 transition-all duration-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                                </svg>
                                            </div>
                                            <p class="text-[14px] text-[#1D1D1F] font-semibold group-hover:text-[#007AFF] transition-colors mb-1">
                                                Pilih atau Seret Foto ke Sini
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Preview Gambar -->
                                    <div v-if="previewUrls.length > 0">
                                        <div class="flex justify-between items-center text-[12px] font-semibold text-[#86868B] mb-3 px-1">
                                            <span>Terpilih: {{ form.proof.length }} dari 5</span>
                                            <span v-if="form.errors.proof" class="text-red-500">{{ form.errors.proof }}</span>
                                        </div>

                                        <div class="grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                                            <div v-for="(url, index) in previewUrls" :key="url" class="relative group aspect-square rounded-[16px] overflow-hidden border border-black/5 bg-[#F5F5F7]">
                                                <img :src="url" class="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity">
                                                
                                                <!-- Tombol Hapus Apple Style (Glassmorphism Blur) -->
                                                <button @click.prevent="removeFile(index)" class="absolute top-1.5 right-1.5 bg-black/40 backdrop-blur-md text-white rounded-full w-6 h-6 flex items-center justify-center opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-all hover:bg-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Submit -->
                                    <button 
                                        type="submit" 
                                        class="w-full py-3.5 bg-[#007AFF] text-white font-semibold text-[15px] rounded-full hover:bg-[#0062CC] transition-all active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 mt-2 shadow-[0_4px_14px_rgba(0,122,255,0.3)]"
                                        :disabled="form.processing || form.proof.length === 0"
                                    >
                                        <span v-if="form.processing">Memproses...</span>
                                        <span v-else>Kirim Bukti Pembayaran</span>
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

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>