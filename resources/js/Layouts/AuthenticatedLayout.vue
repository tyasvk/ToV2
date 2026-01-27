<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
// Ambil roles, pastikan defaultnya array kosong jika tidak ada
const roles = computed(() => user.value?.roles || []);

// Penentu Role
const isAdmin = computed(() => roles.value.includes('admin'));
const isUser = computed(() => roles.value.includes('user'));

const isSidebarOpen = ref(true);
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden font-sans">
        
        <aside :class="['bg-white border-r border-gray-200 transition-all duration-300 flex flex-col z-50 shadow-xl shrink-0', isSidebarOpen ? 'w-64' : 'w-20']">
            <div class="h-20 flex items-center px-6 border-b border-gray-100 bg-white">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-black shadow-lg">TO</div>
                    <span v-if="isSidebarOpen" class="font-black text-xl tracking-tighter uppercase">CAT-V2</span>
                </Link>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                <div v-if="isUser" class="space-y-1">
                    <p v-if="isSidebarOpen" class="text-[10px] uppercase font-bold text-gray-400 px-3 mb-2 tracking-widest">Main Menu</p>
                    <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all']">
                        <span>🏠</span> <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Dashboard</span>
                    </Link>
                    <Link :href="route('tryout.index')" :class="[route().current('tryout.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all']">
                        <span>📝</span> <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Katalog Tryout</span>
                    </Link>
                    <Link :href="route('profile.edit')" :class="[route().current('profile.edit') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all']">
                        <span>👤</span> <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Profil Saya</span>
                    </Link>
                </div>

                <div v-if="isAdmin" class="space-y-1 pt-6 border-t border-gray-50 mt-4">
                    <p v-if="isSidebarOpen" class="text-[10px] uppercase font-bold text-red-500 px-3 mb-2 tracking-widest">Administrator</p>
                    <Link :href="route('admin.dashboard')" :class="[route().current('admin.dashboard') ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all']">
                        <span>🛡️</span> <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Admin Dash</span>
                    </Link>
                    <Link :href="route('admin.tryouts.index')" :class="[route().current('admin.tryouts.*') ? 'bg-red-50 text-red-600' : 'text-gray-500 hover:bg-red-50', 'flex items-center gap-3 p-3.5 rounded-2xl font-bold transition-all']">
                        <span>📑</span> <span v-if="isSidebarOpen" class="text-xs uppercase tracking-tight">Kelola Tryout</span>
                    </Link>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-100">
                <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center gap-2 p-3 text-red-500 hover:bg-red-50 rounded-xl transition font-black text-[10px] uppercase tracking-widest">
                    <span>Logout Account</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = !isSidebarOpen" class="p-2.5 hover:bg-gray-100 rounded-xl text-gray-400 transition">☰</button>
                    <h2 class="font-bold text-gray-400 uppercase text-[10px] tracking-widest">
                        {{ isAdmin ? 'Admin Center' : 'User Workspace' }}
                    </h2>
                </div>
                <div class="text-[9px] font-black text-green-500 uppercase flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span> Swoole Active
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-10 bg-gray-50/30">
                <div v-if="$slots.header" class="mb-10"><slot name="header" /></div>
                <slot />
            </main>
        </div>
    </div>
</template>