<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
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
});

// --- LOGIC PASSWORD VISIBILITY ---
const isPasswordVisible = ref(false);

// --- LOGIC DROPDOWN INSTANSI ---
const searchAgency = ref('');
const isDropdownOpen = ref(false);

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

// Auto Set Tipe Instansi
watch(() => form.agency_name, (newVal) => {
    if (!newVal) return;
    if (newVal.startsWith('Pemerintah')) {
        form.instance_type = '2'; // Daerah
    } else {
        form.instance_type = '1'; // Pusat
    }
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun Premium" />

    <div class="min-h-screen bg-[#F1F5F9] flex items-center justify-center p-4 md:p-8 font-sans selection:bg-indigo-100">
        
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-200/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-100/30 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="w-full max-w-[1150px] flex flex-col md:flex-row bg-white rounded-[3.5rem] shadow-[0_40px_100px_-20px_rgba(15,23,42,0.15)] overflow-hidden relative z-10 min-h-[80vh] border border-white">
            
            <div class="hidden md:flex md:w-5/12 bg-[#F8FAFC] p-12 md:p-20 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center">
                    <div class="transition-all duration-1000 hover:scale-105 group relative mb-12">
                        <div class="absolute inset-0 bg-indigo-500/5 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <img src="/images/logo.png" alt="Logo" 
                             class="h-48 lg:h-56 w-auto object-contain relative z-10 drop-shadow-[0_25px_35px_rgba(0,0,0,0.08)]">
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

            <div class="w-full md:w-7/12 p-8 md:p-16 flex flex-col bg-white relative overflow-y-auto custom-scrollbar">
                
                <div class="md:hidden flex flex-col items-center mb-8">
                    <img src="/images/logo.png" alt="Logo" class="h-16 w-auto drop-shadow-md mb-2">
                    <p class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.4em]">CPNS Nusantara</p>
                </div>

                <div class="mb-10 text-center md:text-left">
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight mb-2 italic leading-none">Sign Up.</h3>
                    <p class="text-sm text-slate-400 font-medium tracking-wide">Mulai persiapan karir impian Anda di sini.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6 pb-6">
                    <div class="space-y-2 group">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Nama Lengkap</label>
                        <input v-model="form.name" type="text" required autofocus placeholder="Nama sesuai KTP/Ijazah"
                            class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                        <p v-if="form.errors.name" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2 group">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Alamat Email</label>
                        <input v-model="form.email" type="email" required placeholder="nama@domain.com"
                            class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                        <p v-if="form.errors.email" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Provinsi Domisili</label>
                            <select v-model="form.province_code" required
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih Provinsi</option>
                                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                            </select>
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Jenis Kelamin</label>
                            <select v-model="form.gender" required
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border appearance-none cursor-pointer">
                                <option value="" disabled selected>Pilih</option>
                                <option value="1">Laki-laki</option>
                                <option value="2">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2 relative group">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Instansi Tujuan</label>
                        <div class="relative">
                            <input v-model="searchAgency" type="text" required @input="onSearchInput" @focus="isDropdownOpen = true" @blur="closeDropdown"
                                placeholder="Cari Instansi (Kementerian/Pemkab...)"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" autocomplete="off" />
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 opacity-30">🔍</span>
                        </div>

                        <div v-if="isDropdownOpen" class="absolute z-50 mt-2 w-full bg-white shadow-2xl rounded-2xl py-2 text-sm border border-slate-100 max-h-56 overflow-y-auto custom-scrollbar">
                            <ul v-if="filteredAgencies.length > 0">
                                <li v-for="(agency, index) in filteredAgencies" :key="index" @mousedown.prevent="selectAgency(agency)"
                                    class="cursor-pointer py-3 px-6 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 font-bold transition-colors">
                                    {{ agency }}
                                </li>
                            </ul>
                            <div v-else class="py-4 px-6 text-slate-400 font-bold text-center text-xs italic">
                                Instansi tidak ditemukan
                            </div>
                        </div>
                        <p v-if="form.errors.agency_name" class="text-[10px] text-red-500 font-bold mt-1 ml-1">{{ form.errors.agency_name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Kata Sandi</label>
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                        </div>
                        <div class="space-y-2 group">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 group-focus-within:text-indigo-600 transition-colors">Konfirmasi</label>
                            <input v-model="form.password_confirmation" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold text-slate-800 placeholder:text-slate-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 transition-all outline-none border" />
                        </div>
                    </div>

                    <div class="flex justify-end px-1">
                        <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="text-[9px] font-black text-indigo-500 uppercase tracking-widest hover:text-indigo-700 transition flex items-center gap-1.5">
                            {{ isPasswordVisible ? 'Sembunyikan' : 'Lihat Kata Sandi' }}
                            <span class="text-xs">{{ isPasswordVisible ? '🙈' : '👁️' }}</span>
                        </button>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white rounded-2xl py-5 font-bold text-xs uppercase tracking-[0.3em] shadow-2xl shadow-slate-200 hover:bg-indigo-600 hover:shadow-indigo-100 active:scale-[0.98] transition-all duration-500 disabled:opacity-50">
                            {{ form.processing ? 'Memproses Akun...' : 'Daftar Sekarang' }}
                        </button>
                    </div>
                </form>

                <div class="mt-8 text-center pb-8 md:pb-0">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Sudah punya akun? 
                        <Link :href="route('login')" class="text-indigo-600 font-black hover:underline underline-offset-4 decoration-2 ml-1">
                            Masuk Disini
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 text-[9px] text-slate-400 font-bold uppercase tracking-[0.6em] hidden md:block">
            Premium Registration &copy; 2026
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

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>