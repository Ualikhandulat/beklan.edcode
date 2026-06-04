import Alpine from 'alpinejs';
import { initWysiwyg } from './wysiwyg.js';
import 'quill/dist/quill.snow.css';
import 'katex/dist/katex.min.css';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', initWysiwyg);
