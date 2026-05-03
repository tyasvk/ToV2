<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ tryouts: Object });

const deleteEvent = (id) => {
    if(confirm('Yakin ingin menghapus event ini?')) {
        router.delete(route('admin.tryout-akbar.destroy', id), { preserveScroll: true });
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric', 
        hour: '2-digit', 
        minute:'2-digit' 
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};
</script>

<template>
    <Head title="Kelola Event Akbar" />
    <AuthenticatedLayout>
        <div class="space-y-8 md:space-y-12 animate-in fade-in duration-700">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-1">
                <div class="space-y-2">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight uppercase leading-none">Event Tryout Akbar</h1>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em] mt-2">Manajemen event skala besar dan verifikasi peserta</p>
                </div>
                <Link 
                    :href="route('admin.tryout-akbar.create')" 
                    class="inline-flex items-center justify-center gap-2 bg-slate-900 text-white px-8 py-4 rounded-2xl text-[10px] font-medium uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-200 active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Event Baru
                </Link>
            </div>

            <div class="space-y-4 md:space-y-6 pb-20">
                <div v-if="tryouts.data.length > 0" class="grid grid-cols-1 gap-4">
                    <div 
                        v-for="tryout in tryouts.data" 
                        :key="tryout.id" 
                        class="bg-white rounded-[2rem] p-6 md:p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 flex flex-col lg:flex-row gap-8"
                    >
                        <div class="flex-1 space-y-4">
                            <div class="flex items-center gap-3">
                                <span 
                                    :class="tryout.is_published ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'"
                                    class="px-3 py-1 rounded-lg text-[8px] font-medium uppercase tracking-widest border"
                                >
                                    {{ tryout.is_published ? 'Published' : 'Draft' }}
                                </span>
                                <span class="text-[9px] text-slate-400 font-medium uppercase tracking-widest">{{ tryout.duration }} Menit</span>
                            </div>

                            <h3 class="text-lg font-medium text-slate-900 uppercase tracking-tight leading-snug">
                                {{ tryout.title }}
                            </h3>

                            <div class="flex flex-wrap gap-5">
                                <div class="flex items-center gap-2 text-slate-500">
                                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="text-[10px] font-medium uppercase tracking-wider">{{ formatDate(tryout.started_at) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-slate-500">
                                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-[10px] font-medium uppercase tracking-wider">{{ tryout.price > 0 ? formatPrice(tryout.price) : 'Gratis' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-6 lg:pt-0 lg:pl-8 border-t lg:border-t-0 lg:border-l border-slate-50">
                            
                            <Link 
                                :href="route('admin.tryout-akbar.participants', tryout.id)" 
                                class="flex-1 lg:flex-none flex flex-col items-center justify-center w-full lg:w-28 h-20 rounded-2xl bg-amber-50/50 text-amber-600 hover:bg-amber-600 hover:text-white transition-all border border-amber-100/50 group"
                            >
                                <span class="text-xl mb-1 group-hover:scale-110 transition-transform">👥</span>
                                <span class="text-[8px] font-medium uppercase tracking-widest text-center leading-tight">Verifikasi Peserta</span>
                            </Link>

                            <Link 
                                :href="route('admin.tryouts.questions.index', tryout.id)" 
                                class="flex-1 lg:flex-none flex flex-col items-center justify-center w-full lg:w-28 h-20 rounded-2xl bg-indigo-50/50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100/50 group"
                            >
                                <span class="text-xl mb-1 group-hover:scale-110 transition-transform">📝</span>
                                <span class="text-[8px] font-medium uppercase tracking-widest text-center leading-tight">Kelola Soal</span>
                            </Link>

                            <div class="flex lg:flex-col gap-2 w-full lg:w-auto">
                                <Link 
                                    :href="route('admin.tryout-akbar.edit', tryout.id)" 
                                    class="flex-1 px-4 py-2.5 rounded-xl text-[9px] font-medium uppercase tracking-widest bg-slate-50 text-slate-600 hover:bg-slate-900 hover:text-white text-center border border-slate-100 transition-all"
                                >
                                    Edit
                                </Link>
                                <button 
                                    @click="deleteEvent(tryout.id)" 
                                    class="flex-1 px-4 py-2.5 rounded-xl text-[9px] font-medium uppercase tracking-widest bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white text-center border border-rose-100 transition-all"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white border border-slate-50 rounded-[2.5rem] p-16 md:p-24 flex flex-col items-center text-center shadow-sm">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mb-6 text-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium uppercase tracking-[0.3em]">Belum Ada Event Akbar Dibuat</p>
                    <Link :href="route('admin.tryout-akbar.create')" class="mt-6 text-[10px] font-medium text-indigo-600 uppercase tracking-widest border-b border-indigo-100 pb-1">Mulai Buat Event Pertama</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div, .space-y-4 > div {
    animation: slideUp 0.6s ease-out forwards;
}
</style>