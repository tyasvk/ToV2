<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    tryout: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const form = useForm({
    payment_method: 'wallet',
    emails: [], 
    voucher_code: '',
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
    form.post(route('tryout.processRegistration', props.tryout.id), {
        onError: (errors) => {
            console.error(errors);
        }
    });
};

// Logika Total Bayar (Dinamis dengan Diskon Voucher)
const totalAmount = computed(() => {
    const baseAmount = props.tryout.price * (form.emails.length + 1);
    // Jika voucher diisi (minimal 3 karakter), beri potongan simulasi Rp 3.000
    if (form.voucher_code && form.voucher_code.length >= 3) {
        return Math.max(0, baseAmount - 3000);
    }
    return baseAmount;
});
</script>

<template>
    <Head title="Pendaftaran Simulasi - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700 pb-12 px-4 sm:px-5 lg:px-6 pt-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <Link 
                    :href="route('tryout.show', props.tryout.id)" 
                    class="inline-flex items-center gap-2 text-xs font-medium uppercase tracking-wider text-slate-500 hover:text-blue-600 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Detail
                </Link>
                
                <div class="text-left md:text-right">
                    <h1 class="text-xl md:text-2xl text-slate-900 uppercase tracking-tight leading-snug font-medium">
                        Pendaftaran Tryout
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 font-normal mt-0.5">
                        Registrasi mandiri atau kolektif bersama tim Anda.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5 items-start mt-4">
                
                <div class="lg:col-span-2 space-y-4 md:space-y-5">
                    
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm">
                        <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">
                            Pendaftar Utama
                        </h3>
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-medium shadow-sm shrink-0">
                                {{ currentUser.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm md:text-base text-slate-900 font-medium truncate tracking-tight">{{ currentUser.name }}</h4>
                                    <span class="text-[9px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-100 px-2 py-0.5 rounded-md uppercase shrink-0 tracking-wider">Verified</span>
                                </div>
                                <p class="text-xs text-slate-500 truncate">{{ currentUser.email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest">
                                Anggota Tambahan (Opsional)
                            </h3>
                            <span class="text-[10px] font-medium text-slate-500 bg-slate-50 border border-slate-100 px-2 py-1 rounded uppercase tracking-wider">
                                {{ form.emails.length }} / 4 Slot
                            </span>
                        </div>

                        <div class="space-y-4">
                            <transition-group enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform opacity-100" leave-to-class="transform opacity-0">
                                
                                <div v-for="(email, index) in form.emails" :key="index" class="relative group">
                                    <div class="flex gap-3 items-start">
                                        <div class="mt-2.5 w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 flex items-center justify-center text-xs font-medium shrink-0">
                                            {{ index + 1 }}
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="relative">
                                                <input 
                                                    v-model="form.emails[index]"
                                                    type="email" 
                                                    class="w-full rounded-xl px-4 py-2.5 border text-xs md:text-sm transition-all outline-none bg-slate-50 focus:bg-white"
                                                    :class="{
                                                        'border-slate-200 focus:border-blue-400 focus:ring-1 focus:ring-blue-400': validationStatus[index] === null,
                                                        'border-emerald-300 bg-emerald-50/30 text-emerald-900 focus:border-emerald-400': validationStatus[index] === 'valid',
                                                        'border-red-300 bg-red-50/30 text-red-900 focus:border-red-400': validationStatus[index] === 'invalid'
                                                    }"
                                                    placeholder="Masukkan email rekan yang sudah terdaftar..."
                                                    required
                                                    @input="checkEmail(index, $event.target.value)"
                                                />

                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2 bg-white/50 pl-2 rounded-r-xl">
                                                    <svg v-if="isLoading[index]" class="animate-spin h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    
                                                    <button v-else-if="!email" @click="removeParticipant(index)" type="button" class="text-slate-300 hover:text-red-500 transition-colors p-1" title="Hapus">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                    
                                                    <svg v-else-if="validationStatus[index] === 'valid'" class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                    
                                                    <svg v-else-if="validationStatus[index] === 'invalid'" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                                </div>
                                            </div>

                                            <div class="flex justify-between items-start mt-1.5 ml-1">
                                                <div class="h-4">
                                                    <p v-if="validationStatus[index] === 'valid'" class="text-[9px] font-medium text-emerald-600 uppercase tracking-wide">
                                                        Email terdaftar & valid
                                                    </p>
                                                    <p v-if="validationStatus[index] === 'invalid'" class="text-[9px] font-medium text-red-500 uppercase tracking-wide">
                                                        {{ errorMessage[index] }}
                                                    </p>
                                                </div>
                                                <button @click="removeParticipant(index)" type="button" class="text-[9px] font-medium text-slate-400 hover:text-red-500 hover:underline transition uppercase tracking-wide">Hapus Baris</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition-group>

                            <div v-if="form.emails.length === 0" class="text-center py-6 bg-slate-50/50 rounded-xl border border-dashed border-slate-200">
                                <p class="text-xs text-slate-500 font-normal">Anda dapat menambahkan maksimal 4 teman untuk didaftarkan bersama.</p>
                            </div>

                            <button v-if="form.emails.length < 4" type="button" @click="addParticipant" class="w-full py-3 border border-dashed border-slate-300 rounded-xl text-slate-500 text-xs font-medium hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-2 group">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span class="uppercase tracking-wider">Tambah Rekan Tim</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1 space-y-4 md:space-y-5 lg:sticky lg:top-6">
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 md:p-6 shadow-sm space-y-5">
                        
                        <div>
                            <h3 class="text-xs font-medium text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-3 mb-4">Ringkasan Pesanan</h3>
                            <h4 class="text-sm text-slate-900 font-medium leading-snug mb-3 uppercase tracking-tight">{{ tryout.title }}</h4>
                            
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-2.5">
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span class="font-normal">Harga Akses</span>
                                    <span class="font-medium text-slate-800">{{ formatRupiah(tryout.price) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span class="font-normal">Total Peserta</span>
                                    <span class="font-medium text-slate-800">{{ form.emails.length + 1 }} Orang</span>
                                </div>
                                <div v-if="form.voucher_code && form.voucher_code.length >= 3" class="flex justify-between text-xs text-emerald-600 font-medium border-t border-emerald-100/50 pt-2.5">
                                    <span>Voucher Diterapkan</span>
                                    <span>- {{ formatRupiah(3000) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2 block">Kode Voucher / Promo</label>
                            <div class="relative">
                                <input 
                                    v-model="form.voucher_code"
                                    type="text" 
                                    placeholder="Opsional..."
                                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:bg-white uppercase tracking-wider transition-all"
                                />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                                </span>
                            </div>
                            <p v-if="form.voucher_code" class="text-[9px] text-emerald-600 font-normal mt-1.5 uppercase tracking-wide">
                                *Sistem memvalidasi potongan Rp 3.000 otomatis.
                            </p>
                        </div>

                        <div class="flex justify-between items-end pb-4 border-b border-slate-100 pt-2">
                            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Tagihan</span>
                            <span class="text-lg md:text-xl font-medium text-slate-900 tracking-tight leading-none">{{ formatRupiah(totalAmount) }}</span>
                        </div>

                        <div>
                            <label class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-2.5 block">Metode Pembayaran</label>
                            <div class="space-y-2.5">
                                
                                <label class="group relative block cursor-pointer">
                                    <input type="radio" v-model="form.payment_method" value="wallet" class="peer sr-only">
                                    <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-all duration-200 peer-checked:border-blue-600 peer-checked:ring-1 peer-checked:ring-blue-600 peer-checked:bg-blue-50/20">
                                        
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="font-medium text-xs uppercase tracking-wide text-slate-800">Saldo Dompet</span>
                                            <div class="w-4 h-4 rounded-full border border-slate-300 bg-white flex items-center justify-center peer-checked:border-blue-600">
                                                <div v-if="form.payment_method === 'wallet'" class="w-2 h-2 rounded-full bg-blue-600"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-[10px] flex justify-between items-center text-slate-500">
                                            <span class="font-normal">Sisa Saldo:</span>
                                            <span class="font-medium bg-emerald-50 text-emerald-600 border border-emerald-100 px-1.5 py-0.5 rounded">
                                                {{ formatRupiah(currentUser.balance) }}
                                            </span>
                                        </div>
                                    </div>
                                </label>

                                <label class="group relative block cursor-pointer">
                                    <input type="radio" v-model="form.payment_method" value="midtrans" class="peer sr-only">
                                    <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition-all duration-200 peer-checked:border-blue-600 peer-checked:ring-1 peer-checked:ring-blue-600 peer-checked:bg-blue-50/20">
                                        
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="font-medium text-xs uppercase tracking-wide text-slate-800">Transfer / QRIS</span>
                                            <div class="w-4 h-4 rounded-full border border-slate-300 bg-white flex items-center justify-center peer-checked:border-blue-600">
                                                <div v-if="form.payment_method === 'midtrans'" class="w-2 h-2 rounded-full bg-blue-600"></div>
                                            </div>
                                        </div>
                                        
                                        <p class="text-[10px] text-slate-500 font-normal leading-relaxed">
                                            Virtual Account (BCA, Mandiri, BRI) & E-Wallet
                                        </p>
                                    </div>
                                </label>

                            </div>
                            <p v-if="form.errors.payment" class="text-red-500 text-[9px] mt-2 font-medium bg-red-50 p-2 rounded border border-red-100 flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ form.errors.payment }}
                            </p>
                        </div>

                        <div class="pt-2">
                            <button 
                                type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl text-xs font-medium uppercase tracking-widest transition-all shadow-sm active:scale-98 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                :disabled="form.processing || validationStatus.includes('invalid') || isLoading.includes(true)"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-else>Selesaikan Pembayaran</span>
                            </button>
                            <p class="text-[9px] text-slate-400 text-center mt-3 font-normal">
                                Pastikan email kolektif valid sebelum memproses.
                            </p>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transisi untuk slot anggota */
.v-enter-active,
.v-leave-active {
  transition: all 0.3s ease;
}
.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>