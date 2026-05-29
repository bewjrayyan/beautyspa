import initWysiwyg from '@admin/js/wysiwyg';

const wysiwyg = initWysiwyg();

initPageContentEditor(wysiwyg);
initPageSeoSidebar();

function initPageContentEditor(wysiwygApi) {
    const shell = document.querySelector('[data-page-content-editor]');

    if (!shell) {
        return;
    }

    const visualBtn = shell.querySelector('[data-editor-mode="visual"]');
    const codeBtn = shell.querySelector('[data-editor-mode="code"]');

    const waitForEditor = window.setInterval(() => {
        const editor = wysiwygApi.get('body');

        if (!editor) {
            return;
        }

        window.clearInterval(waitForEditor);

        const setMode = (mode) => {
            const isCode = mode === 'code';

            shell.classList.toggle('page-editor-content--code', isCode);
            visualBtn?.classList.toggle('page-editor-content__mode--active', !isCode);
            codeBtn?.classList.toggle('page-editor-content__mode--active', isCode);
            visualBtn?.setAttribute('aria-selected', isCode ? 'false' : 'true');
            codeBtn?.setAttribute('aria-selected', isCode ? 'true' : 'false');
        };

        if (!editor.hasSourceEditing()) {
            if (codeBtn) {
                codeBtn.setAttribute('hidden', 'hidden');
                codeBtn.disabled = true;
            }

            return;
        }

        visualBtn?.addEventListener('click', () => {
            if (editor.isSourceMode()) {
                editor.toggleSource();
            }

            setMode('visual');
            editor.focus();
        });

        codeBtn?.addEventListener('click', () => {
            if (!editor.isSourceMode()) {
                editor.toggleSource();
            }

            setMode('code');
            editor.focus();
        });

        const sourceEditingPlugin =
            editor.getCkeditor()?.plugins.get('SourceEditing');

        sourceEditingPlugin?.on('change:isSourceEditingMode', () => {
            setMode(editor.isSourceMode() ? 'code' : 'visual');
        });

        setMode(editor.isSourceMode() ? 'code' : 'visual');
    }, 50);

    window.setTimeout(() => window.clearInterval(waitForEditor), 15000);
}

function initPageSeoSidebar() {
    const titleInput = document.getElementById('meta-title');
    const descriptionInput = document.getElementById('meta-description');
    const nameInput = document.querySelector('[name="name"]');
    const previewTitle = document.getElementById('page-seo-preview-title');
    const previewDescription = document.getElementById('page-seo-preview-description');
    const previewImage = document.getElementById('page-seo-preview-image');
    const ogImagePicker = document.querySelector('.page-seo-field--og');

    const limits = {
        'meta-title': { max: 70, recommended: 60, el: titleInput, counter: document.getElementById('meta-title-count') },
        'meta-description': { max: 320, recommended: 160, el: descriptionInput, counter: document.getElementById('meta-description-count') },
    };

    function getOgImageSrc() {
        const pickerImg = ogImagePicker?.querySelector('.image-holder img');

        if (pickerImg?.src && !pickerImg.src.endsWith('#')) {
            return pickerImg.src;
        }

        return null;
    }

    function updatePreviewImage() {
        if (!previewImage) {
            return;
        }

        const src = getOgImageSrc();

        if (src) {
            previewImage.style.backgroundImage = `url("${src}")`;
            previewImage.classList.add('page-seo-preview__image--filled');
        } else {
            previewImage.style.backgroundImage = '';
            previewImage.classList.remove('page-seo-preview__image--filled');
        }
    }

    function updateCounter(key) {
        const cfg = limits[key];

        if (!cfg?.el || !cfg.counter) {
            return;
        }

        const len = cfg.el.value.length;
        const status = len === 0 ? 'empty' : len <= cfg.recommended ? 'good' : len <= cfg.max ? 'warn' : 'bad';

        cfg.counter.textContent = `${len} / ${cfg.recommended}`;
        cfg.counter.dataset.status = status;
    }

    function updateSocialPreview() {
        if (previewTitle) {
            previewTitle.textContent = titleInput?.value?.trim() || nameInput?.value?.trim() || '…';
        }

        if (previewDescription) {
            previewDescription.textContent = descriptionInput?.value?.trim() || '…';
        }

        updatePreviewImage();
    }

    Object.keys(limits).forEach((key) => {
        const cfg = limits[key];

        if (cfg.el) {
            cfg.el.addEventListener('input', () => {
                updateCounter(key);
                updateSocialPreview();
            });
            updateCounter(key);
        }
    });

    if (nameInput) {
        nameInput.addEventListener('input', updateSocialPreview);
    }

    if (ogImagePicker) {
        const observer = new MutationObserver(() => {
            updatePreviewImage();
        });

        observer.observe(ogImagePicker, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });

        ogImagePicker.addEventListener('click', () => {
            window.setTimeout(updatePreviewImage, 300);
        });
    }

    updateSocialPreview();
}
