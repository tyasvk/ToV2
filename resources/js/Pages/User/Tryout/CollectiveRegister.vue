<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ tryout: Object });

const form = useForm({
    payment_method: 'wallet',
    emails: ['', ''], 
});

const validationStatus = ref([null, null]); 
const isLoading = ref([false, false]);
const errorMessage = ref([null, null]);
const abortControllers = ref([null, null]);

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

const checkEmail = async (index, email) => {
    validationStatus.value.splice(index, 1, null);
    if (abortControllers.value[index]) abortControllers.value[index].abort();
    if (!email) return;

    isLoading.value.splice(index, 1, true);
    const controller = new AbortController();
    abortControllers.value[index] = controller;

    try {
        const response = await axios.post(route('api.check.email'), { email }, { signal: controller.signal });
        if (abortControllers.value[index] === controller) {
            const exists = response.data.exists;
            validationStatus.value.splice(index, 1, exists ? 'valid' : 'invalid');
            errorMessage.value.splice(index, 1, exists ? null : 'User tidak ditemukan');
        }
    } catch (error) {
        if (!axios.isCancel(error)) validationStatus.value.splice(index, 1, 'invalid');
    } finally {
        if (abortControllers.value[index] === controller) isLoading.value.splice(index, 1, false);
    }
};

const submit = () => form.post(route('tryout.register.store', props.tryout.id));
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
</script>

<template>
    <Head title="Pendaftaran Kolektif" />
    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-3xl mx-auto px-4">
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden border border-slate-100">
                    <div class="bg-[#004a87] p-8 text-white relative">
                        <div class="relative z-10">
                            <h2 class="text-2xl font-black uppercase tracking-tight mb-2">Pendaftaran Kolektif</h2>
                            <p class="text-blue-100 text-sm font-medium">{{ tryout.title }}</p>
                        </div>
                        <div class="absolute right-0 bottom-0 opacity-10 translate-y-1/4 translate-x-1/4">
                            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8">
                        <div class="mb-10">
                            <div class="flex items-center justify-between mb-6">
                                <label class="text-sm font-black text-slate-800 uppercase tracking-widest">Daftar Peserta</label>
                                <span class="text-[10px] font-bold px-3 py-1 bg-blue-50 text-blue-600 rounded-full border border-blue-100">MAKS. 5 ORANG</span>
                            </div>

                            <div class="space-y-4">
                                <div class="relative group">
                                    <input type="email" :value="$page.props.auth.user.email" disabled 
                                        class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-12 pr-4 py-3.5 text-sm text-slate-500 cursor-not-allowed font-medium" />
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                    </div>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md border border-emerald-100 uppercase">Ketua Tim</span>
                                </div>

                                <div v-for="(email, index) in form.emails" :key="index" class="flex gap-3 items-start">
                                    <div class="relative flex-1 group">
                                        <input v-model="form.emails[index]" type="email" required
                                            @input="checkEmail(index, form.emails[index])"
                                            class="w-full rounded-2xl pl-12 pr-4 py-3.5 text-sm transition-all duration-300 border-slate-200 focus:ring-4 focus:ring-blue-50 focus:border-[#004a87]"
                                            :class="{'border-red-300 bg-red-50/30': validationStatus[index] === 'invalid', 'border-emerald-300 bg-emerald-50/30': validationStatus[index] === 'valid'}"
                                            placeholder="Masukkan email peserta..." />
                                        
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                                            <svg v-if="isLoading[index]" class="animate-spin h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                            <svg v-else-if="validationStatus[index] === 'valid'" class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <svg v-else-if="validationStatus[index] === 'invalid'" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                                            <svg v-else class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                                        </div>
                                    </div>
                                    <button @click="removeParticipant(index)" type="button" class="mt-2 p-2 text-slate-300 hover:text-red-500 transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>

                                <button v-if="form.emails.length < 4" @click="addParticipant" type="button" 
                                    class="w-full py-3 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-50 hover:border-blue-200 hover:text-blue-500 transition-all">
                                    + Tambah Anggota
                                </button>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100 mb-8">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-slate-500">Total Biaya</span>
                                <span class="text-lg font-black text-[#004a87]">{{ formatRupiah(tryout.price * (form.emails.length + 1)) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase">Metode</span>
                                <select v-model="form.payment_method" class="bg-transparent border-none text-xs font-bold text-slate-600 focus:ring-0 p-0 cursor-pointer">
                                    <option value="wallet">Saldo Dompet</option>
                                    <option value="midtrans">QRIS / Transfer</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing || validationStatus.includes('invalid')"
                            class="w-full py-4 bg-[#004a87] text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-blue-200 hover:bg-blue-800 transition-all disabled:opacity-50 active:scale-95">
                            {{ form.processing ? 'Sedang Memproses...' : 'Daftar & Bayar Sekarang' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>