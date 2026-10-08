// Editor WYSIWYG (TipTap) untuk <x-admin.form-editor>. Dimuat terpisah (dynamic import di
// admin.js) hanya di halaman yang memiliki [data-editor], agar halaman lain tetap ringan.
import { Editor, Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Swal from 'sweetalert2/dist/sweetalert2.esm.js';

// Sama dengan App\Support\PerataanTeksSanitizer::PERATAAN; rata kiri = bawaan, tidak disimpan.
const PERATAAN = ['center', 'right', 'justify'];

// Perataan teks per paragraf (termasuk paragraf di dalam butir daftar) dan sub-judul, disimpan
// sebagai style="text-align: …" agar tampil sama di frontend tanpa kelas CSS khusus.
const BLOK_RATA = ['paragraph', 'heading'];

const RataTeks = Extension.create({
    name: 'rataTeks',

    addGlobalAttributes() {
        return [{
            types: BLOK_RATA,
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
            // Sub-judul hanya ada di editor isi artikel, jadi jenis blok yang tidak ada di skema dilewati.
            aturRata: (rata) => ({ commands }) => BLOK_RATA
                .filter((jenis) => this.editor.schema.nodes[jenis])
                .map((jenis) => commands.updateAttributes(jenis, { rata: PERATAAN.includes(rata) ? rata : null }))
                .some(Boolean),
        };
    },
});

// Hanya format yang tersedia di toolbar; sisanya dimatikan agar teks tempelan (Word, web)
// dinormalkan ke format yang sama dengan yang diizinkan HtmlSanitizerService. Sub-judul
// H2/H3 hanya untuk editor ber-data-judul-bagian (isi artikel); h1 & h4–h6 tempelan menjadi paragraf.
const buatEkstensi = (judulBagian) => [
    StarterKit.configure({
        heading: judulBagian ? { levels: [2, 3] } : false,
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

// Status aktif tombol selain format tanda (tebal, daftar, dst. memakai editor.isActive(nama)).
const statusAktif = {
    judul2: (editor) => editor.isActive('heading', { level: 2 }),
    judul3: (editor) => editor.isActive('heading', { level: 3 }),
    ...Object.fromEntries(Object.entries(tombolRata).map(([nama, rata]) => [
        nama, (editor) => (editor.state.selection.$from.parent.attrs.rata ?? null) === rata,
    ])),
    hapusFormat: () => false,
};

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
    judul2: (editor) => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    judul3: (editor) => editor.chain().focus().toggleHeading({ level: 3 }).run(),
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
            const aktif = statusAktif[nama] ? statusAktif[nama](editor) : editor.isActive(nama);
            el.classList.toggle('aktif', aktif);
            el.setAttribute('aria-pressed', String(aktif));
        });

        if (hitung) {
            hitung.textContent = panjangTeks(editor).toLocaleString('id-ID');
        }
    };

    const editor = new Editor({
        element: isi,
        extensions: buatEkstensi(wadah.hasAttribute('data-judul-bagian')),
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
