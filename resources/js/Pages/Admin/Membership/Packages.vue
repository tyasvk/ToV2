<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    packages: Array,
});

const page = usePage();

// State Modal
const isAddingPackage = ref(false);

const openAddModal = () => {
    isAddingPackage.value = true;
};

const closeAddModal = () => {
    isAddingPackage.value = false;
    form.reset();
    form.clearErrors();
};

// Form Tambah
const form = useForm({
    name: '',
    price: '',
    duration_days: '',
});

// Submit Data
const submitPackage = () => {
    form.post(route('admin.membership-packages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Paket membership berhasil ditambahkan.',
                showConfirmButton: false,
                timer: 1500
            });
        },
    });
};

// Hapus Data dengan SweetAlert
const deletePackage = (id, name) => {
    Swal.fire({
        title: 'Hapus Paket?',
        text: `Anda yakin ingin menghapus paket "${name}"? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.membership-packages.destroy', id), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: 'Paket berhasil dihapus.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        }
    });
};

// Format Rupiah
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number);
};
</script>

<template>
    <Head title="Manajemen Paket Membership" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Paket Membership</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ page.props.flash.success }}</span>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Daftar Paket Membership</h3>
                            <PrimaryButton @click="openAddModal">
                                + Tambah Paket
                            </PrimaryButton>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Paket
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Harga
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Masa Aktif
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(pkg, index) in packages" :key="pkg.id" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ pkg.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                            {{ formatRupiah(pkg.price) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ pkg.duration_days }} Hari
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button 
                                                @click="deletePackage(pkg.id, pkg.name)" 
                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700 rounded-md text-xs font-semibold tracking-widest transition ease-in-out duration-150"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="packages.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                                            Belum ada paket membership yang dibuat.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <Modal :show="isAddingPackage" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2">
                    Buat Paket Membership Baru
                </h2>
                
                <form @submit.prevent="submitPackage">
                    <div class="mb-4">
                        <InputLabel for="name" value="Nama Paket (contoh: Premium VIP)" />
                        <TextInput 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            class="mt-1 block w-full" 
                            placeholder="Masukkan nama paket"
                            required 
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="price" value="Harga Paket (Rp)" />
                        <TextInput 
                            id="price" 
                            v-model="form.price" 
                            type="number" 
                            class="mt-1 block w-full" 
                            placeholder="Contoh: 150000"
                            required 
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                        <p class="text-xs text-gray-500 mt-1">Tanpa titik atau koma.</p>
                    </div>

                    <div class="mb-6">
                        <InputLabel for="duration_days" value="Durasi / Masa Aktif (Hari)" />
                        <TextInput 
                            id="duration_days" 
                            v-model="form.duration_days" 
                            type="number" 
                            class="mt-1 block w-full" 
                            placeholder="Contoh: 30"
                            required 
                        />
                        <InputError :message="form.errors.duration_days" class="mt-2" />
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeAddModal">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton 
                            class="ml-3" 
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            Simpan Paket
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>