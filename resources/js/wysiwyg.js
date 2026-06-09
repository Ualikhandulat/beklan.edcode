import {
    ClassicEditor,
    Autoformat,
    Bold,
    Italic,
    Underline,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageResizeEditing,
    ImageResizeHandles,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    FileRepository,
    List,
    Paragraph,
    SpecialCharacters,
    SpecialCharactersMathematical,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const PLUGINS = [
    Essentials, Autoformat,
    Bold, Italic, Underline,
    Heading, List, Paragraph,
    Image, ImageCaption, ImageResizeEditing, ImageResizeHandles, ImageStyle, ImageToolbar, ImageUpload, FileRepository,
    SpecialCharacters, SpecialCharactersMathematical,
];

const TOOLBAR = [
    'heading', '|',
    'bold', 'italic', 'underline', '|',
    'bulletedList', 'numberedList', '|',
    'specialCharacters', '|',
    'uploadImage', '|',
    'undo', 'redo',
];

function uploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => ({
        upload: () =>
            loader.file.then((file) => {
                const data = new FormData();
                data.append('image', file);
                data.append('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
                return fetch('/admin/uploads/image', { method: 'POST', body: data })
                    .then((r) => r.json())
                    .then((d) => ({ default: d.url }));
            }),
        abort: () => {},
    });
}

export function initWysiwyg() {
    document.querySelectorAll('[data-wysiwyg]').forEach(async (el) => {
        if (el._ckInit) return;
        el._ckInit = true;

        const plain      = 'wysiwygPlain' in el.dataset;
        const targetName = el.dataset.wysiwyg;
        const form       = el.closest('form');
        const hidden     = form?.querySelector(`[data-wysiwyg-target="${targetName}"]`);

        const editor = await ClassicEditor.create(el, {
            licenseKey: 'GPL',
            plugins:    [...PLUGINS, uploadAdapterPlugin],
            toolbar:    plain ? { items: [] } : { items: TOOLBAR },
            image: {
                toolbar: ['imageStyle:inline', 'imageStyle:block', '|', 'imageTextAlternative'],
            },
            placeholder: el.dataset.placeholder ?? 'Введите текст...',
        });

        if (plain) editor.ui.element?.classList.add('ck-editor--plain');
        if ('wysiwygContext' in el.dataset) editor.ui.element?.classList.add('ck-editor--context');

        if (hidden?.value) {
            editor.setData(hidden.value);
        }

        editor.model.document.on('change:data', () => {
            if (hidden) {
                hidden.value = editor.getData();
            }
        });
    });
}
