<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
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

// State Visual & Validasi
const validationStatus = ref([]); 
const isLoading = ref([]);
const errorMessage = ref([]);
const abortControllers = ref([]);

// State Pop-up & Voucher
const showConfirmModal = ref(false);
const showSuccessModal = ref(false);
const voucherErrorMessage = ref('');
const isCheckingVoucher = ref(false);
const isVoucherValid = ref(false);

// Format Rupiah
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(num);

const addParticipant = () => {
    if (form.emails.length < 4) {
        form.emails.push('');
        validationStatus.value.push(null);
        isLoading.value.push(false);
        abortControllers.value.push(null);
        errorMessage.value.push(null);
    }
};

const removeParticipant = (index) => {
    form.emails.splice(index, 1);
    validationStatus.value.splice(index, 1);
    isLoading.value.splice(index, 1);
    abortControllers.value.splice(index, 1);
    errorMessage.value.splice(index, 1);
};

// ==========================================
// PENGECEKAN EMAIL & VALIDASI PEMBELIAN GANDA
// ==========================================
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
        const response = await axios.post('/check-email-availability', { 
            email: emailValue,
            tryout_id: props.tryout.id 
        }, {
            signal: controller.signal
        });

        if (abortControllers.value[index] === controller) {
            validationStatus.value.splice(index, 1, 'valid');
            errorMessage.value.splice(index, 1, response.data.message || 'Email terdaftar & valid');
        }
    } catch (error) {
        if (!axios.isCancel(error) && abortControllers.value[index] === controller) {
            validationStatus.value.splice(index, 1, 'invalid');
            if (error.response && error.response.data && error.response.data.message) {
                errorMessage.value.splice(index, 1, error.response.data.message);
            } else {
                errorMessage.value.splice(index, 1, 'Error memvalidasi email');
            }
        }
    } finally {
        if (abortControllers.value[index] === controller) {
            isLoading.value.splice(index, 1, false);
        }
    }
};

const handleOpenConfirm = () => {
    if (validationStatus.value.includes('invalid') || isCheckingVoucher.value) {
        return;
    }
    form.clearErrors();
    showConfirmModal.value = true;
};

const executeSubmit = () => {
    showConfirmModal.value = false;
    
    form.post(route('tryout.processRegistration', props.tryout.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

const goToMyTryouts = () => {
    showSuccessModal.value = false;
    router.visit(route('tryout.my')); // Diperbaiki: route myTryouts di web.php Anda bernama 'tryout.my'
};

// ==========================================
// PENGECEKAN KODE VOUCHER STRICT
// ==========================================
let voucherTimeout = null;
watch(() => form.voucher_code, (newCode) => {
    clearTimeout(voucherTimeout);
    voucherErrorMessage.value = '';
    isVoucherValid.value = false; 
    form.clearErrors('voucher_code');

    const cleanCode = newCode ? newCode.trim() : '';
    if (cleanCode.length === 0) {
        return; 
    }

    voucherTimeout = setTimeout(async () => {
        isCheckingVoucher.value = true;
        try {
            const response = await axios.post(route('voucher.check'), { voucher_code: cleanCode });
            if (response.data.valid) {
                isVoucherValid.value = true; 
            } else {
                isVoucherValid.value = false;
                voucherErrorMessage.value = response.data.message; 
            }
        } catch (error) {
            isVoucherValid.value = false;
            voucherErrorMessage.value = 'Gagal memvalidasi kode. Sistem sibuk.';
        } finally {
            isCheckingVoucher.value = false;
        }
    }, 600);
});

// Perhitungan Diskon Kelompok
const groupDiscountAmount = computed(() => {
    const qty = form.emails.length + 1;
    let discount = 0;
    
    if (qty === 2) discount = 0.10 * (props.tryout.price * 2);
    else if (qty === 3) discount = 0.15 * (props.tryout.price * 3);
    else if (qty === 4) discount = 0.20 * (props.tryout.price * 4);
    else if (qty >= 5) discount = 0.25 * (props.tryout.price * qty);

    return discount;
});

// Total Akhir
const totalAmount = computed(() => {
    const qty = form.emails.length + 1;
    const baseAmount = props.tryout.price * qty;
    const priceAfterGroupDiscount = baseAmount - groupDiscountAmount.value;

    let voucherDiscount = 0;
    if (form.voucher_code && form.voucher_code.trim().length > 0 && isVoucherValid.value) {
        voucherDiscount = 2000;
    }

    return Math.max(0, priceAfterGroupDiscount - voucherDiscount);
});
</script>

<template>
    <Head :title="`Beli Premium - ${tryout.title}`" />

    <AuthenticatedLayout>
        <!-- Background transparan menyatu dengan layout utama -->
        <div class="min-h-screen bg-transparent w-full pb-24 md:pb-12 animate-in fade-in duration-500 overflow-x-hidden">
            
            <!-- Menggunakan max-w-5xl agar 2 kolom proporsional, padding disamakan dengan Katalog -->
            <div class="max-w-5xl mx-auto px-3 sm:px-4 md:px-5 pt-4 md:pt-6 space-y-4">
                
                <!-- HEADER & KEMBALI -->
                <div class="flex flex-col gap-2 mb-2">
                    <Link :href="route('tryout.show', props.tryout.id)" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity self-start">
                        &larr; Batal & Kembali
                    </Link>

                    <div>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none mt-2">Beli Akses Premium</h1>
                        <p class="text-[12px] md:text-[13px] text-slate-500 font-medium mt-1">
                            Lakukan pembayaran untuk membuka pembahasan, peringkat, dan sertifikat.
                        </p>
                    </div>
                </div>

                <!-- PESAN ERROR (JIKA ADA DARI SERVER) -->
                <div v-if="form.errors.message || form.errors.payment" class="bg-rose-50 border border-rose-100 text-rose-600 rounded-[16px] p-4 text-[13px] font-semibold flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>{{ form.errors.message || form.errors.payment }}</span>
                </div>

                <!-- MAIN FORM LAYOUT -->
                <form @submit.prevent="handleOpenConfirm" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                    
                    <!-- ============================================== -->
                    <!-- KOLOM KIRI (DATA PESERTA & PAKET)              -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-7 space-y-4">
                        
                        <!-- 1. DETAIL PAKET & PENDAFTAR -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50">
                            
                            <!-- Header Paket -->
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-4 relative overflow-hidden">
                                <div class="w-10 h-10 rounded-full bg-[#F0F4FF] text-[#007AFF] flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-0.5">Paket Ujian</p>
                                    <h2 class="text-[14px] md:text-[15px] font-bold text-slate-900 leading-snug">{{ tryout.title }}</h2>
                                </div>
                                <div class="text-right">
                                    <p class="text-[#007AFF] font-bold text-[14px]">{{ formatRupiah(tryout.price) }}</p>
                                    <p class="text-slate-400 text-[10px] font-medium">/ orang</p>
                                </div>
                            </div>

                            <!-- Pendaftar Utama -->
                            <h3 class="text-[13px] font-bold text-slate-900 mb-2.5 uppercase tracking-widest">Pendaftar Utama</h3>
                            <div class="flex items-center gap-3 bg-[#F5F5F7]/80 p-3 rounded-[16px] border border-slate-50">
                                <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[15px] font-bold shrink-0">
                                    {{ currentUser.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <h4 class="text-[13px] text-slate-900 font-bold truncate">{{ currentUser.name }}</h4>
                                        <span class="text-[9px] font-bold bg-[#007AFF] text-white px-2 py-0.5 rounded-full shrink-0">ANDA</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 font-medium truncate">{{ currentUser.email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ANGGOTA TAMBAHAN (DISKON) -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-4">
                                <div>
                                    <h3 class="text-[14px] font-semibold text-slate-900">Beli Kolektif (Diskon s/d 25%)</h3>
                                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Daftarkan email rekan (Opsional).</p>
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 bg-[#F5F5F7] px-2.5 py-1 rounded-full w-fit">
                                    {{ form.emails.length }} / 4 Slot
                                </span>
                            </div>

                            <div class="space-y-3">
                                <transition-group name="list" tag="div" class="space-y-3">
                                    <div v-for="(email, index) in form.emails" :key="index" class="relative">
                                        <div class="flex gap-2.5 items-start">
                                            <div class="mt-1.5 w-7 h-7 rounded-full bg-[#F5F5F7] text-slate-500 flex items-center justify-center text-[11px] font-bold shrink-0">
                                                {{ index + 1 }}
                                            </div>
                                            
                                            <div class="flex-1 relative">
                                                <input 
                                                    v-model="form.emails[index]"
                                                    type="email" 
                                                    class="w-full rounded-[12px] px-3.5 py-2.5 pr-12 border text-[13px] font-medium transition-all outline-none focus:bg-white"
                                                    :class="{
                                                        'bg-[#F5F5F7] border-transparent focus:border-[#007AFF]': validationStatus[index] === null,
                                                        'bg-emerald-50/50 border-emerald-300 text-emerald-900 focus:border-emerald-500': validationStatus[index] === 'valid',
                                                        'bg-rose-50/50 border-rose-300 text-rose-900 focus:border-rose-500': validationStatus[index] === 'invalid'
                                                    }"
                                                    placeholder="Ketik email terdaftar..."
                                                    required
                                                    @input="checkEmail(index, $event.target.value)"
                                                />

                                                <!-- Icons -->
                                                <div class="absolute right-2.5 top-2.5 flex items-center gap-1.5">
                                                    <svg v-if="isLoading[index]" class="animate-spin h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    <template v-else>
                                                        <svg v-if="validationStatus[index] === 'valid'" class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                        <button @click="removeParticipant(index)" type="button" class="text-slate-400 hover:text-rose-500 p-1 rounded-full transition-colors">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Validation Msg -->
                                        <div class="pl-9 mt-1 min-h-[16px]">
                                            <p v-if="validationStatus[index] === 'valid'" class="text-[10px] font-bold text-emerald-600">{{ errorMessage[index] }}</p>
                                            <p v-if="validationStatus[index] === 'invalid'" class="text-[10px] font-bold text-rose-500">{{ errorMessage[index] }}</p>
                                        </div>
                                    </div>
                                </transition-group>

                                <button v-if="form.emails.length < 4" type="button" @click="addParticipant" class="w-full py-3 border-2 border-dashed border-[#E3E3E8] hover:border-[#007AFF] bg-[#F5F5F7]/50 hover:bg-[#F0F4FF] rounded-[16px] text-slate-600 hover:text-[#007AFF] text-[12px] font-bold transition-all flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                    Tambah Rekan Tim
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================== -->
                    <!-- KOLOM KANAN (METODE BAYAR, VOUCHER & TOTAL)    -->
                    <!-- ============================================== -->
                    <div class="lg:col-span-5 space-y-4">
                        
                        <!-- 3. METODE BAYAR & VOUCHER -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 flex flex-col gap-4">
                            
                            <!-- Voucher -->
                            <div>
                                <h3 class="text-[14px] font-semibold text-slate-900 mb-2">Kode Voucher / Afiliasi</h3>
                                <div class="relative">
                                    <input 
                                        v-model="form.voucher_code"
                                        type="text" 
                                        placeholder="KODE PROMO"
                                        class="w-full bg-[#F5F5F7] border-transparent rounded-[12px] px-3.5 py-3 pr-10 text-[13px] font-bold uppercase focus:bg-white focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 outline-none transition-all text-slate-900 tracking-wider"
                                        :class="{
                                            'border-rose-300 focus:border-rose-500 bg-rose-50/20': voucherErrorMessage || form.errors.voucher_code,
                                            'border-emerald-300 focus:border-emerald-500 bg-emerald-50/20': isVoucherValid,
                                        }"
                                    />
                                    <span v-if="isCheckingVoucher" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </span>
                                </div>
                                <p v-if="voucherErrorMessage || form.errors.voucher_code" class="text-[10px] text-rose-500 font-bold mt-1.5 ml-1">
                                    {{ voucherErrorMessage || form.errors.voucher_code }}
                                </p>
                                <p v-else-if="isVoucherValid" class="text-[10px] text-emerald-600 font-bold mt-1.5 ml-1">
                                    Voucher valid! Potongan diterapkan.
                                </p>
                            </div>

                            <!-- Payment -->
                            <div>
                                <h3 class="text-[14px] font-semibold text-slate-900 mb-2 mt-1">Metode Pembayaran</h3>
                                <div class="flex flex-col gap-2.5">
                                    <label class="flex items-center justify-between p-3 border rounded-[16px] cursor-pointer transition-all peer group" :class="form.payment_method === 'wallet' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-slate-100 bg-[#F5F5F7] hover:border-slate-200'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" v-model="form.payment_method" value="wallet" class="hidden">
                                            <div class="w-4 h-4 rounded-full border-[1.5px] flex items-center justify-center shrink-0" :class="form.payment_method === 'wallet' ? 'border-[#007AFF]' : 'border-slate-300'">
                                                <div v-if="form.payment_method === 'wallet'" class="w-2 h-2 bg-[#007AFF] rounded-full"></div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[13px] font-bold text-slate-900 leading-tight">Saldo Dompet</span>
                                                <span class="text-[10px] font-medium text-slate-500 mt-0.5">Sisa: <span class="font-bold" :class="currentUser.balance >= totalAmount ? 'text-emerald-600' : 'text-rose-500'">{{ formatRupiah(currentUser.balance) }}</span></span>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="flex items-center justify-between p-3 border rounded-[16px] cursor-pointer transition-all peer group" :class="form.payment_method === 'midtrans' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-slate-100 bg-[#F5F5F7] hover:border-slate-200'">
                                        <div class="flex items-center gap-3">
                                            <input type="radio" v-model="form.payment_method" value="midtrans" class="hidden">
                                            <div class="w-4 h-4 rounded-full border-[1.5px] flex items-center justify-center shrink-0" :class="form.payment_method === 'midtrans' ? 'border-[#007AFF]' : 'border-slate-300'">
                                                <div v-if="form.payment_method === 'midtrans'" class="w-2 h-2 bg-[#007AFF] rounded-full"></div>
                                            </div>
                                            <span class="text-[13px] font-bold text-slate-900">Transfer / QRIS</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <!-- 4. RINGKASAN TOTAL -->
                        <div class="bg-white rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.03)] border border-slate-100/50 overflow-hidden">
                            <div class="p-4 sm:p-5 bg-slate-900 text-white relative">
                                <!-- Dekorasi -->
                                <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-500 rounded-full blur-[40px] opacity-30"></div>
                                
                                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3 relative z-10">Rincian Tagihan</h3>
                                
                                <div class="space-y-2 text-[12px] font-medium border-b border-slate-700 pb-3 mb-3 relative z-10">
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-300">Harga Paket ({{ form.emails.length + 1 }}x)</span>
                                        <span>{{ formatRupiah(tryout.price * (form.emails.length + 1)) }}</span>
                                    </div>
                                    <div v-if="groupDiscountAmount > 0" class="flex justify-between items-center text-emerald-400">
                                        <span>Diskon Tim</span>
                                        <span>- {{ formatRupiah(groupDiscountAmount) }}</span>
                                    </div>
                                    <div v-if="isVoucherValid" class="flex justify-between items-center text-amber-400">
                                        <span>Potongan Voucher</span>
                                        <span>- {{ formatRupiah(2000) }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-end relative z-10">
                                    <span class="text-[12px] font-medium text-slate-300">Total Akhir</span>
                                    <span class="text-[26px] font-black leading-none tracking-tight">{{ formatRupiah(totalAmount) }}</span>
                                </div>
                            </div>
                            
                            <div class="p-3 bg-white">
                                <button 
                                    type="submit" 
                                    class="w-full py-3.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-[14px] text-[13px] font-bold transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
                                    :disabled="form.processing || validationStatus.includes('invalid') || isLoading.includes(true) || isCheckingVoucher"
                                >
                                    <svg v-if="form.processing || isCheckingVoucher" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span v-else>Bayar Sekarang</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>

        <!-- ============================================== -->
        <!-- POP-UP MODAL KONFIRMASI PEMBAYARAN             -->
        <!-- ============================================== -->
        <Teleport to="body">
            <div v-if="showConfirmModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-[24px] max-w-sm w-full p-6 text-center shadow-2xl animate-in zoom-in-95 duration-200">
                    <h3 class="text-[18px] font-bold text-slate-900 mb-2 tracking-tight">Konfirmasi Pembayaran</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed mb-6 font-medium">
                        Anda akan membayar <span class="font-bold text-slate-800">{{ tryout.title }}</span> dengan total <span class="font-bold text-[#007AFF]">{{ formatRupiah(totalAmount) }}</span>. Lanjutkan?
                    </p>
                    <div class="flex items-center gap-2.5">
                        <button @click="showConfirmModal = false" type="button" class="flex-1 py-3 rounded-full text-[13px] font-bold text-slate-600 bg-[#F2F2F7] hover:bg-[#E3E3E8] transition-colors active:scale-95">
                            Batal
                        </button>
                        <button @click="executeSubmit" type="button" class="flex-1 py-3 rounded-full text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] transition-colors shadow-sm active:scale-95">
                            Ya, Bayar
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ============================================== -->
        <!-- POP-UP MODAL SUKSES                            -->
        <!-- ============================================== -->
        <Teleport to="body">
            <div v-if="showSuccessModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-[24px] max-w-sm w-full p-8 text-center shadow-2xl animate-in zoom-in-95 duration-300">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-8 h-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 class="text-[18px] font-bold text-slate-900 mb-2 tracking-tight">Pembayaran Berhasil!</h3>
                    <p class="text-[13px] text-slate-500 leading-relaxed mb-6 font-medium">
                        Pendaftaran sukses. Silakan buka tab "Milik Saya" untuk mulai mengerjakan tryout.
                    </p>
                    <button @click="goToMyTryouts" type="button" class="w-full py-3 rounded-full text-[13px] font-bold text-white bg-slate-900 hover:bg-black transition-colors shadow-sm active:scale-95">
                        Buka Tryout Saya
                    </button>
                </div>
            </div>
        </Teleport>

    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

.list-enter-active,
.list-leave-active {
    transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>