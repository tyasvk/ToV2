<template>
    <AuthenticatedLayout>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="pkg in packages" :key="pkg.id" class="bg-white p-6 rounded-2xl border shadow-sm">
                <h3 class="font-bold text-lg mb-4">{{ pkg.name }}</h3>
                <form @submit.prevent="update(pkg)">
                    <div class="mb-3">
                        <label class="text-xs font-semibold">Harga (Rp)</label>
                        <input v-model="pkg.price" type="number" class="w-full border rounded-lg p-2 text-sm">
                    </div>
                    <div class="mb-4">
                        <label class="text-xs font-semibold">Durasi (Hari)</label>
                        <input v-model="pkg.duration_days" type="number" class="w-full border rounded-lg p-2 text-sm">
                    </div>
                    <button class="w-full bg-slate-900 text-white py-2 rounded-xl text-xs uppercase font-bold">Simpan</button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ packages: Array });
const update = (pkg) => {
    useForm({ price: pkg.price, duration_days: pkg.duration_days })
        .post(route('admin.membership-packages.update', pkg.id));
};
</script>