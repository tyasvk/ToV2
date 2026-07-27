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
        // Mengirimkan email dan ID tryout untuk dicek oleh backend
        const response = await axios.post('/check-email-availability', { 
            email: emailValue,
            tryout_id: props.tryout.id 
        }, {
            signal: controller.signal
        });

        if (abortControllers.value[index] === controller) {
            // Jika sukses (status 200) dan belum pernah beli
            validationStatus.value.splice(index, 1, 'valid');
            errorMessage.value.splice(index, 1, response.data.message || 'Email terdaftar & valid');
        }
    } catch (error) {
        // Jika error (sudah beli [400] atau tidak terdaftar [404])
        if (!axios.isCancel(error) && abortControllers.value[index] === controller) {
            validationStatus.value.splice(index, 1, 'invalid');
            if (error.response && error.response.data && error.response.data.message) {
                // Mengambil pesan spesifik dari Laravel (misal: "Email ini sudah memiliki tryout ini.")
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

// Modifikasi agar Pop-Up muncul bebas hambatan
const handleOpenConfirm = () => {
    // Cegah jika ada email teman yang masih salah/loading
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
    router.visit(route('tryout.myTryouts'));
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
    
    if (qty === 2) discount = 2000;
    else if (qty === 3) discount = 6000;
    else if (qty === 4) discount = 16000;
    else if (qty >= 5) discount = 25000;

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
    <Head title="Pendaftaran Simulasi - CPNS Nusantara" />

    <AuthenticatedLayout>
        <!-- Container Utama: Lebih lega dan proporsional -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12 animate-in fade-in duration-500">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 lg:mb-10">
                <div>
                    <Link 
                        :href="route('tryout.show', props.tryout.id)" 
                        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-blue-600 transition-colors mb-4"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Kembali ke Detail
                    </Link>
                    <h1 class="text-2xl md:text-3xl text-slate-900 font-bold tracking-tight">
                        Selesaikan Pendaftaran
                    </h1>
                    <p class="text-sm md:text-base text-slate-500 mt-2">
                        Daftar mandiri atau tambahkan rekan tim untuk mendapatkan diskon khusus.
                    </p>
                </div>
            </div>

            <!-- Main Form Layout -->
            <form @submit.prevent="handleOpenConfirm" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                
                <!-- Kolom Kiri: Input Data -->
                <div class="lg:col-span-7 space-y-6 lg:space-y-8">
                    
                    <!-- Kotak Pendaftar Utama -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-900 mb-6 flex items-center gap-2">
                            Pendaftar Utama
                        </h3>
                        
                        <div class="flex items-center gap-5 bg-slate-50 p-4 rounded-2xl border border-slate-100/50">
                            <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-bold shrink-0">
                                {{ currentUser.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="text-base text-slate-900 font-semibold truncate">{{ currentUser.name }}</h4>
                                    <span class="text-xs font-medium bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full shrink-0">Anda</span>
                                </div>
                                <p class="text-sm text-slate-500 truncate">{{ currentUser.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kotak Anggota Tambahan -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-6">
                            <h3 class="text-lg font-semibold text-slate-900">
                                Anggota Tambahan (Opsional)
                            </h3>
                            <span class="text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                                {{ form.emails.length }} / 4 Slot Terisi
                            </span>
                        </div>

                        <div class="space-y-4">
                            <transition-group enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="transform opacity-100" leave-to-class="transform opacity-0">
                                
                                <div v-for="(email, index) in form.emails" :key="index" class="relative group">
                                    <div class="flex gap-4 items-start">
                                        <div class="mt-2.5 w-10 h-10 rounded-full bg-slate-50 border border-slate-200 text-slate-500 flex items-center justify-center text-sm font-semibold shrink-0">
                                            {{ index + 1 }}
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <div class="relative flex items-center">
                                                <input 
                                                    v-model="form.emails[index]"
                                                    type="email" 
                                                    class="w-full rounded-2xl px-4 py-3.5 pr-14 border-2 text-sm transition-all outline-none bg-slate-50 focus:bg-white"
                                                    :class="{
                                                        'border-transparent focus:border-blue-500 focus:ring-0': validationStatus[index] === null,
                                                        'border-emerald-300 bg-emerald-50/30 text-emerald-900 focus:border-emerald-500 focus:ring-0': validationStatus[index] === 'valid',
                                                        'border-red-300 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-0': validationStatus[index] === 'invalid'
                                                    }"
                                                    placeholder="Ketik email rekan yang sudah terdaftar..."
                                                    required
                                                    @input="checkEmail(index, $event.target.value)"
                                                />

                                                <!-- Icons & Action -->
                                                <div class="absolute right-3 flex items-center gap-2">
                                                    <svg v-if="isLoading[index]" class="animate-spin h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                    
                                                    <button v-else-if="!email" @click="removeParticipant(index)" type="button" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded-full hover:bg-red-50" title="Batal">
                                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                    </button>
                                                    
                                                    <template v-else>
                                                        <svg v-if="validationStatus[index] === 'valid'" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                        <button v-if="validationStatus[index] === 'invalid' || validationStatus[index] === 'valid'" @click="removeParticipant(index)" type="button" class="text-slate-400 hover:text-red-500 transition-colors p-1.5 rounded-full hover:bg-red-50 ml-1" title="Hapus">
                                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>

                                            <div class="mt-2 min-h-[20px]">
                                                <p v-if="validationStatus[index] === 'valid'" class="text-xs font-medium text-emerald-600">
                                                    {{ errorMessage[index] }}
                                                </p>
                                                <p v-if="validationStatus[index] === 'invalid'" class="text-xs font-medium text-red-500">
                                                    {{ errorMessage[index] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition-group>

                            <div v-if="form.emails.length === 0" class="text-center py-8 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                                <p class="text-sm text-slate-500">Anda dapat menambahkan maksimal 4 teman untuk didaftarkan bersama <br class="hidden sm:block" /> dan mendapat diskon khusus hingga Rp 25.000.</p>
                            </div>

                            <button v-if="form.emails.length < 4" type="button" @click="addParticipant" class="w-full mt-2 py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-600 text-sm font-semibold hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex items-center justify-center gap-2 group">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                <span>Tambah Anggota Tim</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Panel Tagihan & Pembayaran -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-8">
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100 flex flex-col gap-6">
                        
                        <!-- Ringkasan Produk -->
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 mb-4">Ringkasan Pesanan</h3>
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-100/50 space-y-3">
                                <h4 class="text-base text-slate-900 font-semibold leading-snug mb-1">{{ tryout.title }}</h4>
                                
                                <div class="flex justify-between text-sm text-slate-500">
                                    <span>Harga Akses</span>
                                    <span class="font-semibold text-slate-700">{{ formatRupiah(tryout.price) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-500">
                                    <span>Total Peserta</span>
                                    <span class="font-semibold text-slate-700">{{ form.emails.length + 1 }} Orang</span>
                                </div>
                                
                                <div v-if="groupDiscountAmount > 0" class="flex justify-between text-sm text-blue-600 font-semibold border-t border-slate-200/60 pt-3 mt-1">
                                    <span>Diskon Tim</span>
                                    <span>- {{ formatRupiah(groupDiscountAmount) }}</span>
                                </div>

                                <div v-if="form.voucher_code && isVoucherValid" class="flex justify-between text-sm text-emerald-600 font-semibold border-t border-slate-200/60 pt-3 mt-1">
                                    <span>Promo Voucher</span>
                                    <span>- {{ formatRupiah(2000) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Voucher Input -->
                        <div>
                            <label class="text-sm font-medium text-slate-700 mb-2 block">Punya kode voucher?</label>
                            <div class="relative">
                                <input 
                                    v-model="form.voucher_code"
                                    type="text" 
                                    placeholder="Masukkan kode promo"
                                    class="w-full px-4 py-3.5 bg-slate-50 border-2 border-transparent rounded-xl text-sm focus:ring-0 focus:border-blue-500 focus:bg-white transition-all uppercase"
                                    :class="{
                                        'border-red-300 focus:border-red-500 bg-red-50/20': voucherErrorMessage || form.errors.voucher_code,
                                        'border-emerald-300 focus:border-emerald-500 bg-emerald-50/20': isVoucherValid,
                                        'border-slate-100': !voucherErrorMessage && !form.errors.voucher_code && !isVoucherValid
                                    }"
                                />
                                <span v-if="isCheckingVoucher" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <svg class="animate-spin h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                            </div>
                            <p v-if="voucherErrorMessage || form.errors.voucher_code" class="text-xs text-red-500 font-medium mt-2">
                                {{ voucherErrorMessage || form.errors.voucher_code }}
                            </p>
                            <p v-else-if="isVoucherValid" class="text-xs text-emerald-600 font-medium mt-2">
                                Voucher valid! Potongan Rp 2.000 diterapkan.
                            </p>
                        </div>

                        <!-- Metode Pembayaran Tile -->
                        <div>
                            <label class="text-sm font-medium text-slate-700 mb-3 block">Pilih Metode Pembayaran</label>
                            <div class="space-y-3">
                                
                                <label class="relative block cursor-pointer group">
                                    <input type="radio" v-model="form.payment_method" value="wallet" class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-white group-hover:border-slate-200 transition-all peer-checked:border-blue-600 peer-checked:bg-blue-50/30">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-semibold text-sm text-slate-900">Saldo Dompet</span>
                                            <div class="w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center peer-checked:border-blue-600">
                                                <div v-if="form.payment_method === 'wallet'" class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-slate-500">
                                            <span>Sisa Saldo:</span>
                                            <span class="font-semibold text-emerald-600">
                                                {{ formatRupiah(currentUser.balance) }}
                                            </span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative block cursor-pointer group">
                                    <input type="radio" v-model="form.payment_method" value="midtrans" class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-white group-hover:border-slate-200 transition-all peer-checked:border-blue-600 peer-checked:bg-blue-50/30">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-semibold text-sm text-slate-900">Transfer / QRIS</span>
                                            <div class="w-5 h-5 rounded-full border-2 border-slate-300 bg-white flex items-center justify-center peer-checked:border-blue-600">
                                                <div v-if="form.payment_method === 'midtrans'" class="w-2.5 h-2.5 rounded-full bg-blue-600"></div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-slate-500">
                                            Virtual Account & E-Wallet
                                        </p>
                                    </div>
                                </label>

                            </div>
                            <p v-if="form.errors.payment" class="text-red-500 text-xs mt-3 font-medium flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ form.errors.payment }}
                            </p>
                        </div>

                        <!-- Grand Total & CTA -->
                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-sm font-medium text-slate-500">Total Tagihan</span>
                                <span class="text-2xl font-bold text-slate-900">{{ formatRupiah(totalAmount) }}</span>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-2xl text-sm font-semibold transition-all shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 hover:-translate-y-0.5 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                                :disabled="form.processing || validationStatus.includes('invalid') || isLoading.includes(true) || isCheckingVoucher"
                            >
                                <svg v-if="form.processing || isCheckingVoucher" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span v-else>Selesaikan Pembayaran</span>
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </div>

        <!-- Pop up Modal Konfirmasi Pembayaran -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 md:p-8 shadow-2xl animate-in zoom-in-95 duration-200">
                <h3 class="text-xl font-bold text-slate-900 mb-2">Konfirmasi Pembayaran</h3>
                <p class="text-sm text-slate-500 leading-relaxed mb-8">
                    Anda akan melanjutkan pembelian <span class="font-semibold text-slate-700">{{ tryout.title }}</span> dengan total <span class="font-semibold text-blue-600">{{ formatRupiah(totalAmount) }}</span>. Lanjutkan?
                </p>
                <div class="flex items-center gap-3">
                    <button @click="showConfirmModal = false" type="button" class="flex-1 py-3.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button @click="executeSubmit" type="button" class="flex-1 py-3.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-md">
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>

        <!-- Pop up Modal Pembayaran Sukses -->
        <div v-if="showSuccessModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-sm w-full p-8 md:p-10 text-center shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Pembayaran Berhasil!</h3>
                <p class="text-sm text-slate-500 leading-relaxed mb-8">
                    Pendaftaran tryout Anda sukses tercatat. Silakan buka halaman tryout Anda untuk mulai mengerjakan.
                </p>
                <button @click="goToMyTryouts" type="button" class="w-full py-4 rounded-2xl text-sm font-semibold text-white bg-slate-900 hover:bg-black transition-colors shadow-lg">
                    Buka Tryout Saya
                </button>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
/* Scrollbar dihilangkan dari scoped karena body tidak overflow di elemen ini, tapi tetap dipertahankan jika dibutuhkan global */
.custom-scrollbar::-webkit-scrollbar { height: 2px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

/* Transisi Bawaan Vue yang lebih mulus */
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