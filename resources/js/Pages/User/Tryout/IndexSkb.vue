<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    catalogTryouts: {
        type: Array,
        default: () => []
    },
    myTryouts: {
        type: Array,
        default: () => []
    },
    isPremiumMember: {
        type: Boolean,
        default: false
    }
});

const searchQuery = ref('');
const activeTab = ref('catalog'); 
const isClaiming = ref(null);

// --- 1. SINKRONISASI DATA KATALOG ---
const availableCatalogTryouts = computed(() => {
    const ownedTryoutIds = (props.myTryouts || []).map(t => t.id);
    return (props.catalogTryouts || []).filter(t => !ownedTryoutIds.includes(t.id));
});

// --- 2. FITUR PENCARIAN ---
const filteredTryouts = computed(() => {
    const baseData = activeTab.value === 'catalog' ? availableCatalogTryouts.value : (props.myTryouts || []);
    
    if (!searchQuery.value) return baseData;

    return baseData.filter(t => {
        return t.title?.toLowerCase().includes(searchQuery.value.toLowerCase());
    });
});

// --- 3. HELPER & FORMATTER ---
const getAttemptCount = (tryout) => {
    if (tryout.attempts && Array.isArray(tryout.attempts)) {
        return tryout.attempts.length;
    }
    return Number(tryout.attempts_count) || 0;
};

const getStartDate = (tryout) => {
    const dateStr = tryout.start_time || tryout.start_date || tryout.started_at;
    if (!dateStr) return null;
    return dateStr.includes(' ') ? dateStr.replace(' ', 'T') : dateStr;
};

const isUpcoming = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return false;
    const startTimeMs = new Date(startStr).getTime();
    const nowMs = new Date().getTime();
    return startTimeMs > nowMs;
};

const formatTime = (tryout) => {
    const startStr = getStartDate(tryout);
    if (!startStr) return '-';
    const date = new Date(startStr);
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    }).format(date).replace(/\./g, ':').replace(',', ' •') + ' WIB';
};

const formatOnlyDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric'
    }).format(date);
};

// --- 4. AKSI KLAIM ---
const claimTryout = (tryoutId) => {
    isClaiming.value = tryoutId;
    
    // Perbaikan 1: Gunakan window.route() jika route dari Ziggy belum diimport spesifik
    router.post(window.route('tryout.claim', tryoutId), {}, {
        preserveScroll: true,
        preserveState: true, // Perbaikan 2: Wajib agar tab tidak ke-reset setelah klaim
        onSuccess: () => {
            isClaiming.value = null;
            activeTab.value = 'my_tryouts';
        },
        onError: () => {
            isClaiming.value = null;
        }
    });
};
</script>