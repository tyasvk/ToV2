<template>
  <Head title="Paket Bundling Tryout" />

  <AuthenticatedLayout>
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

    <div class="py-6 md:py-8 pb-64 animate-in fade-in slide-in-from-bottom-4 duration-500 relative z-10">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Banner Penjelasan -->
        <div class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4 flex items-start gap-3 shadow-sm">
          <div class="text-blue-500 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-blue-900">Ketentuan Bundling</h3>
            <p class="text-xs text-blue-700 mt-0.5 leading-relaxed">
              Pilih minimal <strong>3 tryout arsip</strong> di bawah ini untuk membuka fitur bundling.
            </p>
          </div>
        </div>

        <!-- Daftar Tryout -->
        <div class="space-y-3 md:space-y-2">
          <div 
            v-for="tryout in tryouts" 
            :key="tryout.id"
            @click="toggleSelection(tryout.id)"
            class="group relative bg-white p-3.5 sm:px-4 sm:py-3 md:py-2.5 rounded-xl shadow-sm border-2 cursor-pointer transition-all duration-300 overflow-hidden"
            :class="isSelected(tryout.id) ? 'border-blue-500 shadow-blue-100 shadow-md bg-blue-50/30 ring-1 ring-blue-500' : 'border-slate-200 hover:border-blue-300 hover:shadow-md'"
          >
            <div class="flex justify-between items-center relative z-10">
              <div class="pr-3">
                <h2 class="font-bold text-slate-800 text-sm sm:text-base leading-tight group-hover:text-blue-700 transition-colors">
                  {{ tryout.title }}
                </h2>
                <p class="mt-1.5 md:mt-0.5 font-black text-base sm:text-lg text-slate-900">
                  Rp {{ formatPrice(tryout.price) }}
                </p>
              </div>
              <div class="shrink-0 flex items-center justify-center">
                <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                  :class="isSelected(tryout.id) ? 'bg-blue-600 border-blue-600 scale-110 shadow-md shadow-blue-200' : 'border-slate-300 bg-slate-50 group-hover:bg-blue-50'">
                  <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white transition-transform duration-300" 
                    :class="isSelected(tryout.id) ? 'scale-100 opacity-100' : 'scale-50 opacity-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <div v-if="tryouts.length === 0" class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300">
            <h3 class="text-slate-800 font-bold text-base">Tryout Kosong</h3>
            <p class="text-sm text-slate-500 font-medium mt-1">Belum ada tryout arsip yang tersedia.</p>
          </div>
          <div class="h-28 sm:h-20 w-full opacity-0 pointer-events-none"></div>
        </div>
      </div>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white/90 backdrop-blur-md border-t border-slate-200 p-3 sm:p-4 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.1)] z-40 transition-all duration-300">
      <div class="max-w-3xl mx-auto w-full">
        <div class="flex items-center gap-2 mb-2 px-2 sm:px-0">
          <div class="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden flex">
            <div class="h-full bg-blue-500 transition-all duration-500 ease-out" :style="{ width: Math.min((selectedIds.length / 3) * 100, 100) + '%' }"></div>
          </div>
          <span class="text-xs font-bold shrink-0" :class="selectedIds.length >= 3 ? 'text-green-600' : 'text-slate-500'">
            {{ selectedIds.length }} / 3 Item
          </span>
        </div>
        <div class="flex justify-between items-center mb-3 px-2 sm:px-0">
          <span class="text-xs sm:text-sm font-bold text-slate-500 uppercase tracking-wider">Total Tagihan</span>
          <span class="font-black text-lg sm:text-xl text-slate-900 transition-all duration-300">
            Rp {{ formatPrice(totalPrice) }}
          </span>
        </div>
        <div class="px-2 sm:px-0">
          <!-- Tombol memicu modal, bukan langsung submit -->
          <button @click="openPaymentModal" :disabled="selectedIds.length < 3"
            class="w-full py-2.5 sm:py-3 rounded-xl font-bold text-white transition-all duration-300 flex justify-center items-center gap-2 text-sm sm:text-base"
            :class="selectedIds.length < 3 ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-slate-900 hover:bg-indigo-600 shadow-lg'">
            Lanjutkan Pembayaran
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="showModal = false"></div>
      
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative z-10 animate-in zoom-in-95 duration-200">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <h3 class="font-bold text-lg text-slate-800">Metode Pembayaran</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-red-500 bg-white rounded-full p-1 shadow-sm border border-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="p-5 space-y-3">
          <!-- Opsi Dompet -->
          <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
            :class="paymentMethod === 'wallet' ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-blue-300'">
            <div class="flex items-center gap-3">
              <input type="radio" v-model="paymentMethod" value="wallet" class="w-5 h-5 text-blue-600 border-slate-300 focus:ring-blue-500">
              <div>
                <span class="block font-bold text-slate-800">Saldo Dompet</span>
                <span class="block text-xs font-medium" :class="userBalance >= totalPrice ? 'text-green-600' : 'text-red-500'">
                  Saldo Anda: Rp {{ formatPrice(userBalance) }}
                </span>
              </div>
            </div>
            <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
          </label>

          <!-- Opsi Midtrans -->
          <label class="flex items-center justify-between p-4 border-2 rounded-xl cursor-pointer transition-all duration-200"
            :class="paymentMethod === 'midtrans' ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-blue-300'">
            <div class="flex items-center gap-3">
              <input type="radio" v-model="paymentMethod" value="midtrans" class="w-5 h-5 text-blue-600 border-slate-300 focus:ring-blue-500">
              <div>
                <span class="block font-bold text-slate-800">QRIS / Transfer Bank</span>
                <span class="block text-xs text-slate-500 font-medium">Bebas biaya admin</span>
              </div>
            </div>
            <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
          </label>

          <!-- Pesan Error Saldo -->
          <div v-if="paymentMethod === 'wallet' && userBalance < totalPrice" class="mt-2 p-3 bg-red-50 text-red-600 text-xs font-medium rounded-lg border border-red-100 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Saldo Anda tidak mencukupi. Silakan topup atau gunakan metode QRIS.
          </div>
        </div>

        <div class="p-5 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
          <button @click="processPayment" :disabled="isLoading || (paymentMethod === 'wallet' && userBalance < totalPrice)"
            class="w-full py-3 rounded-xl font-bold text-white transition-all duration-300 flex justify-center items-center gap-2 shadow-md"
            :class="(paymentMethod === 'wallet' && userBalance < totalPrice) ? 'bg-slate-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 active:scale-[0.98]'">
            <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span v-else>Bayar Sekarang - Rp {{ formatPrice(totalPrice) }}</span>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  tryouts: { type: Array, default: () => [] }
});

const page = usePage();
// Mengambil data saldo user dari Inertia global props
const userBalance = computed(() => page.props.auth.user.balance || 0);

const selectedIds = ref([]);
const showModal = ref(false);
const paymentMethod = ref('wallet'); // Default pilihan pembayaran
const isLoading = ref(false);

const toggleSelection = (id) => {
  const index = selectedIds.value.indexOf(id);
  if (index === -1) selectedIds.value.push(id);
  else selectedIds.value.splice(index, 1);
};

const isSelected = (id) => selectedIds.value.includes(id);

const totalPrice = computed(() => {
  return props.tryouts
    .filter(t => selectedIds.value.includes(t.id))
    .reduce((sum, t) => sum + Number(t.price), 0);
});

const formatPrice = (value) => new Intl.NumberFormat('id-ID').format(value);

const openPaymentModal = () => {
  if (selectedIds.value.length >= 3) {
    showModal.value = true;
  }
};

const processPayment = () => {
  if (paymentMethod.value === 'wallet' && userBalance.value < totalPrice.value) return;

  isLoading.value = true;
  router.post(route('user.bundling.checkout'), {
    tryout_ids: selectedIds.value,
    payment_method: paymentMethod.value // Kirim data metode pembayaran ke Backend
  }, {
    onFinish: () => { isLoading.value = false; }
  });
};
</script>

<style scoped>
.animate-in { animation-duration: 0.3s; animation-fill-mode: both; animation-timing-function: ease-out; }
@keyframes zoom-in-95 { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>