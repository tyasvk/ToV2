<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ 
    exclusiveTryouts: Array, 
    generalTryouts: Array 
});

const page = usePage();

// --- LOGIKA MEMBERSHIP ---
const user = computed(() => page.props.auth?.user ?? null);
const isPremium = computed(() => {
    if (!user.value?.membership_expires_at) return false;
    return new Date(user.value.membership_expires_at) > new Date();
});
</script>

<template>
    <Head title="Nusantara Adidaya - Akses Premium" />

    <AuthenticatedLayout>
        <div class="relative bg-slate-900 overflow-hidden shadow-md z-0 -mx-6 -mt-6 md:-mx-12 md:-mt-12 mb-10 pb-10 pt-10 md:pt-16">
            <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-950 to-slate-900 opacity-95"></div>
                <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/20 rounded-full blur-[100px]"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full bg-indigo-500/20 border border-indigo-400/20 text-indigo-200 text-[10px] font-black tracking-[0.2em] uppercase mb-6 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span> 
                    {{ isPremium ? 'Akses Terbuka' : 'Konten Terbatas' }}
                </span>
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tighter mb-4 leading-tight">
                    NUSANTARA <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400 italic">ADIDAYA.</span>
                </h1>
                <p class="mt-4 max-w-2xl mx-auto text-sm md:text-base text-slate-400 font-medium leading-relaxed">
                    Koleksi simulasi ujian eksklusif dengan standar soal HOTS terbaru untuk memaksimalkan potensi kelulusan Anda.
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-2 pb-20">
            <div v-if="exclusiveTryouts.length === 0" class="py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-slate-100 shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">📭</div>
                <h3 class="font-black text-slate-800 uppercase tracking-tighter">Belum Ada Paket</h3>
                <p class="text-slate-400 text-sm font-medium">Belum ada paket Adidaya yang dipublikasikan oleh admin.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="(tryout, index) in exclusiveTryouts" :key="tryout.id" 
                    class="group bg-white rounded-[2.5rem] shadow-sm hover:shadow-2xl border border-slate-200 overflow-hidden transition-all duration-500 hover:-translate-y-2 flex flex-col h-full relative">
                    
                    <div v-if="!isPremium" class="absolute inset-0 bg-white/40 backdrop-blur-[2px] z-30 flex flex-col items-center justify-center p-6 text-center transition-all group-hover:backdrop-blur-[4px]">
                        <div class="w-14 h-14 bg-white rounded-2xl shadow-xl flex items-center justify-center text-2xl mb-4 border border-slate-100">🔒</div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-900 mb-1">Konten Terkunci</p>
                    </div>

                    <div class="absolute top-4 right-4 z-20">
                        <span class="px-3 py-1 bg-slate-900/90 backdrop-blur-md text-white text-[9px] font-black uppercase tracking-wider rounded-lg shadow-lg flex items-center gap-1.5">
                            👑 VIP
                        </span>
                    </div>

                    <div class="h-20 bg-gradient-to-br from-indigo-50 via-white to-slate-50 relative overflow-hidden border-b border-slate-100">
                        <div class="absolute right-2 top-2 text-6xl opacity-[0.04] font-black text-slate-900 select-none italic tracking-tighter">#{{ index + 1 }}</div>
                    </div>

                    <div class="px-8 pb-8 pt-0 flex-1 flex flex-col -mt-10 relative z-10">
                        <div class="w-14 h-14 bg-white rounded-2xl shadow-lg border border-slate-100 flex items-center justify-center text-2xl mb-5 text-indigo-600 group-hover:scale-110 transition duration-500">⚡</div>

                        <h3 class="text-xl font-black text-slate-800 leading-tight mb-1 group-hover:text-indigo-600 transition truncate">
                            {{ tryout.title }}
                        </h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6">Premium Intelligence</p>

                        <div class="mb-8 bg-slate-50 p-1.5 rounded-2xl border border-slate-100">
                            <div class="grid grid-cols-2 gap-px bg-slate-200/50 rounded-xl overflow-hidden">
                                <div class="bg-white p-3 text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Soal</p>
                                    <p class="text-sm font-black text-slate-800">{{ tryout.questions_count }}</p>
                                </div>
                                <div class="bg-white p-3 text-center">
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5">Waktu</p>
                                    <p class="text-sm font-black text-slate-800">{{ tryout.duration }}m</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <Link 
                                :href="isPremium ? route('tryout.wait', tryout.id) : route('membership.index')" 
                                class="block w-full py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] text-center transition-all active:scale-95"
                                :class="isPremium ? 'bg-slate-900 text-white shadow-xl' : 'bg-indigo-600 text-white shadow-xl'"
                            >
                                {{ isPremium ? 'Mulai Ujian' : 'Upgrade Ke Adidaya' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>