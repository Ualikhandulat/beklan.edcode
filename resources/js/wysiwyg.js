import Quill from 'quill';
import katex from 'katex';

window.katex = katex;

const TOOLBAR = [
    [{ header: [1, 2, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    ['formula'],
    ['image'],
    ['clean'],
];

/**
 * Convert all [data-wysiwyg] elements to Quill editors.
 * Each element must have a corresponding hidden <input data-wysiwyg-target="name">.
 */
export function initWysiwyg() {
    document.querySelectorAll('[data-wysiwyg]').forEach((el) => {
        if (el._quillInit) return;
        el._quillInit = true;

        const targetName = el.dataset.wysiwyg;
        const form       = el.closest('form');
        const hidden     = form?.querySelector(`[data-wysiwyg-target="${targetName}"]`);

        const wrapper = document.createElement('div');
        wrapper.className = 'wysiwyg-wrapper mb-4';

        const container = document.createElement('div');
        wrapper.appendChild(container);
        el.parentNode.insertBefore(wrapper, el);
        el.remove();

        const quill = new Quill(container, {
            theme: 'snow',
            modules: { toolbar: TOOLBAR },
            placeholder: el.dataset.placeholder || 'Введите текст...',
        });

        const initial = hidden?.value || '';
        if (initial) {
            quill.root.innerHTML = initial;
        }

        quill.on('text-change', () => {
            if (hidden) {
                hidden.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            }
        });

        // Image handler — upload to /admin/uploads/image
        const toolbar = quill.getModule('toolbar');
        toolbar.addHandler('image', () => {
            const input = document.createElement('input');
            input.type    = 'file';
            input.accept  = 'image/*';
            input.onchange = async () => {
                const file = input.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

                const res  = await fetch('/admin/uploads/image', { method: 'POST', body: formData });
                const json = await res.json();
                if (json.url) {
                    const range = quill.getSelection(true);
                    quill.insertEmbed(range.index, 'image', json.url);
                }
            };
            input.click();
        });
    });
}
