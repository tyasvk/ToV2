<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    announcement: String,
    flash: Object
});

const form = useForm({
    announcement: props.announcement || ''
});

const submit = () => {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem - Admin" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="flex flex-col gap-1">
                <h1 class="text-xl md:text-2xl font-medium text-slate-900 tracking-tight uppercase">Pengaturan Sistem</h1>
                <p class="text-xs sm:text-sm text-slate-500 font-normal">
                    Kelola pengaturan aplikasi, termasuk teks pengumuman untuk peserta.
                </p>
            </div>

            <div v-if="flash?.success" class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-xs font-medium shadow-sm">
                {{ flash.success }}
            </div>

            <div class="bg-white border border-slate-200 rounded-[1.5rem] p-5 md:p-8 shadow-sm">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="announcement" class="block text-[10px] sm:text-xs font-medium text-slate-500 uppercase tracking-widest mb-2.5">
                            Teks Pengumuman Dashboard
                        </label>
                        <textarea
                            id="announcement"
                            v-model="form.announcement"
                            rows="4"
                            placeholder="Ketikkan pengumuman yang ingin disampaikan ke peserta di sini..."
                            class="w-full bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white focus:ring-0 rounded-xl px-4 py-3 text-xs sm:text-sm font-normal text-slate-800 transition-colors resize-none"
                        ></textarea>
                        <p v-if="form.errors.announcement" class="text-[10px] text-rose-500 mt-1.5 font-medium">{{ form.errors.announcement }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 font-normal mt-2 leading-relaxed">
                            Teks ini akan muncul di halaman depan dashboard semua peserta. Kosongkan lalu simpan jika Anda tidak ingin menampilkan pengumuman apa pun.
                        </p>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" :disabled="form.processing" class="w-full sm:w-auto px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] sm:text-xs font-medium uppercase tracking-wider hover:bg-blue-600 transition-colors disabled:opacity-50 shadow-sm active:scale-95">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>