<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profil Saya - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Background transparan agar menyatu dengan latar abu-abu/putih sistem -->
        <div class="w-full bg-transparent pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 md:space-y-8">
                
                <!-- HEADER (iCloud Header Style) -->
                <div class="flex flex-col gap-1.5 border-b border-black/5 pb-5">
                    <h1 class="text-[26px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight">
                        Profil Saya
                    </h1>
                    <p class="text-[14px] sm:text-[15px] text-[#86868B] font-medium">
                        Kelola informasi akun dan pengaturan keamanan Anda di sini.
                    </p>
                </div>

                <!-- CARDS CONTAINER -->
                <div class="space-y-6 md:space-y-8 form-stagger">
                    
                    <!-- Form 1: Informasi Profil -->
                    <div class="bg-white border border-black/5 rounded-[24px] sm:rounded-[32px] p-6 sm:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-2xl"
                        />
                    </div>

                    <!-- Form 2: Ubah Password -->
                    <div class="bg-white border border-black/5 rounded-[24px] sm:rounded-[32px] p-6 sm:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                        <UpdatePasswordForm class="max-w-2xl" />
                    </div>

                    <!-- Form 3: Hapus Akun -->
                    <div class="bg-white border border-black/5 rounded-[24px] sm:rounded-[32px] p-6 sm:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                        <DeleteUserForm class="max-w-2xl" />
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

@keyframes slideUpFade {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Terapkan animasi ke setiap div di dalam form-stagger */
.form-stagger > div {
    opacity: 0; /* Mulai dalam keadaan tidak terlihat */
    animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Penundaan animasi berurutan (Stagger Effect) */
.form-stagger > div:nth-child(1) { animation-delay: 0.1s; }
.form-stagger > div:nth-child(2) { animation-delay: 0.2s; }
.form-stagger > div:nth-child(3) { animation-delay: 0.3s; }
</style>