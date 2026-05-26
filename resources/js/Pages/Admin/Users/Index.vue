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
    <Head title="Kelola Pengguna - CPNS Nusantara" />

    <AuthenticatedLayout>
        <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-64 h-64 bg-blue-50 rounded-full blur-[60px] pointer-events-none -mr-20 -mt-20"></div>

                <div class="relative z-10 space-y-1.5">
                    <h1 class="text-2xl md:text-3xl font-medium text-slate-900 tracking-tight">Kelola Pengguna</h1>
                    <p class="text-sm text-slate-500 font-medium">Manajemen otoritas akun, saldo, dan status membership.</p>
                </div>

                <div class="flex items-center w-full md:w-auto relative z-10">
                    <div class="relative w-full md:w-80">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="Cari nama atau email..." 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-slate-800 placeholder:text-slate-400 placeholder:font-normal shadow-sm outline-none" 
                        />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Identitas Pengguna</th>
                                <th class="px-6 py-4 text-center">Role</th>
                                <th class="px-6 py-4">Saldo Dompet</th>
                                <th class="px-6 py-4">Status Member</th>
                                <th class="px-6 py-4 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center font-semibold text-blue-600 text-xs shrink-0">
                                            {{ (user.name || 'U').substring(0, 2).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-900 text-sm truncate max-w-[200px]">{{ user.name || 'Tanpa Nama' }}</p>
                                            <p class="text-xs text-slate-500 mt-0.5 truncate max-w-[200px]">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-center">
                                    <span 
                                        class="px-2.5 py-1 rounded-md text-[10px] font-semibold uppercase tracking-wider border"
                                        :class="user.roles?.[0]?.name === 'admin' ? 'bg-amber-50 text-amber-600 border-amber-200' : 'bg-slate-50 text-slate-500 border-slate-200'"
                                    >
                                        {{ user.roles?.[0]?.name || 'user' }}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900 text-sm">{{ formatCurrency(user.balance) }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="user.membership_expires_at">
                                        <div v-if="new Date(user.membership_expires_at) > new Date()" class="flex flex-col">
                                            <span class="text-emerald-600 font-semibold text-xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Aktif
                                            </span>
                                            <span class="text-xs text-slate-500 mt-0.5 ml-3">s.d {{ formatDate(user.membership_expires_at) }}</span>
                                        </div>
                                        <div v-else class="flex flex-col">
                                            <span class="text-slate-400 font-semibold text-xs flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span> Kedaluwarsa
                                            </span>
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-slate-400 font-medium">Bukan Member</span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openMembershipModal(user)" title="Update Membership" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                        </button>
                                        <button @click="openBalanceModal(user)" title="Tambah Saldo" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        </button>
                                        <button @click="openEditModal(user)" title="Edit Pengguna" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        <button v-if="user.id !== $page.props.auth.user?.id" @click="deleteUser(user.id)" title="Hapus Pengguna" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-50 hover:border-rose-200 rounded-lg transition-colors shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.length">
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 font-medium">
                                    Tidak ada pengguna ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="isMembershipModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeMembershipModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-2xl p-6 sm:p-8 shadow-xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-semibold text-lg text-slate-900 mb-6">Update Durasi Member</h3>
                    <form @submit.prevent="submitAddMembership" class="space-y-5">
                        <div class="grid grid-cols-3 gap-3">
                            <button v-for="d in [7, 30, 365]" :key="d" type="button" @click="membershipForm.days = d"
                                :class="membershipForm.days === d ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="py-2.5 rounded-xl text-xs font-semibold transition-all border shadow-sm">
                                +{{ d }} Hari
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Durasi Kustom (Hari)</label>
                            <input v-model="membershipForm.days" type="number" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" required />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="closeMembershipModal" class="flex-1 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                            <button type="submit" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-sm active:scale-95 transition-all">Update Akses</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="isBalanceModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeBalanceModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-2xl p-6 sm:p-8 shadow-xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-semibold text-lg text-slate-900 mb-6">Tambah Saldo Dompet</h3>
                    <form @submit.prevent="submitAddBalance" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Nominal (Rp)</label>
                            <input v-model="balanceForm.amount" type="number" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="50000" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Keterangan Tambahan</label>
                            <input v-model="balanceForm.description" type="text" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" placeholder="Topup manual admin" />
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeBalanceModal" class="flex-1 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                            <button type="submit" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-sm active:scale-95 transition-all">Tambah Saldo</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="isEditModalOpen" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeEditModal"></div>
                <div class="relative bg-white w-full max-w-md rounded-2xl p-6 sm:p-8 shadow-xl animate-in zoom-in-95 duration-200">
                    <h3 class="font-semibold text-lg text-slate-900 mb-6">Edit Identitas Pengguna</h3>
                    <form @submit.prevent="submitUpdate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                            <input v-model="editForm.name" type="text" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                            <input v-model="editForm.email" type="email" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all" required />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Otoritas (Role)</label>
                            <select v-model="editForm.role" class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all">
                                <option value="user">User Biasa</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="closeEditModal" class="flex-1 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl font-semibold text-xs hover:bg-slate-50 transition-colors shadow-sm">Batal</button>
                            <button type="submit" class="flex-[2] py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-xs shadow-sm active:scale-95 transition-all">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.animate-in {
    animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>