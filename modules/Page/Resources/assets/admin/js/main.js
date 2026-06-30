import initWysiwyg from '@admin/js/wysiwyg';

const wysiwyg = initWysiwyg();

initPageContentEditor(wysiwyg);
initPageFormSubmit(wysiwyg);
initPageSeoSidebar();
initPageEditorHub();
initPageSidebarPanels();
initPagePermalink();

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

function initPageFormSubmit(wysiwygApi) {
    const form = document.getElementById('page-edit-form') || document.getElementById('page-create-form');

    if (!form) {
        return;
    }

    form.addEventListener('submit', () => {
        wysiwygApi.get('body')?.save();
    });
}

function initPageSeoSidebar() {
    const titleInput = document.getElementById('meta-title');
    const descriptionInput = document.getElementById('meta-description');
    const nameInput = document.getElementById('page-name-input') || document.querySelector('[name="name"]');
    const previewTitle = document.getElementById('page-seo-preview-title');
    const previewDescription = document.getElementById('page-seo-preview-description');
    const previewImage = document.getElementById('page-seo-preview-image');
    const ogImagePicker = document.querySelector('.page-seo-field--og');

    const limits = {
        'meta-title': { max: 70, recommended: 60, el: titleInput, counter: document.getElementById('meta-title-count') },
        'meta-description': { max: 320, recommended: 160, el: descriptionInput, counter: document.getElementById('meta-description-count') },
    };

    function getOgImageSrc() {
        const pickerImg = ogImagePicker?.querySelector('.ac-media-preview__inner.image-holder img, .image-holder img');

        if (pickerImg?.src && !pickerImg.src.endsWith('#') && pickerImg.offsetParent !== null) {
            return pickerImg.src;
        }

        return null;
    }

    function renderPreviewImage(src) {
        if (!previewImage) {
            return;
        }

        const emptyLabel = previewImage.dataset.emptyLabel || 'No image selected';

        if (src) {
            previewImage.classList.add('page-seo-preview__image--filled');
            previewImage.innerHTML = `<img src="${src.replace(/"/g, '&quot;')}" alt="" id="page-seo-preview-image-img">`;

            return;
        }

        previewImage.classList.remove('page-seo-preview__image--filled');
        previewImage.innerHTML = `
            <div class="page-seo-preview__placeholder" id="page-seo-preview-placeholder">
                <i class="fa fa-image" aria-hidden="true"></i>
                <span>${emptyLabel}</span>
            </div>
        `;
    }

    function updatePreviewImage() {
        renderPreviewImage(getOgImageSrc());
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

        observer.observe(ogImagePicker, { childList: true, subtree: true, attributes: true, attributeFilter: ['src', 'class'] });

        ogImagePicker.addEventListener('click', () => {
            window.setTimeout(updatePreviewImage, 300);
        });

        if (window.jQuery) {
            window.jQuery(ogImagePicker).on('ac-media:changed', updatePreviewImage);
        }
    }

    updateSocialPreview();
}

function initPageEditorHub() {
    const nameInput = document.getElementById('page-name-input') || document.querySelector('[name="name"]');
    const hubTitle = document.getElementById('page-editor-hub-title');
    const statusBadge = document.getElementById('page-editor-status-badge');
    const activeCheckbox = document.querySelector('[name="is_active"]');

    if (nameInput && hubTitle) {
        const defaultTitle = hubTitle.dataset.defaultTitle || '';

        nameInput.addEventListener('input', () => {
            hubTitle.textContent = nameInput.value.trim() || defaultTitle;
        });
    }

    if (!statusBadge || !activeCheckbox) {
        return;
    }

    const publishedLabel = statusBadge.dataset.publishedLabel || 'Published';
    const draftLabel = statusBadge.dataset.draftLabel || 'Draft';

    const updateStatus = () => {
        const isPublished = activeCheckbox.checked;

        statusBadge.classList.toggle('page-editor-hub__badge--published', isPublished);
        statusBadge.classList.toggle('page-editor-hub__badge--draft', !isPublished);
        statusBadge.innerHTML = `<i class="fa fa-circle" aria-hidden="true"></i> ${isPublished ? publishedLabel : draftLabel}`;
    };

    activeCheckbox.addEventListener('change', updateStatus);
}

function initPageSidebarPanels() {
    document.querySelectorAll('[data-page-panel]').forEach((panel) => {
        const toggle = panel.querySelector('[data-page-panel-toggle]');

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isCollapsed = panel.classList.toggle('page-editor-panel--collapsed');

            toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
        });
    });
}

function initPagePermalink() {
    const slugInput = document.getElementById('page-slug-input');
    const slugPreview = document.getElementById('page-permalink-slug-preview');
    const previewWrap = document.getElementById('page-permalink-preview');
    const copyBtn = document.getElementById('page-permalink-copy');

    if (slugInput && slugPreview) {
        slugInput.addEventListener('input', () => {
            slugPreview.textContent = slugInput.value.trim() || '…';
        });
    }

    if (!copyBtn || !previewWrap) {
        return;
    }

    const copiedLabel = copyBtn.dataset.copiedLabel || 'Copied';
    const defaultLabel = copyBtn.textContent.trim();

    copyBtn.addEventListener('click', async () => {
        const baseUrl = previewWrap.dataset.baseUrl || '';
        const slug = slugInput?.value?.trim() || slugPreview?.textContent?.trim() || '';

        if (!slug || slug === '…') {
            return;
        }

        const url = `${baseUrl}${slug}`;

        try {
            await navigator.clipboard.writeText(url);
            copyBtn.innerHTML = `<i class="fa fa-check" aria-hidden="true"></i> ${copiedLabel}`;
            window.setTimeout(() => {
                copyBtn.innerHTML = `<i class="fa fa-copy" aria-hidden="true"></i> ${defaultLabel}`;
            }, 2000);
        } catch {
            // Clipboard unavailable — ignore.
        }
    });
}
