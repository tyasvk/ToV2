<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ tryouts: Object });

const deleteEvent = (id) => {
    if(confirm('Yakin ingin menghapus event ini?')) {
        router.delete(route('admin.tryout-akbar.destroy', id));
    }
}

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute:'2-digit' });
</script>

<template>
    <Head title="Kelola Event Akbar" />
    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Event Tryout Akbar</h1>
                        <p class="text-slate-500 text-sm mt-1">Kelola event besar, soal, dan verifikasi peserta.</p>
                    </div>
                    <Link :href="route('admin.tryout-akbar.create')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold transition shadow-lg shadow-indigo-200 active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                        Buat Event Baru
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div v-for="tryout in tryouts.data" :key="tryout.id" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:shadow-md transition flex flex-col md:flex-row gap-6">
                        
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span v-if="tryout.is_published" class="px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider">Published</span>
                                <span v-else class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-wider">Draft</span>
                                <span class="text-xs text-slate-400 font-bold">{{ tryout.duration }} Menit</span>
                            </div>
                            <h3 class="text-xl font-black text-slate-800 mb-2">{{ tryout.title }}</h3>
                            <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDate(tryout.started_at) }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ tryout.price > 0 ? 'Rp ' + tryout.price.toLocaleString('id-ID') : 'Gratis' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                            
                            <Link :href="route('admin.tryout-akbar.participants', tryout.id)" class="flex flex-col items-center justify-center w-24 h-full p-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 transition group border border-amber-100">
                                <span class="text-2xl mb-1 group-hover:scale-110 transition">👥</span>
                                <span class="text-[10px] font-bold uppercase text-center leading-tight">Verifikasi Peserta</span>
                            </Link>

                            <Link :href="route('admin.tryouts.questions.index', tryout.id)" class="flex flex-col items-center justify-center w-24 h-full p-2 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 transition group border border-slate-100">
                                <span class="text-2xl mb-1 group-hover:scale-110 transition">📝</span>
                                <span class="text-[10px] font-bold uppercase text-center leading-tight">Kelola Soal</span>
                            </Link>

                            <div class="flex flex-col gap-2">
                                <Link :href="route('admin.tryout-akbar.edit', tryout.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-center">
                                    Edit
                                </Link>
                                <button @click="deleteEvent(tryout.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 text-center">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="tryouts.data.length === 0" class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300">
                        <p class="text-slate-400 italic">Belum ada event akbar dibuat.</p>
                    </div>
                </div>

                <div class="mt-6">
                    </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>