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

    <!-- min-h-screen agar bisa di-scroll alami JIKA layar HP kecil -->
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 sm:p-6 font-sans selection:bg-blue-100 selection:text-blue-900 relative">
        
        <!-- PERBAIKAN: Menggunakan "fixed" agar cahaya tidak menambah tinggi halaman -->
        <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-blue-50/50 rounded-full blur-[100px] pointer-events-none -translate-y-1/3 translate-x-1/3 z-0"></div>
        <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-slate-100/50 rounded-full blur-[100px] pointer-events-none translate-y-1/3 -translate-x-1/3 z-0"></div>

        <!-- Spacer -->
        <div class="flex-grow"></div>

        <!-- KARTU LOGIN UTAMA -->
        <div class="w-full max-w-[420px] bg-white rounded-3xl p-8 sm:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-slate-100 relative z-10 animate-fade-in my-8">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center mb-8">
                <Link href="/" class="mb-5 inline-block transition-transform duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-white border border-slate-100 shadow-sm rounded-2xl flex items-center justify-center p-2.5">
                        <img src="/images/logo.png" alt="Logo CPNS Nusantara" class="w-full h-full object-contain" />
                    </div>
                </Link>
                
                <h1 class="text-[24px] font-bold text-slate-900 tracking-tight leading-tight">
                    Selamat Datang
                </h1>
                <p class="text-[14px] text-slate-500 font-medium mt-1.5">
                    Masuk ke akun belajar Anda.
                </p>
            </div>

            <!-- Status Flash Message -->
            <div v-if="status" class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-[13px] font-medium border border-emerald-100/50 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ status }}</span>
            </div>

            <!-- Error Global -->
            <div v-if="form.errors.email || form.errors.password" class="mb-6 text-center bg-red-50 py-3 rounded-xl border border-red-100/50">
                <p class="text-[13px] text-red-600 font-medium">Kredensial yang Anda masukkan salah.</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Input Email -->
                <div>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        required 
                        autofocus 
                        placeholder="Alamat Email"
                        class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                    />
                </div>

                <!-- Input Password -->
                <div>
                    <div class="relative flex items-center">
                        <input 
                            v-model="form.password" 
                            :type="isPasswordVisible ? 'text' : 'password'" 
                            required 
                            placeholder="Kata Sandi"
                            class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 pl-4 pr-12 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                        />
                        <button 
                            type="button" 
                            @click="isPasswordVisible = !isPasswordVisible" 
                            class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition-colors p-1"
                            title="Tampilkan sandi"
                        >
                            <svg v-if="isPasswordVisible" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer select-none group">
                        <input 
                            type="checkbox" 
                            v-model="form.remember" 
                            class="w-4 h-4 rounded text-[#1D1D1F] border-slate-300 focus:ring-[#1D1D1F]/30 transition cursor-pointer"
                        />
                        <span class="ml-2.5 text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors">
                            Tetap masuk
                        </span>
                    </label>

                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-semibold text-[#1D1D1F] hover:text-slate-600 transition-all">
                        Lupa sandi?
                    </Link>
                </div>

                <!-- Action Button -->
                <div class="pt-4">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-[14px] py-3.5 font-bold text-[14px] shadow-[0_4px_14px_rgba(0,0,0,0.15)] active:scale-[0.98] transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Masuk Sekarang</span>
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center border-t border-slate-100 pt-6">
                <p class="text-sm text-slate-500 font-medium">
                    Belum memiliki akun?
                    <Link :href="route('register')" class="text-[#1D1D1F] font-bold hover:text-slate-600 transition-colors ml-1">
                        Daftar Gratis
                    </Link>
                </p>
            </div>

        </div>

        <div class="flex-grow"></div>

        <!-- Footer Copyright -->
        <div class="mb-4 text-xs text-slate-400 font-medium text-center z-10 w-full relative">
            &copy; 2026 CPNS Nusantara. Hak Cipta Dilindungi.
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: translateY(15px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0 30px white inset !important;
}
</style>