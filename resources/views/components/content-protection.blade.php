{{--
    Защита контента теста от копирования: блокирует выделение, копирование,
    контекстное меню, печать, горячие клавиши (Ctrl+C/X/S/P/U/A, F12, DevTools)
    и размывает страницу при потере фокуса окном (сторонние инструменты
    скриншотов забирают фокус). Полностью запретить скриншот браузер не может —
    это максимально возможный уровень защиты на клиенте.
--}}
<style>
    @media print {
        body { display: none !important; }
    }

    body {
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
    }

    input, textarea {
        -webkit-user-select: text;
        user-select: text;
    }

    img {
        -webkit-user-drag: none;
        pointer-events: none;
    }

    body.security-blur > * {
        filter: blur(14px);
        pointer-events: none;
    }
</style>
<script>
    (() => {
        const isEditable = (el) => el instanceof Element && el.closest('input, textarea, [contenteditable]');

        ['contextmenu', 'copy', 'cut', 'dragstart', 'selectstart'].forEach((type) => {
            document.addEventListener(type, (e) => {
                if (type !== 'contextmenu' && isEditable(e.target)) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
            }, true);
        });

        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            const combo = e.ctrlKey || e.metaKey;
            const editableKeys = ['c', 'x', 'a'];

            const blocked =
                key === 'f12'
                || key === 'printscreen'
                || (combo && e.shiftKey && ['i', 'j', 'c', 's'].includes(key))
                || (combo && ['s', 'p', 'u'].includes(key))
                || (combo && editableKeys.includes(key) && ! isEditable(e.target));

            if (blocked) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        document.addEventListener('keyup', (e) => {
            if (e.key === 'PrintScreen' && navigator.clipboard) {
                navigator.clipboard.writeText('').catch(() => {});
            }
        }, true);

        window.addEventListener('blur', () => document.body.classList.add('security-blur'));
        window.addEventListener('focus', () => document.body.classList.remove('security-blur'));
        document.addEventListener('visibilitychange', () => {
            document.body.classList.toggle('security-blur', document.hidden);
        });
    })();
</script>
