// Editor WYSIWYG (TipTap) untuk <x-admin.form-editor>. Dimuat terpisah (dynamic import di
// admin.js) hanya di halaman yang memiliki [data-editor], agar halaman lain tetap ringan.
import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Swal from 'sweetalert2/dist/sweetalert2.esm.js';

// Sama dengan App\Support\PerataanTeksSanitizer::PERATAAN; rata kiri = bawaan, tidak disimpan.
const PERATAAN = ['center', 'right', 'justify'];

// Perataan teks per paragraf (termasuk paragraf di dalam butir daftar), disimpan sebagai
// style="text-align: …" agar tampil sama di frontend tanpa kelas CSS khusus.
const RataTeks = Extension.create({
    name: 'rataTeks',

    addGlobalAttributes() {
        return [{
            types: ['paragraph'],
            attributes: {
                rata: {
                    default: null,
                    parseHTML: (el) => (PERATAAN.includes(el.style.textAlign) ? el.style.textAlign : null),
                    renderHTML: ({ rata }) => (rata ? { style: `text-align: ${rata}` } : {}),
                },
            },
        }];
    },

    addCommands() {
        return {
            aturRata: (rata) => ({ commands }) => commands.updateAttributes('paragraph', {
                rata: PERATAAN.includes(rata) ? rata : null,
            }),
        };
    },
});

// Hanya format yang tersedia di toolbar; sisanya dimatikan agar teks tempelan (Word, web)
// dinormalkan ke format yang sama dengan yang diizinkan HtmlSanitizerService.
const ekstensi = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
        codeBlock: false,
        code: false,
        horizontalRule: false,
        strike: false,
        link: {
            openOnClick: false,
            autolink: true,
            defaultProtocol: 'https',
            protocols: ['http', 'https', 'mailto'],
            // Target & rel diatur frontend; sanitizer hanya menyimpan href.
            HTMLAttributes: { target: null, rel: null },
        },
    }),
    RataTeks,
];

// Tombol perataan toolbar → nilai atribut rata (null = rata kiri bawaan).
const tombolRata = { rataKiri: null, rataTengah: 'center', rataKanan: 'right', rataKananKiri: 'justify' };

// Sama dengan App\Support\TeksHtml::polos(): teks terlihat, antarblok satu spasi.
const panjangTeks = (editor) => editor.getText({ blockSeparator: ' ' }).replace(/\s+/g, ' ').trim().length;

const aturTautan = async (editor) => {
    const { value, isConfirmed, isDenied } = await Swal.fire({
        titleText: 'Tautan',
        input: 'text',
        inputLabel: 'Alamat tautan',
        inputValue: editor.getAttributes('link').href ?? '',
        inputPlaceholder: 'https://… , /halaman, atau #bagian',
        showCancelButton: true,
        showDenyButton: editor.isActive('link'),
        confirmButtonText: 'Simpan',
        denyButtonText: 'Hapus tautan',
        cancelButtonText: 'Batal',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-success',
            denyButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary',
        },
    });

    const rantai = editor.chain().focus().extendMarkRange('link');

    if (isDenied || (isConfirmed && value.trim() === '')) {
        rantai.unsetLink().run();
    } else if (isConfirmed) {
        rantai.setLink({ href: value.trim() }).run();
    }
};

const perintah = {
    bold: (editor) => editor.chain().focus().toggleBold().run(),
    italic: (editor) => editor.chain().focus().toggleItalic().run(),
    underline: (editor) => editor.chain().focus().toggleUnderline().run(),
    bulletList: (editor) => editor.chain().focus().toggleBulletList().run(),
    orderedList: (editor) => editor.chain().focus().toggleOrderedList().run(),
    ...Object.fromEntries(Object.entries(tombolRata).map(([nama, rata]) => [
        nama, (editor) => editor.chain().focus().aturRata(rata).run(),
    ])),
    link: aturTautan,
    hapusFormat: (editor) => editor.chain().focus().unsetAllMarks().clearNodes().aturRata(null).run(),
    undo: (editor) => editor.chain().focus().undo().run(),
    redo: (editor) => editor.chain().focus().redo().run(),
};

const pasangSatu = (wadah) => {
    const sumber = wadah.querySelector('[data-editor-sumber]');
    const isi = wadah.querySelector('[data-editor-isi]');
    const toolbar = wadah.querySelector('.editor-toolbar');
    const tombol = [...toolbar.querySelectorAll('[data-perintah]')];
    const hitung = wadah.closest('.form-group')?.querySelector('[data-editor-hitung]');

    const segarkan = (editor) => {
        tombol.forEach((el) => {
            const nama = el.dataset.perintah;
            if (nama === 'undo' || nama === 'redo') {
                el.disabled = !editor.can()[nama]();
                return;
            }
            const aktif = nama in tombolRata
                ? (editor.getAttributes('paragraph').rata ?? null) === tombolRata[nama]
                : nama !== 'hapusFormat' && editor.isActive(nama);
            el.classList.toggle('aktif', aktif);
            el.setAttribute('aria-pressed', String(aktif));
        });

        if (hitung) {
            hitung.textContent = panjangTeks(editor).toLocaleString('id-ID');
        }
    };

    const editor = new Editor({
        element: isi,
        extensions: ekstensi,
        content: sumber.value,
        editorProps: {
            attributes: {
                class: 'editor-konten konten-html',
                role: 'textbox',
                'aria-multiline': 'true',
                ...(sumber.dataset.label ? { 'aria-labelledby': sumber.dataset.label } : {}),
            },
        },
        onCreate: ({ editor }) => segarkan(editor),
        onTransaction: ({ editor }) => segarkan(editor),
        onUpdate: ({ editor }) => {
            // Editor kosong dikirim sebagai string kosong agar validasi "wajib diisi" bekerja.
            sumber.value = editor.isEmpty ? '' : editor.getHTML();
        },
    });

    tombol.forEach((el) => {
        // Fokus & seleksi tetap di editor saat tombol ditekan, agar format kena teks yang dipilih.
        el.addEventListener('mousedown', (event) => event.preventDefault());
        el.addEventListener('click', () => perintah[el.dataset.perintah]?.(editor));
    });

    // Label form memfokuskan editor, bukan textarea yang disembunyikan.
    document.querySelector(`label[for="${sumber.id}"]`)?.addEventListener('click', (event) => {
        event.preventDefault();
        editor.commands.focus();
    });

    sumber.hidden = true;
    toolbar.hidden = false;
    hitung?.parentElement.removeAttribute('hidden');
    wadah.classList.add('editor-siap');
};

export function pasangEditor(akar = document) {
    akar.querySelectorAll('[data-editor]').forEach(pasangSatu);
}
