<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    catalogTryouts: Array,
    myTryouts: Array,
    filters: Object,
    isPremiumMember: Boolean
});

const page = usePage();

const formatRupiah = (num) => new Intl.NumberFormat('id-ID', { 
    style: 'currency', 
    currency: 'IDR', 
    minimumFractionDigits: 0 
}).format(num);

onMounted(() => {
    if (page.props.flash.success) {
        Swal.fire({ 
            title: 'Berhasil!', 
            text: page.props.flash.success, 
            icon: 'success', 
            confirmButtonColor: '#4f46e5',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-lg',
                confirmButton: 'rounded-lg px-6 py-2.5 font-bold text-xs uppercase tracking-widest'
            }
        });
    }
});
</script>

<template>
    <Head title="Pusat Simulasi Tryout" />

    <AuthenticatedLayout>
        <div class="relative overflow-hidden -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 px-6 py-16 md:py-24 bg-slate-950">
            <div class="absolute inset-0">
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-500/10 rounded-full blur-[100px] translate-y-1/2 -translate-x-1/2"></div>
                <div class="absolute inset-0 opacity-[0.02]" style="background-image: url('data:image/svg+xml,%3Csvg width=%2230%22 height=%2230%22 viewBox=%220 0 30 30%22 fill=%22none%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cpath d=%22M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z%22 fill=%22white%22/%3E%3C/svg%3E');"></div>
            </div>

            <div class="relative max-w-5xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Database Terupdate 2026</span>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter leading-none mb-6 uppercase">
                    Master Your Potential.
                </h1>
                <p class="max-w-3xl mx-auto text-slate-400 text-sm md:text-base font-medium leading-relaxed">
                    Akses ribuan simulasi soal CAT BKN original. Kelola katalog pendaftaran dan pantau progres belajar Anda dalam satu dashboard terpadu.
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 pb-24 space-y-20">
            
            <section v-if="!isPremiumMember && catalogTryouts.length > 0">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase mb-1">Tryout Terbaru</h2>
                        <div class="flex items-center gap-2">
                            <span class="h-1 w-6 bg-indigo-600 rounded-full"></span>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">Pilihan Paket Belajar Terbaik</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="tryout in catalogTryouts" :key="tryout.id" class="group bg-white rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full max-w-sm mx-auto w-full">
                        <div class="p-6 pb-0">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-10 h-10 aspect-square flex-shrink-0 bg-slate-50 rounded-lg flex items-center justify-center text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <span class="px-2 py-0.5 bg-slate-900 text-white text-[8px] font-black uppercase tracking-widest rounded">
                                    {{ tryout.price > 0 ? 'Premium' : 'Free' }}
                                </span>
                            </div>
                            <h3 class="text-lg font-black text-slate-900 leading-tight mb-2 truncate" :title="tryout.title">{{ tryout.title }}</h3>
                        </div>

                        <div class="px-6 pb-6 mt-auto">
                            <div class="flex items-center justify-between p-3 bg-indigo-50/50 rounded-lg border border-indigo-100/50 mb-5">
                                <div>
                                    <p class="text-[8px] font-black text-indigo-400 uppercase tracking-widest">Harga</p>
                                    <p class="text-base font-black text-indigo-700 leading-none mt-1">
                                        {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'GRATIS' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Waktu</p>
                                    <p class="text-[10px] font-black text-slate-700 mt-1 uppercase">{{ tryout.duration }} Menit</p>
                                </div>
                            </div>
                            <Link :href="route('tryout.register', tryout.id)" class="flex items-center justify-center gap-2 w-full py-3 bg-slate-950 text-white rounded-lg font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all active:scale-95">
                                Ambil Paket
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Tryout Saya</h2>
                            <span v-if="isPremiumMember" class="px-2 py-0.5 bg-amber-400 text-white text-[8px] font-black uppercase rounded shadow-sm">Adidaya Member</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-1 w-6 bg-emerald-500 rounded-full"></span>
                            <p class="text-[10px] text-emerald-600 font-black uppercase tracking-[0.2em]">Paket Aktif & Progres</p>
                        </div>
                    </div>
                </div>

                <div v-if="myTryouts.length === 0" class="flex flex-col items-center justify-center py-20 bg-white rounded-lg border-2 border-dashed border-slate-100 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-lg flex items-center justify-center text-slate-300 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.625-1.219c.837-1.239 2.108-1.961 3.375-1.961 1.267 0 2.538.722 3.375 1.961 1.27 1.883.57 4.132-1.47 4.881a6.189 6.189 0 0 1-1.905.308 6.189 6.189 0 0 1-1.905-.308c-2.04-.749-2.74-2.998-1.47-4.881Z" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em]">Belum Ada Paket Aktif</h3>
                    <p class="text-[10px] text-slate-400 mt-2 font-bold max-w-xs mx-auto">Silakan ambil paket tryout terlebih dahulu pada daftar di atas.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="tryout in myTryouts" :key="tryout.id" class="group bg-white rounded-lg border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col h-full max-w-sm mx-auto w-full">
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-6">
                                <div class="w-10 h-10 aspect-square flex-shrink-0 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.63 8.41m5.96 5.96a14.96 14.96 0 0 1-5.96 5.96m5.96-5.96V9.13a2.98 2.98 0 0 0-5.96 0v5.24" />
                                    </svg>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black uppercase rounded mb-1">Terdaftar</span>
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ tryout.exam_attempts_count || 0 }} Percobaan</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-black text-slate-900 leading-tight mb-1 truncate">{{ tryout.title }}</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-8">Materi Terupdate 2026</p>

                            <div class="mt-auto space-y-2">
                                <Link :href="route('tryout.show', tryout.id)" class="flex items-center justify-center gap-2 w-full py-3.5 bg-indigo-600 text-white rounded-lg font-black text-[10px] uppercase tracking-widest shadow-md hover:bg-slate-950 transition-all active:scale-95">
                                    {{ tryout.exam_attempts_count > 0 ? 'Kerjakan Lagi' : 'Mulai Ujian' }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </Link>
                                
                                <Link :href="route('tryout.history.detail', tryout.id)" class="flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-slate-200 text-slate-500 rounded-lg font-black text-[9px] uppercase tracking-widest hover:bg-slate-50 hover:text-indigo-600 transition-all">
                                    Riwayat Ujian
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </AuthenticatedLayout>
</template>