<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    tryouts: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Logika pengecekan status membership (Adidaya)
const isAdidayaActive = computed(() => {
    if (!user.value?.membership_expires_at) return false;
    return new Date(user.value.membership_expires_at) > new Date();
});

const searchQuery = ref('');

const filteredTryouts = computed(() => {
    return (props.tryouts || []).filter(t => {
        return t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});
</script>

<template>
    <Head title="Nusantara Adidaya" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-10 animate-in fade-in duration-700">
            
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 rounded-[2rem] md:rounded-[3.5rem] p-8 md:p-16 border border-white/5 shadow-2xl shadow-indigo-500/10">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-purple-500/10 rounded-full blur-[60px] -ml-24 -mb-24"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="max-w-xl space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 rounded-full backdrop-blur-md">
                            <span class="w-1.5 h-1.5 bg-purple-400 rounded-full animate-pulse"></span>
                            <span class="text-[9px] font-medium text-purple-200 uppercase tracking-[0.2em]">Premium Access</span>
                        </div>
                        <h1 class="text-2xl md:text-4xl font-medium text-white tracking-tight uppercase leading-none">
                            Nusantara Adidaya
                        </h1>
                        <p class="text-[11px] md:text-sm text-indigo-100/60 font-medium leading-relaxed italic">
                            Akses eksklusif simulasi tingkat tinggi untuk persiapan maksimal menuju seleksi abdi negara.
                        </p>
                    </div>

                    <div v-if="!isAdidayaActive" class="shrink-0">
                        <Link :href="route('membership.index')" class="bg-white text-slate-900 px-8 py-4 rounded-2xl text-[10px] font-medium uppercase tracking-widest hover:bg-indigo-50 transition-all active:scale-95 flex items-center gap-3">
                            Upgrade Adidaya
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-2">
                <div class="flex items-center gap-3">
                    <h2 class="text-sm font-medium text-slate-900 uppercase tracking-widest">Koleksi Tryout</h2>
                    <span class="h-px w-12 bg-slate-200 hidden md:block"></span>
                </div>

                <div class="w-full md:w-80">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari simulasi khusus..."
                        class="w-full bg-white border-slate-200 rounded-2xl px-5 py-3 text-sm focus:ring-purple-500 focus:border-purple-500 transition-all font-medium placeholder:text-slate-400 shadow-sm"
                    >
                </div>
            </div>

            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 pb-10">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group relative bg-white border border-slate-100 rounded-[2.5rem] p-1 transition-all duration-500 hover:shadow-2xl hover:shadow-indigo-500/5"
                >
                    <div v-if="!isAdidayaActive" class="absolute inset-0 z-20 bg-white/60 backdrop-blur-[2px] rounded-[2.5rem] flex items-center justify-center p-8 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-medium text-slate-900 uppercase tracking-widest">Akses Terkunci</p>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-8">
                            <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-indigo-600 font-medium text-[10px]">
                                ADY
                            </div>
                            <span class="text-[8px] font-medium text-slate-400 uppercase tracking-widest border border-slate-100 px-3 py-1 rounded-full">
                                High Complexity
                            </span>
                        </div>

                        <h3 class="text-base md:text-lg font-medium text-slate-900 leading-snug uppercase tracking-tight mb-4 group-hover:text-indigo-600 transition-colors">
                            {{ tryout.title }}
                        </h3>

                        <div class="space-y-3 mb-8">
                            <div class="flex items-center gap-3 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .415.162.798.425 1.081.263.283.63.453 1.037.453s.774-.17 1.037-.453c.263-.283.425-.666.425-1.081 0-.231-.035-.454-.1-.664m-5.801 0A2.251 2.251 0 0 1 13.5 2.25c1.03 0 1.872.692 2.124 1.636m-5.801 0a48.391 48.391 0 0 1 1.123-.08m5.801 0c.666.056 1.323.13 1.974.223L18.414 4.5m-11.414 0c-.651.092-1.308.167-1.974.223L5.586 4.5m0 0a2.25 2.25 0 0 0-2.25 2.25v12.3c0 1.242 1.008 2.25 2.25 2.25h12.75c1.242 0 2.25-1.008 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H5.586Z" />
                                </svg>
                                <span class="text-[10px] uppercase font-medium tracking-widest">{{ tryout.questions_count || 110 }} Materi</span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <Link 
                                :href="route('tryout.start', tryout.id)"
                                class="block w-full text-center bg-slate-900 text-white py-4 rounded-2xl font-medium text-[10px] uppercase tracking-[0.25em] hover:bg-indigo-600 transition-all active:scale-95 shadow-lg shadow-slate-100"
                            >
                                Mulai Simulasi
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-100 rounded-[3rem] p-16 md:p-24 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mb-6 text-slate-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-slate-900 uppercase tracking-[0.2em]">Belum Ada Paket</h3>
                <p class="text-[11px] text-slate-500 font-medium mt-2">Materi Adidaya sedang disiapkan untuk Anda.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.grid > div {
    animation: slideUp 0.6s ease-out forwards;
}
</style>