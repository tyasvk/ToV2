<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const isPasswordVisible = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun CAT-V2" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 font-sans selection:bg-indigo-100 selection:text-indigo-700">
        
        <div class="w-full max-w-[1100px] bg-white rounded-[3rem] shadow-2xl shadow-indigo-100/30 overflow-hidden flex flex-col md:flex-row min-h-[700px] border border-gray-100">
            
            <div class="hidden md:flex md:w-1/2 bg-indigo-600 p-16 flex-col justify-between relative overflow-hidden">
                <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl mb-8">
                        <span class="text-indigo-600 font-black text-2xl tracking-tighter">TO</span>
                    </div>
                    <h1 class="text-4xl font-black text-white leading-tight tracking-tighter uppercase">
                        Langkah Awal<br>Menuju Sukses.
                    </h1>
                    <p class="text-indigo-100 mt-4 text-sm font-medium leading-relaxed max-w-xs uppercase tracking-widest opacity-80">
                        Bergabunglah dengan ribuan peserta lainnya dan mulai asah kemampuan Anda sekarang.
                    </p>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-4 rounded-3xl border border-white/10">
                        <div class="w-10 h-10 bg-amber-400 rounded-full flex items-center justify-center text-xl">✨</div>
                        <div>
                            <p class="text-[10px] font-black text-white uppercase tracking-widest">Akses Gratis</p>
                            <p class="text-[9px] text-indigo-100 font-bold uppercase">Simulasi CAT Standar Nasional</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 md:p-16 flex flex-col justify-center overflow-y-auto custom-scrollbar">
                <div class="mb-8 text-center md:text-left">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Buat Akun Baru</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.3em] mt-2">Isi data diri Anda dengan benar</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Nama Lengkap</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">👤</span>
                            <input v-model="form.name" type="text" required autofocus placeholder="Masukkan nama sesuai ijazah"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" />
                        </div>
                        <p v-if="form.errors.name" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">✉️</span>
                            <input v-model="form.email" type="email" required placeholder="email@contoh.com"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" />
                        </div>
                        <p v-if="form.errors.email" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Kata Sandi</label>
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Konfirmasi</label>
                            <input v-model="form.password_confirmation" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" />
                        </div>
                    </div>
                    
                    <div class="flex items-center ml-2 mt-1">
                        <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="text-[9px] font-black text-indigo-500 uppercase tracking-widest hover:text-gray-900 transition">
                            {{ isPasswordVisible ? 'Sembunyikan Kata Sandi' : 'Lihat Kata Sandi' }}
                        </button>
                    </div>

                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-gray-900 text-white rounded-[1.5rem] py-5 font-black text-[11px] uppercase tracking-[0.2em] shadow-xl hover:bg-indigo-600 disabled:opacity-50 active:scale-95 transition-all duration-300">
                            {{ form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center md:text-left">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Sudah punya akun? 
                        <Link :href="route('login')" class="text-indigo-600 hover:text-gray-900 underline underline-offset-4 decoration-2 ml-1">
                            Masuk di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 0px; }
form { animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>