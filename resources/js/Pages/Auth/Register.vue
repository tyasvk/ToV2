<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { provinces, agencies } from '@/Data/agencies'; 

const form = useForm({
    name: '',
    email: '',
    province_code: '',
    agency_name: '',
    instance_type: '', 
    gender: '',
    password: '',
    password_confirmation: '',
    referral_code: '',
    referred_by: new URLSearchParams(window.location.search).get('ref') || '',
});

const isPasswordVisible = ref(false);
const searchAgency = ref('');
const isDropdownOpen = ref(false);

onMounted(() => {
    form.reset();
    searchAgency.value = '';
    isPasswordVisible.value = false;

    // Otomatis tangkap kode referral dari URL (?ref=KODE)
    const urlParams = new URLSearchParams(window.location.search);
    const refCode = urlParams.get('ref');
    if (refCode) {
        form.referral_code = refCode.toUpperCase();
    }
});

const filteredAgencies = computed(() => {
    const query = searchAgency.value.toLowerCase().trim();
    if (!query) return agencies; 
    let keyword = query;
    if (keyword.includes('pemkab')) keyword = keyword.replace('pemkab', 'kab.');
    if (keyword.includes('pemkot')) keyword = keyword.replace('pemkot', 'kota');
    if (keyword.includes('pemprov')) keyword = keyword.replace('pemprov', 'provinsi');
    return agencies.filter(item => item.toLowerCase().includes(keyword));
});

const selectAgency = (item) => {
    form.agency_name = item;
    searchAgency.value = item;
    isDropdownOpen.value = false;
};

const onSearchInput = () => {
    form.agency_name = searchAgency.value;
    isDropdownOpen.value = true;
};

const closeDropdown = () => {
    setTimeout(() => isDropdownOpen.value = false, 200);
};

watch(() => form.agency_name, (newVal) => {
    if (!newVal) return;
    form.instance_type = newVal.startsWith('Pemerintah') ? '2' : '1';
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun - CPNS Nusantara" />

    <!-- Latar Belakang Clean & Neutral -->
    <div class="min-h-screen bg-[#F8FAFC] flex flex-col items-center justify-center p-4 sm:p-6 font-sans selection:bg-blue-100 selection:text-blue-900 relative overflow-x-hidden">
        
        <!-- Pendaran Latar Belakang Sangat Halus -->
        <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-blue-50/50 rounded-full blur-[100px] pointer-events-none -translate-y-1/3 translate-x-1/3 z-0"></div>
        <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-slate-100/50 rounded-full blur-[100px] pointer-events-none translate-y-1/3 -translate-x-1/3 z-0"></div>

        <!-- Spacer -->
        <div class="flex-grow"></div>

        <!-- KARTU REGISTER (Modern SaaS Style) -->
        <div class="w-full max-w-[640px] bg-white rounded-3xl p-6 sm:p-10 md:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.02)] border border-slate-100 relative z-10 animate-fade-in my-8">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center mb-8">
                <!-- Logo Orisinal Anda -->
                <Link href="/" class="mb-5 inline-block transition-transform duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-white border border-slate-100 shadow-sm rounded-2xl flex items-center justify-center p-2.5">
                        <img src="/images/logo.png" alt="Logo CPNS Nusantara" class="w-full h-full object-contain" />
                    </div>
                </Link>
                
                <h1 class="text-[24px] sm:text-[28px] font-bold text-slate-900 tracking-tight leading-tight">
                    Buat Akun Baru
                </h1>
                <p class="text-[14px] text-slate-500 font-medium mt-1.5">
                    Mulai persiapan seleksi ASN bersama CPNS Nusantara
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4" autocomplete="off">

                <!-- Baris 1: Nama & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Nama Lengkap</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required 
                            placeholder="Sesuai KTP" 
                            autocomplete="off"
                            class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                        />
                        <p v-if="form.errors.name" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Alamat Email</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            required 
                            placeholder="nama@email.com" 
                            autocomplete="off"
                            class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                        />
                        <p v-if="form.errors.email" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.email }}</p>
                    </div>
                </div>

                <!-- Baris 2: Provinsi & Gender -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Provinsi</label>
                        <select 
                            v-model="form.province_code" 
                            required 
                            class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium transition-all outline-none cursor-pointer appearance-none"
                            :class="form.province_code ? 'text-slate-900' : 'text-slate-400'"
                        >
                            <option value="" disabled selected>Pilih Provinsi</option>
                            <option v-for="prov in provinces" :key="prov.code" :value="prov.code" class="text-slate-900">{{ prov.name }}</option>
                        </select>
                        <p v-if="form.errors.province_code" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.province_code }}</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Jenis Kelamin</label>
                        <select 
                            v-model="form.gender" 
                            required 
                            class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium transition-all outline-none cursor-pointer appearance-none"
                            :class="form.gender ? 'text-slate-900' : 'text-slate-400'"
                        >
                            <option value="" disabled selected>Pilih Gender</option>
                            <option value="1" class="text-slate-900">Laki-laki</option>
                            <option value="2" class="text-slate-900">Perempuan</option>
                        </select>
                        <p v-if="form.errors.gender" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.gender }}</p>
                    </div>
                </div>

                <!-- Baris 3: Instansi Tujuan (Autocomplete Dropdown) -->
                <div class="relative z-20">
                    <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Instansi Tujuan</label>
                    <input 
                        v-model="searchAgency" 
                        type="text" 
                        required 
                        @input="onSearchInput" 
                        @focus="isDropdownOpen = true" 
                        @blur="closeDropdown"
                        placeholder="Cari Instansi Target..." 
                        autocomplete="off"
                        class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                    />

                    <!-- Popover Dropdown Hasil Pencarian -->
                    <div 
                        v-if="isDropdownOpen" 
                        class="absolute mt-1.5 w-full bg-white/95 backdrop-blur-xl shadow-xl rounded-2xl py-1.5 border border-slate-100 max-h-52 overflow-y-auto custom-scrollbar"
                    >
                        <ul v-if="filteredAgencies.length > 0">
                            <li 
                                v-for="(agency, index) in filteredAgencies" 
                                :key="index" 
                                @mousedown.prevent="selectAgency(agency)"
                                class="cursor-pointer py-2.5 px-4 hover:bg-slate-50 hover:text-slate-900 text-[13px] font-medium text-slate-700 transition-colors"
                            >
                                {{ agency }}
                            </li>
                        </ul>
                        <div v-else class="py-3 px-4 text-slate-500 italic text-center text-[12px]">Instansi tidak ditemukan</div>
                    </div>
                    <p v-if="form.errors.agency_name" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.agency_name }}</p>
                </div>

                <!-- Baris 4: Kata Sandi & Konfirmasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Kata Sandi</label>
                        <div class="relative flex items-center">
                            <input 
                                v-model="form.password" 
                                :type="isPasswordVisible ? 'text' : 'password'" 
                                required 
                                placeholder="••••••••" 
                                autocomplete="new-password"
                                class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 pl-4 pr-12 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                            />
                            <button 
                                type="button" 
                                @click="isPasswordVisible = !isPasswordVisible" 
                                class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition-colors p-1"
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
                        <p v-if="form.errors.password" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Konfirmasi Sandi</label>
                        <div class="relative flex items-center">
                            <input 
                                v-model="form.password_confirmation" 
                                :type="isPasswordVisible ? 'text' : 'password'" 
                                required 
                                placeholder="••••••••" 
                                autocomplete="new-password"
                                class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 pl-4 pr-12 text-[14px] font-medium text-slate-900 placeholder:text-slate-400 transition-all outline-none" 
                            />
                        </div>
                    </div>
                </div>

                <!-- Baris 5: Kode Referral (Opsional) -->
                <div>
                    <label class="block text-[12px] font-semibold text-slate-700 mb-1.5 px-1">Kode Referral (Opsional)</label>
                    <input 
                        v-model="form.referral_code" 
                        type="text" 
                        placeholder="Masukkan kode referral jika ada" 
                        autocomplete="off"
                        class="w-full bg-slate-50/50 border border-slate-200 focus:border-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-black/5 rounded-[14px] py-3.5 px-4 text-[14px] font-bold text-slate-900 placeholder:font-medium placeholder:text-slate-400 transition-all outline-none uppercase placeholder:normal-case" 
                    />
                    <p v-if="form.errors.referral_code" class="text-[12px] text-red-500 font-medium mt-1 px-1">{{ form.errors.referral_code }}</p>
                </div>

                <!-- Action Button (Hitam Style) -->
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
                        <span v-else class="flex items-center gap-2">
                            Daftar Sekarang
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7M21 12H3" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Footer Link -->
            <div class="mt-8 text-center border-t border-slate-100 pt-6">
                <p class="text-[13px] text-slate-500 font-medium">
                    Sudah memiliki akun?
                    <Link :href="route('login')" class="text-[#1D1D1F] font-bold hover:text-slate-600 transition-colors ml-1">
                        Masuk di Sini
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

input:-webkit-autofill,
select:-webkit-autofill {
    -webkit-box-shadow: 0 0 0 30px white inset !important;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
</style>