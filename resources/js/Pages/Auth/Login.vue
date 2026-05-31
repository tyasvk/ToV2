<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isPasswordVisible = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk Akun - CPNS Nusantara" />

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 font-sans selection:bg-blue-100 selection:text-blue-900 overflow-hidden relative">
        
        <div class="fixed top-0 right-0 w-[300px] sm:w-[400px] h-[300px] sm:h-[400px] bg-blue-100/50 rounded-full blur-[80px] sm:blur-[100px] pointer-events-none -z-0"></div>
        <div class="fixed bottom-0 left-0 w-[250px] sm:w-[300px] h-[250px] sm:h-[300px] bg-slate-200/50 rounded-full blur-[80px] sm:blur-[100px] pointer-events-none -z-0"></div>

        <div class="w-full max-w-[900px] flex flex-col md:flex-row bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative z-10 fade-in mx-auto">
            
            <div class="hidden md:flex md:w-5/12 bg-slate-50 p-8 lg:p-10 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                
                <div class="relative z-10 flex justify-center mb-8 lg:mb-10">
                    <Link href="/" class="transition-transform duration-300 hover:scale-105">
                        <img src="/images/logo.png" alt="Logo" class="h-24 lg:h-32 w-auto drop-shadow-sm object-contain">
                    </Link>
                </div>

                <div class="relative z-10 text-center px-4">
                    <div class="w-8 h-1 bg-blue-600 rounded-full mb-5 mx-auto"></div>
                    <h2 class="text-lg lg:text-2xl font-light text-slate-600 leading-relaxed font-serif italic">
                        "Langkah kecil hari ini adalah jembatan menuju <span class="text-blue-600 font-bold not-italic">kesuksesan besar</span>."
                    </h2>
                    <p class="mt-5 lg:mt-6 text-[10px] lg:text-xs text-slate-400 font-medium tracking-wide uppercase">
                        Platform Persiapan CPNS
                    </p>
                </div>
            </div>

            <div class="w-full md:w-7/12 p-6 sm:p-10 md:p-10 lg:p-14 flex flex-col justify-center bg-white relative">
                
                <div class="md:hidden flex flex-col items-center justify-center gap-2 mb-6 sm:mb-8 mt-2">
                    <Link href="/">
                        <img src="/images/logo.png" alt="Logo" class="h-20 sm:h-24 w-auto object-contain transition-transform hover:scale-105">
                    </Link>
                    <span class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">CPNS <span class="text-blue-600">NUSANTARA</span></span>
                </div>

                <div class="mb-6 sm:mb-8 text-center md:text-left">
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1 sm:mb-1.5">Masuk ke Akun Anda</h3>
                    <p class="text-xs sm:text-sm text-slate-500">Silakan masukkan detail login Anda untuk melanjutkan.</p>
                </div>

                <div v-if="status" class="mb-6 p-3 bg-green-50 text-green-700 rounded-lg text-xs font-medium border border-green-100 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                        <input v-model="form.email" type="email" required autofocus placeholder="nama@email.com"
                            class="w-full bg-white border border-slate-300 rounded-lg py-2.5 sm:py-3 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none shadow-sm" />
                        <p v-if="form.errors.email" class="text-xs text-red-500 font-medium mt-1.5">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">Kata Sandi</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-white border border-slate-300 rounded-lg py-2.5 sm:py-3 px-3.5 pr-10 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none shadow-sm" />
                            <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors p-1">
                                <svg v-if="isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5 sm:w-5 sm:h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5 sm:w-5 sm:h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center pt-1">
                        <label class="flex items-center cursor-pointer select-none group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition cursor-pointer">
                            <span class="ml-2 text-xs sm:text-sm font-medium text-slate-600 group-hover:text-slate-800 transition">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <div class="pt-2 sm:pt-3">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-blue-600 text-white rounded-lg py-2.5 sm:py-3 font-medium text-sm shadow-sm hover:bg-blue-700 hover:shadow-md active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ form.processing ? 'Memproses...' : 'Masuk Dashboard' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 sm:mt-8 text-center border-t border-slate-100 pt-5 sm:pt-6">
                    <p class="text-xs sm:text-sm text-slate-500">
                        Belum punya akun pejuang NIP? 
                        <Link :href="route('register')" class="text-blue-600 font-semibold hover:text-blue-700 transition ml-1 inline-block">
                            Daftar Gratis
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 sm:mt-8 text-[10px] sm:text-xs text-slate-500 font-medium text-center z-20">
            &copy; 2026 CPNS Nusantara. Hak Cipta Dilindungi.
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.fade-in {
    animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.font-serif {
    font-family: Georgia, 'Times New Roman', Times, serif;
}
</style>