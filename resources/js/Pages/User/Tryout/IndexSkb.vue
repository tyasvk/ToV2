<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    catalogTryouts: {
        type: Array,
        default: () => [],
    },
    myTryouts: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    isPremiumMember: {
        type: Boolean,
        default: false,
    },
});

const search = ref(props.filters.search || '');

const filteredCategories = computed(() => {
    if (!search.value) return props.categories;
    const keyword = search.value.toLowerCase();
    return props.categories.filter(
        (cat) =>
            cat.name.toLowerCase().includes(keyword) ||
            (cat.description && cat.description.toLowerCase().includes(keyword))
    );
});
</script>

<template>
    <Head title="Tryout SKB CPNS" />

    <AuthenticatedLayout>
        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header & Pencarian -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tryout SKB (Seleksi Kompetensi Bidang)</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Pilih formasi jabatan untuk melihat paket simulasi CAT SKB yang tersedia.
                    </p>
                </div>

                <div class="w-full md:w-80">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari formasi jabatan..."
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    />
                </div>
            </div>

            <!-- Daftar Formasi SKB dari Database (Admin) -->
            <div v-if="filteredCategories.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="cat in filteredCategories"
                    :key="cat.id"
                    :href="route('tryout.skb.show', cat.slug)"
                    class="group bg-white rounded-2xl border border-gray-200 p-6 shadow-sm hover:shadow-md hover:border-indigo-300 transition flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-3xl bg-indigo-50 w-12 h-12 rounded-xl flex items-center justify-center">
                                {{ cat.icon || '💼' }}
                            </span>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">
                                {{ cat.tryouts_count ?? 0 }} Paket Tryout
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition">
                            {{ cat.name }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-2 line-clamp-2">
                            {{ cat.description || 'Simulasi soal CAT sesuai kisi-kisi formasi ' + cat.name }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-sm font-semibold text-indigo-600">
                        <span>Lihat Paket Tryout</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </div>
                </Link>
            </div>

            <!-- Jika Belum Ada Formasi Aktif di Admin -->
            <div v-else class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <p class="text-gray-500 font-medium">
                    Belum ada formasi SKB yang tersedia atau sesuai dengan pencarian Anda.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>