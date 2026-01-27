<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
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
    <Head title="Masuk ke CAT-V2" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 font-sans selection:bg-indigo-100 selection:text-indigo-700">
        
        <div class="w-full max-w-[1100px] bg-white rounded-[3rem] shadow-2xl shadow-indigo-100/50 overflow-hidden flex flex-col md:flex-row min-h-[650px] border border-gray-100">
            
            <div class="hidden md:flex md:w-1/2 bg-indigo-600 p-16 flex-col justify-between relative overflow-hidden">
                <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl mb-8">
                        <span class="text-indigo-600 font-black text-2xl tracking-tighter">TO</span>
                    </div>
                    <h1 class="text-4xl font-black text-white leading-tight tracking-tighter uppercase">
                        Siapkan Diri,<br>Taklukkan Ujian.
                    </h1>
                    <p class="text-indigo-100 mt-4 text-sm font-medium leading-relaxed max-w-xs uppercase tracking-widest opacity-80">
                        Platform Simulasi CAT CPNS & Kedinasan dengan standar sistem terbaru.
                    </p>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-4 rounded-3xl border border-white/10">
                        <div class="w-10 h-10 bg-green-400 rounded-full flex items-center justify-center text-xl">🚀</div>
                        <div>
                            <p class="text-[10px] font-black text-white uppercase tracking-widest">Swoole Engine</p>
                            <p class="text-[9px] text-indigo-100 font-bold uppercase">Real-time Performance Active</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 md:p-20 flex flex-col justify-center">
                <div class="mb-10 text-center md:text-left">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Selamat Datang</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.3em] mt-2">Silakan masuk ke akun Anda</p>
                </div>

                <div v-if="status" class="mb-6 bg-green-50 border border-green-100 text-green-600 p-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email</label>
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

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kata Sandi</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[9px] font-black text-indigo-500 uppercase tracking-widest hover:text-gray-900 transition">
                                Lupa Sandi?
                            </Link>
                        </div>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">🔒</span>
                            <input 
                                v-model="form.password" 
                                :type="isPasswordVisible ? 'text' : 'password'" 
                                required 
                                placeholder="••••••••"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-14 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                            />
                            <button 
                                type="button"
                                @click="isPasswordVisible = !isPasswordVisible"
                                class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-indigo-600 transition-colors"
                            >
                                {{ isPasswordVisible ? '👁️‍🗨️' : '👁️' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-[10px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center ml-2">
                        <label class="flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                            <span class="ml-3 text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-gray-600 transition">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-gray-900 text-white rounded-[1.5rem] py-5 font-black text-[11px] uppercase tracking-[0.2em] shadow-2xl shadow-gray-200 hover:bg-indigo-600 hover:shadow-indigo-100 disabled:opacity-50 active:scale-95 transition-all duration-300"
                        >
                            {{ form.processing ? 'Memproses...' : 'Masuk ke Dashboard' }}
                        </button>
                    </div>
                </form>

                <div class="mt-12 text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Belum punya akun? 
                        <Link :href="route('register')" class="text-indigo-600 hover:text-gray-900 underline underline-offset-4 decoration-2 ml-1">
                            Daftar Sekarang
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

form {
    animation: slideUp 0.6s ease-out;
}
</style>