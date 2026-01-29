<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array
});

// --- STATE MANAGEMENT: EDIT USER ---
const isEditModalOpen = ref(false);
const editingUser = ref(null);

const editForm = useForm({
    name: '',
    email: '',
    role: '',
});

// --- STATE MANAGEMENT: TAMBAH SALDO ---
const isBalanceModalOpen = ref(false);
const selectedUserForBalance = ref(null);

const balanceForm = useForm({
    amount: '',
    description: '',
});

// --- LOGIKA MODAL: EDIT ---
const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.roles[0]?.name || 'user'; 
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
    editForm.clearErrors();
};

const submitUpdate = () => {
    editForm.patch(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => closeEditModal(),
        preserveScroll: true,
    });
};

// --- LOGIKA MODAL: TAMBAH SALDO ---
const openBalanceModal = (user) => {
    selectedUserForBalance.value = user;
    isBalanceModalOpen.value = true;
};

const closeBalanceModal = () => {
    isBalanceModalOpen.value = false;
    balanceForm.reset();
    balanceForm.clearErrors();
};

const submitAddBalance = () => {
    balanceForm.post(route('admin.users.add-balance', selectedUserForBalance.value.id), {
        onSuccess: () => closeBalanceModal(),
        preserveScroll: true,
    });
};

// --- AKSI HAPUS ---
const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data hasil ujian mereka juga akan terhapus.')) {
        router.delete(route('admin.users.destroy', id), {
            preserveScroll: true
        });
    }
};

// --- UTILITIES ---
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount || 0);
};
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase italic">Kelola Pengguna</h2>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.3em]">Manajemen otoritas akun, saldo, dan data peserta</p>
            </div>
        </template>

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden animate-in fade-in duration-500">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5">Identitas Pengguna</th>
                            <th class="px-8 py-5">Saldo Dompet</th>
                            <th class="px-8 py-5">Otoritas / Role</th>
                            <th class="px-8 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users" :key="user.id" class="group hover:bg-gray-50/50 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 bg-indigo-50 rounded-2xl flex items-center justify-center font-black text-indigo-600 text-xs border border-indigo-100">
                                        {{ user.name.substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm uppercase tracking-tight">{{ user.name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <p class="font-black text-gray-900 text-sm italic">{{ formatCurrency(user.balance) }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Saldo Aktif</p>
                            </td>
                            <td class="px-8 py-6">
                                <span v-for="role in user.roles" :key="role.id"
                                    :class="role.name === 'admin' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100'"
                                    class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm">
                                    {{ role.name }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openBalanceModal(user)" title="Tambah Saldo" class="w-10 h-10 flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm active:scale-95">
                                        💰
                                    </button>
                                    <button @click="openEditModal(user)" title="Edit User" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm active:scale-95">
                                        ✏️
                                    </button>
                                    <button v-if="user.id !== $page.props.auth.user.id" @click="deleteUser(user.id)" title="Hapus User" class="w-10 h-10 flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-red-600 hover:text-white rounded-xl transition-all shadow-sm active:scale-95">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isEditModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeEditModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter">Edit Profil User</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Perbarui data dan hak akses</p>
                        </div>
                        <button @click="closeEditModal" class="p-2 hover:bg-gray-100 rounded-xl transition">✕</button>
                    </div>
                    <form @submit.prevent="submitUpdate" class="space-y-6">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Nama Lengkap</label>
                            <input v-model="editForm.name" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Alamat Email</label>
                            <input v-model="editForm.email" type="email" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Otoritas Role</label>
                            <select v-model="editForm.role" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500">
                                <option value="user">USER (PESERTA UJIAN)</option>
                                <option value="admin">ADMINISTRATOR SYSTEM</option>
                            </select>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeEditModal" class="flex-1 py-4 text-gray-400 font-black text-[10px] uppercase">Batal</button>
                            <button type="submit" :disabled="editForm.processing" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600">
                                {{ editForm.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div v-if="isBalanceModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeBalanceModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter">Tambah Saldo Manual</h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">User: {{ selectedUserForBalance?.name }}</p>
                        </div>
                        <button @click="closeBalanceModal" class="p-2 hover:bg-gray-100 rounded-xl transition">✕</button>
                    </div>
                    <form @submit.prevent="submitAddBalance" class="space-y-6">
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Nominal (Rp)</label>
                            <input v-model="balanceForm.amount" type="number" placeholder="Contoh: 50000"
                                class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500 transition-all" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2 tracking-widest">Keterangan</label>
                            <input v-model="balanceForm.description" type="text" placeholder="Misal: Hadiah Event / Refund"
                                class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500 transition-all" />
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeBalanceModal" class="flex-1 py-4 text-gray-400 font-black text-[10px] uppercase">Batal</button>
                            <button type="submit" :disabled="balanceForm.processing" class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700">
                                {{ balanceForm.processing ? 'Memproses...' : 'Tambah Saldo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-in { animation: fadeIn 0.3s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>