const root = document.querySelector('[data-tr-pos="1"]');

if (root) {
    const state = {
        products: [], categories: [{ id: 'all', name: 'All treatments' }], category: 'all', search: '', view: 'grid',
        items: [], customer: null, rewards: null, beauticians: [], branches: [], receiptFile: null,
        wizard: { step: 0, draft: null, editIndex: -1, busy: false, feedback: '', branchDates: [], dates: [], slots: [] },
    };
    const steps = ['Variant', 'Branch', 'Beautician', 'Date', 'Time'];
    const q = (selector) => root.querySelector(selector);
    const apiBase = root.dataset.apiBase || '/api';
    const apiUrl = (path) => `${apiBase.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const money = (value) => `MYR ${Number(value || 0).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    const plusIcon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>';
    const json = async (url, options = {}) => {
        const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {}) }, ...options });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const validationMessage = payload.errors ? Object.values(payload.errors).flat()[0] : null;
            throw new Error(validationMessage || payload.message || 'Unable to complete request.');
        }
        return payload;
    };
    const optionValues = (item) => Object.values(item?.options || {}).flat().map(String);
    const selectedOptionLabels = (item) => (item?.product?.options || []).flatMap((option) => (option.values || []).filter((value) => optionValues(item).includes(String(value.id))).map((value) => value.label));
    const itemPrice = (item) => Number(item?.product?.variants?.find((variant) => String(variant.id) === String(item.variant_id))?.price ?? item?.product?.price ?? 0)
        + (item?.product?.options || []).flatMap((option) => option.values || []).filter((value) => optionValues(item).includes(String(value.id))).reduce((total, value) => total + Number(value.price || 0), 0);
    const variantName = (item) => item?.product?.variants?.find((variant) => String(variant.id) === String(item.variant_id))?.name || 'Standard treatment';
    const branchName = (id) => state.branches.find((item) => String(item.id) === String(id))?.name || 'Not selected';
    const beauticianName = (id) => state.beauticians.find((item) => String(item.id) === String(id))?.name || 'Not selected';
    const localToday = () => {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    };
    const addDays = (date, days) => {
        const value = new Date(`${date}T12:00:00`);
        value.setDate(value.getDate() + days);
        return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`;
    };
    const displayDate = (date) => new Intl.DateTimeFormat('en-MY', { weekday: 'short', day: 'numeric', month: 'short' }).format(new Date(`${date}T12:00:00`));

    function renderCategories() {
        q('[data-pos-categories]').innerHTML = state.categories.map((category) => `<button type="button" class="${String(category.id) === String(state.category) ? 'is-active' : ''}" data-pos-category="${esc(category.id)}" role="tab" aria-selected="${String(category.id) === String(state.category)}">${esc(category.name)}</button>`).join('');
    }

    function renderProducts() {
        const filtered = state.products.filter((product) => {
            const haystack = `${product.name} ${product.category_name || ''}`.toLowerCase();
            return (state.category === 'all' || String(product.category_id) === String(state.category)) && haystack.includes(state.search.toLowerCase());
        });
        q('[data-pos-count]').textContent = `${filtered.length} treatment${filtered.length === 1 ? '' : 's'}`;
        q('[data-pos-products]').classList.toggle('is-list', state.view === 'list');
        q('[data-pos-products]').innerHTML = filtered.length ? filtered.map((product) => `<button type="button" class="tr-pos-product-card ${state.items.some((item) => item.product.id === product.id) ? 'is-selected' : ''}" data-pos-product="${product.id}">
            ${product.image ? `<img class="tr-pos-product-image" src="${esc(product.image)}" alt="" loading="lazy" decoding="async">` : `<span class="tr-pos-product-icon" aria-hidden="true">${esc((product.name || 'T').slice(0, 1).toUpperCase())}</span>`}
            <span class="tr-pos-product-copy"><strong>${esc(product.name)}</strong><small>${esc(product.category_name || 'Treatment')} · ${esc(product.duration_minutes || 60)} min</small></span>
            <span class="tr-pos-product-price">${money(product.price)} <i>${plusIcon}</i></span>
        </button>`).join('') : '<div class="tr-pos-empty-state"><strong>No treatments found</strong><span>Try another search or category.</span></div>';
    }

    function renderOrder() {
        q('[data-pos-item-count]').textContent = `${state.items.length} item${state.items.length === 1 ? '' : 's'}`;
        q('[data-pos-empty-cart]').hidden = state.items.length > 0;
        q('[data-pos-cart-item]').hidden = state.items.length === 0;
        q('[data-pos-cart-item]').innerHTML = state.items.map((line, index) => `<article class="tr-pos-cart-line">
            <div class="tr-pos-cart-line-head"><span class="tr-pos-cart-avatar">${esc((line.product.name || 'T').slice(0, 1).toUpperCase())}</span><span class="tr-pos-cart-copy"><strong>${esc(line.product.name)}</strong><span>${esc(variantName(line))}${selectedOptionLabels(line).length ? ` · ${esc(selectedOptionLabels(line).join(', '))}` : ''} · ${money(itemPrice(line))}</span></span><button type="button" class="tr-pos-remove" data-pos-remove-line="${index}" aria-label="Remove treatment">×</button></div>
            <div class="tr-pos-cart-line-meta"><span>Spa branch<strong>${esc(branchName(line.spa_branch_id))}</strong></span><span>Beautician<strong>${esc(beauticianName(line.beautician_id))}</strong></span><span>Appointment<strong>${esc(displayDate(line.appointment_date))}</strong></span><span>Time<strong>${esc(line.appointment_time)}</strong></span></div>
            <div class="tr-pos-cart-line-actions"><button type="button" data-pos-edit-line="${index}">Edit appointment</button></div>
        </article>`).join('');
        q('[data-pos-total]').textContent = money(state.items.reduce((total, line) => total + itemPrice(line), 0));
        q('[data-pos-submit]').disabled = !(state.items.length && state.customer && state.receiptFile && state.items.every((line) => line.variant_id !== undefined && line.beautician_id && line.spa_branch_id && line.appointment_date && line.appointment_time));
    }

    function renderReceipt() {
        const zone = q('[data-pos-receipt-dropzone]');
        const title = q('[data-pos-receipt-title]');
        const meta = q('[data-pos-receipt-meta]');
        zone.classList.toggle('has-file', Boolean(state.receiptFile));
        title.textContent = state.receiptFile ? state.receiptFile.name : 'Upload payment receipt';
        meta.textContent = state.receiptFile
            ? (state.receiptFile.size / 1024 / 1024).toFixed(2) + ' MB · ready to attach'
            : 'Required · JPG, PNG, WEBP or PDF · maximum 10 MB';
        q('[data-pos-payment-summary]').textContent = state.receiptFile
            ? 'Offline · receipt attached'
            : 'Offline · receipt required';
        renderOrder();
    }

    function acceptReceipt(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        const feedback = q('[data-pos-feedback]');
        if (!file || !allowedTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
            state.receiptFile = null;
            feedback.textContent = 'Receipt must be a JPG, PNG, WEBP or PDF file up to 10 MB.';
            feedback.className = 'tr-pos-feedback is-error';
            renderReceipt();
            return;
        }
        state.receiptFile = file;
        feedback.textContent = '';
        feedback.className = 'tr-pos-feedback';
        renderReceipt();
    }

    function renderSelectedCustomer() {
        const target = q('[data-pos-selected-customer]');
        target.hidden = !state.customer;
        target.innerHTML = state.customer ? `<span class="tr-pos-customer-avatar">${esc((state.customer.name || 'C').slice(0, 1).toUpperCase())}</span><span><strong>${esc(state.customer.name)}</strong><small>${esc(state.customer.phone || state.customer.email || '')}</small></span><button type="button" data-pos-clear-customer>Change</button>` : '';
        q('[data-pos-customer-search]').hidden = Boolean(state.customer);
        const rewards = q('[data-pos-rewards]');
        rewards.hidden = !state.rewards;
        rewards.innerHTML = state.rewards ? `<div class="tr-pos-reward-stat"><strong>${Number(state.rewards.points).toLocaleString()}</strong><span>loyalty points</span></div><div class="tr-pos-reward-stat"><strong>${esc(state.rewards.stamps_active)}</strong><span>active stamps</span></div><div class="tr-pos-reward-meta">${esc(state.rewards.tier || 'Standard member')} · ${esc(state.rewards.stamps_ready)} stamp reward${state.rewards.stamps_ready === 1 ? '' : 's'} ready · Points value ${money(state.rewards.points_value_rm)}</div>` : '';
        q('[data-pos-summary-customer]').innerHTML = state.customer ? `<strong>${esc(state.customer.name)}</strong><span>${esc(state.customer.phone || state.customer.email || '')}</span>` : 'No customer selected.';
        const summaryRewards = q('[data-pos-summary-rewards]');
        summaryRewards.hidden = !state.rewards;
        summaryRewards.textContent = state.rewards ? `${Number(state.rewards.points).toLocaleString()} points · ${state.rewards.stamps_active} active stamps` : '';
    }

    function wizardCanContinue() {
        const { step, draft, branchDates, dates, slots, busy } = state.wizard;
        if (!draft || busy) return false;
        if (step === 0) return (!draft.product.variants?.length || Boolean(draft.variant_id)) && (draft.product.options || []).every((option) => !option.is_required || optionValues(draft).some((id) => (option.values || []).some((value) => String(value.id) === id)));
        if (step === 1) return Boolean(draft.spa_branch_id && branchDates.length);
        if (step === 2) return Boolean(draft.beautician_id && dates.length);
        if (step === 3) return Boolean(draft.appointment_date && slots.length);
        return Boolean(draft.appointment_time);
    }

    function wizardIntro(subtitle) {
        const product = state.wizard.draft.product;
        return `<div class="tr-pos-wizard__intro">${product.image ? `<img src="${esc(product.image)}" alt="">` : `<span class="tr-pos-wizard__thumb"></span>`}<div><h3>${esc(product.name)}</h3><p>${esc(subtitle)}</p></div></div>`;
    }

    function renderProductOptions(options, draft) {
        if (!options.length) return '';
        return `<div class="tr-pos-wizard__options"><h4>Treatment options</h4>${options.map((option) => `<fieldset><legend>${esc(option.name)}${option.is_required ? ' <em>Required</em>' : ''}</legend><div class="tr-pos-wizard__grid">${(option.values || []).map((value) => { const selected = optionValues(draft).includes(String(value.id)); return `<button type="button" class="tr-pos-choice ${selected ? 'is-active' : ''}" data-wizard-option="${value.id}" data-wizard-option-group="${option.id}" data-wizard-option-multiple="${['checkbox', 'checkbox_custom', 'multiple_select'].includes(option.type) ? '1' : '0'}"><span class="tr-pos-choice__icon">${selected ? '✓' : '+'}</span><span class="tr-pos-choice__copy"><strong>${esc(value.label)}</strong><small>${Number(value.price || 0) ? `Add ${money(value.price)}` : 'Included'}</small></span></button>`; }).join('')}</div></fieldset>`).join('')}</div>`;
    }

    function selectProductOption(button) {
        const draft = state.wizard.draft;
        const group = String(button.dataset.wizardOptionGroup);
        const value = String(button.dataset.wizardOption);
        if (button.dataset.wizardOptionMultiple === '1') {
            const current = Array.isArray(draft.options[group]) ? draft.options[group].map(String) : [];
            draft.options[group] = current.includes(value) ? current.filter((id) => id !== value) : [...current, value];
            if (!draft.options[group].length) delete draft.options[group];
            return;
        }
        draft.options[group] = value;
    }

    function renderWizard() {
        const wizard = q('[data-pos-wizard]');
        if (!state.wizard.draft) return;
        const { step, draft, busy, feedback } = state.wizard;
        q('[data-pos-wizard-title]').textContent = state.wizard.editIndex >= 0 ? 'Edit treatment appointment' : 'Add treatment appointment';
        q('[data-pos-wizard-steps]').innerHTML = steps.map((label, index) => `<span class="tr-pos-wizard__step ${index === step ? 'is-active' : ''} ${index < step ? 'is-complete' : ''}"><i>${index < step ? '✓' : index + 1}</i><span>${label}</span></span>`).join('');
        let content = '';
        if (step === 0) {
            const variants = draft.product.variants || [];
            const options = draft.product.options || [];
            content = wizardIntro('Choose the exact treatment configuration before checking availability.') + `<div class="tr-pos-wizard__grid">${variants.length ? variants.map((variant) => `<button type="button" class="tr-pos-choice ${String(draft.variant_id) === String(variant.id) ? 'is-active' : ''}" data-wizard-variant="${variant.id}"><span class="tr-pos-choice__icon">V</span><span class="tr-pos-choice__copy"><strong>${esc(variant.name || variant.uid)}</strong><small>Product variant</small></span><span class="tr-pos-choice__price">${money(variant.price)}</span></button>`).join('') : `<button type="button" class="tr-pos-choice is-active"><span class="tr-pos-choice__icon">✓</span><span class="tr-pos-choice__copy"><strong>Standard treatment</strong><small>No variant selection required</small></span><span class="tr-pos-choice__price">${money(draft.product.price)}</span></button>`}</div>${renderProductOptions(options, draft)}`;
        } else if (step === 1) {
            content = wizardIntro('Select a spa branch. Capacity is checked before beauticians are shown.') + `<div class="tr-pos-wizard__grid">${state.branches.map((branch) => `<button type="button" class="tr-pos-choice ${String(draft.spa_branch_id) === String(branch.id) ? 'is-active' : ''}" data-wizard-branch="${branch.id}" ${busy ? 'disabled' : ''}><span class="tr-pos-choice__icon">${esc((branch.name || 'B').slice(0, 1))}</span><span class="tr-pos-choice__copy"><strong>${esc(branch.name)}</strong><small>${String(draft.spa_branch_id) === String(branch.id) && state.wizard.branchDates.length ? `${state.wizard.branchDates.length} available dates found` : 'Check branch availability'}</small></span></button>`).join('') || '<p class="tr-pos-wizard__empty">No active spa branches are available.</p>'}</div>`;
        } else if (step === 2) {
            const availableBeauticians = state.beauticians.filter((beautician) => (beautician.spa_branch_ids || []).map(String).includes(String(draft.spa_branch_id)));
            content = wizardIntro(`Available team at ${branchName(draft.spa_branch_id)}. Each selection is checked again.`) + `<div class="tr-pos-wizard__grid">${availableBeauticians.map((beautician) => `<button type="button" class="tr-pos-choice ${String(draft.beautician_id) === String(beautician.id) ? 'is-active' : ''}" data-wizard-beautician="${beautician.id}" ${busy ? 'disabled' : ''}><span class="tr-pos-choice__icon" style="background:${esc(beautician.profile_color || '#f0eeff')}22;color:${esc(beautician.profile_color || '#624bd5')}">${esc((beautician.name || 'B').slice(0, 1))}</span><span class="tr-pos-choice__copy"><strong>${esc(beautician.name)}</strong><small>${esc(beautician.job_title || 'Beautician')}${String(draft.beautician_id) === String(beautician.id) && state.wizard.dates.length ? ` · ${state.wizard.dates.length} dates` : ''}</small></span></button>`).join('') || '<p class="tr-pos-wizard__empty">No active beautician is assigned to this branch.</p>'}</div>`;
        } else if (step === 3) {
            content = wizardIntro(`Choose an available appointment date with ${beauticianName(draft.beautician_id)}.`) + `<div class="tr-pos-wizard__dates">${state.wizard.dates.map((date) => { const parts = displayDate(date).split(', '); return `<button type="button" class="tr-pos-date-choice ${draft.appointment_date === date ? 'is-active' : ''}" data-wizard-date="${date}" ${busy ? 'disabled' : ''}><strong>${esc(parts[1] || parts[0])}</strong><span>${esc(parts[0])}</span></button>`; }).join('') || '<p class="tr-pos-wizard__empty">No available dates for this beautician.</p>'}</div>`;
        } else {
            content = wizardIntro(`${displayDate(draft.appointment_date)} · ${branchName(draft.spa_branch_id)} · ${beauticianName(draft.beautician_id)}`) + `<div class="tr-pos-wizard__slots">${state.wizard.slots.map((slot) => `<button type="button" class="tr-pos-time-choice ${draft.appointment_time === slot ? 'is-active' : ''}" data-wizard-time="${esc(slot)}">${esc(slot)}</button>`).join('') || '<p class="tr-pos-wizard__empty">No available time slots for this date.</p>'}</div>`;
        }
        q('[data-pos-wizard-body]').innerHTML = content;
        q('[data-pos-wizard-feedback]').textContent = busy ? 'Checking live availability…' : feedback;
        q('[data-pos-wizard-back]').hidden = step === 0;
        const next = q('[data-pos-wizard-next]');
        next.textContent = step === steps.length - 1 ? (state.wizard.editIndex >= 0 ? 'Update appointment' : 'Add to booking') : 'Continue';
        next.disabled = !wizardCanContinue();
        wizard.hidden = false;
    }

    function openWizard(product, editIndex = -1) {
        const existing = editIndex >= 0 ? state.items[editIndex] : null;
        state.wizard = {
            step: 0, editIndex, busy: false, feedback: '', branchDates: [], dates: [], slots: [],
            draft: existing ? { ...existing, options: { ...(existing.options || {}) } } : { product, variant_id: product.variants?.length === 1 ? product.variants[0].id : null, options: {}, spa_branch_id: '', beautician_id: '', appointment_date: '', appointment_time: '' },
        };
        document.body.classList.add('tr-pos-modal-open');
        renderWizard();
        q('[data-pos-wizard-close]').focus();
    }

    function closeWizard() {
        q('[data-pos-wizard]').hidden = true;
        document.body.classList.remove('tr-pos-modal-open');
        state.wizard.draft = null;
    }

    async function fetchDates(beauticianId = '') {
        const draft = state.wizard.draft;
        const from = localToday();
        const params = new URLSearchParams({ spa_branch_id: draft.spa_branch_id, product_id: draft.product.id, from, to: addDays(from, 60) });
        if (beauticianId) params.set('beautician_id', beauticianId);
        const payload = await json(apiUrl(`/bookings/available-dates?${params.toString()}`));
        return payload.dates || [];
    }

    async function chooseBranch(id) {
        const draft = state.wizard.draft;
        draft.spa_branch_id = id; draft.beautician_id = ''; draft.appointment_date = ''; draft.appointment_time = '';
        state.wizard.branchDates = []; state.wizard.dates = []; state.wizard.slots = []; state.wizard.busy = true; state.wizard.feedback = '';
        renderWizard();
        try {
            state.wizard.branchDates = await fetchDates();
            if (!state.wizard.branchDates.length) state.wizard.feedback = 'This treatment has no available appointment dates at the selected branch.';
        } catch (error) { state.wizard.feedback = error.message; }
        state.wizard.busy = false; renderWizard();
    }

    async function chooseBeautician(id) {
        const draft = state.wizard.draft;
        draft.beautician_id = id; draft.appointment_date = ''; draft.appointment_time = '';
        state.wizard.dates = []; state.wizard.slots = []; state.wizard.busy = true; state.wizard.feedback = '';
        renderWizard();
        try {
            state.wizard.dates = await fetchDates(id);
            if (!state.wizard.dates.length) state.wizard.feedback = 'This beautician has no available dates for the selected treatment and branch.';
        } catch (error) { state.wizard.feedback = error.message; }
        state.wizard.busy = false; renderWizard();
    }

    async function chooseDate(date) {
        const draft = state.wizard.draft;
        draft.appointment_date = date; draft.appointment_time = '';
        state.wizard.slots = []; state.wizard.busy = true; state.wizard.feedback = '';
        renderWizard();
        try {
            const params = new URLSearchParams({ beautician_id: draft.beautician_id, spa_branch_id: draft.spa_branch_id, product_id: draft.product.id, date });
            state.items.forEach((line, index) => {
                if (index === state.wizard.editIndex) return;
                params.set(`holds[${index}][beautician_id]`, line.beautician_id);
                params.set(`holds[${index}][appointment_date]`, line.appointment_date);
                params.set(`holds[${index}][appointment_time]`, String(line.appointment_time).slice(0, 5));
                params.set(`holds[${index}][product_id]`, line.product.id);
                params.set(`holds[${index}][spa_branch_id]`, line.spa_branch_id);
                params.set(`holds[${index}][duration_minutes]`, line.product.duration_minutes || 60);
            });
            const payload = await json(apiUrl(`/bookings/availability?${params.toString()}`));
            state.wizard.slots = (payload.slots || []).map((slot) => typeof slot === 'string' ? slot : (slot.time || slot.value)).filter(Boolean);
            if (!state.wizard.slots.length) state.wizard.feedback = 'No appointment times remain available. Another selected treatment may already occupy this beautician.';
        } catch (error) { state.wizard.feedback = error.message; }
        state.wizard.busy = false; renderWizard();
    }

    function nextWizard() {
        if (!wizardCanContinue()) return;
        if (state.wizard.step < steps.length - 1) { state.wizard.step += 1; state.wizard.feedback = ''; renderWizard(); return; }
        const item = { ...state.wizard.draft };
        if (state.wizard.editIndex >= 0) state.items[state.wizard.editIndex] = item;
        else state.items.push(item);
        closeWizard(); renderProducts(); renderOrder();
    }

    async function submit() {
        const button = q('[data-pos-submit]');
        const feedback = q('[data-pos-feedback]');
        button.disabled = true; button.classList.add('is-loading'); feedback.textContent = 'Saving booking…'; feedback.className = 'tr-pos-feedback';
        try {
            const formData = new FormData();
            if (! state.requestKey) state.requestKey = crypto.randomUUID();
            formData.append('request_key', state.requestKey);
            formData.append('customer_id', String(state.customer.id));
            formData.append('payment_status', 'full_paid');
            formData.append('payment_receipt', state.receiptFile);
            state.items.forEach((line, index) => {
                const prefix = 'items[' + index + ']';
                formData.append(prefix + '[product_id]', String(line.product.id));
                if (line.variant_id) formData.append(prefix + '[variant_id]', String(line.variant_id));
                Object.entries(line.options || {}).forEach(([optionId, value]) => {
                    (Array.isArray(value) ? value : [value]).forEach((selected, optionIndex) => formData.append(`${prefix}[options][${optionId}]${Array.isArray(value) ? `[${optionIndex}]` : ''}`, String(selected)));
                });
                formData.append(prefix + '[beautician_id]', String(line.beautician_id));
                formData.append(prefix + '[spa_branch_id]', String(line.spa_branch_id));
                formData.append(prefix + '[appointment_date]', line.appointment_date);
                formData.append(prefix + '[appointment_time]', line.appointment_time);
                formData.append(prefix + '[schedule_later]', '0');
            });
            const result = await json(apiUrl('/bookings'), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: formData });
            const count = Array.isArray(result.data) ? result.data.length : state.items.length;
            feedback.textContent = `${count} booking${count === 1 ? '' : 's'} saved successfully.`; feedback.classList.add('is-success');
            state.items = []; state.receiptFile = null; state.requestKey = null; q('[data-pos-payment-receipt]').value = ''; renderProducts(); renderReceipt();
        } catch (error) { feedback.textContent = error.message; feedback.classList.add('is-error'); renderOrder(); }
        button.classList.remove('is-loading');
    }

    async function init() {
        const catalogNode = q('[data-pos-catalog]');
        state.products = catalogNode ? JSON.parse(catalogNode.textContent || '[]') : [];
        state.categories = [{ id: 'all', name: 'All treatments' }, ...[...new Map(state.products.filter((item) => item.category_id).map((item) => [item.category_id, { id: item.category_id, name: item.category_name || 'Treatments' }])).values()]];
        renderCategories(); renderProducts(); renderReceipt();
        try {
            const [beauticians, branches] = await Promise.all([json(apiUrl('/beauticians')), json(apiUrl('/spa-branches'))]);
            state.beauticians = beauticians.data || []; state.branches = branches.data || [];
        } catch (error) { q('[data-pos-feedback]').textContent = `Booking options unavailable: ${error.message}`; q('[data-pos-feedback]').classList.add('is-error'); }
    }

    root.addEventListener('click', async (event) => {
        const product = event.target.closest('[data-pos-product]');
        if (product) { const selected = state.products.find((item) => String(item.id) === product.dataset.posProduct); if (selected) openWizard(selected); return; }
        const category = event.target.closest('[data-pos-category]'); if (category) { state.category = category.dataset.posCategory; renderCategories(); renderProducts(); return; }
        const remove = event.target.closest('[data-pos-remove-line]'); if (remove) { state.items.splice(Number(remove.dataset.posRemoveLine), 1); renderProducts(); renderOrder(); return; }
        const edit = event.target.closest('[data-pos-edit-line]'); if (edit) { const index = Number(edit.dataset.posEditLine); if (state.items[index]) openWizard(state.items[index].product, index); return; }
        if (event.target.closest('[data-pos-wizard-close]')) { closeWizard(); return; }
        if (event.target.closest('[data-pos-wizard-back]')) { state.wizard.step = Math.max(0, state.wizard.step - 1); state.wizard.feedback = ''; renderWizard(); return; }
        if (event.target.closest('[data-pos-wizard-next]')) { nextWizard(); return; }
        const variant = event.target.closest('[data-wizard-variant]'); if (variant) { state.wizard.draft.variant_id = variant.dataset.wizardVariant; renderWizard(); return; }
        const option = event.target.closest('[data-wizard-option]'); if (option) { selectProductOption(option); renderWizard(); return; }
        const branch = event.target.closest('[data-wizard-branch]'); if (branch) { await chooseBranch(branch.dataset.wizardBranch); return; }
        const beautician = event.target.closest('[data-wizard-beautician]'); if (beautician) { await chooseBeautician(beautician.dataset.wizardBeautician); return; }
        const date = event.target.closest('[data-wizard-date]'); if (date) { await chooseDate(date.dataset.wizardDate); return; }
        const time = event.target.closest('[data-wizard-time]'); if (time) { state.wizard.draft.appointment_time = time.dataset.wizardTime; renderWizard(); return; }
        if (event.target.closest('[data-pos-clear-customer]')) { state.customer = null; state.rewards = null; renderSelectedCustomer(); renderOrder(); return; }
        if (event.target.closest('[data-pos-reset]')) { state.items = []; state.customer = null; state.rewards = null; state.receiptFile = null; q('[data-pos-payment-receipt]').value = ''; renderProducts(); renderSelectedCustomer(); renderReceipt(); q('[data-pos-feedback]').textContent = ''; return; }
        if (event.target.closest('[data-pos-submit]')) await submit();
        const view = event.target.closest('[data-pos-view]'); if (view) { state.view = view.dataset.posView; root.querySelectorAll('[data-pos-view]').forEach((item) => item.classList.toggle('is-active', item === view)); renderProducts(); }
    });
    q('[data-pos-search]').addEventListener('input', (event) => { state.search = event.target.value; renderProducts(); });
    q('[data-pos-payment-receipt]').addEventListener('change', (event) => acceptReceipt(event.target.files?.[0]));
    const receiptZone = q('[data-pos-receipt-dropzone]');
    ['dragenter', 'dragover'].forEach((name) => receiptZone.addEventListener(name, (event) => { event.preventDefault(); receiptZone.classList.add('is-dragging'); }));
    ['dragleave', 'drop'].forEach((name) => receiptZone.addEventListener(name, (event) => { event.preventDefault(); receiptZone.classList.remove('is-dragging'); }));
    receiptZone.addEventListener('drop', (event) => acceptReceipt(event.dataTransfer?.files?.[0]));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !q('[data-pos-wizard]').hidden) closeWizard(); });

    let customerTimer;
    q('[data-pos-customer-search]').addEventListener('input', (event) => {
        clearTimeout(customerTimer); const value = event.target.value.trim(); const result = q('[data-pos-customer-results]');
        if (value.length < 3) { result.innerHTML = ''; return; }
        customerTimer = setTimeout(async () => { try { const payload = await json(apiUrl(`/customers?q=${encodeURIComponent(value)}`)); result.innerHTML = (payload.data || []).map((item) => `<button type="button" data-pos-customer="${item.id}" data-pos-membership="${esc(item.membership_id || '')}"><strong>${esc(item.name)}</strong><small>${esc(item.phone || item.email || '')}</small></button>`).join('') || '<span class="tr-pos-no-results">No active customer found.</span>'; } catch (error) { result.innerHTML = `<span class="tr-pos-no-results">${esc(error.message)}</span>`; } }, 220);
    });
    const lookupMembership = async () => {
        const input = q('[data-pos-membership-id]'); const feedback = q('[data-pos-membership-feedback]'); const code = input.value.trim();
        if (!code) { feedback.textContent = 'Enter a membership ID first.'; feedback.className = 'tr-pos-membership-feedback is-error'; return; }
        feedback.textContent = 'Checking membership…'; feedback.className = 'tr-pos-membership-feedback';
        try {
            const payload = await json(apiUrl(`/membership-lookup?membership_id=${encodeURIComponent(code)}`)); state.customer = payload.data.customer;
            const activeStamps = payload.data.stamp_cards?.active || [];
            state.rewards = { points: payload.data.points, points_value_rm: payload.data.points_value_rm, tier: payload.data.tier, stamps_ready: payload.data.stamp_cards?.ready_to_redeem || 0, stamps_active: activeStamps.reduce((total, card) => total + Number(card.earned || 0), 0) };
            feedback.textContent = `Membership ${payload.data.membership_id} found.`; feedback.className = 'tr-pos-membership-feedback is-success'; renderSelectedCustomer(); renderOrder();
        } catch (error) { state.customer = null; state.rewards = null; renderSelectedCustomer(); renderOrder(); feedback.textContent = error.message; feedback.className = 'tr-pos-membership-feedback is-error'; }
    };
    q('[data-pos-membership-lookup]').addEventListener('click', lookupMembership);
    q('[data-pos-membership-id]').addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); lookupMembership(); } });
    q('[data-pos-customer-results]').addEventListener('click', async (event) => { const item = event.target.closest('[data-pos-customer]'); if (!item) return; q('[data-pos-customer-results]').innerHTML = ''; if (item.dataset.posMembership) { q('[data-pos-membership-id]').value = item.dataset.posMembership; await lookupMembership(); return; } state.customer = { id: Number(item.dataset.posCustomer), name: item.querySelector('strong').textContent, phone: item.querySelector('small').textContent }; state.rewards = null; renderSelectedCustomer(); renderOrder(); });
    init();
}
