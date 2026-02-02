<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import Swal from 'sweetalert2';

const page = usePage();
const user = computed(() => page.props.auth.user);

const commonFeatures = [
    '10 Paket Eksklusif (HOTS)',
    'Tryout Umum Unlimited',
    'Ranking Nasional & Regional'
];

/**
 * Daftar paket dengan nama Nusantara & Harga Premium
 */
const plans = [
    {
        id: '7_days',
        name: 'Prawira',
        duration: '7 Hari Akses Full',
        price: '49.000',
        description: 'Langkah awal pejuang tangguh.',
        features: [...commonFeatures],
        buttonText: 'Pilih Paket',
        highlight: false,
    },
    {
        id: '30_days',
        name: 'Satria',
        duration: '30 Hari Akses Full',
        price: '99.000',
        description: 'Dedikasi penuh untuk hasil maksimal.',
        features: [...commonFeatures],
        buttonText: 'Pilih Paket',
        highlight: false,
    },
    {
        id: '90_days',
        name: 'Wiranata',
        duration: '90 Hari Akses Full',
        price: '199.000',
        description: 'Strategi matang menuju kemenangan.',
        features: [...commonFeatures],
        buttonText: 'Investasi Terbaik',
        highlight: true,
    },
    {
        id: '1_year',
        name: 'Mahapatih',
        duration: '1 Tahun Akses Full',
        price: '299.000',
        description: 'Puncak persiapan ASN sejati.',
        features: [...commonFeatures, 'Update FR 2024 Prioritas'],
        buttonText: 'Ambil Akses Penuh',
        highlight: false,
    }
];

/**
 * Fungsi untuk memproses pembelian dengan Pop-up Konfirmasi
 */
const buyPlan = (planId, planName) => {
    Swal.fire({
        title: 'Konfirmasi Pembelian', // Judul sudah diganti sesuai request
        text: `Apakah Anda yakin ingin membeli paket ${planName}? Anda akan diarahkan ke halaman pembayaran aman.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5', 
        cancelButtonColor: '#64748b', 
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        background: '#ffffff',
        customClass: {
            popup: 'rounded-[2rem] border-none shadow-2xl',
            title: 'font-black text-slate-900 uppercase tracking-tight',
            htmlContainer: 'text-slate-500 font-medium',
            confirmButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] py-4 px-8',
            cancelButton: 'rounded-xl font-black uppercase tracking-widest text-[10px] py-4 px-8'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('membership.buy'), {
                plan_id: planId,
                payment_method: 'midtrans'
            });
        }
    });
};
</script>

<template>
    <Head title="Keanggotaan Nusantara Adidaya" />

    <AuthenticatedLayout>
        
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-violet-500/10 rounded-full blur-[80px]"></div>
                <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-indigo-500/20 border border-indigo-400/20 text-indigo-200 text-[10px] font-black tracking-[0.2em] uppercase mb-6 backdrop-blur-sm shadow-lg">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span> Kualitas Di Atas Harga
                </span>
                
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4 leading-tight uppercase">
                    SATU HARGA UNTUK <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400 italic">RIBUAN POTENSI.</span>
                </h1>
                
                <p class="mt-4 max-w-2xl mx-auto text-sm md:text-base text-slate-400 font-medium leading-relaxed">
                    Nusantara Adidaya tidak bersaing dengan harga murah. Kami fokus pada akurasi simulasi, standar HOTS tertinggi, dan keberhasilan Anda menjadi ASN.
                </p>
            </div>
        </div>

        <div class="flex justify-center -mt-16 relative z-10 mb-10">
            <div class="bg-white px-10 py-3 rounded-full shadow-2xl shadow-indigo-900/10 border border-white flex items-center gap-3">
                <span class="text-indigo-600 animate-bounce">💎</span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-900">Pilih Jenjang Belajar</span>
            </div>
        </div>

        <div class="max-w-[1400px] mx-auto px-2 pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="plan in plans" :key="plan.name" 
                    :class="[
                        'group relative rounded-[3rem] p-8 md:p-10 flex flex-col transition-all duration-500 border',
                        plan.highlight 
                            ? 'bg-slate-900 text-white shadow-2xl shadow-indigo-500/20 border-slate-800 lg:scale-105 z-20' 
                            : 'bg-white text-slate-900 shadow-sm border-slate-200 hover:shadow-xl hover:-translate-y-2'
                    ]"
                >
                    <div v-if="plan.highlight" class="absolute -top-4 left-1/2 -translate-x-1/2 z-40">
                        <span class="bg-indigo-600 text-white text-[9px] font-black uppercase tracking-[0.2em] py-2 px-5 rounded-full shadow-xl whitespace-nowrap inline-block">
                            Pilihan Utama
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
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-2', plan.highlight ? 'text-slate-500' : 'text-slate-400']">
                            {{ plan.duration }}
                        </p>
                    </div>

                    <div :class="['w-full h-[1px] mb-8', plan.highlight ? 'bg-white/10' : 'bg-slate-100']"></div>

                    <p :class="['text-[11px] font-medium leading-relaxed mb-6', plan.highlight ? 'text-slate-400' : 'text-slate-500']">
                        {{ plan.description }}
                    </p>

                    <div class="flex-1 space-y-4 mb-10">
                        <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-3">
                            <div :class="['shrink-0 w-5 h-5 rounded-full flex items-center justify-center mt-0.5', plan.highlight ? 'bg-indigo-500/20 text-indigo-400' : 'bg-indigo-50 text-indigo-600']">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <span :class="['text-[11px] font-bold leading-tight', plan.highlight ? 'text-slate-300' : 'text-slate-600']">
                                {{ feature }}
                            </span>
                        </div>
                    </div>

                    <button 
                        @click="buyPlan(plan.id, plan.name)"
                        :class="[
                            'w-full py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all duration-300 active:scale-95 shadow-xl',
                            plan.highlight 
                                ? 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-indigo-600/30' 
                                : 'bg-slate-900 text-white hover:bg-indigo-600 shadow-slate-900/10'
                        ]"
                    >
                        {{ plan.buttonText }}
                    </button>
                </div>
            </div>

            <div class="mt-20 pt-10 border-t border-slate-200/60">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="flex gap-5">
                        <div class="shrink-0 w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-xl">🎯</div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-1 italic">Standar HOTS</h4>
                            <p class="text-[11px] text-slate-500 font-medium leading-relaxed">Materi disusun menggunakan standar kesulitan terbaru sesuai kisi-kisi BKN.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="shrink-0 w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-xl">📂</div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-1 italic">Update Berkala</h4>
                            <p class="text-[11px] text-slate-500 font-medium leading-relaxed">Penambahan bank soal secara rutin mengikuti Field Report (FR) peserta.</p>
                        </div>
                    </div>
                    <div class="flex gap-5">
                        <div class="shrink-0 w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-xl">📊</div>
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-1 italic">Analisis Pintar</h4>
                            <p class="text-[11px] text-slate-500 font-medium leading-relaxed">Lihat grafik perkembangan skor dan perbandingan ranking nasional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style scoped>
.grid > div {
    animation: fadeInSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
}
@keyframes fadeInSlideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }
</style>