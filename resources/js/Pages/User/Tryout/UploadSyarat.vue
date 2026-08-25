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
        forceFormData: true, // Wajib untuk kirim file gambar
    });
};
</script>

<template>
    <Head :title="`Upload Persyaratan - ${tryout.title}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent font-sans selection:bg-[#0071e3] selection:text-white animate-in fade-in duration-700 py-6 sm:py-10">
            <div class="max-w-2xl mx-auto px-4 sm:px-6">
                
                <div class="mb-6">
                    <Link :href="route('tryout.show', tryout.id)" class="inline-flex items-center gap-1 text-[#0071e3] hover:opacity-80 text-[14px] sm:text-[15px] font-medium transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </Link>
                </div>

                <div class="text-center mb-8">
                    <h1 class="text-[24px] sm:text-[30px] font-semibold text-[#1d1d1f] tracking-tight mb-2">
                        Verifikasi Akses Gratis
                    </h1>
                    <p class="text-[13px] sm:text-[15px] text-[#86868b] leading-relaxed max-w-md mx-auto">
                        Selesaikan 2 langkah mudah di bawah ini untuk membuka akses simulasi {{ tryout.title }}.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                    
                    <!-- Langkah 1 -->
                    <div class="bg-white rounded-[20px] sm:rounded-[22px] p-5 sm:p-6 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-7 h-7 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center font-semibold text-[13px]">1</div>
                            <h2 class="text-[15px] sm:text-[16px] font-semibold text-[#1d1d1f]">Follow Instagram</h2>
                        </div>
                        <p class="text-[13px] text-[#86868b] mb-4">Follow <span class="font-medium text-[#1d1d1f]">@cpnsnusantara_</span> lalu unggah screenshot.</p>
                        
                        <a href="https://www.instagram.com/cpnsnusantara_" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#f5f5f7] hover:bg-[#e8e8ed] text-[#0071e3] text-[13px] font-medium rounded-full transition-colors mb-4">
                            Buka @cpnsnusantara_
                        </a>

                        <div class="relative">
                            <input type="file" id="proof_follow" accept="image/*" @change="e => handleFileChange(e, 'proof_follow')" class="hidden" />
                            <label for="proof_follow" class="flex items-center justify-between p-3.5 border border-dashed rounded-[14px] cursor-pointer transition-all" :class="followPreviewName ? 'border-[#0071e3] bg-[#0071e3]/5' : 'border-[#d2d2d7] bg-[#f5f5f7]/50'">
                                <span class="text-[13px] truncate pr-2" :class="followPreviewName ? 'text-[#0071e3] font-medium' : 'text-[#86868b]'">{{ followPreviewName || 'Pilih tangkapan layar...' }}</span>
                                <span class="shrink-0 text-[12px] bg-white border border-[#d2d2d7]/60 text-[#1d1d1f] px-3 py-1 rounded-full font-medium shadow-2xs">Browse</span>
                            </label>
                        </div>
                        <div v-if="form.errors.proof_follow" class="text-red-500 text-[12px] mt-1.5 pl-1">{{ form.errors.proof_follow }}</div>
                    </div>

                    <!-- Langkah 2 -->
                    <div class="bg-white rounded-[20px] sm:rounded-[22px] p-5 sm:p-6 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-[#d2d2d7]/40">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-7 h-7 rounded-full bg-[#0071e3]/10 text-[#0071e3] flex items-center justify-center font-semibold text-[13px]">2</div>
                            <h2 class="text-[15px] sm:text-[16px] font-semibold text-[#1d1d1f]">Komen & Tag 5 Teman</h2>
                        </div>
                        <p class="text-[13px] text-[#86868b] mb-4">Berikan komentar dan tag 5 teman pada postingan yang ditentukan.</p>
                        
                        <a :href="tryout.instagram_post_url || 'https://www.instagram.com/cpnsnusantara_'" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-[#f5f5f7] hover:bg-[#e8e8ed] text-[#0071e3] text-[13px] font-medium rounded-full transition-colors mb-4">
                            Buka Postingan Instagram
                        </a>

                        <div class="relative">
                            <input type="file" id="proof_comment" accept="image/*" @change="e => handleFileChange(e, 'proof_comment')" class="hidden" />
                            <label for="proof_comment" class="flex items-center justify-between p-3.5 border border-dashed rounded-[14px] cursor-pointer transition-all" :class="commentPreviewName ? 'border-[#0071e3] bg-[#0071e3]/5' : 'border-[#d2d2d7] bg-[#f5f5f7]/50'">
                                <span class="text-[13px] truncate pr-2" :class="commentPreviewName ? 'text-[#0071e3] font-medium' : 'text-[#86868b]'">{{ commentPreviewName || 'Pilih tangkapan layar...' }}</span>
                                <span class="shrink-0 text-[12px] bg-white border border-[#d2d2d7]/60 text-[#1d1d1f] px-3 py-1 rounded-full font-medium shadow-2xs">Browse</span>
                            </label>
                        </div>
                        <div v-if="form.errors.proof_comment" class="text-red-500 text-[12px] mt-1.5 pl-1">{{ form.errors.proof_comment }}</div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing" class="w-full py-3 px-6 bg-[#0071e3] hover:bg-[#005bb5] text-white text-[14px] font-medium rounded-full transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Mengirim...' : 'Kirim & Verifikasi' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>