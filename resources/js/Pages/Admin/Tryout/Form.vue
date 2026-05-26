<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    tryout: Object, 
});

// Inisialisasi form dengan seluruh field yang dibutuhkan
const form = useForm({
    title: props.tryout?.title ?? '',
    description: props.tryout?.description ?? '',
    duration: props.tryout?.duration ?? 110,
    is_paid: props.tryout?.is_paid ?? false,
    price: props.tryout?.price ?? 0,
    
    // Field waktu (Pastikan menerima string langsung dari backend, bukan new Date())
    started_at: props.tryout?.started_at ?? '', 
    end_date: props.tryout?.end_date ?? '', 
    
    // Status Publish
    is_published: props.tryout?.is_published ? true : false, 
});

const submit = () => {
    if (props.tryout) {
        form.put(route('admin.tryouts.update', props.tryout.id));
    } else {
        form.post(route('admin.tryouts.store'));
    }
};
</script>

<template>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-[2rem] border border-slate-100 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6">
            
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Nama Paket</label>
                <input 
                    v-model="form.title" 
                    type="text" 
                    placeholder="Contoh: Tryout SKD Nasional 2026"
                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all"
                >
                <div v-if="form.errors.title" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.title }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Durasi (Menit)</label>
                    <input 
                        v-model="form.duration" 
                        type="number" 
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                    <div v-if="form.errors.duration" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.duration }}</div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Tipe Akses</label>
                    <div class="flex items-center gap-4 h-[52px]">
                        <button 
                            type="button"
                            @click="form.is_paid = !form.is_paid"
                            :class="form.is_paid ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-400'"
                            class="flex-1 h-full rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all"
                        >
                            {{ form.is_paid ? 'Berbayar' : 'Gratis' }}
                        </button>
                    </div>
                </div>
            </div>

            <Transition name="slide-fade">
                <div v-if="form.is_paid">
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Harga Jual (IDR)</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 font-black text-slate-300 text-sm">Rp</span>
                        <input 
                            v-model="form.price" 
                            type="number" 
                            placeholder="50000"
                            class="w-full pl-12 pr-5 py-4 bg-indigo-50/30 border-none rounded-2xl text-sm font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                        >
                    </div>
                    <div v-if="form.errors.price" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.price }}</div>
                </div>
            </Transition>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Waktu Mulai Tryout</label>
                    <input 
                        v-model="form.started_at" 
                        type="datetime-local" 
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                    <p class="text-[10px] text-slate-400 mt-2 font-bold italic">*Kosongkan jika bisa dikerjakan kapan saja.</p>
                    <div v-if="form.errors.started_at" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.started_at }}</div>
                </div>

                <div>
                    <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Batas Akhir Pendaftaran</label>
                    <input 
                        v-model="form.end_date" 
                        type="datetime-local" 
                        class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-indigo-500/20 transition-all"
                    >
                    <p class="text-[10px] text-slate-400 mt-2 font-bold italic">*Kosongkan jika pendaftaran dibuka selamanya.</p>
                    <div v-if="form.errors.end_date" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.end_date }}</div>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Keterangan Paket</label>
                <textarea 
                    v-model="form.description" 
                    rows="4"
                    class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"
                    placeholder="Tuliskan detail atau peraturan paket di sini..."
                ></textarea>
                <div v-if="form.errors.description" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.description }}</div>
            </div>

            <div class="flex items-center gap-3 py-2 bg-slate-50 p-4 rounded-2xl">
                <input 
                    type="checkbox" 
                    id="is_published"
                    v-model="form.is_published" 
                    class="w-5 h-5 text-slate-900 rounded-md border-slate-300 focus:ring-indigo-500 cursor-pointer"
                >
                <label for="is_published" class="text-[11px] font-black text-slate-600 cursor-pointer uppercase tracking-wider">
                    Tampilkan Tryout di Katalog Peserta (Publish)
                </label>
            </div>
            <div v-if="form.errors.is_published" class="text-rose-500 text-[10px] mt-1 font-bold uppercase">{{ form.errors.is_published }}</div>

            <div class="pt-4 border-t border-slate-50 flex justify-end gap-3">
                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-xl shadow-slate-100 disabled:opacity-50"
                >
                    {{ props.tryout ? 'Simpan Perubahan' : 'Buat Paket Baru' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.slide-fade-enter-active { transition: all 0.3s ease-out; }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1); }
.slide-fade-enter-from, .slide-fade-leave-to { transform: translateY(-10px); opacity: 0; }
</style>