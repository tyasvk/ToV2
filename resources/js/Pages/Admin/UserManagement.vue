<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    users: Array,
    roles: Array
});

const form = useForm({
    role: ''
});

const changeRole = (user, roleName) => {
    if(confirm(`Ubah role ${user.name} menjadi ${roleName}?`)) {
        form.role = roleName;
        form.patch(route('admin.users.update-role', user.id));
    }
};

const deleteUser = (user) => {
    if(confirm(`Hapus permanen user ${user.name}?`)) {
        form.delete(route('admin.users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Kelola User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-gray-800">Manajemen Pengguna</h2>
            <p class="text-sm text-gray-500">Total terdaftar: {{ users.length }} Peserta</p>
        </template>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b border-gray-100">
                            <th class="px-8 py-4">User</th>
                            <th class="px-8 py-4">Role Saat Ini</th>
                            <th class="px-8 py-4">Ganti Role</th>
                            <th class="px-8 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50/50 transition">
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 text-sm">{{ user.name }}</div>
                                        <div class="text-xs text-gray-400">{{ user.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <span :class="[
                                    user.roles[0]?.name === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600',
                                    'px-3 py-1 rounded-full text-[10px] font-black uppercase'
                                ]">
                                    {{ user.roles[0]?.name || 'No Role' }}
                                </span>
                            </td>
                            <td class="px-8 py-4">
                                <select 
                                    @change="changeRole(user, $event.target.value)"
                                    class="text-xs border-gray-200 rounded-lg focus:ring-indigo-500 py-1"
                                >
                                    <option value="" disabled selected>Pilih Role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        Set as {{ role.name.toUpperCase() }}
                                    </option>
                                </select>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <button 
                                    @click="deleteUser(user)"
                                    class="p-2 text-gray-400 hover:text-red-600 transition"
                                    title="Hapus User"
                                >
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>