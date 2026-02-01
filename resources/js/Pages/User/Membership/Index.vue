<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ plans: Array, is_member: Boolean });
const form = useForm({ plan_id: '', payment_method: 'wallet' });

const submit = (planId) => {
    form.plan_id = planId;
    form.post(route('membership.buy'));
};
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12 max-w-7xl mx-auto px-6">
            <h2 class="text-2xl font-bold mb-8">Pilih Paket Membership CPNS Nusantara</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div v-for="plan in plans" :key="plan.id" class="bg-white p-6 rounded-xl shadow-sm border flex flex-col">
                    <h3 class="font-bold text-lg text-indigo-600">{{ plan.name }}</h3>
                    <p class="text-3xl font-black my-4">Rp {{ plan.price.toLocaleString() }}</p>
                    <ul class="text-sm text-gray-500 mb-8 flex-1">
                        <li>✅ Akses Semua Tryout Premium</li>
                        <li>✅ Ranking Nasional</li>
                        <li>✅ Pembahasan Lengkap</li>
                    </ul>
                    <button @click="submit(plan.id)" class="w-full py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">
                        Beli Sekarang
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>