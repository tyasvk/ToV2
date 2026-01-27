<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ attempt: Object });
</script>

<template>
    <Head title="Hasil Simulasi" />
    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto text-center space-y-8">
            <div class="bg-white p-12 rounded-[3rem] shadow-xl shadow-indigo-50 border border-gray-100">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">🎉</div>
                <h2 class="font-black text-gray-400 uppercase text-[10px] tracking-[0.3em] mb-2">Simulasi Selesai</h2>
                <p class="text-5xl font-black text-gray-900 tracking-tighter mb-10">{{ attempt.total_score }}</p>

                <div class="grid grid-cols-3 gap-4">
                    <div class="p-6 bg-gray-50 rounded-[2rem]">
                        <p class="text-[9px] font-black text-gray-400 uppercase mb-2">TWK</p>
                        <p class="text-xl font-black text-gray-800">{{ attempt.twk_score }}</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-[2rem]">
                        <p class="text-[9px] font-black text-gray-400 uppercase mb-2">TIU</p>
                        <p class="text-xl font-black text-gray-800">{{ attempt.tiu_score }}</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-[2rem]">
                        <p class="text-[9px] font-black text-gray-400 uppercase mb-2">TKP</p>
                        <p class="text-xl font-black text-gray-800">{{ attempt.tkp_score }}</p>
                    </div>
                </div>

                <Link :href="route('dashboard')" class="mt-12 inline-block w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-gray-900 transition">
                    Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>