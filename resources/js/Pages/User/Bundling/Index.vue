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

<template>
  <Head title="Paket Bundling Tryout" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#F5F5F7] border border-black/5 text-[#1D1D1F] rounded-full flex items-center justify-center shadow-sm">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
          </svg>
        </div>
        <h2 class="font-bold text-[20px] text-[#1D1D1F] tracking-tight leading-tight">
          Paket Bundling Tryout
        </h2>
      </div>
    </template>

    <div class="w-full bg-transparent pb-36 animate-in fade-in duration-500 font-sans">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-6 md:pt-8">
        
        <!-- Banner Penjelasan -->
        <div class="mb-6 bg-[#F0F4FF] border border-[#007AFF]/10 rounded-[24px] p-5 flex items-start gap-4 shadow-sm">
          <div class="w-10 h-10 rounded-full bg-white text-[#007AFF] shrink-0 flex items-center justify-center shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="pt-0.5">
            <h3 class="text-[15px] font-bold text-[#1D1D1F] tracking-tight">Ketentuan Bundling</h3>
            <p class="text-[13px] text-[#86868B] mt-1 font-medium leading-relaxed">
              Pilih minimal <strong class="text-[#1D1D1F]">3 tryout arsip</strong> di bawah ini untuk membuka fitur pembayaran bundling.
            </p>
          </div>
        </div>

        <!-- Daftar Tryout -->
        <div class="space-y-3">
          <div 
            v-for="tryout in tryouts" 
            :key="tryout.id"
            @click="toggleSelection(tryout.id)"
            class="group relative bg-white p-5 rounded-[24px] border-2 cursor-pointer transition-all duration-300 overflow-hidden flex items-center justify-between"
            :class="isSelected(tryout.id) ? 'border-[#007AFF] bg-[#F0F4FF] shadow-[0_4px_20px_rgba(0,122,255,0.08)]' : 'border-black/5 hover:border-black/10 shadow-[0_2px_10px_rgba(0,0,0,0.02)]'"
          >
            <div class="pr-4">
              <h2 class="font-bold text-[#1D1D1F] text-[16px] sm:text-[17px] leading-snug mb-1.5 transition-colors"
                  :class="isSelected(tryout.id) ? 'text-[#007AFF]' : 'group-hover:text-[#007AFF]'">
                {{ tryout.title }}
              </h2>
              <p class="font-bold text-[14px] text-[#86868B]">
                Rp {{ formatPrice(tryout.price) }}
              </p>
            </div>
            
            <div class="shrink-0 flex items-center justify-center ml-2">
              <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                :class="isSelected(tryout.id) ? 'bg-[#007AFF] border-[#007AFF] scale-110 shadow-sm' : 'border-[#D1D1D6] bg-white group-hover:border-[#86868B]'">
                <svg class="w-3.5 h-3.5 text-white transition-transform duration-300" 
                  :class="isSelected(tryout.id) ? 'scale-100 opacity-100' : 'scale-50 opacity-0'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>

          <div v-if="tryouts.length === 0" class="text-center p-12 bg-white rounded-[32px] border border-black/5 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <div class="w-16 h-16 bg-[#F5F5F7] text-[#86868B] rounded-full flex items-center justify-center mx-auto mb-4">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
            </div>
            <h3 class="text-[#1D1D1F] font-bold text-[18px] mb-1">Tryout Kosong</h3>
            <p class="text-[14px] text-[#86868B] font-medium">Belum ada tryout arsip yang tersedia untuk dibundel saat ini.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Bottom Bar (Glassmorphism) -->
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white/80 backdrop-blur-xl border-t border-black/5 p-4 sm:p-6 z-40 transition-all duration-300">
      <div class="max-w-3xl mx-auto w-full flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <div class="w-full flex-1">
          <div class="flex items-center justify-between gap-4 mb-2.5">
            <span class="text-[12px] font-bold uppercase tracking-wider" :class="selectedIds.length >= 3 ? 'text-[#34C759]' : 'text-[#86868B]'">
              {{ selectedIds.length }} / 3 Item Terpilih
            </span>
            <div class="flex items-baseline gap-1.5">
              <span class="text-[11px] font-bold text-[#86868B] uppercase tracking-wider">Total</span>
              <span class="font-black text-[18px] sm:text-[20px] text-[#1D1D1F] tracking-tight">
                Rp {{ formatPrice(totalPrice) }}
              </span>
            </div>
          </div>
          <!-- Progress Bar -->
          <div class="w-full h-1.5 bg-[#E3E3E8] rounded-full overflow-hidden">
            <div class="h-full transition-all duration-500 ease-out" 
                 :class="selectedIds.length >= 3 ? 'bg-[#34C759]' : 'bg-[#007AFF]'"
                 :style="{ width: Math.min((selectedIds.length / 3) * 100, 100) + '%' }">
            </div>
          </div>
        </div>

        <div class="w-full sm:w-auto shrink-0">
          <button @click="openPaymentModal" :disabled="selectedIds.length < 3"
            class="w-full sm:w-auto px-8 py-3.5 rounded-full font-semibold text-[14px] transition-all duration-300 flex justify-center items-center gap-2"
            :class="selectedIds.length < 3 ? 'bg-[#F5F5F7] text-[#86868B] cursor-not-allowed border border-black/5' : 'bg-[#007AFF] text-white hover:bg-[#0062CC] shadow-[0_4px_14px_rgba(0,122,255,0.3)] active:scale-[0.98]'">
            Lanjutkan Pembayaran
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/20 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
      
      <!-- Modal Content -->
      <div class="bg-white rounded-[32px] shadow-[0_20px_60px_rgba(0,0,0,0.15)] w-full max-w-md overflow-hidden relative z-10 animate-in zoom-in-95 duration-200">
        
        <!-- Modal Header -->
        <div class="p-6 border-b border-black/5 flex justify-between items-start bg-white">
          <div>
            <h3 class="font-bold text-[20px] text-[#1D1D1F] tracking-tight mb-1">Metode Pembayaran</h3>
            <p class="text-[13px] text-[#86868B] font-medium">Pilih jalur pembayaran untuk transaksi ini.</p>
          </div>
          <button @click="showModal = false" class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-[#E3E3E8] flex items-center justify-center text-[#86868B] transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 space-y-4">
          <!-- Opsi Dompet -->
          <label class="flex items-center justify-between p-5 border-2 rounded-[20px] cursor-pointer transition-all duration-200 group"
            :class="paymentMethod === 'wallet' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-black/5 hover:border-black/10 bg-[#F5F5F7]'">
            <div class="flex items-center gap-4">
              <!-- Radio Button Check -->
              <div class="shrink-0">
                 <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                      :class="paymentMethod === 'wallet' ? 'border-[#007AFF] bg-[#007AFF]' : 'border-[#D1D1D6] bg-white'">
                      <svg v-if="paymentMethod === 'wallet'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                 </div>
              </div>
              <div>
                <span class="block font-bold text-[15px] text-[#1D1D1F] mb-0.5">Saldo Dompet</span>
                <span class="block text-[12px] font-medium" :class="userBalance >= totalPrice ? 'text-[#86868B]' : 'text-[#FF3B30]'">
                  Saldo Anda: Rp {{ formatPrice(userBalance) }}
                </span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5 text-[#007AFF]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
            </div>
          </label>

          <!-- Opsi Midtrans -->
          <label class="flex items-center justify-between p-5 border-2 rounded-[20px] cursor-pointer transition-all duration-200 group"
            :class="paymentMethod === 'midtrans' ? 'border-[#007AFF] bg-[#F0F4FF]' : 'border-black/5 hover:border-black/10 bg-[#F5F5F7]'">
            <div class="flex items-center gap-4">
              <!-- Radio Button Check -->
              <div class="shrink-0">
                 <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                      :class="paymentMethod === 'midtrans' ? 'border-[#007AFF] bg-[#007AFF]' : 'border-[#D1D1D6] bg-white'">
                      <svg v-if="paymentMethod === 'midtrans'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                 </div>
              </div>
              <div>
                <span class="block font-bold text-[15px] text-[#1D1D1F] mb-0.5">QRIS / Transfer Bank</span>
                <span class="block text-[12px] text-[#86868B] font-medium">Bebas biaya penanganan</span>
              </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5 text-[#34C759]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
            </div>
          </label>

          <!-- Pesan Error Saldo -->
          <div v-if="paymentMethod === 'wallet' && userBalance < totalPrice" class="mt-2 p-3.5 bg-[#FFF0F0] text-[#FF3B30] text-[12px] font-medium rounded-[12px] border border-[#FF3B30]/20 flex items-start gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>Saldo Anda tidak mencukupi. Silakan pilih opsi QRIS/Transfer.</span>
          </div>
        </div>

        <div class="p-6 pt-2">
          <button @click="processPayment" :disabled="isLoading || (paymentMethod === 'wallet' && userBalance < totalPrice)"
            class="w-full py-4 rounded-full font-semibold text-[15px] text-white transition-all duration-300 flex justify-center items-center gap-2"
            :class="(paymentMethod === 'wallet' && userBalance < totalPrice) ? 'bg-[#F5F5F7] text-[#86868B] cursor-not-allowed border border-black/5' : 'bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] shadow-[0_4px_14px_rgba(0,122,255,0.3)]'">
            <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span v-else>Bayar Rp {{ formatPrice(totalPrice) }}</span>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation-duration: 0.3s; animation-fill-mode: both; animation-timing-function: ease-out; }
@keyframes zoom-in-95 { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>