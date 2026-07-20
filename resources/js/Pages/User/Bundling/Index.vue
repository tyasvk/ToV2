<template>
  <!-- Komponen Head dari Inertia untuk merubah title tab browser -->
  <Head title="Paket Bundling Tryout" />

  <AuthenticatedLayout>
    <!-- Slot Header -->
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-lg shadow-sm">
          📦
        </div>
        <h2 class="font-bold text-xl text-slate-800 leading-tight">
          Paket Bundling Tryout
        </h2>
      </div>
    </template>

    <!-- Container Utama -->
    <div class="py-6 md:py-8 pb-36 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Banner Penjelasan Singkat -->
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3 shadow-sm">
          <div class="text-blue-500 mt-0.5 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-blue-900">Ketentuan Bundling</h3>
            <p class="text-xs text-blue-700 mt-0.5 leading-relaxed">
              Pilih minimal <strong>3 tryout arsip</strong> di bawah ini untuk membuka fitur bundling. Semakin banyak berlatih, semakin dekat dengan kelulusan!
            </p>
          </div>
        </div>

        <!-- Tryout List -->
        <div class="space-y-4">
          <div 
            v-for="tryout in tryouts" 
            :key="tryout.id"
            @click="toggleSelection(tryout.id)"
            class="group relative bg-white p-4 sm:p-5 rounded-2xl shadow-sm border-2 cursor-pointer transition-all duration-300 overflow-hidden"
            :class="isSelected(tryout.id) ? 'border-blue-500 shadow-blue-100 shadow-md bg-blue-50/30 ring-1 ring-blue-500' : 'border-slate-200 hover:border-blue-300 hover:shadow-md'"
          >
            <!-- Background aksen saat dipilih -->
            <div 
              class="absolute -right-10 -top-10 w-32 h-32 bg-blue-500 rounded-full blur-3xl opacity-0 transition-opacity duration-500 pointer-events-none"
              :class="isSelected(tryout.id) ? 'opacity-10' : ''"
            ></div>

            <div class="flex justify-between items-center relative z-10">
              <div class="pr-4">
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-md">Arsip</span>
                  <span class="text-xs font-medium text-slate-500 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ditutup: {{ formatDate(tryout.end_date) }}
                  </span>
                </div>
                <h2 class="font-bold text-slate-800 text-base sm:text-lg leading-tight group-hover:text-blue-700 transition-colors">
                  {{ tryout.title }}
                </h2>
                <p class="mt-2 font-black text-lg text-slate-900">
                  Rp {{ formatPrice(tryout.price) }}
                </p>
              </div>
              
              <!-- Checkbox UI Animasi -->
              <div class="shrink-0 flex items-center justify-center">
                <div 
                  class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                  :class="isSelected(tryout.id) ? 'bg-blue-600 border-blue-600 scale-110 shadow-md shadow-blue-200' : 'border-slate-300 bg-slate-50 group-hover:bg-blue-50'"
                >
                  <svg 
                    class="w-4 h-4 sm:w-5 sm:h-5 text-white transition-transform duration-300" 
                    :class="isSelected(tryout.id) ? 'scale-100 opacity-100' : 'scale-50 opacity-0'"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- State Kosong jika tidak ada tryout arsip -->
          <div v-if="tryouts.length === 0" class="text-center py-16 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-300 flex flex-col items-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100 mb-4">
              <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <h3 class="text-slate-800 font-bold text-lg">Tryout Kosong</h3>
            <p class="text-slate-500 font-medium mt-1">Belum ada tryout arsip yang tersedia untuk dibeli.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Sticky Bottom Bar (Glassmorphism Effect) -->
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white/80 backdrop-blur-md border-t border-slate-200 p-4 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)] z-40 transition-all duration-300">
      <div class="max-w-3xl mx-auto w-full">
        
        <!-- Progress Bar Indicator untuk Minimal 3 -->
        <div class="flex items-center gap-2 mb-3 px-2 sm:px-0">
          <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden flex">
            <div class="h-full bg-blue-500 transition-all duration-500 ease-out" :style="{ width: Math.min((selectedIds.length / 3) * 100, 100) + '%' }"></div>
          </div>
          <span class="text-xs font-bold shrink-0" :class="selectedIds.length >= 3 ? 'text-green-600' : 'text-slate-500'">
            {{ selectedIds.length }} / 3 Item
          </span>
        </div>

        <div class="flex justify-between items-center mb-4 px-2 sm:px-0">
          <span class="text-sm font-bold text-slate-500 uppercase tracking-wider">
            Total Tagihan
          </span>
          <span class="font-black text-xl text-slate-900 transition-all duration-300">
            Rp {{ formatPrice(totalPrice) }}
          </span>
        </div>
        
        <div class="px-2 sm:px-0">
          <button 
            @click="submitBundle"
            :disabled="selectedIds.length < 3"
            class="w-full py-3.5 rounded-xl font-bold text-white transition-all duration-300 flex justify-center items-center gap-2 relative overflow-hidden group"
            :class="selectedIds.length < 3 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-indigo-600 active:scale-[0.98] shadow-xl hover:shadow-indigo-500/30'"
          >
            <!-- Efek kilap saat tombol aktif -->
            <div v-if="selectedIds.length >= 3" class="absolute inset-0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
            
            <span>Lanjutkan Pembayaran</span>
            <svg v-if="selectedIds.length >= 3" class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  tryouts: {
    type: Array,
    default: () => []
  }
});

const selectedIds = ref([]);

// Fungsi untuk memilih/batal memilih tryout
const toggleSelection = (id) => {
  const index = selectedIds.value.indexOf(id);
  if (index === -1) {
    selectedIds.value.push(id);
  } else {
    selectedIds.value.splice(index, 1);
  }
};

// Cek apakah tryout sedang dipilih
const isSelected = (id) => selectedIds.value.includes(id);

// Menghitung total harga secara dinamis
const totalPrice = computed(() => {
  return props.tryouts
    .filter(t => selectedIds.value.includes(t.id))
    .reduce((sum, t) => sum + Number(t.price), 0);
});

// Format Harga ke Rupiah
const formatPrice = (value) => {
  return new Intl.NumberFormat('id-ID').format(value);
};

// Format Tanggal
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
};

// Fungsi Submit
const submitBundle = () => {
  if (selectedIds.value.length >= 3) {
    router.post(route('user.bundling.checkout'), {
      tryout_ids: selectedIds.value
    });
  }
};
</script>

<style scoped>
/* Custom animations */
.animate-in {
  animation-duration: 0.5s;
  animation-fill-mode: both;
  animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>