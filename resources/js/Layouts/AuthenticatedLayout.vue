<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarNav = ref(null); 

// --- 1. DATA AUTH & REAKTIVITAS ---
const user = computed(() => page.props.auth?.user ?? null);

// --- 2. NORMALISASI ROLE ---
const roles = computed(() => {
    const rawRoles = user.value?.roles;
    if (Array.isArray(rawRoles)) return rawRoles;
    if (typeof rawRoles === 'object' && rawRoles !== null) return Object.values(rawRoles);
    return [];
});

const isAdmin = computed(() => 
    roles.value.some(r => String(r).toLowerCase() === 'admin')
);

const isUser = computed(() => 
    roles.value.some(r => String(r).toLowerCase() === 'user') || roles.value.length === 0
);

// --- 3. LOGIKA MEMBERSHIP ---
const isUserMember = computed(() => {
    if (!user.value || !user.value.membership_expires_at) {
        return false;
    }
    const expiryDate = new Date(user.value.membership_expires_at);
    const today = new Date();
    return expiryDate > today;
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

// --- 4. LOGIKA SCROLL PERSISTENCE ---
const scrollToActive = () => {
    nextTick(() => {
        const activeElement = sidebarNav.value?.querySelector('.active-link');
        if (activeElement) {
            activeElement.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        }
    });
};

onMounted(scrollToActive);
watch(() => page.url, scrollToActive);

// --- 5. LOGIKA NAVIGASI DYNAMIS ---
const logoRoute = computed(() => {
    try {
        return isAdmin.value ? route('admin.dashboard') : route('dashboard');
    } catch (e) {
        return '/dashboard'; 
    }
});

// Helper untuk mengecek tipe tryout yang sedang aktif di props
const activeTryoutType = computed(() => page.props.tryout?.type || 'general');
</script>

<template>
    <div class="flex h-screen bg-[#F8FAFC] overflow-hidden font-sans text-slate-900">
        
        <aside class="w-72 bg-white border-r border-slate-200/60 flex flex-col z-50 shadow-2xl shadow-slate-200/50 shrink-0">
            
            <div class="h-24 flex items-center px-6 border-b border-slate-100 shrink-0 bg-white">
                <Link :href="logoRoute" class="flex items-center gap-4 group overflow-hidden">
                    <div class="w-12 h-12 shrink-0 group-hover:rotate-12 transition-transform duration-500 flex items-center justify-center bg-slate-900 rounded-2xl shadow-lg">
                        <img src="/images/logo.png" alt="Logo" class="w-8 h-8 object-contain brightness-0 invert">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-[11px] tracking-[0.3em] text-slate-900 uppercase leading-tight">NUSANTARA</span>
                        <span class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest leading-none">CPNS Academy</span>
                    </div>
                </Link>
            </div>

            <div v-if="isUser && !isAdmin && user" class="px-6 mt-4 shrink-0">
                <div :class="isUserMember ? 'bg-indigo-600 shadow-indigo-100 shadow-lg' : 'bg-slate-100'" 
                     class="rounded-[1.5rem] p-5 transition-all duration-500 relative overflow-hidden">
                    <div v-if="isUserMember" class="absolute top-0 right-0 w-16 h-16 bg-white/10 -mr-8 -mt-8 rounded-full blur-xl"></div>
                    <p :class="isUserMember ? 'text-white/60' : 'text-slate-400'" class="text-[9px] font-black uppercase tracking-widest mb-1">Status Saya</p>
                    <p :class="isUserMember ? 'text-white' : 'text-slate-600'" class="text-[11px] font-black uppercase tracking-tight leading-none">
                        {{ isUserMember ? 'Akses Adidaya' : 'Anggota Gratis' }}
                    </p>
                    <p v-if="isUserMember" class="text-[9px] font-bold text-indigo-200 mt-1.5 leading-none">
                        Sampai: {{ formatDate(user.membership_expires_at) }}
                    </p>
                </div>
            </div>

            <nav ref="sidebarNav" class="flex-1 overflow-y-auto py-4 px-4 space-y-1.5 custom-scrollbar">
                
                <div v-if="isAdmin" class="space-y-1">
                    <p class="text-[10px] uppercase font-black text-rose-500 px-4 mb-3 mt-4 tracking-[0.3em]">Admin Control</p>
                    
                    <Link :href="route('admin.dashboard')" 
                        :class="[route().current('admin.dashboard') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-rose-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Dashboard Admin</span>
                    </Link>

                    <Link :href="route('admin.users.index')" 
                        :class="[route().current('admin.users.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-blue-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Kelola User</span>
                    </Link>

                    <Link :href="route('admin.tryouts.index')" 
                        :class="[
                            (route().current('admin.tryouts.*') && !route().current('admin.tryouts.questions.*')) || 
                            (route().current('admin.tryouts.questions.*') && activeTryoutType !== 'adidaya')
                            ? 'bg-rose-50 text-rose-600 active-link shadow-sm' 
                            : 'text-slate-500 hover:bg-rose-50', 
                            'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group'
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-indigo-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Kelola Tryout</span>
                    </Link>

                    <Link :href="route('admin.affiliate.leaderboard')" 
                        :class="[route().current('admin.affiliate.leaderboard') ? 'bg-amber-50 text-amber-600 active-link shadow-sm' : 'text-slate-500 hover:bg-amber-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-amber-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0V9.403c0-.621-.504-1.125-1.125-1.125h-2.764c-.621 0-1.125.504-1.125 1.125V14.25m5.014 0h-5.014m5.014 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-5.014-3.75c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m5.014-3.75V6.375c0-.621-.504-1.125-1.125-1.125h-2.764c-.621 0-1.125.504-1.125 1.125V9.403" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Ranking Affiliate</span>
                    </Link>

                    <Link :href="route('admin.adidaya-manage.index')" 
                        :class="[
                            route().current('admin.adidaya-manage.*') || 
                            (route().current('admin.tryouts.questions.*') && activeTryoutType === 'adidaya')
                            ? 'bg-rose-50 text-rose-600 active-link shadow-sm' 
                            : 'text-slate-500 hover:bg-rose-50', 
                            'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group'
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-purple-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Adidaya Manager</span>
                    </Link>

                    <Link :href="route('admin.tryout-akbar.index')" 
                        :class="[route().current('admin.tryout-akbar.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-orange-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Event Akbar</span>
                    </Link>

                    <Link :href="route('admin.transactions.index')" 
                        :class="[route().current('admin.transactions.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-emerald-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5M6.75 20.25v.75m0-1.5v-.75m0-1.5v-.75m0-1.5v-.75m0-1.5V15m-1.5-1.5h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Data Transaksi</span>
                    </Link>

                    <p class="text-[10px] uppercase font-black text-amber-500 px-4 mb-3 mt-6 tracking-[0.3em]">Afiliasi System</p>
                    <Link :href="route('admin.affiliate.withdrawals')" 
                        :class="[route().current('admin.affiliate.withdrawals') ? 'bg-amber-50 text-amber-600 active-link shadow-sm' : 'text-slate-500 hover:bg-amber-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-green-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Permintaan WD</span>
                    </Link>
                    <Link :href="route('admin.affiliate.transactions')" 
                        :class="[route().current('admin.affiliate.transactions') ? 'bg-amber-50 text-amber-600 active-link shadow-sm' : 'text-slate-500 hover:bg-amber-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-cyan-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Data Afiliasi</span>
                    </Link>
                </div>

                <div v-if="isUser && !isAdmin" class="space-y-1.5">
                    <p class="text-[10px] uppercase font-black text-slate-400 px-4 mb-3 mt-4 tracking-[0.3em]">Menu Navigasi</p>
                    
                    <Link :href="route('dashboard')" 
                        :class="[route().current('dashboard') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-blue-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Dashboard</span>
                    </Link>

                    <Link :href="route('tryout.index')" 
                        :class="[
                            (route().current('tryout.index') || route().current('tryout.show') || route().current('tryout.register') || route().current('tryout.my') || (route().current('tryout.wait') && activeTryoutType !== 'adidaya')) 
                            ? 'bg-slate-900 text-white shadow-xl active-link' 
                            : 'text-slate-500 hover:bg-slate-50', 
                            'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group'
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-indigo-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Katalog Tryout</span>
                    </Link>

                    <Link :href="route('tryout-akbar.index')" 
                        :class="[route().current('tryout-akbar.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-orange-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-1.533-2.58 3.75 3.75 0 0 0 3.013 6.501Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Event Akbar</span>
                    </Link>

                    <Link :href="route('tryout.adidaya')" 
                        :class="[
                            (route().current('tryout.adidaya') || (route().current('tryout.wait') && activeTryoutType === 'adidaya')) 
                            ? 'bg-slate-900 text-white shadow-xl active-link' 
                            : 'text-slate-500 hover:bg-slate-100', 
                            'flex items-center justify-between p-4 rounded-2xl font-black transition-all group'
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-purple-500 group-[.active-link]:text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                            </svg>
                            <span class="text-[11px] uppercase tracking-widest">Nusantara Adidaya</span>
                        </div>
                        <span v-if="isUserMember" class="text-[8px] bg-indigo-600 text-white px-2 py-0.5 rounded-md font-black shadow-sm shadow-indigo-200">ACTIVE</span>
                        <span v-else class="text-[8px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded-md font-black italic">LOCKED</span>
                    </Link>

                    <Link :href="route('membership.index')" 
                        :class="[route().current('membership.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-amber-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.091 3.091L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.091 3.091ZM18.213 19.096 17.25 22.5l-.963-3.404a2.25 2.25 0 0 0-1.546-1.546L11.25 16.5l3.491-.987a2.25 2.25 0 0 0 1.546-1.546L17.25 10.5l.963 3.404a2.25 2.25 0 0 0 1.546 1.546L23.25 16.5l-3.491.987a2.25 2.25 0 0 0-1.546 1.546ZM17.25 1.5l.963 3.404a2.25 2.25 0 0 0 1.546 1.546L23.25 7.5l-3.491.987a2.25 2.25 0 0 0-1.546 1.546L17.25 13.5l-.963-3.404a2.25 2.25 0 0 0-1.546-1.546L11.25 7.5l3.491-.987a2.25 2.25 0 0 0 1.546-1.546L17.25 1.5Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Membership</span>
                    </Link>

                    <Link :href="route('affiliate.index')" 
                        :class="[route().current('affiliate.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-teal-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0a5.995 5.995 0 0 0-4.058-3.036m0 0a5.995 5.995 0 0 0-4.058 3.036m0 0a5.97 5.97 0 0 0-.941 3.197m9.411-3.197a5.971 5.971 0 0 0-.941-3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Afiliasi</span>
                    </Link>

                    <Link :href="route('tryout.history')" 
                        :class="[route().current('tryout.history') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-slate-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Riwayat Tryout</span>
                    </Link>

                    <Link :href="route('wallet.index')" 
                        :class="[route().current('wallet.index') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-emerald-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Dompet Saya</span>
                    </Link>

                    <Link :href="route('profile.edit')" 
                        :class="[route().current('profile.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 flex-shrink-0 text-sky-500 group-[.active-link]:text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <span class="text-[11px] uppercase tracking-widest">Profil Saya</span>
                    </Link>
                </div>
            </nav>

            <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                <Link :href="route('logout')" method="post" as="button" 
                    class="w-full flex items-center justify-center gap-3 p-4 text-rose-500 hover:bg-rose-50 rounded-2xl transition font-black text-[11px] uppercase tracking-[0.2em] active:scale-95"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Keluar Akun</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            
            <header class="h-24 bg-white border-b border-slate-100 flex items-center justify-between px-10 shrink-0 z-30 shadow-sm">
                <div class="flex items-center gap-6">
                    <h2 class="font-black text-slate-900 uppercase text-[11px] tracking-[0.3em]">
                        {{ isAdmin ? 'Admin Command Center' : 'Halaman Belajar Nusantara' }}
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100 text-[9px] font-black text-emerald-600 uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Sistem Aktif
                    </div>
                    <img :src="user?.profile_photo_url || `https://ui-avatars.com/api/?name=${user?.name}`" 
                         class="h-10 w-10 rounded-xl border-2 border-white shadow-sm object-cover" />
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-12 bg-[#F8FAFC]">
                <div class="max-w-[1400px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-1000">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

.active-link {
    position: relative;
}

/* Garis indikator aktif */
.active-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 25%;
    bottom: 25%;
    width: 4px;
    background: currentColor;
    border-radius: 0 4px 4px 0;
}
</style>