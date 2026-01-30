<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

// --- 1. DATA AUTH & ROLE ---
const user = computed(() => page.props.auth?.user ?? null);

// --- 2. NORMALISASI ROLES ---
const roles = computed(() => {
    const rawRoles = user.value?.roles;
    if (Array.isArray(rawRoles)) return rawRoles;
    if (typeof rawRoles === 'object' && rawRoles !== null) return Object.values(rawRoles);
    return [];
});

// --- 3. LOGIKA ROLE ---
const isAdmin = computed(() => 
    roles.value.some(r => String(r).toLowerCase() === 'admin')
);

const isUser = computed(() => 
    roles.value.some(r => String(r).toLowerCase() === 'user') || roles.value.length === 0
);

// --- 4. LOGIKA ACTIVE SIDEBAR (FIXED) ---
// Cek apakah halaman ini milik Tryout Akbar (Termasuk halaman soal/questions jika tipenya akbar)
const isAkbarActive = computed(() => {
    // 1. Jika rute depannya memang tryout-akbar
    if (route().current('admin.tryout-akbar.*')) return true;
    
    // 2. Jika rute soal/questions TAPI data tryout-nya bertipe 'akbar'
    if (route().current('admin.tryouts.questions.*') && page.props.tryout?.type === 'akbar') {
        return true;
    }
    return false;
});

// Cek apakah halaman ini milik Tryout Biasa
const isTryoutActive = computed(() => {
    // Jika sudah dideteksi sebagai Akbar, maka ini false
    if (isAkbarActive.value) return false;

    // Logic default untuk tryout biasa
    return route().current('admin.tryouts.*') || route().current('admin.questions.*');
});


// --- DYNAMIC LOGO ROUTE ---
const logoRoute = computed(() => {
    try {
        return isAdmin.value ? route('admin.dashboard') : route('dashboard');
    } catch (e) {
        return '/dashboard'; 
    }
});

const isSidebarOpen = ref(true);

onMounted(() => {
    console.log("Layout Loaded");
});
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden font-sans text-gray-900">
        
        <aside 
            :class="[
                'bg-white border-r border-gray-200 transition-all duration-300 flex flex-col z-50 shadow-xl shrink-0', 
                isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <div class="h-20 flex items-center px-6 border-b border-gray-100 shrink-0 bg-white">
                <Link :href="logoRoute" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100 shrink-0 group-hover:scale-110 transition-transform">
                        TO
                    </div>
                    <span v-if="isSidebarOpen" class="font-black text-xl tracking-tighter text-gray-800 uppercase truncate">
                        CAT-V2
                    </span>
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1 custom-scrollbar">
                
                <div v-if="isAdmin" class="space-y-1">
                    <p v-if="isSidebarOpen" class="text-[10px] uppercase font-black text-red-500 px-3 mb-2 tracking-widest">Admin Control</p>
                    
                    <Link :href="route('admin.dashboard')" :class="[route().current('admin.dashboard') ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">🛡️</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Dashboard</span>
                    </Link>

                    <Link :href="route('admin.tryouts.index')" :class="[isTryoutActive ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">📑</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Kelola Tryout</span>
                    </Link>

                    <Link :href="route('admin.tryout-akbar.index')" :class="[isAkbarActive ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">🏆</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Tryout Akbar</span>
                    </Link>

                    <Link :href="route('admin.transactions.index')" :class="[route().current('admin.transactions.*') ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">💰</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Transaksi</span>
                    </Link>

                    <Link :href="route('admin.users.index')" :class="[route().current('admin.users.*') ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">👥</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Kelola User</span>
                    </Link>
                </div>

                <div v-if="isUser && !isAdmin" class="space-y-1">
                    <p v-if="isSidebarOpen" class="text-[10px] uppercase font-black text-gray-400 px-3 mb-2 tracking-widest">Main Menu</p>
                    
                    <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">🏠</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Dashboard</span>
                    </Link>

                    <Link :href="route('tryout-akbar.index')" :class="[route().current('tryout-akbar.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">🔥</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Event Akbar</span>
                    </Link>

                    <Link :href="route('tryout.index')" 
                        :class="[(route().current('tryout.index') || route().current('tryout.show') || route().current('tryout.register')) ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">📝</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Katalog Tryout</span>
                    </Link>

                    <Link :href="route('tryout.history')" :class="[route().current('tryout.history') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">📜</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Riwayat Tryout</span>
                    </Link>

                    <Link :href="route('wallet.index')" :class="[route().current('wallet.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">💳</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Dompet Saya</span>
                    </Link>

                    <Link :href="route('profile.edit')" :class="[route().current('profile.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all group']">
                        <span class="text-xl">👤</span> 
                        <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Profil Saya</span>
                    </Link>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center gap-3 p-3.5 text-red-500 hover:bg-red-100 rounded-2xl transition font-black text-[10px] uppercase tracking-widest active:scale-95">
                    <span>Logout Account</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-30">
                <div class="flex items-center gap-4">
                    <button 
                        @click="isSidebarOpen = !isSidebarOpen" 
                        class="p-2.5 hover:bg-gray-100 rounded-xl text-gray-400 transition active:scale-90"
                    >
                        <span class="text-xl font-bold">☰</span>
                    </button>
                    <h2 class="font-bold text-gray-400 uppercase text-[10px] tracking-[0.2em]">
                        {{ isAdmin ? 'Admin Control Center' : 'User Workspace' }}
                    </h2>
                </div>

                <div class="hidden sm:flex items-center gap-3">
                    <div class="text-[9px] font-black text-green-500 uppercase bg-green-50 px-4 py-2 rounded-full border border-green-100 flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                        System Active
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 md:p-10 bg-gray-50/20">
                <div v-if="$slots.header" class="mb-10 animate-in fade-in duration-700">
                    <slot name="header" />
                </div>
                
                <div class="animate-in fade-in slide-in-from-bottom-2 duration-700">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-in {
    animation-duration: 0.5s;
    animation-fill-mode: both;
}
</style>