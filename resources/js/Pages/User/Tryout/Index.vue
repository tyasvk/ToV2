<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tryouts: [Object, Array], 
});

// 1. Ambil data list tryout
const tryoutList = computed(() => {
    if (props.tryouts && props.tryouts.data) return props.tryouts.data;
    return Array.isArray(props.tryouts) ? props.tryouts : [];
});

// 2. Cek apakah user sudah daftar (punya transaksi lunas/success)
const isRegistered = (tryout) => {
    return tryout.transactions && tryout.transactions.length > 0;
};

// 3. Cek apakah waktu sekarang sudah masuk jadwal mulai
const isStarted = (startTime) => {
    if (!startTime) return true;
    return new Date() >= new Date(startTime);
};

// 4. Format Tanggal untuk info di Card
const formatDateTime = (dateTime) => {
    if (!dateTime) return 'Kapan saja';
    return new Date(dateTime).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
</script>

<template>
    <Head title="Daftar Tryout" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="tryoutList.length === 0" class="py-20 text-center bg-white rounded-3xl border border-dashed border-slate-300">
                    <p class="text-slate-500 font-bold">Belum ada Tryout tersedia.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="tryout in tryoutList" :key="tryout.id" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                        
                        <div class="h-32 bg-gradient-to-br from-slate-800 to-[#004a87] p-4 relative">
                            <div class="absolute top-3 right-3">
                                <span class="px-2 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase rounded">{{ tryout.price == 0 ? 'Gratis' : 'Premium' }}</span>
                            </div>
                            <h3 class="text-white font-bold text-lg mt-8 line-clamp-2 leading-tight">{{ tryout.title }}</h3>
                        </div>

                        <div class="p-5 flex-1 flex flex-col">
                            <div class="space-y-2 mb-6 text-left">
                                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500 uppercase">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ tryout.duration_minutes }} Menit | CAT BKN
                                </div>
                                <div class="p-2 bg-blue-50 rounded-lg border border-blue-100 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-[10px] font-black text-blue-700 uppercase">Akses: {{ formatDateTime(tryout.start_time) }}</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t flex items-center justify-between">
                                <div class="text-left">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Harga</p>
                                    <p class="text-sm font-black text-slate-900">{{ tryout.price > 0 ? formatRupiah(tryout.price) : 'Rp 0' }}</p>
                                </div>

                                <Link v-if="!isRegistered(tryout)" 
                                    :href="route('tryout.register', tryout.id)"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase rounded-xl transition-all shadow-md active:scale-95"
                                >
                                    Daftar
                                </Link>

                                <button v-else-if="isRegistered(tryout) && !isStarted(tryout.start_time)"
                                    disabled
                                    class="px-5 py-2.5 bg-slate-200 text-slate-400 text-xs font-black uppercase rounded-xl cursor-not-allowed border border-slate-300"
                                >
                                    Belum Dimulai
                                </button>

                                <Link v-else
                                    :href="route('tryout.wait', tryout.id)"
                                    class="px-5 py-2.5 bg-[#004a87] hover:bg-blue-800 text-white text-xs font-black uppercase rounded-xl transition-all shadow-md active:scale-95"
                                >
                                    Mulai Ujian
                                </Link>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>