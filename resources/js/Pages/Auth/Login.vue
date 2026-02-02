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
    <Head title="Login Premium - CPNS Nusantara" />

    <div class="min-h-screen bg-[#F1F5F9] flex items-center justify-center p-4 md:p-8 font-sans selection:bg-indigo-100">
        
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-200/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-100/30 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="w-full max-w-[1150px] flex flex-col md:flex-row bg-white rounded-[3.5rem] shadow-[0_40px_100px_-20px_rgba(15,23,42,0.15)] overflow-hidden relative z-10 min-h-[720px] border border-white">
            
            <div class="hidden md:flex md:w-1/2 bg-[#F8FAFC] p-12 md:p-20 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="transition-all duration-1000 hover:scale-105 group relative mb-12">
                        <div class="absolute inset-0 bg-indigo-500/5 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <img src="/images/logo.png" alt="Logo" 
                             class="h-48 lg:h-60 w-auto object-contain relative z-10 drop-shadow-[0_25px_35px_rgba(0,0,0,0.08)]">
                    </div>

                    <div class="flex items-center gap-6 opacity-30 mb-10">
                        <div class="w-16 h-[1px] bg-gradient-to-r from-transparent to-slate-900"></div>
                        <div class="w-2 h-2 rounded-full bg-slate-900"></div>
                        <div class="w-16 h-[1px] bg-gradient-to-l from-transparent to-slate-900"></div>
                    </div>
                    
                    <div class="max-w-sm px-4">
                        <h2 class="text-2xl md:text-3xl font-light italic text-slate-500 leading-relaxed font-serif">
                            "Langkah kecil hari ini adalah jembatan menuju <span class="text-indigo-600 font-bold not-italic">Kesuksesan Besar</span> esok hari."
                        </h2>
                        <div class="w-12 h-1 bg-indigo-100 mx-auto mt-10 rounded-full"></div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 md:p-24 flex flex-col justify-center bg-white relative">
                
                <div class="md:hidden flex flex-col items-center mb-12">
                    <img src="/images/logo.png" alt="Logo" class="h-20 w-auto drop-shadow-md mb-4">
                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.4em]">CPNS Nusantara</p>
                </div>

                <div class="mb-12 text-center md:text-left">
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-3 italic leading-none">Sign In.</h3>
                    <p class="text-sm text-slate-400 font-medium tracking-wide">Persiapkan dirimu untuk masa depan yang lebih baik.</p>
                </div>

                <div v-if="status" class="mb-8 p-4 bg-indigo-50 text-indigo-700 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center border border-indigo-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2 group">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Alamat Email</label>
                        <input v-model="form.email" type="email" required autofocus placeholder="nama@domain.com"
                            class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4.5 px-6 text-sm font-bold text-slate-800 placeholder:text-gray-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                        <p v-if="form.errors.email" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2 group">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kata Sandi</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[9px] font-black text-indigo-600 hover:text-slate-900 uppercase tracking-widest transition">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4.5 px-6 pr-14 text-sm font-bold text-slate-800 placeholder:text-gray-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                            <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 transition-colors">
                                <svg v-if="isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-[10px] text-red-500 font-bold mt-1 ml-1 tracking-tight">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center px-1">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded-md border-slate-200 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                            <span class="ml-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition">Ingat perangkat ini</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white rounded-2xl py-5 font-bold text-xs uppercase tracking-[0.3em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:shadow-indigo-100 active:scale-[0.98] transition-all duration-500 disabled:opacity-50">
                            {{ form.processing ? 'Memverifikasi...' : 'Masuk Dashboard' }}
                        </button>
                    </div>
                </form>

                <div class="mt-16 text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Belum punya akun? 
                        <Link :href="route('register')" class="text-indigo-600 font-black hover:underline underline-offset-4 decoration-2 ml-1">
                            Daftar Sekarang
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 text-[9px] text-slate-400 font-bold uppercase tracking-[0.6em]">
            Portal Premium &copy; 2026
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
.w-full {
    animation: fadeIn 1s cubic-bezier(0.16, 1, 0.3, 1);
}
.font-serif {
    font-family: Georgia, 'Times New Roman', Times, serif;
}
/* Utility untuk padding-top/bottom 18px */
.py-4\.5 {
    padding-top: 1.125rem;
    padding-bottom: 1.125rem;
}
</style>