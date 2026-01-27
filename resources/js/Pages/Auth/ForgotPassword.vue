<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Reset Kata Sandi" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 font-sans">
        
        <div class="w-full max-w-[500px] bg-white rounded-[3rem] shadow-2xl shadow-indigo-100/50 p-10 md:p-14 border border-gray-100">
            
            <div class="flex justify-center mb-10">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-100">
                    <span class="text-white font-black text-2xl tracking-tighter">TO</span>
                </div>
            </div>

            <div class="text-center mb-8">
                <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Pulihkan Sandi</h2>
                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-[0.2em] mt-3 leading-relaxed">
                    Masukkan email Anda untuk menerima tautan pengaturan ulang kata sandi.
                </p>
            </div>

            <div v-if="status" class="mb-8 bg-green-50 border border-green-100 text-green-600 p-5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email Terdaftar</label>
                    <div class="relative group">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">✉️</span>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            required 
                            autofocus
                            placeholder="email@contoh.com"
                            class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                        />
                    </div>
                    <p v-if="form.errors.email" class="text-[10px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.email }}</p>
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-gray-900 text-white rounded-[1.5rem] py-5 font-black text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 disabled:opacity-50 active:scale-95 transition-all duration-300"
                    >
                        {{ form.processing ? 'Mengirim...' : 'Kirim Link Reset' }}
                    </button>
                </div>
            </form>

            <div class="mt-10 text-center">
                <Link :href="route('login')" class="text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition flex items-center justify-center gap-2">
                    <span>←</span> Kembali ke Halaman Masuk
                </Link>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Animasi halus agar card muncul perlahan */
.min-h-screen {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>