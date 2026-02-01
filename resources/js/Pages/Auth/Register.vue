<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { provinces, agencies } from '@/Data/agencies'; // Pastikan file data ini ada

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

// --- LOGIC DROPDOWN INSTANSI (SAMA SEPERTI SEBELUMNYA) ---
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
    <Head title="Daftar Akun Baru" />

    <div class="min-h-screen bg-[#F8FAFC] flex items-center justify-center p-6 font-sans selection:bg-indigo-100 selection:text-indigo-700">
        
        <div class="w-full max-w-[1200px] bg-white rounded-[3rem] shadow-2xl shadow-indigo-100/50 overflow-hidden flex flex-col md:flex-row h-[85vh] min-h-[700px] border border-gray-100">
            
            <div class="hidden md:flex md:w-5/12 bg-indigo-600 p-16 flex-col justify-between relative overflow-hidden">
                <div class="absolute top-[-10%] left-[-10%] w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl"></div>
                <div class="absolute top-[40%] left-[20%] w-40 h-40 bg-purple-500/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-xl mb-8">
                        <span class="text-indigo-600 font-black text-2xl tracking-tighter">TO</span>
                    </div>
                    <h1 class="text-4xl font-black text-white leading-tight tracking-tighter uppercase">
                        Bergabung<br>Sekarang.
                    </h1>
                    <p class="text-indigo-100 mt-6 text-sm font-medium leading-relaxed max-w-xs uppercase tracking-widest opacity-80">
                        Satu akun untuk akses ribuan soal latihan CPNS & Kedinasan dengan sistem CAT terbaru.
                    </p>
                </div>

                <div class="relative z-10">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md p-4 rounded-3xl border border-white/10">
                            <div class="w-10 h-10 bg-amber-400 rounded-full flex items-center justify-center text-xl">🏆</div>
                            <div>
                                <p class="text-[10px] font-black text-white uppercase tracking-widest">Ranking Nasional</p>
                                <p class="text-[9px] text-indigo-100 font-bold uppercase">Bandingkan skor dengan pesaing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col overflow-y-auto custom-scrollbar relative">
                <div class="mb-8">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tighter uppercase">Buat Akun</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-[0.3em] mt-2">Lengkapi data diri Anda</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5 pb-4">
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Nama Lengkap</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">👤</span>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                required 
                                autofocus
                                placeholder="Nama sesuai KTP/Ijazah"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">✉️</span>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                required 
                                placeholder="email@contoh.com"
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Provinsi Domisili</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">🗺️</span>
                                <select 
                                    v-model="form.province_code" 
                                    required
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-8 text-sm font-bold text-gray-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none appearance-none cursor-pointer"
                                >
                                    <option value="" disabled selected>Pilih Provinsi</option>
                                    <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                                </select>
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs">▼</span>
                            </div>
                            <p v-if="form.errors.province_code" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.province_code }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Jenis Kelamin</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">⚧️</span>
                                <select 
                                    v-model="form.gender" 
                                    required
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-8 text-sm font-bold text-gray-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none appearance-none cursor-pointer"
                                >
                                    <option value="" disabled selected>Pilih</option>
                                    <option value="1">Laki-laki</option>
                                    <option value="2">Perempuan</option>
                                </select>
                                <span class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs">▼</span>
                            </div>
                            <p v-if="form.errors.gender" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.gender }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 relative">
                        <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Instansi Tujuan</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">🏢</span>
                            <input 
                                v-model="searchAgency" 
                                type="text" 
                                required 
                                @input="onSearchInput"
                                @focus="isDropdownOpen = true"
                                @blur="closeDropdown"
                                placeholder="Ketik 'Kementerian', 'Pemkab', atau nama daerah..."
                                class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                                autocomplete="off"
                            />
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none">🔍</span>
                        </div>

                        <div v-if="isDropdownOpen" class="absolute z-50 mt-2 w-full bg-white shadow-2xl shadow-indigo-100/50 max-h-60 rounded-2xl py-2 text-sm border border-gray-100 overflow-auto custom-scrollbar">
                            <ul v-if="filteredAgencies.length > 0">
                                <li 
                                    v-for="(agency, index) in filteredAgencies" 
                                    :key="index"
                                    @mousedown.prevent="selectAgency(agency)"
                                    class="cursor-pointer select-none relative py-3 px-6 hover:bg-indigo-50 hover:text-indigo-700 text-gray-600 font-bold border-b border-gray-50 last:border-0 transition-colors"
                                >
                                    {{ agency }}
                                </li>
                            </ul>
                            <div v-else class="py-4 px-6 text-gray-400 font-bold text-center text-xs uppercase tracking-wider">
                                Instansi tidak ditemukan
                            </div>
                        </div>
                        <p v-if="form.errors.agency_name" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.agency_name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Kata Sandi</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">🔒</span>
                                <input 
                                    v-model="form.password" 
                                    :type="isPasswordVisible ? 'text' : 'password'" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                                />
                            </div>
                            <p v-if="form.errors.password" class="text-[9px] text-red-500 font-black uppercase ml-2 mt-1 tracking-wider">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Konfirmasi</label>
                            <div class="relative group">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-600 transition-colors">🔒</span>
                                <input 
                                    v-model="form.password_confirmation" 
                                    :type="isPasswordVisible ? 'text' : 'password'" 
                                    required 
                                    placeholder="••••••••"
                                    class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 pl-14 pr-6 text-sm font-bold placeholder:text-gray-300 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pr-2">
                        <button 
                            type="button" 
                            @click="isPasswordVisible = !isPasswordVisible" 
                            class="text-[9px] font-black text-indigo-500 uppercase tracking-widest hover:text-indigo-700 transition flex items-center gap-1"
                        >
                            {{ isPasswordVisible ? 'Sembunyikan' : 'Lihat Sandi' }}
                            <span>{{ isPasswordVisible ? '🙈' : '👁️' }}</span>
                        </button>
                    </div>

                    <div class="pt-6">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-gray-900 text-white rounded-[1.5rem] py-5 font-black text-[11px] uppercase tracking-[0.2em] shadow-2xl shadow-gray-200 hover:bg-indigo-600 hover:shadow-indigo-100 disabled:opacity-50 active:scale-95 transition-all duration-300"
                        >
                            {{ form.processing ? 'Memproses Pendaftaran...' : 'Daftar Sekarang' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center pb-4">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        Sudah memiliki akun? 
                        <Link :href="route('login')" class="text-indigo-600 hover:text-gray-900 underline underline-offset-4 decoration-2 ml-1 transition">
                            Masuk di sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Animasi Slide Up untuk Form */
form {
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Scrollbar untuk Panel Kanan */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e2e8f0;
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #cbd5e1;
}
</style>