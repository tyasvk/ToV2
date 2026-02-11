<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tryout: Object,
    is_registered: Boolean,
    pending_transaction: Object,
    is_registration_closed: Boolean, // Menerima status penutupan
});

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const registerTryout = () => {
    router.post(route('tryout.register', props.tryout.id));
};
</script>

<template>
    <Head :title="tryout.title" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Tryout</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="relative h-48 bg-indigo-600 text-white p-8 flex flex-col justify-center">
                        <h1 class="text-3xl font-bold">{{ tryout.title }}</h1>
                        <div class="mt-4 flex items-center gap-4 text-sm font-medium">
                            <span v-if="tryout.type === 'premium'" class="bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full">Premium</span>
                            <span v-else class="bg-green-400 text-green-900 px-3 py-1 rounded-full">Gratis</span>
                            <span>⏱ {{ tryout.duration }} Menit</span>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="md:col-span-2 space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi</h3>
                                    <p class="text-gray-600 whitespace-pre-line leading-relaxed">
                                        {{ tryout.description }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                    <h4 class="font-medium text-gray-900 mb-2">Jadwal Pelaksanaan</h4>
                                    <ul class="text-sm text-gray-600 space-y-1">
                                        <li>Mulai: <span class="font-semibold">{{ formatDate(tryout.start_date) }}</span></li>
                                        <li v-if="tryout.end_date">Batas Daftar: <span class="font-semibold text-red-500">{{ formatDate(tryout.end_date) }}</span></li>
                                    </ul>
                                </div>
                            </div>

                            <div class="md:col-span-1">
                                <div class="border rounded-xl p-6 shadow-sm sticky top-4">
                                    <div class="mb-6 text-center">
                                        <p class="text-gray-500 text-sm">Harga Paket</p>
                                        <p class="text-3xl font-bold text-indigo-600 mt-1">
                                            {{ tryout.price > 0 ? formatRupiah(tryout.price) : 'Gratis' }}
                                        </p>
                                    </div>

                                    <div class="space-y-3">
                                        <div v-if="is_registered">
                                            <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-center text-sm font-medium mb-3">
                                                Kamu sudah terdaftar
                                            </div>
                                            <Link :href="route('tryout.exam', tryout.id)" class="block w-full text-center py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition">
                                                Mulai Ujian
                                            </Link>
                                        </div>

                                        <div v-else-if="pending_transaction">
                                            <Link :href="route('checkout.show', pending_transaction.id)" class="block w-full text-center py-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-semibold transition">
                                                Lanjutkan Pembayaran
                                            </Link>
                                            <p class="text-xs text-center text-gray-500 mt-2">Menunggu pembayaran</p>
                                        </div>

                                        <div v-else-if="is_registration_closed">
                                            <button disabled class="w-full py-3 bg-gray-300 text-gray-500 cursor-not-allowed rounded-lg font-bold">
                                                Pendaftaran Ditutup
                                            </button>
                                            <p class="text-xs text-red-500 text-center mt-2">
                                                Melewati batas waktu: {{ formatDate(tryout.end_date) }}
                                            </p>
                                        </div>

                                        <div v-else>
                                            <button @click="registerTryout" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition shadow-lg shadow-indigo-200">
                                                {{ tryout.price > 0 ? 'Beli Sekarang' : 'Daftar Sekarang' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>