<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { provinces, agencies } from '@/Data/agencies'; 

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: 'PATCH', // Penting untuk upload file di Inertia saat update
    name: user.name,
    email: user.email,
    avatar: null, // Field baru untuk file
    participant_number: user.participant_number || '-',
    province_code: user.province_code || '',
    agency_name: user.agency_name || '',
    instance_type: user.instance_type || '',
    gender: user.gender || '', 
});

// --- LOGIC FOTO PROFIL ---
const photoPreview = ref(null);
const photoInput = ref(null);

// Generate URL foto (User Avatar atau Default Initials)
const currentPhotoUrl = computed(() => {
    return user.avatar 
        ? `/storage/${user.avatar}` 
        : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&color=7F9CF5&background=EBF4FF`;
});

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;

    form.avatar = photo; // Masukkan ke form
    
    // Buat preview lokal
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

// --- LOGIC SEARCH INSTANSI ---
const searchAgency = ref(user.agency_name || '');
const isDropdownOpen = ref(false);

const filteredAgencies = computed(() => {
    const query = searchAgency.value.toLowerCase().trim();
    if (!query) return agencies.slice(0, 100); 
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
    if (newVal.startsWith('Pemerintah')) {
        form.instance_type = '2';
    } else {
        form.instance_type = '1';
    }
});

// Submit Form (Gunakan POST dengan _method: PATCH agar file terkirim)
const submitForm = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            form.clearErrors();
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Informasi Profil</h2>
            <p class="mt-1 text-sm text-gray-600">
                Perbarui foto, informasi akun, dan instansi tujuan Anda.
            </p>
        </header>

        <form @submit.prevent="submitForm" class="mt-6 space-y-6">
            
            <div class="flex items-center gap-6">
                <input 
                    ref="photoInput"
                    type="file" 
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <div class="relative">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-indigo-100 shadow-sm">
                        <img 
                            :src="photoPreview || currentPhotoUrl" 
                            alt="Foto Profil" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>

                <div>
                    <InputLabel value="Foto Profil" class="mb-1" />
                    <button 
                        type="button" 
                        class="text-xs font-bold text-indigo-600 uppercase tracking-widest border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-50 transition"
                        @click="selectNewPhoto"
                    >
                        Pilih Foto Baru
                    </button>
                    <InputError :message="form.errors.avatar" class="mt-1" />
                </div>
            </div>

            <div>
                <InputLabel value="Nomor Peserta" />
                <div class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-500 font-mono font-bold tracking-widest select-all">
                    {{ form.participant_number }}
                </div>
            </div>

            <div>
                <InputLabel for="name" value="Nama Lengkap" />
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="province_code" value="Provinsi Domisili" />
                <select id="province_code" v-model="form.province_code" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="" disabled>Pilih Provinsi</option>
                    <option v-for="prov in provinces" :key="prov.code" :value="prov.code">{{ prov.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.province_code" />
            </div>

            <div class="relative">
                <InputLabel for="agency_name" value="Instansi Tujuan" />
                <div class="relative">
                    <TextInput 
                        id="agency_name" 
                        type="text" 
                        class="mt-1 block w-full pr-10" 
                        v-model="searchAgency"
                        @input="onSearchInput"
                        @focus="isDropdownOpen = true"
                        @blur="closeDropdown"
                        placeholder="Cari Instansi..."
                        autocomplete="off"
                        required
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </div>
                </div>
                <div v-if="isDropdownOpen" class="absolute z-50 mt-1 w-full bg-white shadow-xl max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-200">
                    <ul v-if="filteredAgencies.length > 0">
                        <li v-for="(agency, index) in filteredAgencies" :key="index" @mousedown.prevent="selectAgency(agency)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 border-b border-gray-50 last:border-0">
                            <span class="block truncate font-medium">{{ agency }}</span>
                        </li>
                    </ul>
                    <div v-else class="py-2 px-3 text-gray-500 italic text-center">Instansi tidak ditemukan.</div>
                </div>
                <InputError class="mt-2" :message="form.errors.agency_name" />
            </div>

            <div>
                <InputLabel for="gender" value="Jenis Kelamin" />
                <select id="gender" v-model="form.gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                    <option value="" disabled>Pilih Gender</option>
                    <option value="1">Laki-laki</option>
                    <option value="2">Perempuan</option>
                </select>
                <InputError class="mt-2" :message="form.errors.gender" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan Perubahan</PrimaryButton>
                <Transition enter-active-class="transition ease-in-out duration-300" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in-out duration-1000" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="form.recentlySuccessful" class="flex items-center gap-2 text-sm font-medium text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        Perubahan Berhasil Disimpan
                    </div>
                </Transition>
            </div>
        </form>
    </section>
</template>