<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    tryout: Object
});

const form = useForm({
    emails: []
});

const emailErrors = reactive([]);
const isValidating = reactive([]);

const totalParticipants = computed(() => 1 + form.emails.length);
const maxAdditionalParticipants = 5;

const addParticipant = () => {
    if (form.emails.length < maxAdditionalParticipants) {
        form.emails.push('');
        emailErrors.push(null);
        isValidating.push(false);
    }
};

const removeParticipant = (index) => {
    form.emails.splice(index, 1);
    emailErrors.splice(index, 1);
    isValidating.splice(index, 1);
};

let debounceTimer = null;
const validateEmail = (index, email) => {
    emailErrors[index] = null;
    if (!email || !email.includes('@')) return;

    clearTimeout(debounceTimer);
    isValidating[index] = true;

    debounceTimer = setTimeout(() => {
        axios.post(route('api.check.email'), { email: email })
            .then(() => {
                emailErrors[index] = null;
            })
            .catch(error => {
                if (error.response && error.response.status === 404) {
                    emailErrors[index] = "Peserta tidak terdaftar di CPNS Nusantara.";
                } else {
                     emailErrors[index] = "Format email tidak valid.";
                }
            })
            .finally(() => {
                isValidating[index] = false;
            });
    }, 500);
};

const calculation = computed(() => {
    const qty = totalParticipants.value;
    const price = props.tryout.price || 0;
    let discount = 0;
    
    if (qty === 2) discount = 0.05;
    else if (qty === 3) discount = 0.10;
    else if (qty === 4) discount = 0.15;
    else if (qty >= 5) discount = 0.20;

    const totalNormal = price * qty;
    const totalDiscount = totalNormal * discount;
    const finalPrice = totalNormal - totalDiscount;

    return {
        unitPrice: price,
        discountPercent: discount * 100,
        totalDiscount: totalDiscount,
        finalPrice: finalPrice
    };
});

const formatPrice = (p) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(p);

const hasValidationErrors = computed(() => {
     return emailErrors.some(error => error !== null) || (form.emails.length > 0 && form.emails.some(email => email === ''));
});

const submit = () => {
    if(hasValidationErrors.value) return;
    form.transform((data) => ({
        ...data,
        qty: totalParticipants.value
    })).post(route('tryout.register.store', props.tryout.id));
};
</script>

<template>
    <Head title="Pendaftaran" />

    <AuthenticatedLayout>
        <template #header></template>

        <div class="bg-slate-50/50 min-h-screen pt-0">
            <div class="max-w-2xl mx-auto px-0 sm:px-4">
                
                <div class="bg-white rounded-t-[3rem] sm:rounded-[3rem] p-6 sm:p-10 shadow-2xl border-x border-b sm:border border-slate-100 overflow-hidden relative z-10 -mt-2 sm:-mt-8">
                    
                    <div class="mb-8 mt-2">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-[8px] font-black text-indigo-500 uppercase tracking-[0.3em] bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Checkout System</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight leading-tight">{{ tryout.title }}</h3>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 sm:space-y-8">
                        
                        <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 border-dashed flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs">👤</div>
                            <p class="text-[9px] sm:text-[10px] font-bold text-indigo-900 uppercase tracking-wide flex-1 leading-tight">
                                Anda terdaftar sebagai <span class="font-black text-indigo-600">Peserta #1</span> (Ketua)
                            </p>
                        </div>

                        <div class="space-y-4">
                             <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Anggota Kelompok</label>
                            
                             <div v-for="(email, index) in form.emails" :key="index" class="relative group animate-in slide-in-from-bottom-2 duration-300">
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input 
                                            v-model="form.emails[index]"
                                            @input="validateEmail(index, form.emails[index])"
                                            type="email" 
                                            required 
                                            placeholder="Email peserta terdaftar..." 
                                            :class="[emailErrors[index] ? 'border-red-300 bg-red-50 focus:ring-red-200' : 'border-slate-100 bg-slate-50 focus:ring-indigo-500/20']"
                                            class="w-full border rounded-2xl p-4 pr-10 font-bold text-sm focus:ring-2 transition-all outline-none" 
                                        />
                                        <div v-if="isValidating[index]" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <svg class="animate-spin h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </div>
                                    </div>

                                    <button type="button" @click="removeParticipant(index)" class="p-4 bg-red-50 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                                    </button>
                                </div>
                                <p v-if="emailErrors[index]" class="text-[10px] font-black text-red-500 mt-2 ml-2 tracking-wide italic">
                                    ! {{ emailErrors[index] }}
                                </p>
                            </div>

                            <button 
                                type="button"
                                @click="addParticipant"
                                :disabled="form.emails.length >= maxAdditionalParticipants"
                                class="w-full py-4 border-2 border-dashed border-slate-200 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:border-indigo-400 hover:text-indigo-500 transition-all active:scale-95 disabled:opacity-30 disabled:grayscale flex items-center justify-center gap-2"
                            >
                                <span class="text-lg">+</span> Tambah Peserta {{ form.emails.length >= maxAdditionalParticipants ? '(Maksimal)' : '' }}
                            </button>
                        </div>

                        <div class="bg-slate-900 rounded-[2rem] p-6 sm:p-8 text-white shadow-2xl shadow-indigo-100">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">Total Slot</span>
                                <span class="text-xs font-black">{{ totalParticipants }} Peserta</span>
                            </div>
                            <div class="flex justify-between items-center mb-4 border-b border-white/5 pb-4">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">Biaya Dasar</span>
                                <span class="text-xs font-black text-slate-300">{{ formatPrice(calculation.unitPrice) }}</span>
                            </div>
                            <div v-if="calculation.discountPercent > 0" class="flex justify-between items-center mb-4 text-emerald-400">
                                <span class="text-[9px] font-black uppercase tracking-widest">Potongan Kolektif ({{ calculation.discountPercent }}%)</span>
                                <span class="text-xs font-black">- {{ formatPrice(calculation.totalDiscount) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em]">Total Pembayaran</span>
                                <span class="text-xl font-black text-indigo-400">{{ formatPrice(calculation.finalPrice) }}</span>
                            </div>
                        </div>

                        <button 
                            type="submit" 
                            :disabled="form.processing || hasValidationErrors" 
                            class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.3em] hover:bg-indigo-600 transition-all shadow-xl active:scale-95 disabled:opacity-50 disabled:grayscale"
                        >
                            {{ hasValidationErrors ? 'Perbaiki Data' : 'Konfirmasi Pendaftaran' }}
                        </button>
                    </form>
                </div>
                <p class="text-[8px] sm:text-[9px] text-center text-slate-300 font-bold uppercase tracking-widest mt-6 mb-12">
                    Maksimal 6 peserta dalam 1 transaksi pendaftaran kelompok.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-all {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Memastikan konten yang naik tidak tertutup header */
.z-10 {
    z-index: 10;
}
</style>