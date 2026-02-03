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

// Helper untuk mengecek tipe tryout yang sedang aktif di props (untuk halaman Kelola Soal)
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
                        <span class="font-black text-sm tracking-[0.3em] text-slate-900 uppercase">NUSANTARA</span>
                        <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest leading-none">CPNS Academy</span>
                    </div>
                </Link>
            </div>

            <div v-if="isUser && !isAdmin && user" class="px-6 mt-4 shrink-0">
                <div :class="isUserMember ? 'bg-indigo-600 shadow-indigo-100 shadow-lg' : 'bg-slate-100'" 
                     class="rounded-[1.5rem] p-5 transition-all duration-500 relative overflow-hidden">
                    <div v-if="isUserMember" class="absolute top-0 right-0 w-16 h-16 bg-white/10 -mr-8 -mt-8 rounded-full blur-xl"></div>
                    <p :class="isUserMember ? 'text-white/60' : 'text-slate-400'" class="text-[10px] font-black uppercase tracking-widest mb-1">Status Saya</p>
                    <p :class="isUserMember ? 'text-white' : 'text-slate-600'" class="text-xs font-black uppercase tracking-tight leading-none">
                        {{ isUserMember ? 'Akses Adidaya' : 'Anggota Gratis' }}
                    </p>
                    <p v-if="isUserMember" class="text-[10px] font-bold text-indigo-200 mt-1.5 leading-none">
                        Sampai: {{ formatDate(user.membership_expires_at) }}
                    </p>
                </div>
            </div>

            <nav ref="sidebarNav" class="flex-1 overflow-y-auto py-4 px-4 space-y-1.5 custom-scrollbar">
                
                <div v-if="isAdmin" class="space-y-1">
                    <p class="text-[11px] uppercase font-black text-rose-500 px-4 mb-3 mt-4 tracking-[0.3em]">Admin Control</p>
                    
                    <Link :href="route('admin.dashboard')" 
                        :class="[route().current('admin.dashboard') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-xl">🛡️</span> <span class="text-xs uppercase tracking-widest">Dashboard Admin</span>
                    </Link>

                    <Link :href="route('admin.users.index')" 
                        :class="[route().current('admin.users.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-xl">👥</span> <span class="text-xs uppercase tracking-widest">Kelola User</span>
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
                        <span class="text-xl">📚</span> <span class="text-xs uppercase tracking-widest">Kelola Tryout</span>
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
                        <span class="text-xl">⚡</span> <span class="text-xs uppercase tracking-widest">Adidaya Manager</span>
                    </Link>

                    <Link :href="route('admin.tryout-akbar.index')" 
                        :class="[route().current('admin.tryout-akbar.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-xl">🏆</span> <span class="text-xs uppercase tracking-widest">Event Akbar</span>
                    </Link>

                    <Link :href="route('admin.transactions.index')" 
                        :class="[route().current('admin.transactions.*') ? 'bg-rose-50 text-rose-600 active-link shadow-sm' : 'text-slate-500 hover:bg-rose-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-xl">💸</span> <span class="text-xs uppercase tracking-widest">Data Transaksi</span>
                    </Link>
                </div>

                <div v-if="isUser && !isAdmin" class="space-y-1.5">
                    <p class="text-[11px] uppercase font-black text-slate-400 px-4 mb-3 mt-4 tracking-[0.3em]">Menu Navigasi</p>
                    
                    <Link :href="route('dashboard')" 
                        :class="[route().current('dashboard') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">🏠</span> <span class="text-xs uppercase tracking-widest">Dashboard</span>
                    </Link>

                    <Link :href="route('tryout.index')" 
                        :class="[(route().current('tryout.index') || route().current('tryout.show') || route().current('tryout.register') || route().current('tryout.my')) ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">📝</span> <span class="text-xs uppercase tracking-widest">Katalog Tryout</span>
                    </Link>

                    <Link :href="route('tryout-akbar.index')" 
                        :class="[route().current('tryout-akbar.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">🔥</span> <span class="text-xs uppercase tracking-widest">Event Akbar</span>
                    </Link>

                    <Link :href="route('tryout.adidaya')" 
                        :class="[route().current('tryout.adidaya') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-100', 'flex items-center justify-between p-4 rounded-2xl font-black transition-all group']"
                    >
                        <div class="flex items-center gap-4">
                            <span class="text-2xl">⚡</span> <span class="text-xs uppercase tracking-widest">Nusantara Adidaya</span>
                        </div>
                        <span v-if="isUserMember" class="text-[8px] bg-indigo-600 text-white px-2 py-0.5 rounded-md font-black shadow-sm shadow-indigo-200">ACTIVE</span>
                        <span v-else class="text-[8px] bg-slate-200 text-slate-500 px-2 py-0.5 rounded-md font-black italic">LOCKED</span>
                    </Link>

                    <Link :href="route('membership.index')" 
                        :class="[route().current('membership.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">💎</span> <span class="text-xs uppercase tracking-widest">Membership</span>
                    </Link>

                    <Link :href="route('tryout.history')" 
                        :class="[route().current('tryout.history') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">📜</span> <span class="text-xs uppercase tracking-widest">Riwayat Tryout</span>
                    </Link>

                    <Link :href="route('wallet.index')" 
                        :class="[route().current('wallet.index') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">💳</span> <span class="text-xs uppercase tracking-widest">Dompet Saya</span>
                    </Link>

                    <Link :href="route('profile.edit')" 
                        :class="[route().current('profile.*') ? 'bg-slate-900 text-white shadow-xl active-link' : 'text-slate-500 hover:bg-slate-50', 'flex items-center gap-4 p-4 rounded-2xl font-black transition-all group']"
                    >
                        <span class="text-2xl">👤</span> <span class="text-xs uppercase tracking-widest">Profil Saya</span>
                    </Link>
                </div>
            </nav>

            <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                <Link :href="route('logout')" method="post" as="button" 
                    class="w-full flex items-center justify-center gap-3 p-4 text-rose-500 hover:bg-rose-50 rounded-2xl transition font-black text-xs uppercase tracking-[0.2em] active:scale-95"
                >
                    <span>Keluar Akun</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <header class="h-24 bg-white border-b border-slate-100 flex items-center justify-between px-10 shrink-0 z-30 shadow-sm">
                <div class="flex items-center gap-6">
                    <h2 class="font-black text-slate-900 uppercase text-xs tracking-[0.3em]">
                        {{ isAdmin ? 'Admin Command Center' : 'Halaman Belajar Nusantara' }}
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
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

/* Garis indikator aktif untuk style admin */
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