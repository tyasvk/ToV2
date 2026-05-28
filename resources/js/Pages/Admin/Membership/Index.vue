<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    currentPrice: Number
});

const form = useForm({
    price: props.currentPrice
});

const submit = () => {
    form.post(route('admin.membership-setting.update'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Harga berhasil diperbarui!');
        }
    });
};
</script>

<template>
    <Head title="Pengaturan Membership" />

    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="relative z-10 flex flex-col gap-2 mb-8">
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-100 rounded-lg text-[9px] font-semibold text-amber-700 uppercase tracking-[0.2em] w-max">
                        Administrator
                    </span>
                    <h1 class="text-2xl font-medium text-slate-900 tracking-tight uppercase">
                        Pengaturan Membership
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Atur harga pendaftaran atau perpanjangan fitur Premium (Adidaya).
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6 relative z-10">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-widest mb-2">
                            Harga Membership (Rp)
                        </label>
                        <input 
                            v-model="form.price" 
                            type="number" 
                            min="0"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-medium outline-none"
                            placeholder="Contoh: 150000"
                        >
                        <p v-if="form.errors.price" class="text-xs text-rose-500 mt-2">{{ form.errors.price }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full flex items-center justify-center bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-xl font-semibold text-xs uppercase tracking-[0.2em] transition-all active:scale-95 disabled:opacity-50 shadow-sm"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Harga' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}
</style>