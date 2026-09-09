<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Kata Sandi - CPNS Nusantara" />

    <div class="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 sm:p-6 font-sans selection:bg-blue-100 selection:text-blue-900 relative overflow-x-hidden">
        
        <!-- Pendaran Latar Belakang Sangat Halus -->
        <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-blue-50/50 rounded-full blur-[100px] pointer-events-none -translate-y-1/3 translate-x-1/3 z-0"></div>
        <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-slate-100/50 rounded-full blur-[100px] pointer-events-none translate-y-1/3 -translate-x-1/3 z-0"></div>

        <!-- Spacer -->
        <div class="flex-grow"></div>

        <!-- KARTU RESET PASSWORD -->
        <div class="w-full max-w-[420px] bg-white rounded-3xl p-8 sm:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-slate-100 relative z-10 animate-fade-in my-8">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center mb-8">
                <Link href="/" class="mb-5 inline-block transition-transform duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-white border border-slate-100 shadow-sm rounded-2xl flex items-center justify-center p-2.5">
                        <img src="/images/logo.png" alt="Logo CPNS Nusantara" class="w-full h-full object-contain" />
                    </div>
                </Link>
                
                <h1 class="text-[24px] font-bold text-slate-900 tracking-tight leading-tight">
                    Buat Sandi Baru
                </h1>
                <p class="text-[14px] text-slate-500 font-medium mt-1.5 leading-relaxed">
                    Silakan masukkan kata sandi baru Anda.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Input Email (Readonly) -->
                <div>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        required 
                        readonly
                        class="w-full bg-slate-100 border border-slate-200 text-slate-500 cursor-not-allowed rounded-[14px] py-3.5 px-4 text-[14px] font-medium outline-none" 
                    />
                    <p v-if="form.errors.email" class="text-xs text-red-500 font-medium mt-1.5 ml-1">{{ form.errors.email }}</p>
                </div>

                <!-- Input Password Baru -->
                <div>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        required 
                        autofocus
                        placeholder="Kata Sandi Baru"
                        class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                    />
                    <p v-if="form.errors.password" class="text-xs text-red-500 font-medium mt-1.5 ml-1">{{ form.errors.password }}</p>
                </div>

                <!-- Input Konfirmasi Password -->
                <div>
                    <input 
                        v-model="form.password_confirmation" 
                        type="password" 
                        required 
                        placeholder="Konfirmasi Kata Sandi"
                        class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                    />
                    <p v-if="form.errors.password_confirmation" class="text-xs text-red-500 font-medium mt-1.5 ml-1">{{ form.errors.password_confirmation }}</p>
                </div>

                <!-- Action Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-[#1D1D1F] hover:bg-[#333336] text-white rounded-[14px] py-3.5 font-bold text-[14px] shadow-[0_4px_14px_rgba(0,0,0,0.15)] active:scale-[0.98] transition-all duration-200 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Simpan Kata Sandi Baru</span>
                    </button>
                </div>
            </form>

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