<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lupa Password - CPNS Nusantara" />

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
                    <h3 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1 sm:mb-1.5">Lupa Kata Sandi?</h3>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Jangan khawatir. Masukkan email yang terdaftar dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
                    </p>
                </div>

                <div v-if="status" class="mb-6 p-3 bg-green-50 text-green-700 rounded-lg text-xs font-medium border border-green-100 text-center flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 shrink-0">
                        <path fill-rule="evenodd" d="M2.25 12c0 5.385 4.365 9.75 9.75 9.75s9.75-4.365 9.75-9.75S17.385 2.25 12 2.25 2.25 6.615 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.5 2.5a.75.75 0 0 0 1.14-.094l3.74-5.24Z" clip-rule="evenodd" />
                    </svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                        <input v-model="form.email" type="email" required autofocus placeholder="nama@email.com"
                            class="w-full bg-white border border-slate-300 rounded-lg py-2.5 sm:py-3 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none shadow-sm" />
                        <p v-if="form.errors.email" class="text-xs text-red-500 font-medium mt-1.5">{{ form.errors.email }}</p>
                    </div>

                    <div class="pt-2 sm:pt-3">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-blue-600 text-white rounded-lg py-2.5 sm:py-3 font-medium text-sm shadow-sm hover:bg-blue-700 hover:shadow-md active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ form.processing ? 'Mengirim Tautan...' : 'Kirim Tautan Reset' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 sm:mt-8 text-center border-t border-slate-100 pt-5 sm:pt-6">
                    <p class="text-xs sm:text-sm text-slate-500">
                        Ingat kata sandi Anda? 
                        <Link :href="route('login')" class="text-blue-600 font-semibold hover:text-blue-700 transition ml-1 inline-block">
                            Masuk Sekarang
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