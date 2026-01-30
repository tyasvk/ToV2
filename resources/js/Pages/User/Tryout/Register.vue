<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    tryout: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const form = useForm({
    payment_method: 'wallet',
    emails: [], // Awal kosong
});

// State Visual
const validationStatus = ref([]); 
const isLoading = ref([]);
const errorMessage = ref([]);
const abortControllers = ref([]);

// Format Rupiah
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(num);

// Tambah Peserta
const addParticipant = () => {
    if (form.emails.length < 4) {
        form.emails.push('');
        validationStatus.value.push(null);
        isLoading.value.push(false);
        abortControllers.value.push(null);
        errorMessage.value.push(null);
    }
};

// Hapus Peserta
const removeParticipant = (index) => {
    form.emails.splice(index, 1);
    validationStatus.value.splice(index, 1);
    isLoading.value.splice(index, 1);
    abortControllers.value.splice(index, 1);
    errorMessage.value.splice(index, 1);
};

// Cek Email Realtime
const checkEmail = async (index, emailValue) => {
    validationStatus.value.splice(index, 1, null);
    errorMessage.value.splice(index, 1, null);

    if (abortControllers.value[index]) abortControllers.value[index].abort();

    if (!emailValue) {
        isLoading.value.splice(index, 1, false);
        return;
    }

    isLoading.value.splice(index, 1, true);
    const controller = new AbortController();
    abortControllers.value[index] = controller;

    try {
        const response = await axios.post('/check-email-availability', { email: emailValue }, {
            signal: controller.signal
        });

        if (abortControllers.value[index] === controller) {
            const exists = response.data.exists;
            if (exists) {
                validationStatus.value.splice(index, 1, 'valid');
                errorMessage.value.splice(index, 1, 'User terdaftar');
            } else {
                validationStatus.value.splice(index, 1, 'invalid');
                errorMessage.value.splice(index, 1, 'Email tidak ditemukan');
            }
        }
    } catch (error) {
        if (!axios.isCancel(error)) {
            validationStatus.value.splice(index, 1, 'invalid');
            errorMessage.value.splice(index, 1, 'Error validasi');
        }
    } finally {
        if (abortControllers.value[index] === controller) {
            isLoading.value.splice(index, 1, false);
        }
    }
};

const submit = () => {
    form.post(route('tryout.processRegistration', props.tryout.id));
};

const totalAmount = computed(() => {
    return props.tryout.price * (form.emails.length + 1);
});
</script>

<template>
    <Head title="Pendaftaran Kolektif" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-50 pt-4 pb-20 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                    
                    <div class="lg:col-span-8 space-y-5">
                        
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#004a87]"></span>
                                Pendaftar Utama
                            </h3>
                            
                            <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="w-12 h-12 rounded-full bg-[#004a87] text-white flex items-center justify-center font-bold text-lg shadow-md shadow-blue-200 shrink-0">
                                    {{ currentUser.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-bold text-slate-800 truncate">{{ currentUser.name }}</h4>
                                        <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full uppercase shrink-0">Verified</span>
                                    </div>
                                    <p class="text-sm text-slate-500 truncate">{{ currentUser.email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                    Anggota Tambahan
                                </h3>
                                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded">
                                    {{ form.emails.length }} / 4 Slot
                                </span>
                            </div>

                            <div class="space-y-4">
                                <transition-group enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform opacity-100" leave-to-class="transform opacity-0">
                                    
                                    <div v-for="(email, index) in form.emails" :key="index" class="relative group">
                                        <div class="flex gap-3 items-start">
                                            <div class="mt-3 w-8 h-8 rounded-lg bg-blue-50 text-[#004a87] flex items-center justify-center font-bold text-sm shrink-0">
                                                {{ index + 1 }}
                                            </div>
                                            
                                            <div class="flex-1">
                                                <div class="relative">
                                                    <input 
                                                        v-model="form.emails[index]"
                                                        type="email" 
                                                        class="w-full rounded-xl px-4 py-3.5 border-2 transition-all outline-none"
                                                        :class="{
                                                            'border-slate-200 focus:border-[#004a87] focus:ring-4 focus:ring-blue-50/50': validationStatus[index] === null,
                                                            'border-emerald-400 bg-emerald-50/20 text-emerald-900 focus:border-emerald-500': validationStatus[index] === 'valid',
                                                            'border-red-300 bg-red-50/20 text-red-900 focus:border-red-500': validationStatus[index] === 'invalid'
                                                        }"
                                                        placeholder="Masukkan email anggota..."
                                                        required
                                                        @input="checkEmail(index, $event.target.value)"
                                                    />

                                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                                        <svg v-if="isLoading[index]" class="animate-spin h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        
                                                        <button v-else-if="!email" @click="removeParticipant(index)" type="button" class="text-slate-300 hover:text-red-500 transition" title="Hapus">
                                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                        
                                                        <svg v-else-if="validationStatus[index] === 'valid'" class="h-6 w-6 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                        
                                                        <svg v-else-if="validationStatus[index] === 'invalid'" class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-start mt-1.5 ml-1">
                                                    <div class="h-4">
                                                        <p v-if="validationStatus[index] === 'valid'" class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                                            User terdaftar
                                                        </p>
                                                        <p v-if="validationStatus[index] === 'invalid'" class="text-xs font-bold text-red-500 flex items-center gap-1">
                                                            {{ errorMessage[index] }}
                                                        </p>
                                                    </div>
                                                    <button @click="removeParticipant(index)" type="button" class="text-[10px] font-bold text-slate-400 hover:text-red-500 hover:underline transition">Hapus Baris</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </transition-group>

                                <div v-if="form.emails.length === 0" class="text-center py-10 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm border border-slate-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">Belum ada anggota tambahan.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol di bawah untuk menambah peserta.</p>
                                </div>

                                <button v-if="form.emails.length < 4" type="button" @click="addParticipant" class="w-full py-4 border-2 border-dashed border-slate-300 rounded-2xl text-slate-400 font-bold hover:border-[#004a87] hover:text-[#004a87] hover:bg-blue-50 transition-all flex flex-col items-center justify-center gap-1 group">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-50 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    <span class="text-xs uppercase tracking-widest">Tambah Anggota</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                        
                        <div class="bg-white rounded-2xl shadow-xl shadow-blue-900/5 border border-slate-200 overflow-hidden">
                            <div class="bg-[#004a87] p-6 relative">
                                <div class="relative z-10">
                                    <h2 class="text-lg font-black text-white uppercase tracking-wider">PENDAFTARAN</h2>
                                    <p class="text-blue-200 text-xs font-medium mt-1 opacity-90">Kolektif / Tim</p>
                                </div>
                                <div class="absolute right-0 bottom-0 w-24 h-24 bg-white/10 rounded-tl-full blur-md"></div>
                            </div>

                            <div class="p-6 bg-white">
                                <div class="mb-6">
                                    <h3 class="font-bold text-slate-800 leading-tight mb-2">{{ tryout.title }}</h3>
                                    <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                                            <span>Harga Satuan</span>
                                            <span class="font-bold text-slate-700">{{ formatRupiah(tryout.price) }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs text-slate-500">
                                            <span>Peserta</span>
                                            <span class="font-bold text-slate-700">{{ form.emails.length + 1 }} Orang</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-between items-end pb-6 border-b border-dashed border-slate-200 mb-6">
                                    <span class="text-sm font-bold text-slate-500">Total Bayar</span>
                                    <span class="text-2xl font-black text-[#004a87]">{{ formatRupiah(totalAmount) }}</span>
                                </div>

                                <div class="mb-6">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Metode Pembayaran</label>
                                    <div class="space-y-3">
                                        
                                        <label class="group relative block cursor-pointer">
                                            <input type="radio" v-model="form.payment_method" value="wallet" class="peer sr-only">
                                            <div class="p-4 rounded-xl border-2 transition-all duration-200 
                                                        border-slate-200 bg-white hover:border-blue-300
                                                        peer-checked:border-[#004a87] peer-checked:bg-[#004a87] peer-checked:text-white peer-checked:shadow-md">
                                                
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-sm" :class="form.payment_method === 'wallet' ? 'text-white' : 'text-slate-700'">Saldo Dompet</span>
                                                    
                                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                                         :class="form.payment_method === 'wallet' ? 'border-white bg-white' : 'border-slate-300 bg-white'">
                                                        <div v-if="form.payment_method === 'wallet'" class="w-2.5 h-2.5 rounded-full bg-[#004a87]"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-xs flex justify-between items-center mt-2" :class="form.payment_method === 'wallet' ? 'text-blue-100' : 'text-slate-500'">
                                                    <span>Sisa Saldo:</span>
                                                    <span class="font-bold px-2 py-0.5 rounded"
                                                          :class="form.payment_method === 'wallet' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600'">
                                                        {{ formatRupiah(currentUser.balance) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="group relative block cursor-pointer">
                                            <input type="radio" v-model="form.payment_method" value="midtrans" class="peer sr-only">
                                            <div class="p-4 rounded-xl border-2 transition-all duration-200 
                                                        border-slate-200 bg-white hover:border-blue-300
                                                        peer-checked:border-[#004a87] peer-checked:bg-[#004a87] peer-checked:text-white peer-checked:shadow-md">
                                                
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="font-bold text-sm" :class="form.payment_method === 'midtrans' ? 'text-white' : 'text-slate-700'">QRIS / Transfer</span>
                                                    
                                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center transition-colors"
                                                         :class="form.payment_method === 'midtrans' ? 'border-white bg-white' : 'border-slate-300 bg-white'">
                                                        <div v-if="form.payment_method === 'midtrans'" class="w-2.5 h-2.5 rounded-full bg-[#004a87]"></div>
                                                    </div>
                                                </div>
                                                
                                                <p class="text-[10px] mt-1" :class="form.payment_method === 'midtrans' ? 'text-blue-100' : 'text-slate-400'">
                                                    Virtual Account (BCA, Mandiri, dll) & E-Wallet
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    <p v-if="form.errors.payment" class="text-red-500 text-xs mt-2 font-bold bg-red-50 p-2 rounded border border-red-100 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        {{ form.errors.payment }}
                                    </p>
                                </div>

                                <button 
                                    type="submit" 
                                    class="w-full py-4 bg-[#004a87] text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-800 hover:shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="form.processing || validationStatus.includes('invalid') || isLoading.includes(true)"
                                >
                                    <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span v-else>Bayar Sekarang</span>
                                </button>

                                <div class="mt-4 text-center">
                                    <p class="text-[10px] text-slate-400">Pastikan semua email valid sebelum membayar.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>