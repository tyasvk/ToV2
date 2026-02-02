<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const commonFeatures = [
    '10 Paket Eksklusif (HOTS)',
    'Tryout Umum Unlimited',
    'Ranking Regional'
];

const plans = [
    {
        name: 'Sprint Flash',
        duration: 'Akses 7 Minggu',
        price: '49.000',
        description: 'Persiapan kilat untuk hasil yang akurat.',
        features: [...commonFeatures],
        buttonText: 'Pilih Paket',
        highlight: false,
    },
    {
        name: 'Standard Pro',
        duration: 'Akses 30 Hari',
        price: '89.000',
        description: 'Latihan stabil untuk penguasaan materi harian.',
        features: [...commonFeatures],
        buttonText: 'Pilih Paket',
        highlight: false,
    },
    {
        name: 'Mastery Plan',
        duration: 'Akses 90 Hari',
        price: '159.000',
        description: 'Pilihan paling ideal untuk persiapan matang.',
        features: [...commonFeatures],
        buttonText: 'Investasi Terbaik',
        highlight: true, // BEST SELLER
    },
    {
        name: 'Ultimate Pass',
        duration: 'Akses 1 Tahun',
        price: '249.000',
        description: 'Jaminan akses penuh hingga hari H ujian.',
        features: [
            ...commonFeatures,
            'Tryout Berbasis Field Report 2024'
        ],
        buttonText: 'Ambil Akses Penuh',
        highlight: false,
    }
];
</script>

<template>
    <Head title="Membership Premium" />

    <AuthenticatedLayout>
        <div class="py-12 bg-[#F1F5F9] min-h-screen font-sans selection:bg-indigo-100">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                
                <div class="text-center space-y-4 animate-in fade-in slide-in-from-top-4 duration-1000">
                    <div class="inline-flex items-center px-4 py-1.5 bg-indigo-50 rounded-full mb-2 border border-indigo-100 shadow-sm">
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em]">Premium Learning Access</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 tracking-tighter leading-none uppercase">
                        Satu Harga Untuk <br> <span class="italic text-indigo-600">Ribuan Potensi.</span>
                    </h1>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-6 pt-10">
                    <div v-for="plan in plans" :key="plan.name" 
                        :class="[
                            'relative rounded-[3.5rem] p-8 md:p-10 flex flex-col transition-all duration-700 group border',
                            plan.highlight 
                                ? 'bg-slate-900 text-white shadow-[0_50px_100px_-20px_rgba(79,70,229,0.3)] border-slate-800 lg:scale-105 z-20' 
                                : 'bg-white text-slate-900 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.05)] border-white hover:-translate-y-2'
                        ]"
                    >
                        <div v-if="plan.highlight" class="absolute -top-5 left-1/2 -translate-x-1/2 z-30">
                            <span class="bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] py-2.5 px-6 rounded-full shadow-xl shadow-indigo-500/40 whitespace-nowrap">
                                Terpopuler
                            </span>
                        </div>

                        <div class="mb-8 relative z-10">
                            <h3 :class="['text-[11px] font-black uppercase tracking-[0.2em] mb-4', plan.highlight ? 'text-indigo-400' : 'text-indigo-600']">
                                {{ plan.name }}
                            </h3>
                            <div class="flex items-baseline gap-1">
                                <span class="text-lg font-bold">Rp</span>
                                <span class="text-4xl font-black tracking-tighter">{{ plan.price }}</span>
                            </div>
                            <p :class="['text-[10px] font-bold uppercase tracking-widest mt-2', plan.highlight ? 'text-slate-400' : 'text-slate-400']">
                                {{ plan.duration }}
                            </p>
                        </div>

                        <div :class="['w-full h-[1px] mb-8', plan.highlight ? 'bg-white/10' : 'bg-slate-100']"></div>

                        <div class="flex-1 space-y-5 mb-10">
                            <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                                <div :class="['shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5', plan.highlight ? 'bg-indigo-500/20 text-indigo-400' : 'bg-indigo-50 text-indigo-600']">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <span :class="[
                                    'text-[11px] font-bold leading-tight', 
                                    plan.highlight ? 'text-slate-300' : 'text-slate-600',
                                    feature.includes('Field Report') ? 'text-indigo-500 underline decoration-indigo-500/30' : ''
                                ]">
                                    {{ feature }}
                                </span>
                            </div>
                        </div>

                        <button 
                            :class="[
                                'w-full py-5 rounded-[2rem] font-black text-[10px] uppercase tracking-[0.25em] transition-all duration-300 active:scale-95 shadow-xl',
                                plan.highlight 
                                    ? 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-indigo-600/30' 
                                    : 'bg-slate-900 text-white hover:bg-indigo-600 shadow-slate-900/10'
                            ]"
                        >
                            {{ plan.buttonText }}
                        </button>
                    </div>
                </div>

                <div class="pt-12">
                    <div class="bg-white rounded-[4rem] p-10 md:p-20 shadow-sm border border-white relative overflow-hidden">
                        <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-12">
                            <div class="space-y-4">
                                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">🎯</div>
                                <h4 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Kualitas Premium</h4>
                                <p class="text-xs text-slate-500 leading-relaxed font-medium">10 Paket Eksklusif kami dikurasi menggunakan standar HOTS terbaru untuk menjamin kemiripan dengan soal ujian asli.</p>
                            </div>
                            <div class="space-y-4">
                                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📂</div>
                                <h4 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Field Report 2024</h4>
                                <p class="text-xs text-slate-500 leading-relaxed font-medium">Khusus paket Ultimate, dapatkan materi simulasi yang disusun berdasarkan laporan asli peserta ujian tahun 2024.</p>
                            </div>
                            <div class="space-y-4">
                                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm">📊</div>
                                <h4 class="text-xl font-black text-slate-900 tracking-tighter uppercase italic">Unlimited Practice</h4>
                                <p class="text-xs text-slate-500 leading-relaxed font-medium">Setelah menyelesaikan paket eksklusif, Anda tetap bisa melatih kemampuan dengan bank soal umum tanpa batas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center pb-12 opacity-50">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pembayaran Otomatis via QRIS & Transfer Bank</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.grid > div {
    animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }
</style>