<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    packages: Array,
});

// Membuat state form yang reaktif untuk masing-masing paket berdasarkan ID-nya
const forms = ref(
    props.packages.reduce((acc, pkg) => {
        acc[pkg.id] = useForm({
            name: pkg.name || '', // Mengambil nama paket dari DB
            price: pkg.price,
            duration_days: pkg.duration_days,
            is_active: pkg.is_active !== undefined ? pkg.is_active : true,
        });
        return acc;
    }, {})
);

const updatePackage = (id) => {
    // Mengambil nama terbaru dari form untuk keperluan alert notification
    const currentName = forms.value[id].name || `Paket ID ${id}`;

    forms.value[id].post(route('admin.membership-packages.update', id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Diperbarui!',
                text: `${currentName} telah berhasil disimpan ke database.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                customClass: {
                    popup: 'rounded-xl border-none shadow-lg',
                    title: 'font-medium text-slate-800'
                }
            });
        },
        onError: () => {
             Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: `Mohon periksa kembali kelengkapan input data paket Anda.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                customClass: {
                    popup: 'rounded-xl border-none shadow-lg'
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Kelola Paket Membership" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Manajemen Paket Adidaya</h2>
        </template>

        <div class="py-8 md:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 animate-in fade-in duration-500">
                
                <div class="mb-8 bg-indigo-50 border border-indigo-100 rounded-[2rem] p-6 md:p-8 flex items-center justify-between shadow-sm">
                    <div class="max-w-xl">
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                            Live Database
                        </span>
                        <h3 class="text-xl md:text-2xl font-bold text-slate-900 mb-2 tracking-tight">Pengaturan Atribut Paket</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Perubahan Nama Paket, Harga (Rp), dan Durasi masa aktif (Hari) akan langsung tersinkronisasi dan berubah secara otomatis pada halaman pembelian User.
                        </p>
                    </div>
                    <div class="hidden lg:flex shrink-0">
                        <div class="w-24 h-24 bg-white/60 rounded-full flex items-center justify-center shadow-inner">
                            <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div v-for="pkg in packages" :key="pkg.id" 
                         class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-slate-100 hover:shadow-lg transition-all duration-300 hover:border-indigo-100 group">
                        
                        <div class="p-6 md:p-8">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center group-hover:bg-indigo-50 group-hover:text-indigo-600 group-hover:border-indigo-100 transition-colors">
                                        <svg class="w-6 h-6 text-slate-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-medium text-slate-400 uppercase tracking-widest mb-1">Pratinjau Nama</p>
                                        <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                            {{ forms[pkg.id].name || 'Tanpa Nama Paket...' }}
                                        </h3>
                                    </div>
                                </div>
                                <span :class="forms[pkg.id].is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-red-50 text-red-600 border-red-200'" 
                                      class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-widest border">
                                    {{ forms[pkg.id].is_active ? 'Status: Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <form @submit.prevent="updatePackage(pkg.id)" class="space-y-4">
                                
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Nama Paket Premium</label>
                                    <input 
                                        type="text" 
                                        v-model="forms[pkg.id].name"
                                        class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full px-4 sm:text-sm border-slate-200 rounded-xl py-3.5 bg-slate-50 hover:bg-white transition-all text-slate-900 font-semibold shadow-sm"
                                        placeholder="Contoh: Mastery Plan / Sprint Flash"
                                        required
                                    >
                                    <p v-if="forms[pkg.id].errors.name" class="mt-2 text-[11px] text-red-500 font-medium">{{ forms[pkg.id].errors.name }}</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Harga (Rupiah)</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <span class="text-slate-400 font-medium sm:text-sm">Rp</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                v-model="forms[pkg.id].price"
                                                class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 pr-4 sm:text-sm border-slate-200 rounded-xl py-3.5 bg-slate-50 hover:bg-white transition-all text-slate-900 font-semibold shadow-sm"
                                                placeholder="0"
                                                required
                                            >
                                        </div>
                                        <p v-if="forms[pkg.id].errors.price" class="mt-2 text-[11px] text-red-500 font-medium">{{ forms[pkg.id].errors.price }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 uppercase tracking-wide mb-2">Masa Aktif</label>
                                        <div class="relative">
                                            <input 
                                                type="number" 
                                                v-model="forms[pkg.id].duration_days"
                                                class="focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-4 pr-16 sm:text-sm border-slate-200 rounded-xl py-3.5 bg-slate-50 hover:bg-white transition-all text-slate-900 font-semibold shadow-sm text-left"
                                                placeholder="30"
                                                required
                                            >
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none border-l border-slate-200 pl-4 my-2">
                                                <span class="text-slate-400 font-medium sm:text-sm">Hari</span>
                                            </div>
                                        </div>
                                        <p v-if="forms[pkg.id].errors.duration_days" class="mt-2 text-[11px] text-red-500 font-medium">{{ forms[pkg.id].errors.duration_days }}</p>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button 
                                        type="submit" 
                                        :disabled="forms[pkg.id].processing"
                                        :class="forms[pkg.id].processing ? 'bg-slate-300 cursor-not-allowed text-slate-500' : 'bg-slate-900 text-white hover:bg-indigo-600 hover:shadow-lg hover:shadow-indigo-500/20 active:scale-[0.98]'"
                                        class="w-full py-4 rounded-xl text-[11px] font-bold uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 border border-transparent"
                                    >
                                        <span v-if="forms[pkg.id].processing">Menyimpan Perubahan...</span>
                                        <span v-else>Simpan Paket</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation-name: fadeIn;
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>