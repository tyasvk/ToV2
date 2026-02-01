<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    tryouts: [Object, Array], 
    filters: Object, // Added filters prop if you use search later
});

const page = usePage();

const tryoutList = computed(() => {
    if (props.tryouts && props.tryouts.data) return props.tryouts.data;
    return Array.isArray(props.tryouts) ? props.tryouts : [];
});

const paginationLinks = computed(() => {
    return (props.tryouts && props.tryouts.links) ? props.tryouts.links : [];
});

// Helper Format Rupiah
const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

// Helper Format Tanggal Waktu
const formatDateTime = (dateTime) => {
    if (!dateTime) return 'Sekarang';
    return new Date(dateTime).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// --- SWEETALERT LOGIC START ---
const showSuccessAlert = (message) => {
    Swal.fire({
        title: 'Berhasil!',
        text: message,
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0F172A', // Matches your dark theme
        background: '#fff',
        iconColor: '#10B981' // Emerald-500
    });
};

// Check flash message on mount (initial load/redirect)
onMounted(() => {
    if (page.props.flash.success) {
        showSuccessAlert(page.props.flash.success);
    }
});

// Watch for flash message changes (reactive Inertia visits)
watch(() => page.props.flash.success, (newMessage) => {
    if (newMessage) {
        showSuccessAlert(newMessage);
    }
}, { deep: true });
// --- SWEETALERT LOGIC END ---

</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        
        <div class="relative bg-[#0F172A] text-white overflow-hidden py-10 px-4 sm:px-6 lg:px-8 border-b border-gray-800 font-sans">
            <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-amber-500/5 rounded-full blur-[80px]"></div>
            
            <div class="relative z-10 max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white leading-tight mb-1 tracking-tight">
                        Katalog <span class="text-amber-400">Paket</span>
                    </h1>
                    <p class="text-slate-400 text-xs md:text-sm max-w-lg font-normal">
                        Pilih paket simulasi CAT BKN terbaik untuk persiapan Anda.
                    </p>
                </div>

                <div class="flex bg-white/5 backdrop-blur-sm p-1.5 rounded-lg border border-white/10 shadow-xl">
                    <Link :href="route('tryout.index')" 
                        class="px-7 py-2.5 rounded-md text-xs font-bold uppercase tracking-widest transition-all bg-white text-[#0F172A] shadow-lg">
                        Katalog
                    </Link>
                    <Link :href="route('tryout.my')" 
                        class="px-7 py-2.5 rounded-md text-xs font-bold uppercase tracking-widest transition-all text-slate-400 hover:text-white hover:bg-white/5">
                        Tryout Saya
                    </Link>
                </div>
            </div>
        </div>

        <div class="min-h-screen bg-[#F8F9FA] relative z-20 py-8 font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div v-if="tryoutList.length === 0" class="py-20 text-center bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl text-gray-300">❖</div>
                    <h3 class="text-gray-900 font-bold text-lg mb-1">Katalog Kosong</h3>
                    <p class="text-gray-500 text-xs mb-6">Belum ada paket tersedia saat ini.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="tryout in tryoutList" :key="tryout.id" 
                         class="group bg-white rounded-xl shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_16px_rgba(0,0,0,0.08)] border border-gray-100 hover:border-amber-500/30 transition-all duration-300 flex flex-col h-full relative overflow-hidden">
                        
                        <div class="h-0.5 w-full bg-gradient-to-r from-[#0F172A] via-amber-500 to-[#0F172A] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <div class="p-5 flex flex-col h-full">
                            
                            <div class="flex justify-between items-start gap-3 mb-3">
                                <h3 class="text-base font-bold text-gray-900 leading-snug line-clamp-2 group-hover:text-amber-700 transition-colors tracking-tight" :title="tryout.title">
                                    {{ tryout.title }}
                                </h3>
                                <span class="shrink-0 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-widest border"
                                      :class="tryout.price == 0 
                                        ? 'bg-emerald-50 text-emerald-600 border-emerald-100' 
                                        : 'bg-[#0F172A] text-white border-[#0F172A]'">
                                    {{ tryout.price == 0 ? 'Free' : 'Pro' }}
                                </span>
                            </div>

                            <div class="h-px w-full bg-gray-100 mb-3"></div>

                            <div class="flex flex-col gap-2 text-[10px] text-gray-500 font-medium mb-5">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Durasi
                                    </span>
                                    <span class="font-bold text-gray-700">{{ tryout.duration_minutes || tryout.duration || 0 }} Menit</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Jumlah Soal
                                    </span>
                                    <span class="font-bold text-gray-700">{{ tryout.questions_count ?? 0 }} Butir</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#0F172A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Akses
                                    </span>
                                    <span class="font-bold text-gray-700">{{ formatDateTime(tryout.started_at) }}</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        Sistem
                                    </span>
                                    <span class="font-bold text-gray-700">CAT BKN</span>
                                </div>
                            </div>

                            <div class="mt-auto pt-2 flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">Harga</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'Gratis' }}
                                    </p>
                                </div>

                                <Link :href="route('tryout.register', tryout.id)"
                                    class="group/btn relative overflow-hidden bg-[#0F172A] text-white px-5 py-2 rounded-md text-[10px] font-bold uppercase tracking-[0.15em] hover:shadow-lg transition-all"
                                >
                                    <span class="relative z-10 flex items-center gap-1.5">
                                        Daftar
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform duration-300 group-hover/btn:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-900 to-[#0F172A] opacity-0 group-hover/btn:opacity-100 transition-opacity duration-500"></div>
                                </Link>
                            </div>

                        </div>
                    </div>
                </div>

                <div v-if="paginationLinks.length > 3" class="mt-10 flex justify-center">
                    <div class="bg-white p-1 rounded-full shadow-sm border border-gray-100 inline-flex gap-1">
                        <template v-for="(link, k) in paginationLinks" :key="k">
                            <Link v-if="link.url" 
                                :href="link.url" 
                                v-html="link.label" 
                                class="w-8 h-8 flex items-center justify-center text-[10px] font-bold rounded-full transition-all" 
                                :class="link.active 
                                    ? 'bg-[#0F172A] text-amber-400' 
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-[#0F172A]'" 
                            />
                            <span v-else 
                                v-html="link.label" 
                                class="w-8 h-8 flex items-center justify-center text-[10px] text-gray-300 cursor-default">
                            </span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>