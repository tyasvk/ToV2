<script setup>
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch, computed, onMounted } from 'vue';

const props = defineProps({
    tryouts: Array,
    leaderboard: {
        type: Array,
        default: () => []
    },
    selectedTryoutId: Number,
});

// State
const selectedId = ref(props.selectedTryoutId || '');
const scope = ref('nasional'); 
const isLoading = ref(false);
const search = ref('');
const itemsPerPage = ref(20); 
const currentPage = ref(1);

// --- FETCH DATA DARI BACKEND SAAT TRYOUT BERUBAH ---
watch(selectedId, (newId) => {
    if (newId) {
        isLoading.value = true;
        scope.value = 'nasional'; 
        search.value = '';
        currentPage.value = 1;
        router.get(route('ranking.index'), { tryout_id: newId }, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => isLoading.value = false
        });
    }
});

// Reset pagination jika filter berubah
watch([itemsPerPage, search, scope], () => { currentPage.value = 1; });

// --- FILTER LOKAL (PROVINSI & INSTANSI) + TRIK BOT PENYAMARAN ---
const filteredLeaderboard = computed(() => {
    let list = props.leaderboard.map(u => ({ ...u })); // Clone agar tidak merusak data asli
    const me = list.find(u => u.is_current_user);

    if (scope.value === 'provinsi' && me?.province_code) {
        const myProv = String(me.province_code).trim();
        list = list.filter((u, index) => {
            if (u.is_current_user) return true;
            if (u.province_code != null && String(u.province_code).trim() === myProv) return true;
            
            // Trik: Gunakan index array untuk memaksa ~20% peserta lain masuk ke provinsi Anda
            // (Kita tidak menggunakan email bot lagi karena email disembunyikan backend demi privasi)
            if (index % 5 === 0) {
                u.province_code = myProv;
                return true;
            }
            return false;
        });
    } else if (scope.value === 'instansi' && me?.agency_name) {
        const myAgency = String(me.agency_name).trim();
        const myAgencyLower = myAgency.toLowerCase();
        list = list.filter((u, index) => {
            if (u.is_current_user) return true;
            if (u.agency_name && String(u.agency_name).toLowerCase().trim() === myAgencyLower) return true;
            
            // Trik: Gunakan index array untuk memaksa ~15% peserta lain berseragam instansi Anda
            if (index % 7 === 0) {
                u.agency_name = myAgency;
                u.instansi = myAgency;
                return true;
            }
            return false;
        });
    }

    // Terapkan Pencarian Nama/Instansi
    if (search.value) {
        const q = search.value.toLowerCase().trim();
        list = list.filter(u => 
            (u.user_name && u.user_name.toLowerCase().includes(q)) ||
            (u.agency_name && u.agency_name.toLowerCase().includes(q)) ||
            (u.instansi && u.instansi.toLowerCase().includes(q))
        );
    }

    // Beri nomor urut statis setelah semua filter selesai
    return list.map((user, index) => ({
        ...user,
        displayRank: index + 1
    }));
});

// Pagination
const totalPages = computed(() => Math.ceil(filteredLeaderboard.value.length / itemsPerPage.value) || 1);
const paginatedLeaderboard = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredLeaderboard.value.slice(start, start + itemsPerPage.value);
});

// --- STICKY BAR: PERINGKAT SAYA ---
const activeMyRank = computed(() => {
    return filteredLeaderboard.value.find(u => u.is_current_user) || null;
});

// Fokus ke Peringkat Sendiri
onMounted(() => {
    setTimeout(() => {
        const myRow = document.getElementById('my-ranking-row');
        if (myRow) {
            myRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 500);
});
</script>

<template>
    <Head title="Klasemen Peringkat - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F5F7] w-full pb-36 animate-in fade-in duration-500 overflow-x-hidden relative">
            
            <div class="max-w-5xl mx-auto px-3 sm:px-6 pt-5 md:pt-10 space-y-5 relative z-10 w-full box-border">
                
                <!-- HEADER & DROPDOWN TRYOUT -->
                <div class="bg-white/90 backdrop-blur-xl p-5 sm:p-6 md:p-8 rounded-[24px] md:rounded-[32px] border border-black/5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] flex flex-col md:flex-row md:items-center justify-between gap-5 w-full box-border">
                    <div class="relative z-10 space-y-1 min-w-0 flex-1">
                        <h1 class="text-[22px] sm:text-[32px] font-bold text-[#1D1D1F] tracking-tight leading-tight">Klasemen Peringkat</h1>
                        <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium">Bandingkan skormu secara Nasional, Provinsi, maupun Instansi.</p>
                    </div>

                    <!-- Pencarian & Dropdown -->
                    <div class="flex flex-col gap-3 w-full md:w-80 shrink-0">
                        <div class="relative w-full z-10">
                            <select 
                                v-model="selectedId"
                                class="w-full bg-[#F5F5F7] hover:bg-[#EAEAEF] border border-transparent rounded-[16px] px-4 py-3.5 text-[13px] sm:text-[14px] font-bold text-[#1D1D1F] focus:bg-white focus:ring-4 focus:ring-[#007AFF]/10 focus:border-[#007AFF]/40 outline-none transition-all shadow-inner appearance-none cursor-pointer"
                            >
                                <option value="" disabled>Pilih Tryout untuk melihat...</option>
                                <option v-for="to in tryouts" :key="to.id" :value="to.id">
                                    {{ to.title }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#1D1D1F]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <!-- Search Input (Hanya tampil jika ada tryout yg dipilih) -->
                        <div v-if="selectedId" class="relative w-full">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                            </div>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Cari nama / instansi..."
                                class="w-full bg-[#F5F5F7] border border-transparent rounded-[12px] pl-10 pr-4 py-2.5 text-[12px] font-medium focus:ring-2 focus:bg-white focus:ring-[#007AFF]/20 transition-all text-[#1D1D1F] placeholder:text-[#86868B] outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- TABS SEGMENTED CONTROL -->
                <div v-if="selectedId && !isLoading" class="flex justify-center w-full overflow-x-auto pb-1">
                    <div class="inline-flex bg-[#EAEAEF]/70 p-1.5 rounded-[20px] shadow-inner backdrop-blur-md border border-black/5">
                        <button @click="scope = 'nasional'" 
                            :class="['px-5 sm:px-6 py-2 rounded-[14px] text-[12px] sm:text-[13px] font-bold transition-all duration-300 whitespace-nowrap', scope === 'nasional' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                            Nasional
                        </button>
                        <button @click="scope = 'provinsi'" 
                            :class="['px-5 sm:px-6 py-2 rounded-[14px] text-[12px] sm:text-[13px] font-bold transition-all duration-300 whitespace-nowrap', scope === 'provinsi' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                            Provinsi
                        </button>
                        <button @click="scope = 'instansi'" 
                            :class="['px-5 sm:px-6 py-2 rounded-[14px] text-[12px] sm:text-[13px] font-bold transition-all duration-300 whitespace-nowrap', scope === 'instansi' ? 'bg-white text-[#1D1D1F] shadow-sm' : 'text-[#86868B] hover:text-[#1D1D1F]']">
                            Instansi
                        </button>
                    </div>
                </div>

                <!-- STATE: LOADING -->
                <div v-if="isLoading" class="bg-white/80 backdrop-blur-xl rounded-[24px] border border-black/5 p-16 flex flex-col items-center justify-center shadow-sm">
                    <svg class="animate-spin h-8 w-8 text-[#007AFF] mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p class="text-[14px] font-bold text-[#1D1D1F]">Memuat Data Klasemen...</p>
                </div>

                <!-- STATE: KOSONG -->
                <div v-else-if="!selectedId || filteredLeaderboard.length === 0" class="bg-white/80 backdrop-blur-xl rounded-[24px] sm:rounded-[32px] p-10 sm:p-16 flex flex-col items-center text-center shadow-sm border border-black/5">
                    <div class="w-16 h-16 bg-[#F5F5F7] rounded-full flex items-center justify-center mb-5 text-[#86868B]">
                        <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99-2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                    </div>
                    <h3 class="text-[18px] sm:text-[22px] text-[#1D1D1F] mb-2 font-bold">{{ !selectedId ? 'Pilih Tryout Dulu' : 'Data Tidak Ditemukan' }}</h3>
                    <p class="text-[13px] sm:text-[14px] text-[#86868B] font-medium max-w-sm leading-relaxed">
                        {{ !selectedId ? 'Silakan pilih judul tryout pada kotak di atas untuk melihat klasemen peringkat.' : 'Belum ada data peserta untuk kategori ini, atau pastikan profil Anda sudah diatur.' }}
                    </p>
                </div>

                <!-- DAFTAR PERINGKAT (JIKA ADA DATA) -->
                <div v-else class="space-y-4 w-full">
                    
                    <!-- PAGINATION (DIPINDAH KE ATAS CARD LIST) -->
                    <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between bg-white p-3 sm:p-4 rounded-[20px] shadow-[0_2px_12px_rgba(0,0,0,0.02)] border border-slate-100/80 gap-4 relative z-10 w-full box-border">
                        <div class="flex items-center gap-2 text-[11px] sm:text-[12px] text-slate-500 font-semibold">
                            <span>Tampilkan:</span>
                            <select v-model="itemsPerPage" class="bg-[#F5F5F7] border-transparent rounded-[8px] text-[11px] sm:text-[12px] py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-[#007AFF]/20 focus:bg-white focus:border-[#007AFF] outline-none cursor-pointer transition-all">
                                <option :value="20">20 Baris</option>
                                <option :value="50">50 Baris</option>
                                <option :value="100">100 Baris</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
                            <button @click="currentPage--" :disabled="currentPage === 1" class="px-4 py-2 bg-[#F2F2F7] hover:bg-[#E3E3E8] disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 text-[12px] font-bold rounded-xl transition-colors active:scale-95">Prev</button>
                            <span class="text-[11px] sm:text-[12px] font-semibold text-slate-500">Hal {{ currentPage }} / {{ totalPages }}</span>
                            <button @click="currentPage++" :disabled="currentPage === totalPages" class="px-4 py-2 bg-[#F2F2F7] hover:bg-[#E3E3E8] disabled:opacity-40 disabled:cursor-not-allowed text-slate-700 text-[12px] font-bold rounded-xl transition-colors active:scale-95">Next</button>
                        </div>
                    </div>

                    <!-- CARD LIST RESPONSIF -->
                    <div class="space-y-3 relative z-10 w-full">
                        <div v-for="(user) in paginatedLeaderboard" :key="'rank-'+user.rank" 
                             :id="user.is_current_user ? 'my-ranking-row' : ''"
                             class="bg-white rounded-[20px] p-3.5 sm:p-5 shadow-[0_2px_12px_rgba(0,0,0,0.02)] border flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition-all w-full box-border"
                             :class="user.is_current_user ? 'ring-2 ring-[#007AFF] bg-[#F0F4FF] border-transparent' : 'border-black/5 hover:shadow-sm'">
                            
                            <!-- Kiri: Peringkat & Nama & Instansi -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 shrink-0 rounded-full flex items-center justify-center font-black text-[13px] sm:text-[16px] shadow-sm tabular-nums border"
                                     :class="{
                                         'bg-gradient-to-tr from-amber-200 to-yellow-400 text-amber-900 border-amber-300': user.displayRank === 1 && !search,
                                         'bg-gradient-to-tr from-slate-200 to-slate-300 text-slate-700 border-slate-300': user.displayRank === 2 && !search,
                                         'bg-gradient-to-tr from-orange-200 to-orange-300 text-orange-900 border-orange-300': user.displayRank === 3 && !search,
                                         'bg-[#007AFF] text-white border-transparent': user.is_current_user && (user.displayRank > 3 || search),
                                         'bg-[#F5F5F7] text-[#86868B] border-transparent': !user.is_current_user && (user.displayRank > 3 || search)
                                     }">
                                    {{ user.displayRank }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 mb-0.5 flex-wrap">
                                        <h4 class="text-[13px] sm:text-[15px] font-bold truncate max-w-[160px] sm:max-w-xs" :class="user.is_current_user ? 'text-[#007AFF]' : 'text-[#1D1D1F]'">
                                            {{ user.user_name }}
                                            <span v-if="user.displayRank === 1 && !search" class="text-amber-500 ml-0.5">👑</span>
                                        </h4>
                                        <span v-if="user.is_current_user" class="px-1.5 py-0.5 bg-[#007AFF] text-white text-[8px] sm:text-[9px] font-bold rounded uppercase tracking-wider shrink-0">ME</span>
                                    </div>
                                    <p class="text-[10px] sm:text-[11px] font-medium text-[#86868B] truncate max-w-[180px] sm:max-w-md">
                                        {{ user.instansi || user.agency_name || 'Instansi belum diatur' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Kanan: Nilai TWK, TIU, TKP, Skor Akhir & Status Lulus -->
                            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-6 pt-2.5 sm:pt-0 border-t border-black/5 sm:border-0 w-full sm:w-auto shrink-0">
                                <div class="flex gap-1.5 sm:gap-2">
                                    <div class="flex flex-col items-center justify-center w-10 sm:w-14 h-10 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                        <span class="text-[7px] sm:text-[9px] font-bold text-[#86868B] uppercase">TWK</span>
                                        <span class="text-[11px] sm:text-[14px] font-bold text-[#1D1D1F] tabular-nums leading-none mt-0.5">{{ user.twk_score || user.twk }}</span>
                                    </div>
                                    <div class="flex flex-col items-center justify-center w-10 sm:w-14 h-10 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                        <span class="text-[7px] sm:text-[9px] font-bold text-[#86868B] uppercase">TIU</span>
                                        <span class="text-[11px] sm:text-[14px] font-bold text-[#1D1D1F] tabular-nums leading-none mt-0.5">{{ user.tiu_score || user.tiu }}</span>
                                    </div>
                                    <div class="flex flex-col items-center justify-center w-10 sm:w-14 h-10 sm:h-12 bg-[#F5F5F7] rounded-[10px]">
                                        <span class="text-[7px] sm:text-[9px] font-bold text-[#86868B] uppercase">TKP</span>
                                        <span class="text-[11px] sm:text-[14px] font-bold text-[#1D1D1F] tabular-nums leading-none mt-0.5">{{ user.tkp_score || user.tkp }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end min-w-[55px] sm:min-w-[70px]">
                                    <span class="text-[18px] sm:text-[24px] font-black tracking-tight tabular-nums leading-none" :class="user.is_passed ? 'text-[#34C759]' : 'text-[#1D1D1F]'">
                                        {{ user.total_score || user.score }}
                                    </span>
                                    <span class="text-[7px] sm:text-[9px] font-bold px-1.5 py-0.5 rounded mt-1 uppercase tracking-widest border inline-flex" 
                                          :class="user.is_passed ? 'bg-[#E5F5EA] text-[#34C759] border-[#34C759]/20' : 'bg-[#FFF0F0] text-[#FF3B30] border-[#FF3B30]/20'">
                                        {{ user.is_passed ? 'Lulus' : 'Gagal' }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- STICKY BOTTOM BAR : PERINGKAT SAYA -->
                <div v-if="activeMyRank && activeMyRank.displayRank > 3" class="sticky bottom-4 sm:bottom-6 z-50 pt-2 pb-4 w-full box-border">
                    <div class="bg-white/95 backdrop-blur-xl border border-black/5 shadow-[0_12px_40px_rgba(0,0,0,0.12)] rounded-[24px] p-3 sm:p-4 w-full flex items-center justify-between gap-2 sm:gap-4 ring-1 ring-white/50 box-border">
                        
                        <div class="flex items-center gap-2.5 sm:gap-4 min-w-0 flex-1">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-black text-[13px] sm:text-[17px] shadow-sm shrink-0 tabular-nums px-1">
                                {{ activeMyRank.displayRank }}
                            </div>
                            <div class="min-w-0 flex flex-col justify-center">
                                <div class="text-[8px] sm:text-[10px] font-bold text-[#86868B] uppercase tracking-widest leading-none mb-0.5">
                                    Peringkat {{ scope }}
                                </div>
                                <h4 class="text-[12px] sm:text-[14px] font-bold text-[#1D1D1F] truncate leading-tight">{{ activeMyRank.user_name || activeMyRank.name }}</h4>
                                <p class="text-[9px] sm:text-[11px] text-[#86868B] font-medium truncate mt-0.5">{{ activeMyRank.instansi || activeMyRank.agency_name || 'Instansi belum diatur' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-5 shrink-0">
                            <div class="hidden md:flex gap-2 sm:gap-3 border-r border-black/5 pr-4 sm:pr-5">
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] font-bold text-[#86868B] uppercase">TWK</span>
                                    <span class="text-[12px] font-bold text-[#1D1D1F] leading-none mt-0.5">{{ activeMyRank.twk_score || activeMyRank.twk }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] font-bold text-[#86868B] uppercase">TIU</span>
                                    <span class="text-[12px] font-bold text-[#1D1D1F] leading-none mt-0.5">{{ activeMyRank.tiu_score || activeMyRank.tiu }}</span>
                                </div>
                                <div class="flex flex-col items-center justify-center w-12 h-10 bg-[#F5F5F7] rounded-[10px]">
                                    <span class="text-[8px] font-bold text-[#86868B] uppercase">TKP</span>
                                    <span class="text-[12px] font-bold text-[#1D1D1F] leading-none mt-0.5">{{ activeMyRank.tkp_score || activeMyRank.tkp }}</span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col items-end min-w-[55px] sm:min-w-[70px]">
                                <span class="text-[18px] sm:text-[26px] font-black tracking-tight tabular-nums leading-none text-[#007AFF]">
                                    {{ activeMyRank.total_score || activeMyRank.score }}
                                </span>
                                <span class="text-[7px] sm:text-[9px] font-bold px-1.5 sm:px-2 py-0.5 rounded mt-1 uppercase tracking-widest border inline-flex" 
                                      :class="activeMyRank.is_passed ? 'bg-[#E5F5EA] text-[#34C759] border-[#34C759]/20' : 'bg-[#FFF0F0] text-[#FF3B30] border-[#FF3B30]/20'">
                                    {{ activeMyRank.is_passed ? 'Lulus' : 'Gagal' }}
                                </span>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.tabular-nums { font-variant-numeric: tabular-nums; }
.animate-in { animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1); }
</style>