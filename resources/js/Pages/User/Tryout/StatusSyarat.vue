<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tryout: Object,
    status: String,
});
</script>

<template>
    <Head title="Menunggu Verifikasi" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-transparent flex flex-col items-center justify-center p-4">
            <div class="bg-white rounded-[24px] p-8 max-w-sm w-full text-center shadow-[0_2px_20px_rgba(0,0,0,0.04)] border border-[#d2d2d7]/40">
                
                <div v-if="status === 'pending'" class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-[#0071e3] animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div v-else-if="status === 'approved'" class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-[20px] font-semibold text-[#1d1d1f] mb-2">
                    {{ status === 'approved' ? 'Verifikasi Berhasil' : 'Menunggu Verifikasi' }}
                </h2>
                <p class="text-[14px] text-[#86868b] leading-relaxed mb-6">
                    {{ status === 'approved' 
                        ? 'Akses tryout Anda sudah dibuka. Silakan mulai ujian sekarang.' 
                        : 'Persyaratan Anda sedang dicek oleh Admin. Silakan cek kembali halaman ini secara berkala.' 
                    }}
                </p>

                <div class="space-y-3">
                    <Link v-if="status === 'approved'" :href="route('tryout.show', tryout.id)" class="block w-full py-2.5 bg-[#0071e3] text-white rounded-full text-[14px] font-medium transition hover:bg-[#005bb5]">
                        Mulai Ujian
                    </Link>
                    <Link :href="route('tryout.index')" class="block w-full py-2.5 bg-[#f5f5f7] text-[#1d1d1f] rounded-full text-[14px] font-medium transition hover:bg-[#e8e8ed]">
                        Kembali ke Katalog
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>