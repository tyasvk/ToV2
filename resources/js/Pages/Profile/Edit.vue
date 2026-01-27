<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});
</script>

<template>
    <Head title="Profil & Ranking" />

    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-2 animate-in fade-in slide-in-from-top-4 duration-700">
                <h2 class="font-black text-4xl text-gray-900 tracking-tighter uppercase leading-none">
                    Pengaturan Akun
                </h2>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em]">
                    Lengkapi data wilayah untuk sinkronisasi ranking nasional
                </p>
            </div>
        </template>

        <div class="max-w-5xl space-y-10 pb-24">
            
            <div class="bg-white rounded-[3.5rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-2 bg-indigo-600"></div>
                
                <div class="p-10 md:p-16 relative z-10">
                    <div class="flex flex-col md:flex-row gap-12">
                        <div class="md:w-1/3 space-y-4">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl">🌍</div>
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter leading-tight">Data Wilayah & Identitas</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                                Pilih provinsi domisili anda dengan benar. Data ini akan digunakan untuk menentukan peringkat anda dalam skala regional dan nasional.
                            </p>
                        </div>

                        <div class="md:w-2/3 bg-gray-50/50 p-8 md:p-10 rounded-[2.5rem] border border-gray-100">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3.5rem] border border-gray-100 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-2 bg-amber-400"></div>
                
                <div class="p-10 md:p-16 relative z-10">
                    <div class="flex flex-col md:flex-row gap-12">
                        <div class="md:w-1/3 space-y-4">
                            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-2xl">🔑</div>
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter leading-tight">Keamanan Akses</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                                Perbarui kata sandi secara berkala untuk memastikan akun dan hasil ujian anda tetap terlindungi.
                            </p>
                        </div>

                        <div class="md:w-2/3 bg-gray-50/50 p-8 md:p-10 rounded-[2.5rem] border border-gray-100">
                            <UpdatePasswordForm />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>