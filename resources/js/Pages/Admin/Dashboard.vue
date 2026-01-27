<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Data dummy untuk statistik
const stats = [
    { name: 'Total Pengguna', value: '1,234', icon: '👤', color: 'bg-blue-500' },
    { name: 'Pendapatan Hari Ini', value: 'Rp 2.4M', icon: '💰', color: 'bg-green-500' },
    { name: 'Pendaftar Baru', value: '42', icon: '📈', color: 'bg-purple-500' },
    { name: 'Laporan Error', value: '3', icon: '⚠️', color: 'bg-red-500' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Control Center</h2>
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold uppercase">
                    Role: {{ user.roles[0] }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="stat in stats" :key="stat.name" 
                         class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4"
                         :class="stat.color.replace('bg-', 'border-')">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full text-white" :class="stat.color">
                                <span class="text-2xl">{{ stat.icon }}</span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ stat.name }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700">Aktivitas Pengguna Terakhir</h3>
                            <button class="text-blue-600 hover:underline text-sm font-medium">Lihat Semua</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                                    <tr>
                                        <th class="px-6 py-3">User</th>
                                        <th class="px-6 py-3">Aksi</th>
                                        <th class="px-6 py-3">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 mr-3"></div>
                                            <div>
                                                <p class="text-sm font-bold">Ahmad Syarif</p>
                                                <p class="text-xs text-gray-500">ahmad@mail.com</p>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm">Membeli Paket Premium</td>
                                        <td class="px-6 py-4 text-xs text-gray-400">2 menit yang lalu</td>
                                    </tr>
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-gray-700 mb-4 text-center">Server Health (Octane)</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Swoole Worker Status</span>
                                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                            </div>
                            <hr>
                            <div class="text-center py-4">
                                <p class="text-4xl font-black text-blue-600">0.02s</p>
                                <p class="text-xs text-gray-400 mt-1 uppercase">Avg Response Time</p>
                            </div>
                            <button class="w-full py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-bold text-sm">
                                System Logs
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>