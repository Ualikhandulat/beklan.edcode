import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-wysiwyg]')) {
        import('./wysiwyg.js').then(({ initWysiwyg }) => initWysiwyg());
    }
});
