<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tryout: Object,
});

const form = useForm({
    proof_follow: null,
    proof_comment: null,
});

const followPreviewName = ref('');
const commentPreviewName = ref('');

const handleFileChange = (e, field) => {
    const file = e.target.files[0];
    if (!file) return;
    
    form[field] = file;
    if (field === 'proof_follow') followPreviewName.value = file.name;
    if (field === 'proof_comment') commentPreviewName.value = file.name;
};

const submit = () => {
    form.post(route('tryout.store-register', props.tryout.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Upload Persyaratan - ${tryout.title}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent font-sans text-slate-700 pb-16 animate-in fade-in duration-500">
            
            <!-- Padding dan margin disamakan dengan Show.vue / Result.vue (max-w-4xl, py-8, px-4/6/8) -->
            <main class="max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                
                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col gap-4 mb-2">
                    <Link :href="route('tryout.show', tryout.id)" class="inline-flex items-center gap-1.5 text-[#007AFF] hover:opacity-80 text-[14px] font-medium transition-opacity self-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </Link>

                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">Verifikasi Akses</h1>
                        <p class="text-[13px] md:text-sm text-slate-500 font-medium mt-1">
                            Selesaikan 2 langkah mudah di bawah ini untuk membuka akses simulasi {{ tryout.title }}.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    
                    <!-- ========================================== -->
                    <!-- LANGKAH 1: FOLLOW INSTAGRAM                -->
                    <!-- ========================================== -->
                    <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-[0_2px_15px_rgba(0,0,0,0.02)] border border-slate-100 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center font-bold text-[14px] shrink-0">1</div>
                            <h2 class="text-[16px] md:text-[17px] font-semibold text-slate-900">Follow Instagram</h2>
                        </div>
                        <p class="text-[13px] text-slate-500 font-medium">Follow akun <span class="text-slate-900 font-semibold">@cpnsnusantara_</span> lalu unggah bukti tangkapan layarnya di bawah ini.</p>
                        
                        <a href="https://www.instagram.com/cpnsnusantara_" target="_blank" class="self-start inline-flex items-center gap-2 px-5 py-2.5 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-[#007AFF] text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                            Buka Instagram
                        </a>

                        <div class="mt-2 relative w-full">
                            <input type="file" id="proof_follow" accept="image/*" @change="e => handleFileChange(e, 'proof_follow')" class="hidden" />
                            <label for="proof_follow" class="flex items-center justify-between p-4 border border-dashed rounded-[16px] cursor-pointer transition-all" 
                                   :class="followPreviewName ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-slate-300 bg-[#F5F5F7]/50 hover:bg-[#F5F5F7]'">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0" :class="followPreviewName ? 'text-[#007AFF]' : 'text-slate-400'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                    </div>
                                    <span class="text-[13px] truncate font-medium" :class="followPreviewName ? 'text-[#007AFF]' : 'text-slate-500'">
                                        {{ followPreviewName || 'Pilih tangkapan layar...' }}
                                    </span>
                                </div>
                                <span class="shrink-0 text-[12px] bg-white border border-slate-200 text-slate-700 px-4 py-1.5 rounded-full font-semibold shadow-sm ml-2">Browse</span>
                            </label>
                        </div>
                        <div v-if="form.errors.proof_follow" class="text-rose-500 text-[12px] font-medium pl-1">{{ form.errors.proof_follow }}</div>
                    </div>

                    <!-- ========================================== -->
                    <!-- LANGKAH 2: KOMEN & TAG TEMAN               -->
                    <!-- ========================================== -->
                    <div class="bg-white rounded-[24px] p-6 md:p-8 shadow-[0_2px_15px_rgba(0,0,0,0.02)] border border-slate-100 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center font-bold text-[14px] shrink-0">2</div>
                            <h2 class="text-[16px] md:text-[17px] font-semibold text-slate-900">Komen & Tag 5 Teman</h2>
                        </div>
                        <p class="text-[13px] text-slate-500 font-medium">Berikan komentar dan tag minimal 5 teman Anda pada postingan yang telah ditentukan.</p>
                        
                        <a :href="tryout.instagram_post_url || 'https://www.instagram.com/cpnsnusantara_'" target="_blank" class="self-start inline-flex items-center gap-2 px-5 py-2.5 bg-[#F2F2F7] hover:bg-[#E3E3E8] text-[#007AFF] text-[13px] font-semibold rounded-full transition-colors active:scale-95">
                            Buka Postingan
                        </a>

                        <div class="mt-2 relative w-full">
                            <input type="file" id="proof_comment" accept="image/*" @change="e => handleFileChange(e, 'proof_comment')" class="hidden" />
                            <label for="proof_comment" class="flex items-center justify-between p-4 border border-dashed rounded-[16px] cursor-pointer transition-all" 
                                   :class="commentPreviewName ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-slate-300 bg-[#F5F5F7]/50 hover:bg-[#F5F5F7]'">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0" :class="commentPreviewName ? 'text-[#007AFF]' : 'text-slate-400'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                    </div>
                                    <span class="text-[13px] truncate font-medium" :class="commentPreviewName ? 'text-[#007AFF]' : 'text-slate-500'">
                                        {{ commentPreviewName || 'Pilih tangkapan layar...' }}
                                    </span>
                                </div>
                                <span class="shrink-0 text-[12px] bg-white border border-slate-200 text-slate-700 px-4 py-1.5 rounded-full font-semibold shadow-sm ml-2">Browse</span>
                            </label>
                        </div>
                        <div v-if="form.errors.proof_comment" class="text-rose-500 text-[12px] font-medium pl-1">{{ form.errors.proof_comment }}</div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TOMBOL SUBMIT                              -->
                    <!-- ========================================== -->
                    <div class="pt-4 pb-6">
                        <button type="submit" :disabled="form.processing" class="w-full py-4 bg-[#007AFF] hover:bg-[#0062CC] text-white text-[14px] font-semibold rounded-full transition-colors active:scale-95 disabled:opacity-50 shadow-sm flex items-center justify-center gap-2">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Mengunggah...' : 'Kirim & Ajukan Verifikasi' }}
                        </button>
                    </div>

                </form>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>