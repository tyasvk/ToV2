<template>
  <!-- Komponen Head dari Inertia untuk merubah title tab browser -->
  <Head title="Paket Bundling Tryout" />

  <AuthenticatedLayout>
    <!-- Slot Header (Bawaan standar Laravel Breeze/Jetstream) -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Paket Bundling Tryout
      </h2>
    </template>

    <!-- Container Utama -->
    <!-- pb-32 memberikan ruang ekstra di bawah agar konten tidak tertutup sticky bar -->
    <div class="py-8 pb-32">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Penjelasan Singkat -->
        <div class="px-4 sm:px-0 mb-4">
          <p class="text-sm text-gray-500">Pilih minimal 3 tryout arsip untuk berlatih.</p>
        </div>

        <!-- Tryout List -->
        <div class="px-4 sm:px-0 space-y-3">
          <div 
            v-for="tryout in tryouts" 
            :key="tryout.id"
            @click="toggleSelection(tryout.id)"
            class="bg-white p-4 rounded-xl shadow-sm border-2 cursor-pointer transition-all duration-200 hover:shadow-md"
            :class="isSelected(tryout.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-100'"
          >
            <div class="flex justify-between items-center">
              <div>
                <h2 class="font-semibold text-gray-800">{{ tryout.title }}</h2>
                <p class="text-xs text-gray-500 mt-1">Ditutup: {{ formatDate(tryout.end_date) }}</p>
                <p class="mt-2 font-bold text-blue-600">Rp {{ formatPrice(tryout.price) }}</p>
              </div>
              
              <!-- Checkbox UI -->
              <div 
                class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors flex-shrink-0 ml-4"
                :class="isSelected(tryout.id) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 bg-white'"
              >
                <svg v-if="isSelected(tryout.id)" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>

          <!-- State Kosong jika tidak ada tryout arsip -->
          <div v-if="tryouts.length === 0" class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="text-gray-500 font-medium">Belum ada tryout yang tersedia.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Sticky Bottom Bar untuk Checkout -->
    <!-- Catatan: Class lg:left-64 / md:left-64 menyesuaikan lebar sidebar Anda di desktop. 
         Jika lebar sidebar Anda berbeda, Anda bisa menyesuaikan angka 64 ini (64 = 16rem = 256px). -->
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white border-t p-4 shadow-[0_-4px_15px_-3px_rgba(0,0,0,0.1)] z-40 transition-all duration-300">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 w-full">
        <div class="flex justify-between items-center mb-3 px-4 sm:px-0">
          <span class="text-sm font-medium text-gray-600">
            Terpilih: <span :class="selectedIds.length < 3 ? 'text-red-500' : 'text-green-500'">{{ selectedIds.length }}/3</span>
          </span>
          <span class="font-bold text-lg text-blue-600">Rp {{ formatPrice(totalPrice) }}</span>
        </div>
        
        <div class="px-4 sm:px-0">
          <button 
            @click="submitBundle"
            :disabled="selectedIds.length < 3"
            class="w-full py-3 rounded-lg font-bold text-white transition-all duration-200 flex justify-center items-center gap-2"
            :class="selectedIds.length < 3 ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-lg hover:shadow-xl'"
          >
            Lanjutkan Pembayaran
            <svg v-if="selectedIds.length >= 3" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
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

// 1. IMPORT LAYOUT ANDA DI SINI
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