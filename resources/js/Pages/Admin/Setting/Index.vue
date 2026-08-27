<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    announcement: String,
    manual_total_users: Number, // Tambahan prop untuk total user
    flash: Object
});

const form = useForm({
    announcement: props.announcement || '',
    manual_total_users: props.manual_total_users || 0, // Inisialisasi dari prop
});

const submit = () => {
    // Pastikan methodnya sesuai dengan route (put/post) di web.php Anda.
    // Di kode lama Anda menggunakan put, jadi kita pertahankan put.
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem - Admin" />

    <AuthenticatedLayout>
        <!-- Background Abu-abu Sistem iCloud -->
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-20 md:pb-28 font-sans animate-in fade-in duration-500">
            
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 md:pt-10 space-y-6">
                
                <!-- HEADER -->
                <div class="flex flex-col gap-1.5 px-2">
                    <h1 class="text-[24px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight">Pengaturan Sistem</h1>
                    <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium">
                        Kelola pengaturan aplikasi, pengumuman, dan tampilan dashboard.
                    </p>
                </div>

                <!-- ALERT SUCCESS -->
                <div v-if="flash?.success" class="px-5 py-3.5 bg-[#E5F5EA] border border-[#34C759]/20 rounded-[16px] flex items-center gap-3 shadow-sm transition-all">
                    <div class="w-7 h-7 rounded-full bg-[#34C759] flex items-center justify-center text-white shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    </div>
                    <p class="text-[13px] font-bold text-[#1D1D1F]">{{ flash.success }}</p>
                </div>

                <!-- MAIN CARD -->
                <div class="bg-white border border-black/5 rounded-[24px] md:rounded-[32px] p-6 md:p-10 shadow-[0_8px_30px_rgba(0,0,0,0.03)]">
                    <form @submit.prevent="submit" class="space-y-8">
                        
                        <!-- Input Pengumuman -->
                        <div>
                            <label for="announcement" class="block text-[12px] sm:text-[13px] font-bold text-[#1D1D1F] mb-2">
                                Teks Pengumuman Dashboard
                            </label>
                            <textarea
                                id="announcement"
                                v-model="form.announcement"
                                rows="4"
                                placeholder="Ketikkan pengumuman yang ingin disampaikan ke peserta di sini..."
                                class="w-full bg-[#F5F5F7] border border-transparent focus:border-[#007AFF]/40 focus:bg-white focus:ring-4 focus:ring-[#007AFF]/10 rounded-[16px] px-4 py-3.5 text-[14px] font-medium text-[#1D1D1F] placeholder:text-[#86868B] transition-all resize-none shadow-inner"
                            ></textarea>
                            <p v-if="form.errors.announcement" class="text-[12px] text-[#FF3B30] mt-2 font-bold">{{ form.errors.announcement }}</p>
                            <p class="text-[11px] sm:text-[12px] text-[#86868B] font-medium mt-2 leading-relaxed">
                                Teks ini akan muncul di halaman depan dashboard semua peserta. Kosongkan lalu simpan jika Anda tidak ingin menampilkan pengumuman apa pun.
                            </p>
                        </div>

                        <hr class="border-black/5">

                        <!-- Input Manipulasi Total User -->
                        <div>
                            <label for="manual_total_users" class="block text-[12px] sm:text-[13px] font-bold text-[#1D1D1F] mb-2">
                                Manipulasi Total User (Tampilan Dashboard)
                            </label>
                            <input
                                id="manual_total_users"
                                v-model="form.manual_total_users"
                                type="number"
                                min="0"
                                placeholder="Contoh: 5000"
                                class="w-full sm:w-1/2 bg-[#F5F5F7] border border-transparent focus:border-[#007AFF]/40 focus:bg-white focus:ring-4 focus:ring-[#007AFF]/10 rounded-[16px] px-4 py-3.5 text-[14px] font-bold text-[#1D1D1F] placeholder:text-[#86868B] transition-all shadow-inner"
                            />
                            <p v-if="form.errors.manual_total_users" class="text-[12px] text-[#FF3B30] mt-2 font-bold">{{ form.errors.manual_total_users }}</p>
                            <p class="text-[11px] sm:text-[12px] text-[#86868B] font-medium mt-2 leading-relaxed">
                                Isi dengan angka lebih dari <span class="font-bold">0</span> untuk mengganti angka asli. Biarkan <span class="font-bold">0</span> untuk menampilkan total user asli secara otomatis dari database.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="w-full sm:w-auto px-8 py-3.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full text-[13px] font-bold transition-all disabled:opacity-50 shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-95 flex items-center justify-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                            </button>
                        </div>

                    </form>
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