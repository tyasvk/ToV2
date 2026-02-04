<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    users: Array,
    filters: Object
});

// --- FITUR PENCARIAN ---
const search = ref(props.filters?.search || '');

const performSearch = (value) => {
    router.get(route('admin.users.index'), { search: value }, { 
        preserveState: true, 
        replace: true 
    });
};

let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch(value);
    }, 500);
});

// --- STATE: MODAL MEMBERSHIP ---
const isMembershipModalOpen = ref(false);
const selectedUserForMembership = ref(null);
const membershipForm = useForm({ days: 30 });

const openMembershipModal = (user) => {
    selectedUserForMembership.value = user;
    isMembershipModalOpen.value = true;
};

const closeMembershipModal = () => {
    isMembershipModalOpen.value = false;
    membershipForm.reset();
};

const submitAddMembership = () => {
    membershipForm.post(route('admin.users.add-membership', selectedUserForMembership.value.id), {
        onSuccess: () => closeMembershipModal(),
        preserveScroll: true,
    });
};

// --- STATE: MODAL EDIT ---
const isEditModalOpen = ref(false);
const editingUser = ref(null);
const editForm = useForm({
    name: '',
    email: '',
    role: '',
});

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user?.name || '';
    editForm.email = user?.email || '';
    // Ambil nama role pertama jika ada
    editForm.role = (user?.roles && user.roles[0]) ? user.roles[0].name : 'user'; 
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    editForm.reset();
};

const submitUpdate = () => {
    editForm.patch(route('admin.users.update', editingUser.value.id), {
        onSuccess: () => closeEditModal(),
        preserveScroll: true,
    });
};

// --- STATE: MODAL TAMBAH SALDO ---
const isBalanceModalOpen = ref(false);
const selectedUserForBalance = ref(null);
const balanceForm = useForm({
    amount: '',
    description: '',
});

const openBalanceModal = (user) => {
    selectedUserForBalance.value = user;
    isBalanceModalOpen.value = true;
};

const closeBalanceModal = () => {
    isBalanceModalOpen.value = false;
    balanceForm.reset();
};

const submitAddBalance = () => {
    balanceForm.post(route('admin.users.add-balance', selectedUserForBalance.value.id), {
        onSuccess: () => closeBalanceModal(),
        preserveScroll: true,
    });
};

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
        router.delete(route('admin.users.destroy', id), { preserveScroll: true });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric',
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0
    }).format(amount || 0);
};
</script>

<template>
    <Head title="Kelola Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex flex-col gap-1">
                    <h2 class="font-black text-3xl text-gray-900 tracking-tighter uppercase italic">Kelola Pengguna</h2>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.3em]">Manajemen otoritas akun, saldo, dan durasi member</p>
                </div>
                <div class="relative w-full md:w-80">
                    <input v-model="search" type="text" placeholder="Cari nama atau email..." 
                        class="w-full pl-11 pr-4 py-3 bg-white border border-gray-100 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-indigo-500 shadow-sm transition-all" />
                </div>
            </div>
        </template>

        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-8 py-5">Identitas Pengguna</th>
                            <th class="px-8 py-5 text-center">Role</th>
                            <th class="px-8 py-5">Saldo</th>
                            <th class="px-8 py-5">Membership</th>
                            <th class="px-8 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="user in users" :key="user.id" class="group hover:bg-gray-50/50 transition-all">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 bg-indigo-50 rounded-2xl flex items-center justify-center font-black text-indigo-600 text-xs border border-indigo-100">
                                        {{ (user.name || '??').substring(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 text-sm uppercase tracking-tight">{{ user.name || 'Tanpa Nama' }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest"
                                    :class="user.roles?.[0]?.name === 'admin' ? 'bg-rose-50 text-rose-600' : 'bg-slate-100 text-slate-600'">
                                    {{ user.roles?.[0]?.name || 'user' }}
                                </span>
                            </td>
                            <td class="px-8 py-6">
                                <p class="font-black text-gray-900 text-sm italic">{{ formatCurrency(user.balance) }}</p>
                            </td>
                            <td class="px-8 py-6">
                                <div v-if="user.membership_expires_at">
                                    <span v-if="new Date(user.membership_expires_at) > new Date()" class="text-emerald-600 font-black text-[10px] uppercase">👑 Aktif</span>
                                    <span v-else class="text-gray-400 font-black text-[10px] uppercase tracking-widest">Expired</span>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">{{ formatDate(user.membership_expires_at) }}</p>
                                </div>
                                <span v-else class="text-[9px] text-gray-300 font-black uppercase tracking-widest italic">Bukan Member</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="openMembershipModal(user)" title="Membership" class="w-9 h-9 flex items-center justify-center bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm">💎</button>
                                    <button @click="openBalanceModal(user)" title="Saldo" class="w-9 h-9 flex items-center justify-center bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl transition-all shadow-sm">💰</button>
                                    <button @click="openEditModal(user)" title="Edit" class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm">✏️</button>
                                    <button v-if="user.id !== $page.props.auth.user?.id" @click="deleteUser(user.id)" title="Hapus" class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-400 hover:bg-red-600 hover:text-white rounded-xl transition-all shadow-sm">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isMembershipModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeMembershipModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter italic mb-6">Update Durasi Member</h3>
                    <form @submit.prevent="submitAddMembership" class="space-y-4">
                        <div class="grid grid-cols-3 gap-2">
                            <button v-for="d in [7, 30, 365]" :key="d" type="button" @click="membershipForm.days = d"
                                :class="membershipForm.days === d ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-400'"
                                class="py-3 rounded-xl text-[10px] font-black uppercase transition-all border border-transparent">+{{ d }} Hari</button>
                        </div>
                        <input v-model="membershipForm.days" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold" required />
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeMembershipModal" class="flex-1 text-[10px] font-black uppercase text-gray-400">Batal</button>
                            <button type="submit" class="flex-[2] py-4 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase shadow-xl">Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="isBalanceModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeBalanceModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter italic mb-6">Tambah Saldo User</h3>
                    <form @submit.prevent="submitAddBalance" class="space-y-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Jumlah (Rp)</label>
                            <input v-model="balanceForm.amount" type="number" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold" placeholder="50000" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Keterangan</label>
                            <input v-model="balanceForm.description" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold" placeholder="Topup dari admin" />
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeBalanceModal" class="flex-1 text-[10px] font-black uppercase text-gray-400">Batal</button>
                            <button type="submit" class="flex-[2] py-4 bg-emerald-600 text-white rounded-2xl font-black text-[10px] uppercase shadow-xl">Tambah Saldo</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="isEditModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeEditModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-black text-xl text-gray-900 uppercase tracking-tighter italic mb-6">Edit Data Pengguna</h3>
                    <form @submit.prevent="submitUpdate" class="space-y-5">
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Nama Lengkap</label>
                            <input v-model="editForm.name" type="text" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Email</label>
                            <input v-model="editForm.email" type="email" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold" required />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[9px] font-black text-gray-400 uppercase ml-2">Otoritas (Role)</label>
                            <select v-model="editForm.role" class="w-full border-gray-100 bg-gray-50 rounded-2xl p-4 text-sm font-bold">
                                <option value="user">USER (Biasa)</option>
                                <option value="admin">ADMIN (Kontrol Penuh)</option>
                            </select>
                        </div>
                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeEditModal" class="flex-1 text-[10px] font-black uppercase text-gray-400">Batal</button>
                            <button type="submit" class="flex-[2] py-4 bg-gray-900 text-white rounded-2xl font-black text-[10px] uppercase shadow-xl">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>