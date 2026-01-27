<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Transition } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Mengambil data user terbaru dari shared props Inertia
const user = usePage().props.auth.user;

// Inisialisasi form: Data otomatis terisi dari database setiap kali halaman dimuat
const form = useForm({
    name: user.name,
    email: user.email,
    province: user.province || '', // Kolom baru untuk perangkingan
});

// Fungsi untuk mengirim perubahan ke server
const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Logika tambahan jika diperlukan setelah berhasil
        },
    });
};

// Daftar Provinsi (Gaya Tegak & Tegas menggunakan Huruf Kapital)
const provinces = [
    'ACEH', 'SUMATERA UTARA', 'SUMATERA BARAT', 'RIAU', 'KEPULAUAN RIAU',
    'JAMBI', 'SUMATERA SELATAN', 'KEPULAUAN BANGKA BELITUNG', 'BENGKULU', 'LAMPUNG',
    'DKI JAKARTA', 'JAWA BARAT', 'BANTEN', 'JAWA TENGAH', 'DI YOGYAKARTA', 'JAWA TIMUR',
    'BALI', 'NUSA TENGGARA BARAT', 'NUSA TENGGARA TIMUR', 'KALIMANTAN BARAT', 
    'KALIMANTAN TENGAH', 'KALIMANTAN SELATAN', 'KALIMANTAN TIMUR', 'KALIMANTAN UTARA',
    'SULAWESI UTARA', 'SULAWESI TENGAH', 'SULAWESI SELATAN', 'SULAWESI TENGGARA', 
    'GORONTALO', 'SULAWESI BARAT', 'MALUKU', 'MALUKU UTARA', 'PAPUA', 'PAPUA BARAT'
];
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <div class="space-y-2">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Nama Lengkap Peserta</label>
            <input 
                v-model="form.name" 
                type="text" 
                required
                placeholder="MASUKKAN NAMA SESUAI IJAZAH..."
                class="w-full bg-white border-gray-200 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" 
            />
            <div v-if="form.errors.name" class="text-red-500 text-[10px] font-black uppercase ml-2 mt-1">{{ form.errors.name }}</div>
        </div>

        <div class="space-y-2">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email Terdaftar</label>
            <input 
                v-model="form.email" 
                type="email" 
                required
                placeholder="EMAIL@CONTOH.COM"
                class="w-full bg-white border-gray-200 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none" 
            />
            <div v-if="form.errors.email" class="text-red-500 text-[10px] font-black uppercase ml-2 mt-1">{{ form.errors.email }}</div>
        </div>

        <div class="space-y-2">
            <label class="text-[10px] font-black text-gray-400 uppercase ml-2 tracking-widest">Wilayah Provinsi (Ranking Nasional)</label>
            <div class="relative">
                <select 
                    v-model="form.province" 
                    required
                    class="w-full bg-white border-gray-200 rounded-2xl py-4 px-6 text-sm font-bold appearance-none focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 transition-all outline-none cursor-pointer"
                >
                    <option value="" disabled>PILIH PROVINSI DOMISILI...</option>
                    <option v-for="prov in provinces" :key="prov" :value="prov">
                        {{ prov }}
                    </option>
                </select>
                </div>
            <div v-if="form.errors.province" class="text-red-500 text-[10px] font-black uppercase ml-2 mt-1">{{ form.errors.province }}</div>
        </div>

        <div class="flex items-center gap-6 pt-4">
            <button 
                type="submit" 
                :disabled="form.processing"
                class="bg-gray-900 text-white px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all shadow-xl shadow-gray-200 disabled:opacity-50 active:scale-95"
            >
                {{ form.processing ? 'MEMPROSES...' : 'SIMPAN PERUBAHAN' }}
            </button>

            <Transition
                enter-active-class="transition ease-in-out duration-300"
                enter-from-class="opacity-0 translate-x-4"
                leave-active-class="transition ease-in-out duration-300"
                leave-to-class="opacity-0 translate-x-4"
            >
                <div v-if="form.recentlySuccessful" class="flex items-center gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <p class="text-[10px] font-black text-green-600 uppercase tracking-widest">DATA BERHASIL DISIMPAN</p>
                </div>
            </Transition>
        </div>
    </form>
</template>

<style scoped>
/* Memastikan tidak ada tulisan miring di seluruh form */
* {
    font-style: normal !important;
}

/* Custom styling untuk select agar teks terlihat konsisten */
select {
    color: #111827;
}

select:invalid {
    color: #D1D5DB;
}
</style>