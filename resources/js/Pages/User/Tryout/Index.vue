<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    tryouts: [Object, Array], 
    filters: Object,
});

const page = usePage();

const tryoutList = computed(() => {
    if (props.tryouts && props.tryouts.data) return props.tryouts.data;
    return Array.isArray(props.tryouts) ? props.tryouts : [];
});

const paginationLinks = computed(() => {
    return (props.tryouts && props.tryouts.links) ? props.tryouts.links : [];
});

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);

// --- SWEETALERT ---
const showSuccessAlert = (message) => {
    Swal.fire({
        title: 'Berhasil!',
        text: message,
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0F172A',
        background: '#fff',
        iconColor: '#10B981'
    });
};

onMounted(() => {
    if (page.props.flash.success) showSuccessAlert(page.props.flash.success);
});

watch(() => page.props.flash.success, (newMessage) => {
    if (newMessage) showSuccessAlert(newMessage);
}, { deep: true });
</script>

<template>
    <Head title="Katalog Tryout Reguler" />

    <AuthenticatedLayout>
        
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-900 to-indigo-950 opacity-95"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-500/10 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px]"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-200 text-[10px] font-black tracking-[0.2em] uppercase mb-6 backdrop-blur-sm shadow-lg shadow-cyan-900/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> Katalog Resmi
                </span>
                
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4 leading-tight">
                    SIMULASI <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-400 italic">CAT BKN.</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-sm md:text-base text-slate-400 font-medium leading-relaxed">
                    Pilih paket latihan mandiri sesuai kebutuhanmu. 
                    Tingkatkan skor SKD secara bertahap dengan materi terupdate.
                </p>
            </div>
        </div>

        <div class="flex justify-center -mt-16 relative z-10 mb-10">
            <div class="bg-white p-1.5 rounded-full shadow-2xl shadow-slate-900/5 border border-white flex gap-1">
                <Link :href="route('tryout.index')" 
                    class="px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 bg-slate-900 text-white shadow-lg transform scale-105"
                >
                    <span>📝</span> Katalog Paket
                </Link>
                <Link :href="route('tryout.my')" 
                    class="px-8 py-3 rounded-full text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50"
                >
                    <span>👤</span> Tryout Saya
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-2 pb-20">
            
            <div v-if="tryoutList.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-[3rem] border border-dashed border-slate-200 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-4xl mb-4 grayscale opacity-50">📂</div>
                <h3 class="text-xl font-bold text-slate-800">Katalog Kosong</h3>
                <p class="text-slate-500 text-sm mt-2 max-w-md">Belum ada paket tryout reguler yang tersedia saat ini.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tryout in tryoutList" :key="tryout.id" class="group bg-white rounded-3xl shadow-sm hover:shadow-xl border border-slate-200 overflow-hidden transition-all duration-500 hover:-translate-y-2 flex flex-col h-full relative">
                    
                    <div class="absolute top-4 right-4 z-20">
                        <span class="px-3 py-1 backdrop-blur-md text-[9px] font-black uppercase tracking-wider rounded-lg shadow-lg flex items-center gap-1.5 transition-all"
                            :class="tryout.price > 0 
                                ? 'bg-slate-900/90 text-white' 
                                : 'bg-emerald-500/90 text-white'">
                            <span v-if="tryout.price > 0" class="text-amber-400 text-xs">💎</span>
                            <span v-else class="text-white text-xs">🎁</span>
                            {{ tryout.price > 0 ? 'PRO' : 'FREE' }}
                        </span>
                    </div>

                    <div class="h-20 bg-gradient-to-br from-cyan-50 via-white to-slate-50 relative overflow-hidden border-b border-slate-100">
                        <div class="absolute -left-4 -top-4 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:bg-cyan-500/10 transition duration-700"></div>
                        <div class="absolute right-2 top-2 text-6xl opacity-[0.04] font-black text-slate-900 select-none italic tracking-tighter">
                            TO
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-0 flex-1 flex flex-col -mt-10 relative z-10">
                        <div class="w-14 h-14 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex items-center justify-center text-2xl mb-4 group-hover:scale-110 group-hover:rotate-3 transition duration-500 text-cyan-600">
                            📝
                        </div>

                        <h3 class="text-lg font-black text-slate-800 leading-tight mb-1 group-hover:text-cyan-600 transition truncate" :title="tryout.title">
                            {{ tryout.title }}
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-5">Regular Simulation</p>

                        <div class="mb-6 bg-slate-50 p-1.5 rounded-2xl border border-slate-100 group-hover:border-cyan-100 transition-colors">
                            <div class="grid grid-cols-2 gap-px bg-slate-200/50 rounded-xl overflow-hidden">
                                <div class="bg-white p-3 flex flex-col items-center justify-center text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Soal</p>
                                    <p class="text-sm font-black text-slate-800 leading-none">{{ tryout.questions_count ?? 0 }}</p>
                                </div>
                                <div class="bg-white p-3 flex flex-col items-center justify-center text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Waktu</p>
                                    <p class="text-sm font-black text-slate-800 leading-none">{{ tryout.duration_minutes || tryout.duration || 0 }}m</p>
                                </div>
                            </div>
                            
                            <div class="mt-1.5 px-3 py-2 bg-white rounded-xl flex items-center justify-between border border-slate-100">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Investasi</span>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded text-right"
                                    :class="tryout.price > 0 ? 'text-slate-900' : 'text-emerald-600'">
                                    {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'GRATIS' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <Link :href="route('tryout.register', tryout.id)" 
                                class="block w-full py-3.5 rounded-xl font-black text-[10px] uppercase tracking-[0.2em] text-center bg-slate-900 text-white shadow-lg shadow-slate-200 hover:bg-cyan-600 hover:shadow-cyan-200 transition-all active:scale-95 flex items-center justify-center gap-2 group-hover:gap-3"
                            >
                                Daftar Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="paginationLinks.length > 3" class="mt-12 flex justify-center">
                <div class="bg-white p-1.5 rounded-full shadow-lg border border-slate-100 inline-flex gap-1">
                    <template v-for="(link, k) in paginationLinks" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            v-html="link.label" 
                            class="w-9 h-9 flex items-center justify-center text-[10px] font-bold rounded-full transition-all" 
                            :class="link.active 
                                ? 'bg-slate-900 text-white shadow-md' 
                                : 'text-slate-400 hover:bg-slate-50 hover:text-slate-900'" 
                        />
                        <span v-else 
                            v-html="link.label" 
                            class="w-9 h-9 flex items-center justify-center text-[10px] text-slate-300 cursor-default font-bold">
                        </span>
                    </template>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>