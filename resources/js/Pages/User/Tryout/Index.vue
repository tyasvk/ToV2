<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tryouts: Array
});
</script>

<template>
    <Head title="Katalog Tryout" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase italic">Katalog Tryout</h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.3em]">Pilih paket simulasi untuk memulai latihan</p>
            </div>
        </template>

        <div class="max-w-7xl mx-auto">
            <div v-if="tryouts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tryout in tryouts" :key="tryout.id" 
                    class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all group flex flex-col">
                    
                    <div class="p-8 flex-1">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition duration-500">🎯</div>
                        
                        <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter leading-tight mb-2">
                            {{ tryout.title }}
                        </h3>
                        
                        <p class="text-sm text-gray-500 line-clamp-2 mb-6">
                            {{ tryout.description || 'Simulasi ujian CAT dengan materi standar terbaru.' }}
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <span class="bg-gray-50 text-gray-500 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border border-gray-100">
                                ⏱️ {{ tryout.duration_minutes }} Menit
                            </span>
                            <span class="bg-gray-50 text-gray-500 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest border border-gray-100">
                                📄 {{ tryout.questions_count }} Soal
                            </span>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50/50 border-t border-gray-50">
                        <Link :href="route('tryout.start', tryout.id)" 
                            class="w-full inline-flex justify-center items-center py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition shadow-xl active:scale-95">
                            Mulai Simulasi
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                <div class="text-5xl mb-4">📭</div>
                <h3 class="font-black text-gray-400 uppercase text-sm tracking-widest">Belum ada paket tersedia</h3>
                <p class="text-xs text-gray-300 mt-2">Silakan hubungi admin atau cek kembali nanti.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>