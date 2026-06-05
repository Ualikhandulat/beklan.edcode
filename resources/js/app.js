import Alpine from 'alpinejs';
import { initWysiwyg } from './wysiwyg.js';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', initWysiwyg);
