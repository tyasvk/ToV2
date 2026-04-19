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

const isPasswordVisible = ref(false);
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

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4 font-sans selection:bg-amber-100">
        
        <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-amber-200/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-slate-200/40 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="w-full max-w-[1000px] flex flex-col md:flex-row bg-white rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(15,23,42,0.1)] overflow-hidden relative z-10 border border-white">
            
            <div class="hidden md:flex md:w-4/12 bg-slate-50 p-10 flex-col justify-center items-center relative overflow-hidden border-r border-slate-100">
                <div class="absolute inset-0 opacity-[0.02] bg-[url('https://www.transparenttextures.com/patterns/gplay.png')]"></div>
                <div class="relative z-10 flex flex-col items-center text-center">
                    <img src="/images/logo.png" alt="Logo" class="h-28 lg:h-36 w-auto mb-8 drop-shadow-md">
                    <div class="flex items-center gap-3 opacity-30 mb-6">
                        <div class="w-10 h-[1px] bg-slate-900"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                        <div class="w-10 h-[1px] bg-slate-900"></div>
                    </div>
                    <h2 class="text-base font-light italic text-slate-500 leading-relaxed font-serif px-2">
                        "Mulai perjalanan suksesmu di <span class="text-slate-900 font-bold not-italic">CPNS Nusantara</span>."
                    </h2>
                </div>
            </div>

            <div class="w-full md:w-8/12 p-6 md:p-10 flex flex-col justify-center bg-white">
                
                <div class="md:hidden flex items-center justify-center gap-3 mb-6">
                    <img src="/images/logo.png" alt="Logo" class="h-10 w-auto">
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">CPNS <span class="text-amber-500">Nusantara</span></span>
                </div>

                <div class="mb-6 text-center md:text-left">
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight italic leading-none mb-2">Sign Up.</h3>
                    <p class="text-[11px] text-slate-500 font-medium tracking-wide">Lengkapi formulir pendaftaran di bawah ini.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-3.5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Nama Lengkap</label>
                            <input v-model="form.name" type="text" required placeholder="Sesuai KTP"
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none border" />
                        </div>
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Email</label>
                            <input v-model="form.email" type="email" required placeholder="nama@email.com"
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none border" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Provinsi</label>
                            <select v-model="form.province_code" required class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white border cursor-pointer outline-none transition-all appearance-none">
                                <option value="" disabled selected>Pilih Provinsi</option>
                                <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Gender</label>
                            <select v-model="form.gender" required class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white border cursor-pointer outline-none transition-all appearance-none">
                                <option value="" disabled selected>Pilih</option>
                                <option value="1">Laki-laki</option>
                                <option value="2">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1 relative group">
                        <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Instansi Tujuan</label>
                        <div class="relative">
                            <input v-model="searchAgency" type="text" required @input="onSearchInput" @focus="isDropdownOpen = true" @blur="closeDropdown"
                                placeholder="Cari Instansi..."
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none border" autocomplete="off" />
                            
                            <div v-if="isDropdownOpen" class="absolute z-50 mt-1 w-full bg-white shadow-2xl rounded-xl py-1 text-xs border border-slate-100 max-h-40 overflow-y-auto custom-scrollbar">
                                <ul v-if="filteredAgencies.length > 0">
                                    <li v-for="(agency, index) in filteredAgencies" :key="index" @mousedown.prevent="selectAgency(agency)"
                                        class="cursor-pointer py-2 px-4 hover:bg-amber-50 hover:text-amber-700 text-slate-600 font-medium transition-colors">
                                        {{ agency }}
                                    </li>
                                </ul>
                                <div v-else class="py-3 px-4 text-slate-400 italic text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Kata Sandi</label>
                            <input v-model="form.password" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white border outline-none transition-all" />
                        </div>
                        <div class="space-y-1 group">
                            <label class="text-[9px] font-bold text-slate-500 uppercase tracking-widest ml-1">Konfirmasi</label>
                            <input v-model="form.password_confirmation" :type="isPasswordVisible ? 'text' : 'password'" required placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-xl py-2.5 px-4 text-sm font-medium text-slate-800 focus:bg-white border outline-none transition-all" />
                        </div>
                    </div>

                    <div class="flex justify-start px-1">
                        <button type="button" @click="isPasswordVisible = !isPasswordVisible" class="text-[9px] font-black text-amber-600 uppercase tracking-widest hover:text-slate-900 transition">
                            {{ isPasswordVisible ? 'Sembunyikan' : 'Lihat Sandi' }}
                        </button>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-slate-900 text-white rounded-xl py-4 font-bold text-xs uppercase tracking-[0.25em] shadow-xl shadow-slate-100 hover:bg-amber-600 hover:shadow-amber-100 active:scale-[0.98] transition-all duration-300 disabled:opacity-50">
                            {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center border-t border-slate-50 pt-4">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Sudah punya akun? 
                        <Link :href="route('login')" class="text-amber-600 font-black hover:underline underline-offset-4 decoration-2 ml-1">
                            Masuk di Sini
                        </Link>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 text-[10px] text-slate-600 font-bold uppercase tracking-[0.4em] text-center z-20">
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
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>