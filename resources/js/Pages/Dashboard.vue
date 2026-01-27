<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    availableTryouts: Array,
    stats: Object
});
</script>

<template>
    <Head title="Dashboard Peserta" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase italic">
                    Selamat Datang, {{ $page.props.auth.user.name }}!
                </h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.3em]">Panel Kontrol Progres Belajar</p>
            </div>
        </template>

        <div class="space-y-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Simulasi Selesai</p>
                    <div class="flex items-end gap-2">
                        <span class="text-4xl font-black text-gray-900">{{ stats.completed_count }}</span>
                        <span class="text-[10px] font-bold text-green-500 mb-1.5 uppercase tracking-widest">Paket</span>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm border-l-4 border-l-indigo-600">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Rata-rata Skor</p>
                    <div class="flex items-end gap-2">
                        <span class="text-4xl font-black text-indigo-600">{{ stats.average_score }}</span>
                        <span class="text-[10px] font-bold text-indigo-400 mb-1.5 uppercase tracking-widest">Poin</span>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm border-l-4 border-l-amber-500">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Ranking Global</p>
                    <div class="flex items-end gap-2">
                        <span class="text-4xl font-black text-amber-600">{{ stats.global_rank }}</span>
                        <span class="text-[10px] font-bold text-amber-400 mb-1.5 uppercase tracking-widest">Siswa</span>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex justify-between items-center px-4">
                    <h3 class="font-black text-sm text-gray-900 uppercase tracking-widest">Lanjutkan Simulasi</h3>
                    <Link :href="route('tryout.index')" class="text-[9px] font-black text-indigo-600 uppercase border-b-2 border-indigo-100 hover:border-indigo-600 transition">Lihat Semua ➜</Link>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div v-for="tryout in availableTryouts" :key="tryout.id" 
                        class="bg-white p-6 rounded-[2.2rem] border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-center group hover:border-indigo-200 transition-all">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition duration-500">🎯</div>
                            <div>
                                <h4 class="font-black text-gray-900 uppercase tracking-tight text-base">{{ tryout.title }}</h4>
                                <div class="flex gap-4 mt-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">⏱️ {{ tryout.duration_minutes }} Menit</span>
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">📄 {{ tryout.questions_count }} Soal</span>
                                </div>
                            </div>
                        </div>
                        <Link :href="route('tryout.start', tryout.id)" class="mt-4 md:mt-0 bg-gray-900 text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition shadow-xl active:scale-95">Mulai Ujian</Link>
                    </div>

                    <div v-if="availableTryouts.length === 0" class="py-20 text-center bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-100">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-[0.2em]">Belum ada tryout aktif yang tersedia.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>