<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'
import axios from 'axios'

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Tulis pertanyaan di sini... Anda juga bisa klik tombol Sisipkan Gambar atau langsung Paste (Ctrl+V) gambar dari Word.',
    },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const isUploading = ref(false)
const uploadError = ref('')

// Ekstensi Image kustom agar mendukung pengaturan lebar & perataan seperti di Microsoft Word
const WordLikeImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: '60%',
                parseHTML: (el) => el.style.width || el.getAttribute('data-width') || '60%',
            },
            align: {
                default: 'center',
                parseHTML: (el) => el.getAttribute('data-align') || 'center',
            },
        }
    },
    renderHTML({ HTMLAttributes }) {
        const { width, align, ...rest } = HTMLAttributes
        let marginStyle = 'margin-left: 0; margin-right: auto;'
        if (align === 'center') {
            marginStyle = 'margin-left: auto; margin-right: auto;'
        } else if (align === 'right') {
            marginStyle = 'margin-left: auto; margin-right: 0;'
        }

        return [
            'img',
            {
                ...rest,
                'data-width': width,
                'data-align': align,
                style: `display: block; max-width: 100%; width: ${width}; height: auto; border-radius: 0.5rem; margin-top: 0.75rem; margin-bottom: 0.75rem; ${marginStyle}`,
            },
        ]
    },
})

const uploadAndInsertImage = async (file) => {
    if (!file || !file.type.startsWith('image/')) return

    uploadError.value = ''
    isUploading.value = true

    const formData = new FormData()
    formData.append('image', file)

    try {
        const response = await axios.post(route('admin.questions.upload-image'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })

        if (response.data?.url && editor.value) {
            editor.value
                .chain()
                .focus()
                .setImage({
                    src: response.data.url,
                    width: '60%',
                    align: 'center',
                })
                .run()
        }
    } catch (err) {
        uploadError.value =
            err.response?.data?.message || 'Gagal mengunggah gambar. Pastikan ukuran maksimal 5MB (JPG/PNG/WEBP).'
    } finally {
        isUploading.value = false
    }
}

const editor = useEditor({
    content: props.modelValue || '',
    extensions: [
        StarterKit,
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        WordLikeImage,
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose max-w-none min-h-[180px] p-4 focus:outline-none text-gray-800',
        },
        // Mendukung Ctrl+V langsung gambar dari Word / Screenshot
        handlePaste(view, event) {
            const items = event.clipboardData?.items
            if (!items) return false

            for (const item of items) {
                if (item.type.startsWith('image/')) {
                    event.preventDefault()
                    const file = item.getAsFile()
                    if (file) uploadAndInsertImage(file)
                    return true
                }
            }
            return false
        },
        // Mendukung Drag & Drop file gambar ke dalam kolom soal
        handleDrop(view, event, _slice, moved) {
            if (moved) return false
            const files = event.dataTransfer?.files
            if (!files || files.length === 0) return false

            const imageFile = Array.from(files).find((f) => f.type.startsWith('image/'))
            if (imageFile) {
                event.preventDefault()
                uploadAndInsertImage(imageFile)
                return true
            }
            return false
        },
    },
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getHTML())
    },
})

// Sinkronisasi saat form dibuka untuk Tambah Soal (reset) atau Edit Soal
watch(
    () => props.modelValue,
    (newVal) => {
        if (!editor.value) return
        const currentHTML = editor.value.getHTML()
        if ((newVal || '') !== currentHTML) {
            editor.value.commands.setContent(newVal || '', false)
        }
    }
)

const triggerFilePicker = () => {
    fileInput.value?.click()
}

const onFileChange = (event) => {
    const file = event.target.files?.[0]
    if (file) {
        uploadAndInsertImage(file)
    }
    event.target.value = ''
}

const setImageWidth = (width) => {
    editor.value?.chain().focus().updateAttributes('image', { width }).run()
}

const setImageAlign = (align) => {
    editor.value?.chain().focus().updateAttributes('image', { align }).run()
}

const deleteSelectedImage = () => {
    editor.value?.chain().focus().deleteSelection().run()
}

onBeforeUnmount(() => {
    editor.value?.destroy()
})
</script>

<template>
    <div class="border border-gray-300 rounded-lg overflow-hidden bg-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
        <!-- Toolbar Ala Microsoft Word -->
        <div v-if="editor" class="flex flex-wrap items-center gap-1 px-2.5 py-2 bg-gray-50 border-b border-gray-200 text-gray-700">
            <!-- Format Teks -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBold().run()"
                :class="['px-2.5 py-1 rounded text-sm font-bold transition', editor.isActive('bold') ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Tebal (Ctrl+B)"
            >
                B
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleItalic().run()"
                :class="['px-2.5 py-1 rounded text-sm italic transition', editor.isActive('italic') ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Miring (Ctrl+I)"
            >
                I
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="['px-2.5 py-1 rounded text-sm underline transition', editor.isActive('underline') ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Garis Bawah (Ctrl+U)"
            >
                U
            </button>

            <span class="h-5 w-px bg-gray-300 mx-1"></span>

            <!-- Daftar / List -->
            <button
                type="button"
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="['px-2 py-1 rounded text-xs font-medium transition', editor.isActive('bulletList') ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Bullet List"
            >
                • List
            </button>
            <button
                type="button"
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="['px-2 py-1 rounded text-xs font-medium transition', editor.isActive('orderedList') ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Numbered List"
            >
                1. List
            </button>

            <span class="h-5 w-px bg-gray-300 mx-1"></span>

            <!-- Perataan Paragraf -->
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('left').run()"
                :class="['px-2 py-1 rounded text-xs font-medium transition', editor.isActive({ textAlign: 'left' }) ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Rata Kiri"
            >
                Kiri
            </button>
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('center').run()"
                :class="['px-2 py-1 rounded text-xs font-medium transition', editor.isActive({ textAlign: 'center' }) ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Rata Tengah"
            >
                Tengah
            </button>
            <button
                type="button"
                @click="editor.chain().focus().setTextAlign('right').run()"
                :class="['px-2 py-1 rounded text-xs font-medium transition', editor.isActive({ textAlign: 'right' }) ? 'bg-indigo-600 text-white' : 'hover:bg-gray-200']"
                title="Rata Kanan"
            >
                Kanan
            </button>

            <span class="h-5 w-px bg-gray-300 mx-1"></span>

            <!-- Tombol Insert Gambar -->
            <button
                type="button"
                @click="triggerFilePicker"
                :disabled="isUploading"
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>{{ isUploading ? 'Mengunggah...' : 'Sisipkan Gambar' }}</span>
            </button>

            <input
                ref="fileInput"
                type="file"
                accept="image/png,image/jpeg,image/jpg,image/gif,image/webp"
                class="hidden"
                @change="onFileChange"
            />

            <!-- Undo / Redo -->
            <div class="ml-auto flex items-center gap-1">
                <button
                    type="button"
                    @click="editor.chain().focus().undo().run()"
                    :disabled="!editor.can().undo()"
                    class="px-2 py-1 rounded text-xs hover:bg-gray-200 disabled:opacity-40"
                    title="Undo"
                >
                    ↺
                </button>
                <button
                    type="button"
                    @click="editor.chain().focus().redo().run()"
                    :disabled="!editor.can().redo()"
                    class="px-2 py-1 rounded text-xs hover:bg-gray-200 disabled:opacity-40"
                    title="Redo"
                >
                    ↻
                </button>
            </div>
        </div>

        <!-- Toolbar Kontekstual Saat Gambar Diklik (Resize & Align) -->
        <div
            v-if="editor && editor.isActive('image')"
            class="flex flex-wrap items-center gap-2 px-3 py-1.5 bg-indigo-50 border-b border-indigo-200 text-xs text-indigo-900"
        >
            <span class="font-semibold">Ukuran Gambar:</span>
            <button
                v-for="size in ['25%', '50%', '75%', '100%']"
                :key="size"
                type="button"
                @click="setImageWidth(size)"
                :class="[
                    'px-2 py-0.5 rounded border transition',
                    editor.getAttributes('image').width === size
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white border-indigo-200 hover:bg-indigo-100',
                ]"
            >
                {{ size }}
            </button>

            <span class="h-4 w-px bg-indigo-200 mx-1"></span>
            <span class="font-semibold">Posisi:</span>
            <button
                v-for="pos in [
                    { label: 'Kiri', value: 'left' },
                    { label: 'Tengah', value: 'center' },
                    { label: 'Kanan', value: 'right' },
                ]"
                :key="pos.value"
                type="button"
                @click="setImageAlign(pos.value)"
                :class="[
                    'px-2 py-0.5 rounded border transition',
                    editor.getAttributes('image').align === pos.value
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white border-indigo-200 hover:bg-indigo-100',
                ]"
            >
                {{ pos.label }}
            </button>

            <button
                type="button"
                @click="deleteSelectedImage"
                class="ml-auto px-2 py-0.5 rounded bg-red-100 text-red-700 hover:bg-red-200 font-medium"
            >
                Hapus Gambar
            </button>
        </div>

        <!-- Pesan Error Upload -->
        <div v-if="uploadError" class="px-3 py-1.5 bg-red-50 border-b border-red-200 text-xs text-red-600">
            {{ uploadError }}
        </div>

        <!-- Area Ketik & Gambar -->
        <EditorContent :editor="editor" class="cursor-text" />
    </div>
</template>

<style>
/* Placeholder TipTap */
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #9ca3af;
    pointer-events: none;
    height: 0;
}

/* Highlight biru saat gambar diklik/dipilih */
.ProseMirror img.ProseMirror-selectednode {
    outline: 3px solid #4f46e5;
    outline-offset: 2px;
}

.ProseMirror ul {
    list-style-type: disc;
    padding-left: 1.5rem;
}

.ProseMirror ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
}
</style>