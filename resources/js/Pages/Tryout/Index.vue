<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tryouts: Array
});
</script>

<template>
    <Head title="Daftar Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">Pilih Paket Tryout</h2>
            <p class="text-sm text-gray-500">Asah kemampuanmu dengan simulasi CAT terbaru.</p>
        </template>

        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="tryout in tryouts" :key="tryout.id" 
                     class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:scale-[1.02] transition-all duration-300 overflow-hidden flex flex-col">
                    
                    <div class="p-8">
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-indigo-100 text-indigo-700 p-3 rounded-2xl text-2xl">📝</div>
                            <span class="bg-green-100 text-green-700 text-[10px] font-black px-2 py-1 rounded-lg uppercase tracking-widest">Tersedia</span>
                        </div>
                        
                        <h3 class="text-xl font-black text-gray-900 mb-2">{{ tryout.title }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed mb-6">
                            {{ tryout.description || 'Simulasi ujian sesuai kisi-kisi PERMENPAN RB terbaru.' }}
                        </p>

                        <div class="flex items-center gap-4 text-xs font-bold text-gray-400 border-t border-gray-50 pt-6">
                            <div class="flex items-center gap-1">
                                <span>⏱️</span> {{ tryout.duration_minutes }} Menit
                            </div>
                            <div class="flex items-center gap-1">
                                <span>📄</span> {{ tryout.total_questions }} Soal
                            </div>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 mt-auto">
                        <Link :href="route('tryout.start', tryout.id)" 
                              class="block w-full text-center py-4 bg-indigo-600 text-white rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition">
                            KERJAKAN SEKARANG
                        </Link>
                    </div>
                </div>
                
                <div v-if="tryouts.length === 0" class="col-span-full text-center py-20 bg-gray-100 rounded-3xl border-2 border-dashed border-gray-200">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Belum ada paket tryout yang tersedia.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>