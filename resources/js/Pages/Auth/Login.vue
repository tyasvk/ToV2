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

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 font-sans selection:bg-amber-100">
        
        <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-amber-200/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-slate-200/40 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="w-full max-w-[1000px] flex flex-col md:flex-row bg-white rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(15,23,42,0.1)] overflow-hidden relative z-10 border border-white">
            
            <div class="hidden md:flex md:w-5/12 bg-slate-50 p-10 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.02] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="transition-all duration-1000 hover:scale-105 mb-8">
                        <img src="/images/logo.png" alt="Logo" class="h-32 lg:h-40 w-auto object-contain drop-shadow-md">
                    </div>

                    <div class="flex items-center gap-4 opacity-30 mb-6">
                        <div class="w-12 h-[1px] bg-slate-900"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                        <div class="w-12 h-[1px] bg-slate-900"></div>
                    </div>
                    
                    <div class="max-w-xs px-2">
                        <h2 class="text-lg lg:text-xl font-light italic text-slate-500 leading-relaxed font-serif">
                            "Langkah kecil hari ini adalah jembatan menuju <span class="text-slate-900 font-bold not-italic">Kesuksesan Besar</span>."
                        </h2>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center bg-white relative">
                
                <div class="md:hidden flex items-center justify-center gap-3 mb-8">
                    <img src="/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">CPNS <span class="text-amber-500">Nusantara</span></span>
                </div>

                <div class="mb-8 text-center md:text-left">
                    <h3 class="text-3xl font-black text-slate-900 tracking-tight italic leading-none mb-2">Sign In.</h3>
                    <p class="text-xs text-slate-500 font-medium tracking-wide">Selamat datang kembali di portal belajar Anda.</p>
                </div>

                <div v-if="status" class="mb-6 p-3 bg-amber-50 text-amber-700 rounded-xl text-[10px] font-black uppercase tracking-widest text-center border border-amber-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-1.5 group">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest ml-1 group-focus-within:text-amber-600 transition-colors">Alamat Email</label>
                        <input v-model="form.email" type="email" required autofocus placeholder="nama@email.com"
                            class="w-full bg-slate-50 border-slate-100 rounded-xl py-3.5 px-5 text-sm font-medium text-slate-800 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none border" />
                        <p v-if="form.errors.email" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-1.5 group">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kata Sandi</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[9px] font-black text-amber-600 hover:text-slate-900 transition">
                                Lupa sandi?
                            </Link>
                        </div>
                        <div class="relative">
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-3.5 px-5 pr-12 text-sm font-medium text-slate-800 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none border" />
                            <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-amber-500 transition-colors">
                                <svg v-if="isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center py-2 px-1">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-slate-200 text-amber-500 focus:ring-amber-500 transition cursor-pointer">
                            <span class="ml-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover:text-slate-700 transition">Ingat saya</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white rounded-xl py-4 font-bold text-xs uppercase tracking-[0.25em] shadow-xl shadow-slate-100 hover:bg-amber-600 hover:shadow-amber-100 active:scale-[0.98] transition-all duration-300 disabled:opacity-50">
                            {{ form.processing ? 'Proses...' : 'Masuk Dashboard' }}
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center border-t border-slate-50 pt-6">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Baru di sini? 
                        <Link :href="route('register')" class="text-amber-600 font-black hover:underline underline-offset-4 decoration-2 ml-1">
                            Buat Akun Baru
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8 text-[10px] text-slate-600 font-bold uppercase tracking-[0.4em] text-center z-20">
            Portal Premium &copy; 2026 CPNS Nusantara
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.w-full {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}
.font-serif {
    font-family: Georgia, 'Times New Roman', Times, serif;
}
</style>