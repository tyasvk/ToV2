<script setup>
import { computed, nextTick, ref, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const sidebarNav = ref(null); 
const isSidebarOpen = ref(false);

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

// --- 4. LOGIKA SCROLL PERSISTENCE (SIDEBAR) ---
const scrollToActive = () => {
    nextTick(() => {
        const activeElement = sidebarNav.value?.querySelector('.active-link');
        if (activeElement) {
            activeElement.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        }
    });
};

onMounted(scrollToActive);
watch(() => page.url, () => {
    isSidebarOpen.value = false;
    scrollToActive();
});

// --- 5. LOGIKA NAVIGASI DINAMIS ---
const logoRoute = computed(() => {
    try {
        return isAdmin.value ? route('admin.dashboard') : route('dashboard');
    } catch (e) {
        return '/dashboard'; 
    }
});

const activeTryoutType = computed(() => {
    return page.props.tryout?.type || page.props.attempt?.tryout?.type || null;
});

const isInsideTryoutScope = computed(() => {
    return route().current('tryout.show') || 
           route().current('tryout.wait') || 
           route().current('tryout.result') || 
           route().current('tryout.review') || 
           route().current('tryout.leaderboard');
});

// --- 6. DETEKSI HALAMAN UJIAN ---
const isExamPage = computed(() => {
    try {
        return route().current('tryout.exam') || page.url.includes('/exam') || page.url.includes('/ujian');
    } catch (e) {
        return page.url.includes('/exam') || page.url.includes('/ujian');
    }
});
</script>

<template>
    <div class="flex min-h-screen bg-[#F5F5F7] font-sans text-[#1D1D1F] relative selection:bg-[#007AFF] selection:text-white">
        
        <!-- Backdrop Mobile -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false" 
            class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 lg:hidden transition-opacity duration-300"
        ></div>

        <!-- SIDEBAR -->
        <aside 
            :class="[
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                'fixed lg:sticky lg:top-0 h-screen inset-y-0 left-0 w-72 bg-white border-r border-black/5 flex flex-col z-50 shadow-[4px_0_24px_rgba(0,0,0,0.02)] shrink-0 transition-transform duration-300 ease-in-out'
            ]"
        >
            <!-- Logo Header dengan Gradasi Berwarna -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-black/5 shrink-0 bg-white">
                <Link :href="logoRoute" class="flex items-center gap-3 group overflow-hidden">
                    <div class="w-10 h-10 shrink-0 group-hover:scale-105 transition-transform duration-300 flex items-center justify-center bg-gradient-to-br from-[#007AFF] via-[#5E5CE6] to-[#AF52DE] rounded-[14px] shadow-md shadow-blue-500/20">
                        <img src="/images/logo.png" alt="Logo" class="w-5 h-5 object-contain brightness-0 invert">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-[12px] tracking-wider text-[#1D1D1F] uppercase leading-tight">NUSANTARA</span>
                        <span class="text-[10px] font-bold text-[#007AFF] uppercase tracking-wider leading-none">CPNS Academy</span>
                    </div>
                </Link>
                
                <button @click="isSidebarOpen = false" class="lg:hidden p-1.5 -mr-2 text-[#86868B] hover:bg-[#F5F5F7] rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Widget Status Member (Colorful Apple Gradient Widget) -->
            <div v-if="isUser && !isAdmin && user" class="px-4 mt-4 shrink-0">
                <div :class="isUserMember ? 'bg-gradient-to-r from-[#34C759] via-[#007AFF] to-[#5E5CE6] text-white shadow-[0_6px_20px_rgba(0,122,255,0.25)]' : 'bg-gradient-to-r from-[#F5F5F7] to-[#E3E3E8] text-[#1D1D1F] border border-black/5'" 
                     class="rounded-[20px] p-4 transition-all duration-300 relative overflow-hidden">
                    <div v-if="isUserMember" class="absolute -right-4 -top-4 w-24 h-24 bg-white/20 rounded-full blur-xl pointer-events-none"></div>
                    <p :class="isUserMember ? 'text-white/90' : 'text-[#86868B]'" class="text-[10px] font-bold uppercase tracking-wider mb-0.5">Status Akun</p>
                    <p class="text-[14px] font-extrabold uppercase tracking-tight leading-tight">
                        {{ isUserMember ? '✨ Akses Adidaya' : '🔒 Anggota Gratis' }}
                    </p>
                    <p v-if="isUserMember" class="text-[11px] font-semibold text-white/95 mt-1">
                        Aktif s.d {{ formatDate(user.membership_expires_at) }}
                    </p>
                </div>
            </div>

            <!-- Navigasi Sidebar -->
            <nav ref="sidebarNav" class="flex-1 overflow-y-auto py-3 px-3 custom-scrollbar space-y-1">
                
                <!-- MENU ADMIN -->
                <div v-if="isAdmin" class="flex flex-col space-y-1">
                    <p class="text-[10px] uppercase font-bold text-[#FF9500] px-3 mb-1 mt-2 tracking-[0.15em]">⚡ Admin Command</p>
                    
                    <Link :href="route('admin.dashboard')" :class="[route().current('admin.dashboard') ? 'bg-[#FFF9E6] text-[#FF9500] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF9500]"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        <span class="text-[13px]">Dashboard Admin</span>
                    </Link>

                    <Link :href="route('admin.users.index')" :class="[route().current('admin.users.*') ? 'bg-[#F0F4FF] text-[#007AFF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#007AFF]"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        <span class="text-[13px]">Kelola User</span>
                    </Link>

                    <Link :href="route('admin.tryouts.index')" :class="[(route().current('admin.tryouts.*') && !route().current('admin.tryouts.questions.*')) || (route().current('admin.tryouts.questions.*') && activeTryoutType !== 'adidaya' && activeTryoutType !== 'akbar') ? 'bg-[#F3E8FF] text-[#AF52DE] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#AF52DE]"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18s4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25" /></svg>
                        <span class="text-[13px]">Kelola Tryout</span>
                    </Link>

                    <Link :href="route('admin.adidaya.index')" :class="[route().current('admin.adidaya.*') || (route().current('admin.tryouts.questions.*') && activeTryoutType === 'adidaya') ? 'bg-[#F3E8FF] text-[#AF52DE] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#AF52DE]"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                        <span class="text-[13px]">Adidaya Manager</span>
                    </Link>

                    <Link :href="route('admin.membership-packages.index')" :class="[route().current('admin.membership-packages.*') ? 'bg-[#FFF9E6] text-[#FF9500] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF9500]"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                        <span class="text-[13px]">Paket Adidaya</span>
                    </Link>

                    <Link :href="route('admin.tryout-akbar.index')" :class="[route().current('admin.tryout-akbar.*') || (route().current('admin.tryouts.questions.*') && activeTryoutType === 'akbar') ? 'bg-[#FFF0F0] text-[#FF3B30] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF3B30]"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        <span class="text-[13px]">Event Akbar</span>
                    </Link>

                    <Link :href="route('admin.transactions.index')" :class="[route().current('admin.transactions.*') ? 'bg-[#E5F5EA] text-[#34C759] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#34C759]"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5M6.75 20.25v.75m0-1.5v-.75m0-1.5v-.75m0-1.5v-.75m0-1.5V15m-1.5-1.5h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75m1.5 0h.75" /></svg>
                        <span class="text-[13px]">Data Transaksi</span>
                    </Link>

                    <Link :href="route('admin.affiliate.index')" :class="[route().current('admin.affiliate.*') ? 'bg-[#F0F4FF] text-[#007AFF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#33C1FF]"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0a5.995 5.995 0 0 0-4.058-3.036m0 0a5.995 5.995 0 0 0-4.058 3.036m0 0a5.97 5.97 0 0 0-.941 3.197m9.411-3.197a5.971 5.971 0 0 0-.941-3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        <span class="text-[13px]">Kelola Afiliasi</span>
                    </Link>

                    <Link :href="route('admin.vouchers.index')" :class="[route().current('admin.vouchers.*') ? 'bg-[#FFF9E6] text-[#FF9500] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF9500]"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg>
                        <span class="text-[13px]">Kelola Voucher</span>
                    </Link>

                    <Link :href="route('admin.question-reports.index')" :class="[route().current('admin.question-reports.*') ? 'bg-[#FFF0F0] text-[#FF3B30] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF3B30]"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                        <span class="text-[13px]">Laporan Soal</span>
                    </Link>

                    <Link :href="route('admin.settings.index')" :class="[route().current('admin.settings.*') ? 'bg-[#F5F5F7] text-[#1D1D1F] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#86868B]"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71-.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <span class="text-[13px]">Pengaturan</span>
                    </Link>
                </div>

                <!-- MENU USER -->
                <div v-if="isUser && !isAdmin" class="flex flex-col space-y-1">
                    <p class="text-[10px] uppercase font-bold text-[#86868B] px-3 mb-1 mt-2 tracking-[0.15em]">Menu Navigasi</p>
                    
                    <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'bg-[#F0F4FF] text-[#007AFF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#007AFF]"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span class="text-[13px]">Dashboard</span>
                    </Link>

                    <Link :href="route('tryout.index')" :class="[(route().current('tryout.index') || route().current('tryout.my') || route().current('tryout.register') || (isInsideTryoutScope && activeTryoutType === 'general')) ? 'bg-[#F3E8FF] text-[#AF52DE] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#AF52DE]"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                        <span class="text-[13px]">Katalog Tryout</span>
                    </Link>

                    <Link :href="route('tryout-akbar.index')" :class="[(route().current('tryout-akbar.*') || (isInsideTryoutScope && activeTryoutType === 'akbar')) ? 'bg-[#FFF0F0] text-[#FF3B30] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF3B30]"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-1.533-2.58 3.75 3.75 0 0 0 3.013 6.501Z" /></svg>
                        <span class="text-[13px]">Event Akbar</span>
                    </Link>

                    <Link :href="route('tryout.adidaya')" :class="[(route().current('tryout.adidaya') || (isInsideTryoutScope && activeTryoutType === 'adidaya')) ? 'bg-[#F0F4FF] text-[#007AFF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center justify-between py-2.5 px-3 rounded-[12px] transition-all']">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#007AFF]"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                            <span class="text-[13px]">Nusantara Adidaya</span>
                        </div>
                        <span v-if="isUserMember" class="text-[9px] bg-[#E5F5EA] text-[#34C759] px-2 py-0.5 rounded-full font-bold">ACTIVE</span>
                        <span v-else class="text-[9px] bg-[#F5F5F7] text-[#86868B] px-2 py-0.5 rounded-full font-semibold">LOCKED</span>
                    </Link>

                    <Link :href="route('membership.index')" :class="[route().current('membership.*') ? 'bg-[#FFF9E6] text-[#FF9500] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#FF9500]"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.091 3.091L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.091 3.091ZM18.213 19.096 17.25 22.5l-.963-3.404a2.25 2.25 0 0 0-1.546-1.546L11.25 16.5l3.491-.987a2.25 2.25 0 0 0 1.546-1.546L17.25 10.5l.963 3.404a2.25 2.25 0 0 0 1.546 1.546L23.25 16.5l-3.491.987a2.25 2.25 0 0 0-1.546 1.546ZM17.25 1.5l.963 3.404a2.25 2.25 0 0 0 1.546 1.546L23.25 7.5l-3.491.987a2.25 2.25 0 0 0-1.546 1.546L17.25 13.5l-.963-3.404a2.25 2.25 0 0 0-1.546-1.546L11.25 7.5l3.491-.987a2.25 2.25 0 0 0 1.546-1.546L17.25 1.5Z" /></svg>
                        <span class="text-[13px]">Membership</span>
                    </Link>

                    <Link :href="route('affiliate.index')" :class="[route().current('affiliate.*') ? 'bg-[#F0F4FF] text-[#33C1FF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#33C1FF]"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0a5.995 5.995 0 0 0-4.058-3.036m0 0a5.995 5.995 0 0 0-4.058 3.036m0 0a5.97 5.97 0 0 0-.941 3.197m9.411-3.197a5.971 5.971 0 0 0-.941-3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        <span class="text-[13px]">Afiliasi</span>
                    </Link>

                    <Link :href="route('tryout.history')" :class="[(route().current('tryout.history') && !isInsideTryoutScope) ? 'bg-[#F3E8FF] text-[#AF52DE] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#AF52DE]"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        <span class="text-[13px]">Riwayat Tryout</span>
                    </Link>

                    <Link :href="route('wallet.index')" :class="[(route().current('wallet.index') || route().current('wallet.*')) ? 'bg-[#E5F5EA] text-[#34C759] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#34C759]"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" /></svg>
                        <span class="text-[13px]">Dompet Saya</span>
                    </Link>

                    <Link :href="route('profile.edit')" :class="[(route().current('profile.edit') || route().current('profile.*')) ? 'bg-[#F0F4FF] text-[#007AFF] active-link font-bold' : 'text-[#1D1D1F] hover:bg-[#F5F5F7] font-medium', 'flex items-center gap-3 py-2.5 px-3 rounded-[12px] transition-all']">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 text-[#007AFF]"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        <span class="text-[13px]">Profil Saya</span>
                    </Link>
                </div>
            </nav>

            <!-- Tombol Keluar / Logout -->
            <div class="p-3 border-t border-black/5 bg-white shrink-0">
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 text-[#FF3B30] hover:bg-[#FFF0F0] rounded-[12px] transition-all font-semibold text-[13px] active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                    <span>Keluar Akun</span>
                </Link>
            </div>
        </aside>

        <!-- KONTEN UTAMA HALAMAN -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header Atas (Glassmorphism) -->
            <header class="sticky top-0 h-16 bg-white/80 backdrop-blur-xl border-b border-black/5 flex items-center justify-between px-6 md:px-10 z-30">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="p-2 -ml-2 text-[#1D1D1F] hover:bg-[#F5F5F7] rounded-xl lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                    <h2 class="font-bold text-[#1D1D1F] uppercase text-[11px] tracking-[0.2em] truncate">
                        {{ isAdmin ? 'Admin Command Center' : 'Halaman Belajar Nusantara' }}
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 bg-[#E5F5EA] px-3.5 py-1.5 rounded-full border border-[#34C759]/20 text-[11px] font-bold text-[#34C759] uppercase tracking-wider">
                        <span class="w-2 h-2 bg-[#34C759] rounded-full animate-pulse"></span> Sistem Aktif
                    </div>
                    <img :src="user?.profile_photo_url || `https://ui-avatars.com/api/?name=${user?.name}`" class="h-9 w-9 rounded-full border border-black/5 shadow-sm object-cover" />
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 p-4 md:p-10 pb-24 lg:pb-12">
                <div class="max-w-[1400px] mx-auto animate-in fade-in duration-500">
                    <slot />
                </div>
            </main>
        </div>

        <!-- MOBILE BOTTOM TAB NAVIGATION (Glassmorphism) -->
        <div 
            v-if="!isExamPage && isUser && !isAdmin"
            class="md:hidden fixed bottom-0 left-0 w-full bg-white/80 backdrop-blur-xl border-t border-black/5 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] z-[999] px-8 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] flex justify-between items-center"
        >
            <!-- 1. Home -->
            <Link :href="route('dashboard')" class="flex flex-col items-center gap-1 transition-colors" :class="{ 'text-[#007AFF] font-bold': $page.url === '/dashboard', 'text-[#86868B] hover:text-[#007AFF] font-medium': $page.url !== '/dashboard' }">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[10px]">Home</span>
            </Link>
            
            <!-- 2. Tryout -->
            <Link :href="route('tryout.index')" class="flex flex-col items-center gap-1 transition-colors" :class="{ 'text-[#007AFF] font-bold': $page.url.startsWith('/tryout'), 'text-[#86868B] hover:text-[#007AFF] font-medium': !$page.url.startsWith('/tryout') }">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                <span class="text-[10px]">Tryout</span>
            </Link>
            
            <!-- 3. Dompet -->
            <Link :href="route('wallet.index')" class="flex flex-col items-center gap-1 transition-colors" :class="{ 'text-[#007AFF] font-bold': $page.url.startsWith('/wallet'), 'text-[#86868B] hover:text-[#007AFF] font-medium': !$page.url.startsWith('/wallet') }">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                <span class="text-[10px]">Dompet</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #D1D1D6; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>