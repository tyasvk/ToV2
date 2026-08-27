<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts'; // Import library grafik

const props = defineProps({
    chartData: Object,
});

const goBack = () => {
    if (window.history.length > 1) window.history.back();
    else router.visit('/dashboard');
};

// --- LOGIKA GRAFIK UTAMA (GARIS) ---
const mainTab = ref('total'); // Pilihan: total, twk, tiu, tkp

const mainSeries = computed(() => {
    let data = props.chartData.main[mainTab.value] || [];
    let name = 'Skor Total';
    if(mainTab.value === 'twk') name = 'Skor TWK';
    if(mainTab.value === 'tiu') name = 'Skor TIU';
    if(mainTab.value === 'tkp') name = 'Skor TKP';

    return [{ name: name, data: data }];
});

const mainChartOptions = computed(() => {
    let color = '#007AFF'; // Biru (Total)
    if(mainTab.value === 'twk') color = '#FF9500'; // Orange
    if(mainTab.value === 'tiu') color = '#34C759'; // Hijau
    if(mainTab.value === 'tkp') color = '#FF3B30'; // Merah

    return {
        chart: {
            type: 'area',
            fontFamily: 'inherit',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: [color],
        dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 'bold' } },
        stroke: { curve: 'smooth', width: 4 },
        xaxis: { categories: props.chartData.categories, tooltip: { enabled: false } },
        yaxis: { min: 0 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0, stops: [0, 100] }
        },
        grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
    };
});

// --- LOGIKA GRAFIK SUBTES (BATANG) ---
const selectedTryoutForSubtest = ref(props.chartData.categories[props.chartData.categories.length - 1] || '');

// Konversi data subtes JSON dari Backend menjadi format ApexCharts
const getSubtestChartData = (type) => {
    const toData = props.chartData.subtests[selectedTryoutForSubtest.value];
    if (!toData || !toData[type]) return { series: [], categories: [] };

    const topics = Object.keys(toData[type]);
    const scores = Object.values(toData[type]);

    return {
        series: [{ name: `Nilai ${type}`, data: scores }],
        options: {
            chart: { type: 'bar', fontFamily: 'inherit', toolbar: { show: false } },
            plotOptions: {
                bar: { horizontal: true, borderRadius: 6, dataLabels: { position: 'top' } }
            },
            colors: [type === 'TWK' ? '#FF9500' : (type === 'TIU' ? '#34C759' : '#FF3B30')],
            dataLabels: { 
                enabled: true, 
                offsetX: 20, 
                style: { fontSize: '11px', colors: ['#1D1D1F'] } 
            },
            xaxis: { categories: topics, labels: { show: false }, axisBorder: { show: false } },
            yaxis: { labels: { style: { fontWeight: 'bold', fontSize: '11px' } } },
            grid: { show: false }
        }
    };
};
</script>

<template>
    <Head title="Grafik Perkembangan - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F5F7] w-full pb-36 animate-in fade-in duration-500 overflow-x-hidden relative">
            
            <div class="fixed top-[-10%] right-[-5%] w-[400px] h-[400px] bg-[#007AFF]/5 rounded-full blur-[100px] pointer-events-none z-0"></div>

            <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 md:pt-10 space-y-6 relative z-10 w-full box-border">
                
                <!-- HEADER -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-2">
                    <div class="min-w-0 flex-1">
                        <button @click="goBack" class="inline-flex items-center gap-1 text-[#007AFF] hover:underline text-[13px] md:text-[14px] font-bold transition-opacity mb-2">
                            &larr; Kembali ke Riwayat
                        </button>
                        <h1 class="text-2xl md:text-3xl font-semibold text-slate-900 tracking-tight leading-none">Grafik Perkembangan</h1>
                        <p class="text-[13px] text-slate-500 font-medium mt-1.5 leading-relaxed">
                            Pantau kenaikan nilai dan evaluasi penguasaan materi Anda di setiap Tryout.
                        </p>
                    </div>
                </div>

                <!-- JIKA BELUM ADA DATA -->
                <div v-if="props.chartData.categories.length === 0" class="bg-white/80 backdrop-blur-xl rounded-[32px] p-12 flex flex-col items-center text-center shadow-sm border border-black/5 mt-4">
                    <h3 class="text-[20px] sm:text-[24px] text-[#1D1D1F] mb-2 font-bold">Data Grafik Kosong</h3>
                    <p class="text-[14px] text-[#86868B] font-medium mb-6">Anda harus menyelesaikan setidaknya 1 Tryout untuk melihat grafik analitik.</p>
                    <button @click="router.visit(route('tryout.index'))" class="px-6 py-3 bg-[#007AFF] text-white rounded-full text-[13px] font-bold shadow-md hover:bg-[#0062cc]">Kerjakan Tryout Sekarang</button>
                </div>

                <div v-else class="space-y-6 w-full">
                    
                    <!-- 1. KARTU GRAFIK GARIS (PERKEMBANGAN SKOR) -->
                    <div class="bg-white rounded-[24px] sm:rounded-[32px] p-5 sm:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 w-full overflow-hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                            <h2 class="text-[18px] font-bold text-[#1D1D1F]">Tren Nilai Anda</h2>
                            
                            <!-- Segmented Control untuk Filter Line Chart -->
                            <div class="inline-flex bg-[#F5F5F7] p-1.5 rounded-[16px] w-full sm:w-auto">
                                <button @click="mainTab = 'total'" :class="mainTab === 'total' ? 'bg-white text-[#007AFF] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'" class="flex-1 px-4 py-2 rounded-[12px] text-[12px] font-bold transition-all">Total</button>
                                <button @click="mainTab = 'twk'" :class="mainTab === 'twk' ? 'bg-white text-[#FF9500] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'" class="flex-1 px-4 py-2 rounded-[12px] text-[12px] font-bold transition-all">TWK</button>
                                <button @click="mainTab = 'tiu'" :class="mainTab === 'tiu' ? 'bg-white text-[#34C759] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'" class="flex-1 px-4 py-2 rounded-[12px] text-[12px] font-bold transition-all">TIU</button>
                                <button @click="mainTab = 'tkp'" :class="mainTab === 'tkp' ? 'bg-white text-[#FF3B30] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]'" class="flex-1 px-4 py-2 rounded-[12px] text-[12px] font-bold transition-all">TKP</button>
                            </div>
                        </div>

                        <!-- Render Grafik ApexCharts -->
                        <div class="w-full relative h-[300px]">
                            <VueApexCharts width="100%" height="100%" :options="mainChartOptions" :series="mainSeries" />
                        </div>
                    </div>

                    <!-- 2. KARTU ANALISIS MATERI SUBTES -->
                    <div class="bg-white rounded-[24px] sm:rounded-[32px] p-5 sm:p-8 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-black/5 w-full">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                            <div>
                                <h2 class="text-[18px] font-bold text-[#1D1D1F]">Analisis Penguasaan Materi</h2>
                                <p class="text-[12px] text-[#86868B] font-medium mt-1">Cek kelemahan materi Anda pada setiap Tryout.</p>
                            </div>
                            
                            <!-- Dropdown Pilih Tryout -->
                            <select v-model="selectedTryoutForSubtest" class="bg-[#F5F5F7] border border-transparent rounded-[12px] px-4 py-2.5 text-[13px] font-bold text-[#1D1D1F] outline-none focus:ring-2 focus:ring-[#007AFF]/20 cursor-pointer">
                                <option v-for="cat in chartData.categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <!-- Grid 3 Kolom untuk TWK, TIU, TKP -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- TWK -->
                            <div class="bg-[#F9FAFB] rounded-[20px] p-5 border border-black/5">
                                <h3 class="text-[14px] font-black text-[#FF9500] mb-4 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span> TWK
                                </h3>
                                <VueApexCharts v-if="getSubtestChartData('TWK').series[0].data.length > 0" 
                                    width="100%" height="250" :options="getSubtestChartData('TWK').options" :series="getSubtestChartData('TWK').series" />
                                <p v-else class="text-xs text-center text-slate-400 py-10">Materi tidak ditemukan</p>
                            </div>

                            <!-- TIU -->
                            <div class="bg-[#F9FAFB] rounded-[20px] p-5 border border-black/5">
                                <h3 class="text-[14px] font-black text-[#34C759] mb-4 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span> TIU
                                </h3>
                                <VueApexCharts v-if="getSubtestChartData('TIU').series[0].data.length > 0" 
                                    width="100%" height="250" :options="getSubtestChartData('TIU').options" :series="getSubtestChartData('TIU').series" />
                                <p v-else class="text-xs text-center text-slate-400 py-10">Materi tidak ditemukan</p>
                            </div>

                            <!-- TKP -->
                            <div class="bg-[#F9FAFB] rounded-[20px] p-5 border border-black/5">
                                <h3 class="text-[14px] font-black text-[#FF3B30] mb-4 uppercase tracking-widest flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span> TKP
                                </h3>
                                <VueApexCharts v-if="getSubtestChartData('TKP').series[0].data.length > 0" 
                                    width="100%" height="250" :options="getSubtestChartData('TKP').options" :series="getSubtestChartData('TKP').series" />
                                <p v-else class="text-xs text-center text-slate-400 py-10">Materi tidak ditemukan</p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>