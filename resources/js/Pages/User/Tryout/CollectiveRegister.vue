<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    tryout: Object,
});

const form = useForm({
    payment_method: 'wallet',
    emails: ['', ''], 
});

// State Visual
const validationStatus = ref([null, null]); // 'valid', 'invalid', null
const isLoading = ref([false, false]);

// Controller untuk membatalkan request lama (biar tidak bentrok saat mengetik cepat)
const abortControllers = ref([null, null]);

// Tambah Peserta
const addParticipant = () => {
    if (form.emails.length < 5) {
        form.emails.push('');
        validationStatus.value.push(null);
        isLoading.value.push(false);
        abortControllers.value.push(null);
    }
};

// Hapus Peserta
const removeParticipant = (index) => {
    form.emails.splice(index, 1);
    validationStatus.value.splice(index, 1);
    isLoading.value.splice(index, 1);
    abortControllers.value.splice(index, 1);
};

// Fungsi Cek Email (REALTIME)
const checkEmail = async (index, email) => {
    // 1. Reset Status Visual (Gunakan splice agar reaktif)
    validationStatus.value.splice(index, 1, null); 

    // 2. Batalkan request sebelumnya jika masih jalan
    if (abortControllers.value[index]) {
        abortControllers.value[index].abort();
    }

    // 3. Jika kosong, berhenti
    if (!email) return;

    // 4. Validasi Format Email Sederhana
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        validationStatus.value.splice(index, 1, 'invalid'); // Format salah
        return;
    }

    // 5. Mulai Loading & Request
    isLoading.value.splice(index, 1, true);
    
    const controller = new AbortController();
    abortControllers.value[index] = controller;

    try {
        // PANGGIL API
        const response = await axios.post(route('api.check.email'), { email }, {
            signal: controller.signal
        });
        
        // Update Status: Ada = valid, Tidak = invalid
        const status = response.data.exists ? 'valid' : 'invalid';
        validationStatus.value.splice(index, 1, status);

    } catch (error) {
        if (axios.isCancel(error)) return; // Abaikan jika dibatalkan user
        console.error("API Error:", error);
        // Jangan ubah status jadi null, biarkan user tau ada masalah (opsional)
    } finally {
        isLoading.value.splice(index, 1, false);
    }
};

const submit = () => {
    form.post(route('tryout.processRegistration', props.tryout.id));
};

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
</script>

<template>
    <Head title="Pendaftaran Kolektif" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pendaftaran Kolektif</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <div class="mb-6 border-b pb-4">
                        <h3 class="text-lg font-bold text-slate-800">{{ tryout.title }}</h3>
                        <p class="text-slate-500 text-sm">Harga Satuan: {{ formatRupiah(tryout.price) }}</p>
                    </div>

                    <form @submit.prevent="submit">
                        
                        <div class="space-y-4 mb-6">
                            <label class="block text-sm font-medium text-gray-700">Email Peserta (Maks. 5 Orang)</label>
                            
                            <div class="flex items-center gap-2">
                                <div class="relative w-full">
                                    <input type="email" :value="$page.props.auth.user.email" disabled 
                                        class="w-full border-slate-300 bg-slate-100 rounded-lg text-slate-500 cursor-not-allowed pl-3 pr-20 py-2" 
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 z-10">
                                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded">Ketua</span>
                                    </div>
                                </div>
                            </div>

                            <div v-for="(email, index) in form.emails" :key="index" class="flex items-center gap-2 relative">
                                <div class="relative w-full group">
                                    <input 
                                        v-model="form.emails[index]"
                                        type="email" 
                                        class="w-full border-slate-300 rounded-lg focus:ring-[#004a87] focus:border-[#004a87] pr-12 transition-colors"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-200': validationStatus[index] === 'invalid',
                                            'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-200': validationStatus[index] === 'valid'
                                        }"
                                        placeholder="Masukkan email anggota..."
                                        required
                                        @input="checkEmail(index, form.emails[index])"
                                    />

                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center z-20 pointer-events-none">
                                        
                                        <svg v-if="isLoading[index]" class="animate-spin h-5 w-5 text-[#004a87]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        
                                        <svg v-else-if="validationStatus[index] === 'valid'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>

                                        <svg v-else-if="validationStatus[index] === 'invalid'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>

                                    </div>

                                    <div v-if="validationStatus[index] === 'invalid'" class="absolute right-0 top-full mt-1 z-30 hidden group-hover:block">
                                        <div class="bg-red-600 text-white text-xs rounded py-1 px-2 shadow-lg">
                                            Email tidak terdaftar
                                        </div>
                                    </div>
                                </div>

                                <button type="button" @click="removeParticipant(index)" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>

                            <button 
                                v-if="form.emails.length < 5"
                                type="button" 
                                @click="addParticipant" 
                                class="text-sm font-bold text-[#004a87] hover:underline flex items-center gap-1 mt-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Tambah Anggota
                            </button>
                        </div>

                        <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="border p-4 rounded-xl flex items-center gap-3 cursor-pointer transition bg-white hover:border-blue-300" :class="form.payment_method === 'wallet' ? 'border-[#004a87] ring-1 ring-[#004a87]' : 'border-slate-200'">
                                    <input type="radio" v-model="form.payment_method" value="wallet" class="text-[#004a87] focus:ring-[#004a87]">
                                    <div>
                                        <div class="text-sm font-bold text-slate-700">Saldo Dompet</div>
                                        <div class="text-xs text-slate-500">Bayar instan dengan saldo</div>
                                    </div>
                                </label>
                                <label class="border p-4 rounded-xl flex items-center gap-3 cursor-pointer transition bg-white hover:border-blue-300" :class="form.payment_method === 'midtrans' ? 'border-[#004a87] ring-1 ring-[#004a87]' : 'border-slate-200'">
                                    <input type="radio" v-model="form.payment_method" value="midtrans" class="text-[#004a87] focus:ring-[#004a87]">
                                    <div>
                                        <div class="text-sm font-bold text-slate-700">QRIS / Transfer</div>
                                        <div class="text-xs text-slate-500">Virtual Account, E-Wallet</div>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.payment" class="text-red-500 text-sm mt-2 font-medium">{{ form.errors.payment }}</p>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button 
                                type="submit" 
                                class="px-6 py-2.5 bg-[#004a87] text-white font-bold rounded-xl hover:bg-blue-800 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                :disabled="form.processing || validationStatus.includes('invalid') || isLoading.includes(true)"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>