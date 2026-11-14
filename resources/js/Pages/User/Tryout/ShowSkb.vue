<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    category: Object,
    tryouts: Array,
});

const formatCurrency = (value) => {
    if (value === 0) return 'Gratis';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head :title="`Tryout SKB ${category.name} - CPNS Nusantara`" />

    <AuthenticatedLayout>
        <div class="w-full bg-[#F5F5F7] min-h-screen pb-24 md:pb-32 font-sans animate-in fade-in duration-500">
            <div class="max-w-3xl mx-auto sm:px-6 pt-6 sm:pt-10 space-y-6">

                <!-- Header -->
                <div class="px-4 sm:px-2 flex items-start gap-3">
                    <Link :href="route('tryout.skb.index')" class="w-8 h-8 mt-1 flex items-center justify-center rounded-full bg-black/5 hover:bg-black/10 transition-colors text-[#1D1D1F] shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                    </Link>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#AF52DE] bg-[#F5E6FF] px-2.5 py-0.5 rounded-md">
                            Formasi SKB
                        </span>
                        <h1 class="text-2xl font-bold text-[#1D1D1F] mt-1">{{ category.name }}</h1>
                        <p class="text-[14px] text-[#86868B] mt-0.5">{{ category.description }}</p>
                    </div>
                </div>

                <!-- Daftar Paket Tryout -->
                <div class="space-y-4 px-4 sm:px-0">
                    <template v-if="tryouts.length > 0">
                        <div v-for="tryout in tryouts" :key="tryout.id" class="bg-white rounded-[24px] border border-black/5 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            
                            <div class="space-y-1.5">
                                <h3 class="text-[17px] font-bold text-[#1D1D1F]">{{ tryout.title }}</h3>
                                <div class="flex flex-wrap items-center gap-3 text-[13px] text-[#86868B] font-medium">
                                    <span class="flex items-center gap-1">
                                        ⏱️ {{ tryout.duration }} Menit
                                    </span>
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        📝 {{ tryout.total_questions }} Soal
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 border-black/5 pt-3 sm:pt-0">
                                <div class="text-left sm:text-right">
                                    <p class="text-[11px] text-[#86868B] font-medium">Biaya Akses</p>
                                    <p class="text-[16px] font-bold text-[#1D1D1F]">
                                        {{ tryout.is_purchased ? 'Sudah Dimiliki' : formatCurrency(tryout.price) }}
                                    </p>
                                </div>

                                <!-- Tombol Aksi (Sesuaikan route dengan alur ujian/beli Anda) -->
                                <Link 
                                    v-if="tryout.is_purchased"
                                    :href="route('tryout.wait', tryout.id)" 
                                    class="px-5 py-2.5 bg-[#34C759] hover:bg-[#2DB34E] text-white rounded-full font-semibold text-[13px] transition-all active:scale-[0.98] shrink-0"
                                >
                                    Mulai Ujian
                                </Link>
                                <Link 
                                    v-else
                                    :href="route('tryout.register', tryout.id)" 
                                    class="px-5 py-2.5 bg-[#007AFF] hover:bg-[#0062CC] text-white rounded-full font-semibold text-[13px] transition-all active:scale-[0.98] shrink-0"
                                >
                                    Beli Paket
                                </Link>
                            </div>

                        </div>
                    </template>

                    <div v-else class="bg-white rounded-[24px] border border-black/5 p-10 text-center">
                        <p class="text-[#86868B] font-medium">Belum ada paket tryout untuk formasi ini.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>