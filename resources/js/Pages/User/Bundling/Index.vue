<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <!-- Header -->
    <header class="bg-white p-4 shadow-sm sticky top-0 z-40">
      <h1 class="text-xl font-bold text-gray-800">Paket Bundling Tryout</h1>
      <p class="text-sm text-gray-500">Pilih minimal 3 tryout arsip untuk berlatih.</p>
    </header>

    <!-- Tryout List -->
    <div class="p-4 space-y-3">
      <div 
        v-for="tryout in tryouts" 
        :key="tryout.id"
        @click="toggleSelection(tryout.id)"
        class="bg-white p-4 rounded-xl shadow-sm border-2 cursor-pointer transition-all duration-200"
        :class="isSelected(tryout.id) ? 'border-blue-500 bg-blue-50' : 'border-transparent'"
      >
        <div class="flex justify-between items-center">
          <div>
            <h2 class="font-semibold text-gray-800">{{ tryout.title }}</h2>
            <p class="text-xs text-gray-500">Ditutup: {{ formatDate(tryout.end_date) }}</p>
            <p class="mt-2 font-bold text-blue-600">Rp {{ formatPrice(tryout.price) }}</p>
          </div>
          <!-- Checkbox UI -->
          <div 
            class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors"
            :class="isSelected(tryout.id) ? 'bg-blue-500 border-blue-500' : 'border-gray-300'"
          >
            <svg v-if="isSelected(tryout.id)" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
      </div>

      <!-- State Kosong jika tidak ada tryout arsip -->
      <div v-if="tryouts.length === 0" class="text-center py-10 text-gray-500">
        Belum ada tryout yang tersedia untuk bundling saat ini.
      </div>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50">
      <div class="flex justify-between items-center mb-3">
        <span class="text-sm font-medium text-gray-600">
          Terpilih: <span :class="selectedIds.length < 3 ? 'text-red-500' : 'text-green-500'">{{ selectedIds.length }}/3</span>
        </span>
        <span class="font-bold text-lg text-blue-600">Rp {{ formatPrice(totalPrice) }}</span>
      </div>
      
      <button 
        @click="submitBundle"
        :disabled="selectedIds.length < 3"
        class="w-full py-3 rounded-lg font-bold text-white transition-colors"
        :class="selectedIds.length < 3 ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800'"
      >
        Lanjutkan Pembayaran
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

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