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
    <Head title="Nusantara Adidaya - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 md:space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-10 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[80px] pointer-events-none -mr-20 -mt-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-100 rounded-lg text-[9px] font-semibold text-blue-700 uppercase tracking-[0.2em]">
                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span> Premium Access
                        </span>
                        <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight uppercase">
                            Nusantara Adidaya
                        </h1>
                        <p class="text-xs md:text-sm text-slate-500 font-medium leading-relaxed max-w-lg">
                            Akses eksklusif simulasi tingkat tinggi untuk persiapan maksimal menuju seleksi abdi negara.
                        </p>
                    </div>

                    <div v-if="!isAdidayaActive" class="shrink-0">
                        <Link :href="route('membership.index')" class="bg-blue-600 text-white px-8 py-3 rounded-xl text-[10px] font-semibold uppercase tracking-widest hover:bg-blue-700 transition-all active:scale-95 shadow-sm">
                            Upgrade Adidaya
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
                <h2 class="text-xs font-semibold text-slate-900 uppercase tracking-widest">Koleksi Tryout Adidaya</h2>
                <div class="w-full md:w-72">
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari simulasi..."
                        class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium placeholder:text-slate-400 shadow-sm outline-none"
                    >
                </div>
            </div>

            <div v-if="filteredTryouts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6 pb-10">
                <div 
                    v-for="tryout in filteredTryouts" 
                    :key="tryout.id"
                    class="group relative bg-white border border-slate-200 rounded-2xl p-5 md:p-6 transition-all duration-300 hover:shadow-md flex flex-col"
                >
                    <div v-if="!isAdidayaActive" class="absolute inset-0 z-20 bg-white/70 backdrop-blur-[2px] rounded-2xl flex items-center justify-center p-8 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Terkunci</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-blue-600 font-semibold text-[10px]">
                            ADY
                        </div>
                        <span class="text-[9px] font-medium text-slate-400 uppercase tracking-widest border border-slate-100 px-3 py-1 rounded-full">
                            Complexity
                        </span>
                    </div>

                    <h3 class="text-sm font-medium text-slate-900 leading-snug uppercase tracking-tight mb-6 group-hover:text-blue-600 transition-colors flex-1">
                        {{ tryout.title }}
                    </h3>

                    <div class="mt-auto">
                        <Link 
    :href="route('tryout.wait', tryout.id)"
    class="block w-full text-center bg-slate-900 hover:bg-blue-600 text-white py-3 rounded-xl font-semibold text-[10px] uppercase tracking-[0.2em] transition-all active:scale-95 shadow-sm"
>
    Mulai Simulasi
</Link>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-16 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-900 mb-1 uppercase tracking-widest">Belum Ada Paket</h3>
                <p class="text-xs text-slate-500 font-medium max-w-sm">Materi Adidaya sedang disiapkan untuk Anda.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}
</style>