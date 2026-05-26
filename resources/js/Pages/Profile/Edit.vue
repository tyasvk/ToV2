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
    <Head title="Profil Saya" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-6 md:space-y-8 animate-in fade-in duration-700">
            
            <div>
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight">Profil Saya</h1>
                <p class="text-[11px] text-slate-500 font-medium tracking-wide mt-1">
                    Kelola informasi akun dan pengaturan keamanan Anda di sini.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-8 shadow-sm transition-all hover:shadow-md">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-8 shadow-sm transition-all hover:shadow-md">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="bg-white border border-slate-100 rounded-[1.5rem] p-5 md:p-8 shadow-sm transition-all hover:shadow-md hover:border-rose-100">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
            
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.space-y-6 > div {
    animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Penundaan animasi untuk setiap form agar muncul satu per satu (efek berurutan) */
.space-y-6 > div:nth-child(1) { animation-delay: 0s; }
.space-y-6 > div:nth-child(2) { animation-delay: 0.1s; }
.space-y-6 > div:nth-child(3) { animation-delay: 0.2s; }
</style>