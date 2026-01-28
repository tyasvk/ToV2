<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    tryout: Object
});

const qty = ref(2); // Default minimal kolektif

// Form data
const form = useForm({
    tryout_id: props.tryout.id,
    total_participants: qty,
    emails: ['', ''], // Array email untuk anggota (di luar penginput)
});

// Update jumlah field email saat qty berubah
watch(qty, (newQty) => {
    const currentEmails = [...form.emails];
    const needed = newQty - 1; // User sendiri tidak perlu input email
    
    if (currentEmails.length < needed) {
        // Tambah field
        for (let i = currentEmails.length; i < needed; i++) {
            currentEmails.push('');
        }
    } else {
        // Kurangi field
        currentEmails.splice(needed);
    }
    form.emails = currentEmails;
});

// Perhitungan Diskon
const pricing = computed(() => {
    const basePrice = parseFloat(props.tryout.price);
    let discountPercent = 0;
    
    if (qty.value === 2) discountPercent = 5;
    else if (qty.value === 3) discountPercent = 10;
    else if (qty.value === 4) discountPercent = 15;
    else if (qty.value >= 5) discountPercent = 20;

    const totalPrice = basePrice * qty.value;
    const discountAmount = totalPrice * (discountPercent / 100);
    
    return {
        percent: discountPercent,
        total: totalPrice - discountAmount,
        perPerson: (totalPrice - discountAmount) / qty.value
    };
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

const submit = () => {
    form.post(route('tryout.collective.store'));
};
</script>

<template>
    <Head title="Pendaftaran Kolektif" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter italic">Pendaftaran Kolektif</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Dapatkan diskon hingga 20% dengan mengajak rekan anda</p>
            </div>
        </template>

        <div class="py-12 bg-slate-50/50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                        <div class="mb-8">
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Konfigurasi Kelompok</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Pilih jumlah peserta dan lengkapi email anggota</p>
                        </div>

                        <div class="mb-10">
                            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-center">Jumlah Peserta</label>
                            <div class="flex gap-2 p-2 bg-slate-50 rounded-3xl border border-slate-100">
                                <button v-for="n in [2,3,4,5]" :key="n"
                                    @click="qty = n"
                                    type="button"
                                    :class="qty === n ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-400 hover:bg-indigo-50'"
                                    class="flex-1 py-4 rounded-2xl text-xs font-black transition-all active:scale-95 uppercase"
                                >
                                    {{ n }} Orang
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div v-for="(email, index) in form.emails" :key="index" class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase ml-4 tracking-widest">Email Anggota #{{ index + 2 }}</label>
                                <input 
                                    v-model="form.emails[index]"
                                    type="email" 
                                    required
                                    placeholder="contoh: rekan@email.com"
                                    class="w-full border-none bg-slate-50 rounded-[1.5rem] p-5 focus:ring-2 focus:ring-indigo-500/20 font-bold text-sm shadow-inner"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-slate-900 rounded-[3rem] p-8 text-white shadow-2xl sticky top-8">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-500 mb-6">Ringkasan Biaya</h4>
                        
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Paket</span>
                                <span class="text-xs font-black uppercase tracking-tighter text-right">{{ tryout.title }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Harga Satuan</span>
                                <span class="text-xs font-black">{{ formatPrice(tryout.price) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Diskon Grup ({{ pricing.percent }}%)</span>
                                <span class="text-xs font-black text-emerald-400">- {{ formatPrice(tryout.price * qty * (pricing.percent/100)) }}</span>
                            </div>
                            <div class="pt-4 border-t border-slate-800 flex justify-between items-end">
                                <span class="text-[10px] font-black text-slate-500 uppercase">Total Bayar</span>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-white leading-none tracking-tighter">{{ formatPrice(pricing.total) }}</p>
                                    <p class="text-[8px] font-bold text-slate-500 uppercase mt-1">Hanya {{ formatPrice(pricing.perPerson) }} / Orang</p>
                                </div>
                            </div>
                        </div>

                        <button 
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full py-5 bg-indigo-600 rounded-[2rem] text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-900/50 hover:bg-white hover:text-indigo-600 transition-all active:scale-95 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Memproses...' : 'Lanjut Pembayaran' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Sembunyikan scrollbar untuk pengalaman UI yang lebih clean */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>