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
    referral_code: '', // Kolom opsional untuk referral
});

const isPasswordVisible = ref(false);
const searchAgency = ref('');
const isDropdownOpen = ref(false);

// Memaksa form untuk dikosongkan setiap kali halaman di-reload/dimuat, kecuali kode referral dari URL
onMounted(() => {
    form.reset();
    searchAgency.value = '';
    isPasswordVisible.value = false;

    // OTOMATIS TANGKAP KODE DARI URL (?ref=KODE)
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

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 sm:p-6 font-sans selection:bg-blue-100 selection:text-blue-900">
        
        <div class="fixed top-0 right-0 w-[400px] h-[400px] bg-blue-100/50 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="fixed bottom-0 left-0 w-[300px] h-[300px] bg-slate-200/50 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="w-full max-w-[900px] flex flex-col md:flex-row bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative z-10 fade-in">
            
            <div class="hidden md:flex md:w-5/12 bg-slate-50 p-10 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                
                <div class="relative z-10 flex justify-center mb-10">
                    <Link href="/" class="transition-transform duration-300 hover:scale-105">
                        <img src="/images/logo.png" alt="Logo" class="h-32 w-auto drop-shadow-sm object-contain">
                    </Link>
                </div>

                <div class="relative z-10 text-center">
                    <div class="w-8 h-1 bg-blue-600 rounded-full mb-6 mx-auto"></div>
                    <h2 class="text-sm lg:text-base font-light text-slate-500 leading-relaxed font-serif italic px-2">
                        "Mulai perjalanan suksesmu di <span class="text-blue-600 font-medium not-italic">CPNS Nusantara</span>."
                    </h2>
                    <p class="mt-6 text-[10px] text-slate-400 font-medium tracking-wide uppercase">
                        Platform Persiapan CPNS
                    </p>
                </div>
            </div>

            <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center bg-white relative">
                
                <div class="md:hidden flex flex-col items-center justify-center gap-2 mb-8">
                    <img src="/images/logo.png" alt="Logo" class="h-32 w-auto object-contain">
                    <span class="text-sm font-medium text-slate-900 tracking-tight">CPNS <span class="text-blue-600">NUSANTARA</span></span>
                </div>

                <div class="mb-8 text-center md:text-left">
                    <h3 class="text-2xl font-medium text-slate-900 tracking-tight mb-1.5">Daftar Akun Baru</h3>
                    <p class="text-sm text-slate-500 font-normal">Lengkapi formulir pendaftaran di bawah ini untuk memulai.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4" autocomplete="off">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" required placeholder="Sesuai KTP" autocomplete="off"
                                class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" />
                            <div v-if="form.errors.name" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Alamat Email</label>
                            <input v-model="form.email" type="email" required placeholder="nama@email.com" autocomplete="off"
                                class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" />
                            <div v-if="form.errors.email" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Provinsi</label>
                            <select v-model="form.province_code" required class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none cursor-pointer">
                                <option value="" disabled selected>Pilih Provinsi</option>
                                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                            </select>
                            <div v-if="form.errors.province_code" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.province_code }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Gender</label>
                            <select v-model="form.gender" required class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none cursor-pointer">
                                <option value="" disabled selected>Pilih</option>
                                <option value="1">Laki-laki</option>
                                <option value="2">Perempuan</option>
                            </select>
                            <div v-if="form.errors.gender" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.gender }}</div>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Instansi Tujuan</label>
                        <input v-model="searchAgency" type="text" required @input="onSearchInput" @focus="isDropdownOpen = true" @blur="closeDropdown"
                            placeholder="Cari Instansi..." autocomplete="off"
                            class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" />
                        
                        <div v-if="isDropdownOpen" class="absolute z-50 mt-1 w-full bg-white shadow-xl rounded-lg py-1 text-sm border border-slate-100 max-h-48 overflow-y-auto custom-scrollbar">
                            <ul v-if="filteredAgencies.length > 0">
                                <li v-for="(agency, index) in filteredAgencies" :key="index" @mousedown.prevent="selectAgency(agency)"
                                    class="cursor-pointer py-2 px-4 hover:bg-blue-50 hover:text-blue-700 text-slate-700 transition-colors">
                                    {{ agency }}
                                </li>
                            </ul>
                            <div v-else class="py-3 px-4 text-slate-500 italic text-center text-xs">Instansi tidak ditemukan</div>
                        </div>
                        <div v-if="form.errors.agency_name" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.agency_name }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Kata Sandi</label>
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••" autocomplete="new-password"
                                class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" />
                            <div v-if="form.errors.password" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.password }}</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Konfirmasi Sandi</label>
                            <input v-model="form.password_confirmation" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••" autocomplete="new-password"
                                class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none" />
                        </div>
                    </div>

                    <div class="flex items-center pt-1">
                        <label class="flex items-center cursor-pointer select-none group">
                            <input type="checkbox" v-model="isPasswordVisible" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition cursor-pointer">
                            <span class="ml-2 text-xs font-medium text-slate-600 group-hover:text-slate-800 transition">Tampilkan kata sandi</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Kode Referral (Opsional)</label>
                        <input v-model="form.referral_code" type="text" placeholder="Masukkan kode referral jika ada" autocomplete="off"
                            class="w-full bg-slate-100 border border-transparent rounded-lg py-2.5 px-3.5 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none uppercase placeholder:normal-case" />
                        <div v-if="form.errors.referral_code" class="text-[10px] text-rose-500 mt-1 font-medium">{{ form.errors.referral_code }}</div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-blue-600 text-white rounded-lg py-2.5 font-medium text-sm shadow-sm hover:bg-blue-700 hover:shadow-md active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center border-t border-slate-100 pt-6">
                    <p class="text-xs text-slate-500 font-normal">
                        Sudah punya akun pejuang NIP? 
                        <Link :href="route('login')" class="text-blue-600 font-medium hover:text-blue-700 transition ml-1">
                            Masuk di Sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8 text-xs text-slate-500 font-medium text-center z-20">
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
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 8px;
}
</style>