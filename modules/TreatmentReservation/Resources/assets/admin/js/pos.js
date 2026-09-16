const root = document.querySelector('[data-tr-pos="1"]');

if (root) {
    const state = {
        products: [], categories: [{ id: 'all', name: 'All treatments' }], category: 'all', search: '', view: 'grid',
        catalogPage: 1, catalogPageSize: 20,
        items: [], customer: null, rewards: null, beauticians: [], branches: [], receiptFile: null,
        loyalty: { input: '', appliedPoints: 0, appliedDiscount: 0, error: '' },
        coupon: { input: '', applied: null, error: '', busy: false },
        availabilityCache: new Map(), slotCache: new Map(),
        wizard: { step: 0, draft: null, editIndex: -1, busy: false, feedback: '', branchDates: [], dates: [], slots: [], datesAbort: null, tbaAllowed: false, scheduleMode: null },
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
    const serviceTotal = () => state.items.reduce((total, line) => total + itemPrice(line), 0);
    const loyaltyPointValue = () => Number(state.rewards?.point_value_rm || 0.1);
    const loyaltyMaxPoints = () => {
        if (!state.rewards) return 0;
        const balance = Number(state.rewards.points || 0);
        const percent = Number(state.rewards.max_redeem_percent || 30);
        const discountableTotal = Math.max(0, serviceTotal() - Number(state.coupon.applied?.discount || 0));
        const maxByPercent = Math.floor((discountableTotal * (percent / 100)) / Math.max(loyaltyPointValue(), 0.0001));
        return Math.max(0, Math.min(balance, maxByPercent));
    };
    const loyaltyDiscountFor = (points) => Number((Math.max(0, Number(points) || 0) * loyaltyPointValue()).toFixed(2));
    const clearLoyalty = () => { state.loyalty = { input: '', appliedPoints: 0, appliedDiscount: 0, error: '' }; };
    const clearCoupon = () => { state.coupon = { input: '', applied: null, error: '', busy: false }; };
    const applyLoyaltyPoints = (requested) => {
        const points = Math.floor(Number(requested) || 0);
        const max = loyaltyMaxPoints();
        if (!state.customer || !state.rewards) { state.loyalty.error = 'Select a member first.'; return false; }
        if (!state.items.length) { state.loyalty.error = 'Add a treatment before redeeming points.'; return false; }
        if (points <= 0) { state.loyalty.error = 'Enter how many points you would like to use.'; return false; }
        if (points > max) { state.loyalty.error = `Maximum redeemable is ${max.toLocaleString()} pts.`; return false; }
        state.loyalty.appliedPoints = points;
        state.loyalty.appliedDiscount = loyaltyDiscountFor(points);
        state.loyalty.input = String(points);
        state.loyalty.error = '';
        return true;
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
    const dateMonth = (date) => String(date).slice(0, 7);
    const displayMonth = (month) => new Intl.DateTimeFormat('en-MY', { month: 'long', year: 'numeric' }).format(new Date(`${month}-01T12:00:00`));

    function setMobileCheckoutOpen(open) {
        const rail = q('.tr-pos-side-rail');
        const scrim = q('[data-pos-mobile-scrim]');
        const trigger = q('[data-pos-mobile-checkout]');
        if (!rail || !scrim || !trigger) return;
        rail.classList.toggle('is-mobile-open', open);
        scrim.hidden = !open;
        trigger.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('tr-pos-mobile-checkout-open', open);
    }

    function renderCategories() {
        q('[data-pos-categories]').innerHTML = state.categories.map((category) => `<button type="button" class="${String(category.id) === String(state.category) ? 'is-active' : ''}" data-pos-category="${esc(category.id)}" role="tab" aria-selected="${String(category.id) === String(state.category)}">${esc(category.name)}</button>`).join('');
    }

    function filteredProducts() {
        return state.products.filter((product) => {
            const haystack = `${product.name} ${product.category_name || ''}`.toLowerCase();
            return (state.category === 'all' || String(product.category_id) === String(state.category)) && haystack.includes(state.search.toLowerCase());
        });
    }

    function resetCatalogPage() {
        state.catalogPage = 1;
    }

    function catalogPageWindow(totalPages, current) {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const pages = new Set([1, totalPages, current]);
        for (let page = current - 1; page <= current + 1; page += 1) {
            if (page > 1 && page < totalPages) pages.add(page);
        }
        if (current <= 3) {
            pages.add(2);
            pages.add(3);
            pages.add(4);
        }
        if (current >= totalPages - 2) {
            pages.add(totalPages - 1);
            pages.add(totalPages - 2);
            pages.add(totalPages - 3);
        }

        const sorted = [...pages].sort((a, b) => a - b);
        const items = [];
        sorted.forEach((page, index) => {
            if (index > 0 && page - sorted[index - 1] > 1) items.push('…');
            items.push(page);
        });
        return items;
    }

    function renderCatalogPagination(totalItems) {
        const totalPages = Math.max(1, Math.ceil(totalItems / state.catalogPageSize));
        if (state.catalogPage > totalPages) state.catalogPage = totalPages;
        if (totalPages <= 1) return '';

        const pages = catalogPageWindow(totalPages, state.catalogPage).map((page) => {
            if (page === '…') return '<span class="tr-pos-catalog-pager__ellipsis" aria-hidden="true">…</span>';
            const active = page === state.catalogPage ? ' is-active' : '';
            return `<button type="button" class="tr-pos-catalog-pager__page${active}" data-pos-catalog-page="${page}" aria-label="Page ${page}" aria-current="${page === state.catalogPage ? 'page' : 'false'}">${page}</button>`;
        }).join('');

        return `<nav class="tr-pos-catalog-pager" aria-label="Catalog pages">
            <button type="button" class="tr-pos-catalog-pager__nav" data-pos-catalog-page="${state.catalogPage - 1}" ${state.catalogPage <= 1 ? 'disabled' : ''} aria-label="Previous page">‹</button>
            ${pages}
            <button type="button" class="tr-pos-catalog-pager__nav" data-pos-catalog-page="${state.catalogPage + 1}" ${state.catalogPage >= totalPages ? 'disabled' : ''} aria-label="Next page">›</button>
        </nav>`;
    }

    function renderProducts() {
        const filtered = filteredProducts();
        const totalPages = Math.max(1, Math.ceil(filtered.length / state.catalogPageSize));
        if (state.catalogPage > totalPages) state.catalogPage = totalPages;
        if (state.catalogPage < 1) state.catalogPage = 1;

        const start = (state.catalogPage - 1) * state.catalogPageSize;
        const visible = filtered.slice(start, start + state.catalogPageSize);
        q('[data-pos-count]').textContent = `${filtered.length} treatment${filtered.length === 1 ? '' : 's'}`;
        q('[data-pos-products]').classList.toggle('is-list', state.view === 'list');

        if (!filtered.length) {
            q('[data-pos-products]').innerHTML = '<div class="tr-pos-empty-state"><strong>No treatments found</strong><span>Try another search or category.</span></div>';
            return;
        }

        const cards = visible.map((product) => `<button type="button" class="tr-pos-product-card ${state.items.some((item) => item.product.id === product.id) ? 'is-selected' : ''}" data-pos-product="${product.id}">
            ${product.image ? `<img class="tr-pos-product-image" src="${esc(product.image)}" alt="" loading="lazy" decoding="async">` : `<span class="tr-pos-product-icon" aria-hidden="true">${esc((product.name || 'T').slice(0, 1).toUpperCase())}</span>`}
            <span class="tr-pos-product-copy"><strong>${esc(product.name)}</strong><small>${esc(product.category_name || 'Treatment')} · ${esc(product.duration_minutes || 60)} min</small></span>
            <span class="tr-pos-product-price">${money(product.price)} <i>${plusIcon}</i></span>
        </button>`).join('');

        q('[data-pos-products]').innerHTML = cards + renderCatalogPagination(filtered.length);
    }

    function renderOrder() {
        q('[data-pos-item-count]').textContent = `${state.items.length} item${state.items.length === 1 ? '' : 's'}`;
        q('[data-pos-empty-cart]').hidden = state.items.length > 0;
        q('[data-pos-cart-item]').hidden = state.items.length === 0;
        q('[data-pos-cart-item]').innerHTML = state.items.map((line, index) => {
            const catalogProduct = state.products.find((item) => String(item.id) === String(line.product?.id));
            const image = line.product?.image || catalogProduct?.image || '';
            const thumb = image
                ? `<img class="tr-pos-cart-avatar tr-pos-cart-avatar--image" src="${esc(image)}" alt="" loading="lazy" decoding="async">`
                : `<span class="tr-pos-cart-avatar">${esc((line.product.name || 'T').slice(0, 1).toUpperCase())}</span>`;
            return `<article class="tr-pos-cart-line">
            <div class="tr-pos-cart-line-head">${thumb}<span class="tr-pos-cart-copy"><strong>${esc(line.product.name)}</strong><span>${esc(variantName(line))}${selectedOptionLabels(line).length ? ` · ${esc(selectedOptionLabels(line).join(', '))}` : ''} · ${money(itemPrice(line))}</span></span><button type="button" class="tr-pos-remove" data-pos-remove-line="${index}" aria-label="Remove treatment">×</button></div>
            <div class="tr-pos-cart-line-meta"><span>Spa branch<strong>${esc(branchName(line.spa_branch_id))}</strong></span><span>Beautician<strong>${esc(beauticianName(line.beautician_id))}</strong></span><span>Appointment<strong>${line.schedule_later ? 'TBA · schedule later' : esc(displayDate(line.appointment_date))}</strong></span><span>Time<strong>${line.schedule_later ? '—' : esc(line.appointment_time)}</strong></span></div>
            <div class="tr-pos-cart-line-actions"><button type="button" data-pos-edit-line="${index}">Edit appointment</button></div>
        </article>`;
        }).join('');
        const total = serviceTotal();
        if (state.loyalty.appliedPoints > 0) {
            const max = loyaltyMaxPoints();
            if (max <= 0) clearLoyalty();
            else if (state.loyalty.appliedPoints > max) applyLoyaltyPoints(max);
        }
        const couponDiscount = Number(state.coupon.applied?.discount || 0);
        const payable = Math.max(0, total - couponDiscount - Number(state.loyalty.appliedDiscount || 0));
        q('[data-pos-total]').textContent = money(total);
        q('[data-pos-mobile-count]').textContent = `${state.items.length} item${state.items.length === 1 ? '' : 's'}`;
        q('[data-pos-mobile-total]').textContent = money(payable);
        const couponRow = q('[data-pos-coupon-discount-row]');
        const discountRow = q('[data-pos-loyalty-discount-row]');
        const payableRow = q('[data-pos-payable-row]');
        const hasDiscount = couponDiscount > 0 || Number(state.loyalty.appliedPoints || 0) > 0;
        couponRow.hidden = couponDiscount <= 0;
        discountRow.hidden = Number(state.loyalty.appliedPoints || 0) <= 0;
        payableRow.hidden = !hasDiscount;
        q('[data-pos-coupon-discount]').textContent = `− ${money(couponDiscount)}`;
        q('[data-pos-loyalty-discount]').textContent = `− ${money(state.loyalty.appliedDiscount)}`;
        q('[data-pos-payable]').textContent = money(payable);
        renderLoyaltyRedeem();
        renderCouponRedeem();
        q('[data-pos-submit]').disabled = !(state.items.length && state.customer && state.receiptFile && state.items.every((line) => line.variant_id !== undefined && line.beautician_id && line.spa_branch_id && (line.schedule_later || (line.appointment_date && line.appointment_time))));
    }

    function renderCouponRedeem() {
        const panel = q('[data-pos-coupon-redeem]');
        const hasContext = Boolean(state.customer && state.items.length);
        panel.hidden = !state.customer;
        if (!state.customer) return;
        q('[data-pos-coupon-input]').value = state.coupon.input;
        q('[data-pos-coupon-empty]').hidden = hasContext || Boolean(state.coupon.applied);
        q('[data-pos-coupon-form]').hidden = !hasContext || Boolean(state.coupon.applied);
        q('[data-pos-coupon-applied]').hidden = !state.coupon.applied;
        q('[data-pos-coupon-error]').textContent = state.coupon.error || '';
        if (state.coupon.applied) q('[data-pos-coupon-applied-label]').textContent = `${state.coupon.applied.code} applied (−${money(state.coupon.applied.discount)})`;
    }

    async function applyCoupon() {
        const code = state.coupon.input.trim();
        if (!code) { state.coupon.error = 'Enter a coupon code.'; renderOrder(); return; }
        if (!state.customer || !state.items.length) { state.coupon.error = 'Select a member and treatment first.'; renderOrder(); return; }
        state.coupon.busy = true; state.coupon.error = ''; renderCouponRedeem();
        try {
            const payload = await json(apiUrl('/bookings/coupon-quote'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ coupon_code: code, customer_id: state.customer.id, items: state.items.map((line) => ({ product_id: line.product.id, variant_id: line.variant_id || null, options: line.options || {} })) }),
            });
            if (state.loyalty.appliedPoints > 0 && !payload.loyalty_compatible) clearLoyalty();
            state.coupon.applied = payload;
            state.coupon.input = payload.code;
        } catch (error) { state.coupon.error = error.message; }
        state.coupon.busy = false; renderOrder();
    }

    function renderLoyaltyRedeem() {
        const panel = q('[data-pos-loyalty-redeem]');
        const hasMemberPoints = Boolean(state.customer && state.rewards && Number(state.rewards.points || 0) > 0);
        panel.hidden = !hasMemberPoints;
        if (!hasMemberPoints) return;

        const balance = Number(state.rewards.points || 0);
        const worth = Number(state.rewards.points_value_rm || loyaltyDiscountFor(balance));
        q('[data-pos-loyalty-balance]').textContent = `Available: ${balance.toLocaleString()} pts (≈ RM ${worth.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;

        const form = q('[data-pos-loyalty-form]');
        const applied = q('[data-pos-loyalty-applied]');
        const empty = q('[data-pos-loyalty-empty]');
        const hasItems = state.items.length > 0;
        const hasApplied = Number(state.loyalty.appliedPoints || 0) > 0;

        empty.hidden = hasItems || hasApplied;
        form.hidden = !hasItems || hasApplied;
        applied.hidden = !hasApplied;
        q('[data-pos-loyalty-points]').value = state.loyalty.input;
        q('[data-pos-loyalty-error]').textContent = state.loyalty.error || '';
        if (hasApplied) {
            q('[data-pos-loyalty-applied-label]').textContent = `${Number(state.loyalty.appliedPoints).toLocaleString()} pts applied (−${money(state.loyalty.appliedDiscount)})`;
        }
    }

    let receiptPreviewUrl = null;

    function clearReceiptPreviewUrl() {
        if (receiptPreviewUrl) {
            URL.revokeObjectURL(receiptPreviewUrl);
            receiptPreviewUrl = null;
        }
    }

    function formatReceiptSize(bytes) {
        if (bytes >= 1024 * 1024) {
            return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
        }

        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    function renderReceipt() {
        const zone = q('[data-pos-receipt-dropzone]');
        const empty = q('[data-pos-receipt-empty]');
        const preview = q('[data-pos-receipt-preview]');
        const image = q('[data-pos-receipt-image]');
        const fileIcon = q('[data-pos-receipt-file]');
        const title = q('[data-pos-receipt-title]');
        const meta = q('[data-pos-receipt-meta]');
        const hasFile = Boolean(state.receiptFile);

        zone.classList.toggle('has-file', hasFile);
        empty.hidden = hasFile;
        preview.hidden = !hasFile;

        clearReceiptPreviewUrl();

        if (hasFile) {
            const isImage = state.receiptFile.type.startsWith('image/');
            title.textContent = state.receiptFile.name;
            meta.textContent = `${formatReceiptSize(state.receiptFile.size)} · ready to attach`;
            image.hidden = !isImage;
            fileIcon.hidden = isImage;

            if (isImage) {
                receiptPreviewUrl = URL.createObjectURL(state.receiptFile);
                image.src = receiptPreviewUrl;
                image.alt = state.receiptFile.name;
            } else {
                image.removeAttribute('src');
                image.alt = '';
            }
        } else {
            title.textContent = 'Upload payment receipt';
            meta.textContent = '';
            image.hidden = true;
            fileIcon.hidden = true;
            image.removeAttribute('src');
            image.alt = '';
        }

        q('[data-pos-payment-summary]').textContent = hasFile
            ? 'Offline · receipt attached'
            : 'Offline · receipt required';
        renderOrder();
    }

    function clearReceipt() {
        state.receiptFile = null;
        q('[data-pos-payment-receipt]').value = '';
        q('[data-pos-receipt-dropzone]').classList.remove('has-error');
        clearReceiptPreviewUrl();
        renderReceipt();
    }

    function acceptReceipt(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        const feedback = q('[data-pos-feedback]');
        const zone = q('[data-pos-receipt-dropzone]');

        if (!file || !allowedTypes.includes(file.type) || file.size > 10 * 1024 * 1024) {
            state.receiptFile = null;
            q('[data-pos-payment-receipt]').value = '';
            zone.classList.add('has-error');
            feedback.textContent = 'Receipt must be a JPG, PNG, WEBP or PDF file up to 10 MB.';
            feedback.className = 'tr-pos-feedback is-error';
            renderReceipt();
            return;
        }

        state.receiptFile = file;
        zone.classList.remove('has-error');
        feedback.textContent = '';
        feedback.className = 'tr-pos-feedback';
        renderReceipt();
    }

    function renderSelectedCustomer() {
        const desk = q('[data-pos-customer-desk]');
        const tools = q('[data-pos-customer-tools]');
        const target = q('[data-pos-selected-customer]');
        const status = q('[data-pos-customer-status]');
        const title = q('#tr-pos-customer-title');
        const hasCustomer = Boolean(state.customer);

        desk.classList.toggle('has-customer', hasCustomer);
        tools.hidden = hasCustomer;
        target.hidden = !hasCustomer;

        if (hasCustomer) {
            const membership = state.customer.membership_id || q('[data-pos-membership-id]')?.value?.trim() || '';
            const contact = state.customer.phone || state.customer.email || '';
            title.textContent = 'Member selected';
            status.textContent = 'Ready';
            status.className = 'tr-pos-customer-desk__status is-ready';
            target.innerHTML = `
                <div class="tr-pos-member-card">
                    <span class="tr-pos-customer-avatar" aria-hidden="true">${esc((state.customer.name || 'C').slice(0, 1).toUpperCase())}</span>
                    <div class="tr-pos-member-card__copy">
                        <strong>${esc(state.customer.name)}</strong>
                        ${contact ? `<span class="tr-pos-member-card__contact">${esc(contact)}</span>` : ''}
                        ${membership ? `<span class="tr-pos-member-card__id">ID ${esc(membership)}</span>` : ''}
                    </div>
                    <button type="button" class="tr-pos-member-card__change" data-pos-clear-customer>Change</button>
                </div>`;
        } else {
            title.textContent = 'Find or look up member';
            status.textContent = 'No member yet';
            status.className = 'tr-pos-customer-desk__status';
            target.innerHTML = '';
            q('[data-pos-customer-search]').value = '';
            q('[data-pos-customer-results]').innerHTML = '';
        }

        const rewards = q('[data-pos-rewards]');
        rewards.hidden = !state.rewards;
        rewards.innerHTML = state.rewards ? `
            <div class="tr-pos-rewards__tier">${esc(state.rewards.tier || 'Standard member')}</div>
            <div class="tr-pos-rewards__stats">
                <div class="tr-pos-reward-stat"><strong>${Number(state.rewards.points).toLocaleString()}</strong><span>loyalty points</span></div>
                <div class="tr-pos-reward-stat"><strong>${esc(state.rewards.stamps_active)}</strong><span>active stamps</span></div>
            </div>
            <div class="tr-pos-reward-meta">${esc(state.rewards.stamps_ready)} stamp reward${Number(state.rewards.stamps_ready) === 1 ? '' : 's'} ready · Value ${money(state.rewards.points_value_rm)}</div>` : '';

        q('[data-pos-summary-customer]').innerHTML = hasCustomer
            ? `<strong>${esc(state.customer.name)}</strong><span>${esc(state.customer.phone || state.customer.email || '')}</span>`
            : 'No customer selected.';
        const summaryRewards = q('[data-pos-summary-rewards]');
        summaryRewards.hidden = !state.rewards;
        summaryRewards.textContent = state.rewards
            ? `${Number(state.rewards.points).toLocaleString()} points · ${state.rewards.stamps_active} active stamps`
            : '';
    }

    function wizardConfigurationReady(draft) {
        return (!draft.product.variants?.length || Boolean(draft.variant_id))
            && (draft.product.options || []).every((option) => !option.is_required || optionValues(draft).some((id) => (option.values || []).some((value) => String(value.id) === id)));
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
            content = wizardIntro('Choose the exact treatment configuration before checking availability.') + `<div class="tr-pos-wizard__grid">${variants.length ? variants.map((variant) => `<button type="button" class="tr-pos-choice ${String(draft.variant_id) === String(variant.id) ? 'is-active' : ''}" data-wizard-variant="${variant.id}"><span class="tr-pos-choice__icon" aria-hidden="true"><i class="fa fa-tags"></i></span><span class="tr-pos-choice__copy"><strong>${esc(variant.name || variant.uid)}</strong><small>Product variant</small></span><span class="tr-pos-choice__price">${money(variant.price)}</span></button>`).join('') : `<button type="button" class="tr-pos-choice is-active"><span class="tr-pos-choice__icon">✓</span><span class="tr-pos-choice__copy"><strong>Standard treatment</strong><small>No variant selection required</small></span><span class="tr-pos-choice__price">${money(draft.product.price)}</span></button>`}</div>${renderProductOptions(options, draft)}`;
        } else if (step === 1) {
            content = wizardIntro('Select a spa branch. Capacity is checked before beauticians are shown.') + `<div class="tr-pos-wizard__grid">${state.branches.map((branch) => {
                const selected = String(draft.spa_branch_id) === String(branch.id);
                let hint = 'Tap to check availability';
                if (selected && busy) hint = 'Checking availability…';
                else if (selected && state.wizard.branchDates.length) hint = `${state.wizard.branchDates.length}+ available dates found`;
                else if (selected && !busy) hint = 'No dates available in the next 3 weeks';
                return `<button type="button" class="tr-pos-choice ${selected ? 'is-active' : ''}" data-wizard-branch="${branch.id}" ${busy && selected ? 'disabled' : ''}><span class="tr-pos-choice__icon">${esc((branch.name || 'B').slice(0, 1))}</span><span class="tr-pos-choice__copy"><strong>${esc(branch.name)}</strong><small>${hint}</small></span></button>`;
            }).join('') || '<p class="tr-pos-wizard__empty">No active spa branches are available.</p>'}</div>`;
        } else if (step === 2) {
            const availableBeauticians = state.beauticians.filter((beautician) => (beautician.spa_branch_ids || []).map(String).includes(String(draft.spa_branch_id)));
            const modeChoices = draft.beautician_id && !busy ? `<div class="tr-pos-schedule-mode" role="group" aria-label="Appointment scheduling option">
                ${state.wizard.dates.length ? `<button type="button" class="tr-pos-schedule-mode__choice" data-wizard-schedule-now><strong>Schedule now</strong><span>Choose from ${state.wizard.dates.length} available date${state.wizard.dates.length === 1 ? '' : 's'}.</span></button>` : ''}
                ${state.wizard.tbaAllowed ? `<button type="button" class="tr-pos-schedule-mode__choice tr-pos-schedule-mode__choice--tba" data-wizard-tba><strong>Set as TBA</strong><span>Save now and schedule the appointment later.</span></button>` : ''}
            </div>` : '';
            content = wizardIntro(`Available team at ${branchName(draft.spa_branch_id)}. Select a beautician, then choose how to schedule.`) + `<div class="tr-pos-wizard__grid">${availableBeauticians.map((beautician) => `<button type="button" class="tr-pos-choice ${String(draft.beautician_id) === String(beautician.id) ? 'is-active' : ''}" data-wizard-beautician="${beautician.id}" ${busy ? 'disabled' : ''}><span class="tr-pos-choice__icon" style="background:${esc(beautician.profile_color || '#f0eeff')}22;color:${esc(beautician.profile_color || '#624bd5')}">${esc((beautician.name || 'B').slice(0, 1))}</span><span class="tr-pos-choice__copy"><strong>${esc(beautician.name)}</strong><small>${esc(beautician.job_title || 'Beautician')}${String(draft.beautician_id) === String(beautician.id) && state.wizard.dates.length ? ` · ${state.wizard.dates.length} dates` : ''}</small></span></button>`).join('') || '<p class="tr-pos-wizard__empty">No active beautician is assigned to this branch.</p>'}</div>${modeChoices}`;
        } else if (step === 3) {
            const months = [...new Set(state.wizard.dates.map(dateMonth))];
            const selectedMonth = months.includes(state.wizard.dateMonth) ? state.wizard.dateMonth : months[0];
            const dates = state.wizard.dates.filter((date) => dateMonth(date) === selectedMonth);
            content = wizardIntro(`Choose an available appointment date with ${beauticianName(draft.beautician_id)}.`) + `<div class="tr-pos-date-months" role="tablist" aria-label="Available appointment months">${months.map((month) => `<button type="button" class="tr-pos-date-month ${month === selectedMonth ? 'is-active' : ''}" data-wizard-date-month="${month}" role="tab" aria-selected="${month === selectedMonth}">${esc(displayMonth(month))}</button>`).join('')}</div><div class="tr-pos-wizard__dates">${dates.map((date) => { const parts = displayDate(date).split(', '); return `<button type="button" class="tr-pos-date-choice ${draft.appointment_date === date ? 'is-active' : ''}" data-wizard-date="${date}" ${busy ? 'disabled' : ''}><strong>${esc(parts[1] || parts[0])}</strong><span>${esc(parts[0])}</span></button>`; }).join('') || '<p class="tr-pos-wizard__empty">No available dates for this month.</p>'}</div><button type="button" class="tr-pos-date-load-more" data-wizard-load-later ${state.wizard.loadingMoreDates ? 'disabled' : ''}>${state.wizard.loadingMoreDates ? 'Loading later months…' : 'Load next available months'}</button>`;
        } else {
            content = wizardIntro(`${displayDate(draft.appointment_date)} · ${branchName(draft.spa_branch_id)} · ${beauticianName(draft.beautician_id)}`) + `<div class="tr-pos-wizard__slots">${state.wizard.slots.map((slot) => `<button type="button" class="tr-pos-time-choice ${draft.appointment_time === slot ? 'is-active' : ''}" data-wizard-time="${esc(slot)}">${esc(slot)}</button>`).join('') || '<p class="tr-pos-wizard__empty">No available time slots for this date.</p>'}</div>`;
        }
        q('[data-pos-wizard-body]').innerHTML = content;
        q('[data-pos-wizard-feedback]').textContent = busy
            ? (state.wizard.step === 1 ? 'Checking branch availability…' : 'Checking live availability…')
            : feedback;
        q('[data-pos-wizard-back]').hidden = step === 0;
        wizard.hidden = false;
    }

    function openWizard(product, editIndex = -1) {
        const existing = editIndex >= 0 ? state.items[editIndex] : null;
        state.wizard = {
            step: 0, editIndex, busy: false, feedback: '', branchDates: [], dates: [], slots: [], dateMonth: '', dateRangeEnd: '', loadingMoreDates: false, tbaAllowed: false, scheduleMode: null,
            draft: existing ? { ...existing, options: { ...(existing.options || {}) } } : { product, variant_id: product.variants?.length === 1 ? product.variants[0].id : null, options: {}, spa_branch_id: '', beautician_id: '', appointment_date: '', appointment_time: '', schedule_later: false },
        };
        if (wizardConfigurationReady(state.wizard.draft) && !(product.options || []).length) state.wizard.step = 1;
        document.body.classList.add('tr-pos-modal-open');
        renderWizard();
        if (wizardConfigurationReady(state.wizard.draft)) warmProductAvailability();
        q('[data-pos-wizard-close]').focus();
    }

    function closeWizard() {
        q('[data-pos-wizard]').hidden = true;
        document.body.classList.remove('tr-pos-modal-open');
        state.wizard.draft = null;
    }

    function cached(cache, key, request) {
        if (!cache.has(key)) cache.set(key, request().catch((error) => { cache.delete(key); throw error; }));
        return cache.get(key);
    }

    async function fetchDatesFor(productId, branchId, beauticianId = '', options = {}) {
        const from = options.from || localToday();
        const days = beauticianId ? 62 : 21;
        const params = new URLSearchParams({ spa_branch_id: branchId, product_id: productId, from, to: options.to || addDays(from, days) });
        if (beauticianId) params.set('beautician_id', beauticianId);
        else params.set('limit', '12');
        const key = `dates:${params.toString()}`;
        return cached(state.availabilityCache, key, () => json(apiUrl(`/bookings/available-dates?${params.toString()}`), options));
    }

    async function fetchDates(beauticianId = '', options = {}) {
        const draft = state.wizard.draft;
        const payload = await fetchDatesFor(draft.product.id, draft.spa_branch_id, beauticianId, options);
        state.wizard.tbaAllowed = Boolean(payload.tba_allowed);
        return payload.dates || [];
    }

    function warmProductAvailability() {
        const draft = state.wizard.draft;
        const jobs = state.branches.flatMap((branch) => state.beauticians
            .filter((beautician) => (beautician.spa_branch_ids || []).map(String).includes(String(branch.id)))
            .map((beautician) => fetchDatesFor(draft.product.id, branch.id, beautician.id)));
        Promise.all(jobs).catch(() => {});
    }

    async function chooseBranch(id) {
        const draft = state.wizard.draft;
        draft.spa_branch_id = id; draft.beautician_id = ''; draft.appointment_date = ''; draft.appointment_time = ''; draft.schedule_later = false;
        state.wizard.branchDates = []; state.wizard.dates = []; state.wizard.slots = []; state.wizard.busy = false; state.wizard.feedback = ''; state.wizard.step = 2;
        renderWizard();
        fetchDates('').then((dates) => {
            if (String(draft.spa_branch_id) === String(id)) state.wizard.branchDates = dates;
        }).catch(() => {});
    }

    async function chooseBeautician(id) {
        const draft = state.wizard.draft;
        draft.beautician_id = id; draft.appointment_date = ''; draft.appointment_time = ''; draft.schedule_later = false;
        state.wizard.dates = []; state.wizard.slots = []; state.wizard.busy = false; state.wizard.feedback = '';
        renderWizard();
        try {
            state.wizard.dates = await fetchDates(id);
            state.wizard.dateMonth = dateMonth(state.wizard.dates[0] || '');
            state.wizard.dateRangeEnd = addDays(localToday(), 62);
            void loadLaterMonths(true);
            if (!state.wizard.dates.length) state.wizard.feedback = 'This beautician has no available dates for the selected treatment and branch.';
        } catch (error) {
            state.wizard.feedback = error.message;
        }
        if (String(draft.beautician_id) === String(id)) renderWizard();
    }

    async function loadLaterMonths(background = false) {
        const draft = state.wizard.draft;
        if (state.wizard.loadingMoreDates || !draft?.beautician_id) return;
        const from = addDays(state.wizard.dateRangeEnd || addDays(localToday(), 62), 1);
        const to = addDays(from, 89);
        state.wizard.loadingMoreDates = true;
        if (!background) renderWizard();
        try {
            const payload = await fetchDatesFor(draft.product.id, draft.spa_branch_id, draft.beautician_id, { from, to });
            const laterDates = payload.dates || [];
            state.wizard.dates = [...new Set([...state.wizard.dates, ...laterDates])].sort();
            state.wizard.dateRangeEnd = to;
            if (laterDates.length) state.wizard.dateMonth = dateMonth(laterDates[0]);
            else state.wizard.feedback = 'No later appointment dates are currently available in this period.';
        } catch (error) { state.wizard.feedback = error.message; }
        state.wizard.loadingMoreDates = false;
        if (!background || state.wizard.step === 3) renderWizard();
    }

    async function chooseDate(date) {
        const draft = state.wizard.draft;
        draft.appointment_date = date; draft.appointment_time = '';
        const holds = state.items.filter((_, index) => index !== state.wizard.editIndex).map((line) => [line.beautician_id, line.appointment_date, line.appointment_time, line.product.id, line.spa_branch_id].join(':')).sort().join('|');
        const key = `slots:${draft.product.id}:${draft.spa_branch_id}:${draft.beautician_id}:${date}:${holds}`;
        state.wizard.slots = []; state.wizard.busy = false; state.wizard.feedback = 'Preparing current appointment times…'; state.wizard.step = 4;
        renderWizard();
        try {
            state.wizard.slots = await cached(state.slotCache, key, async () => {
                const params = new URLSearchParams({ beautician_id: draft.beautician_id, spa_branch_id: draft.spa_branch_id, product_id: draft.product.id, date });
                state.items.forEach((line, index) => {
                    if (index === state.wizard.editIndex || line.schedule_later) return;
                    params.set(`holds[${index}][beautician_id]`, line.beautician_id);
                    params.set(`holds[${index}][appointment_date]`, line.appointment_date);
                    params.set(`holds[${index}][appointment_time]`, String(line.appointment_time).slice(0, 5));
                    params.set(`holds[${index}][product_id]`, line.product.id);
                    params.set(`holds[${index}][spa_branch_id]`, line.spa_branch_id);
                    params.set(`holds[${index}][duration_minutes]`, line.product.duration_minutes || 60);
                });
                const payload = await json(apiUrl(`/bookings/availability?${params.toString()}`));
                return (payload.slots || []).map((slot) => typeof slot === 'string' ? slot : (slot.time || slot.value)).filter(Boolean);
            });
            if (!state.wizard.slots.length) state.wizard.feedback = 'No appointment times remain available. Another selected treatment may already occupy this beautician.';
        } catch (error) { state.wizard.feedback = error.message; }
        if (draft.appointment_date === date) renderWizard();
    }

    function commitWizard() {
        const item = { ...state.wizard.draft };
        if (state.wizard.editIndex >= 0) state.items[state.wizard.editIndex] = item;
        else state.items.push(item);
        state.availabilityCache.clear(); state.slotCache.clear(); clearCoupon(); closeWizard(); renderProducts(); renderOrder();
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
            if (state.loyalty.appliedPoints > 0) formData.append('loyalty_points', String(state.loyalty.appliedPoints));
            if (state.coupon.applied?.code) formData.append('coupon_code', state.coupon.applied.code);
            state.items.forEach((line, index) => {
                const prefix = 'items[' + index + ']';
                formData.append(prefix + '[product_id]', String(line.product.id));
                if (line.variant_id) formData.append(prefix + '[variant_id]', String(line.variant_id));
                Object.entries(line.options || {}).forEach(([optionId, value]) => {
                    (Array.isArray(value) ? value : [value]).forEach((selected, optionIndex) => formData.append(`${prefix}[options][${optionId}]${Array.isArray(value) ? `[${optionIndex}]` : ''}`, String(selected)));
                });
                formData.append(prefix + '[beautician_id]', String(line.beautician_id));
                formData.append(prefix + '[spa_branch_id]', String(line.spa_branch_id));
                if (!line.schedule_later) {
                    formData.append(prefix + '[appointment_date]', line.appointment_date);
                    formData.append(prefix + '[appointment_time]', line.appointment_time);
                }
                formData.append(prefix + '[schedule_later]', line.schedule_later ? '1' : '0');
            });
            const result = await json(apiUrl('/bookings'), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: formData });
            const count = Array.isArray(result.data) ? result.data.length : state.items.length;
            feedback.textContent = `${count} booking${count === 1 ? '' : 's'} saved successfully.`; feedback.classList.add('is-success');
            state.items = []; state.requestKey = null; clearLoyalty(); clearCoupon(); clearReceipt(); setMobileCheckoutOpen(false); renderProducts(); renderSelectedCustomer();
        } catch (error) { feedback.textContent = error.message; feedback.classList.add('is-error'); renderOrder(); }
        button.classList.remove('is-loading');
    }

    async function init() {
        const catalogNode = q('[data-pos-catalog]');
        state.products = catalogNode ? JSON.parse(catalogNode.textContent || '[]') : [];
        state.categories = [{ id: 'all', name: 'All treatments' }, ...[...new Map(state.products.filter((item) => item.category_id).map((item) => [item.category_id, { id: item.category_id, name: item.category_name || 'Treatments' }])).values()]];
        renderCategories(); renderProducts(); renderReceipt(); renderOrder();
        try {
            const [beauticians, branches] = await Promise.all([json(apiUrl('/beauticians')), json(apiUrl('/spa-branches'))]);
            state.beauticians = beauticians.data || []; state.branches = branches.data || [];
        } catch (error) { q('[data-pos-feedback]').textContent = `Booking options unavailable: ${error.message}`; q('[data-pos-feedback]').classList.add('is-error'); }
    }

    root.addEventListener('click', async (event) => {
        if (event.target.closest('[data-pos-mobile-checkout]')) { setMobileCheckoutOpen(true); return; }
        if (event.target.closest('[data-pos-mobile-close]')) { setMobileCheckoutOpen(false); return; }
        const product = event.target.closest('[data-pos-product]');
        if (product) { const selected = state.products.find((item) => String(item.id) === product.dataset.posProduct); if (selected) openWizard(selected); return; }
        const category = event.target.closest('[data-pos-category]'); if (category) { state.category = category.dataset.posCategory; resetCatalogPage(); renderCategories(); renderProducts(); return; }
        const page = event.target.closest('[data-pos-catalog-page]'); if (page && !page.disabled) { state.catalogPage = Number(page.dataset.posCatalogPage); renderProducts(); q('[data-pos-products]')?.scrollIntoView({ block: 'start', behavior: 'smooth' }); return; }
        const remove = event.target.closest('[data-pos-remove-line]'); if (remove) { state.items.splice(Number(remove.dataset.posRemoveLine), 1); clearCoupon(); renderProducts(); renderOrder(); return; }
        const edit = event.target.closest('[data-pos-edit-line]'); if (edit) { const index = Number(edit.dataset.posEditLine); if (state.items[index]) { setMobileCheckoutOpen(false); openWizard(state.items[index].product, index); } return; }
        if (event.target.closest('[data-pos-wizard-close]')) { closeWizard(); return; }
        if (event.target.closest('[data-pos-wizard-back]')) { state.wizard.step = Math.max(0, state.wizard.step - 1); state.wizard.feedback = ''; renderWizard(); return; }
        const variant = event.target.closest('[data-wizard-variant]'); if (variant) { state.wizard.draft.variant_id = variant.dataset.wizardVariant; if (wizardConfigurationReady(state.wizard.draft) && !(state.wizard.draft.product.options || []).length) state.wizard.step = 1; renderWizard(); return; }
        const option = event.target.closest('[data-wizard-option]'); if (option) { selectProductOption(option); if (wizardConfigurationReady(state.wizard.draft)) state.wizard.step = 1; renderWizard(); return; }
        const branch = event.target.closest('[data-wizard-branch]'); if (branch) { await chooseBranch(branch.dataset.wizardBranch); return; }
        const beautician = event.target.closest('[data-wizard-beautician]'); if (beautician) { await chooseBeautician(beautician.dataset.wizardBeautician); return; }
        const month = event.target.closest('[data-wizard-date-month]'); if (month) { state.wizard.dateMonth = month.dataset.wizardDateMonth; renderWizard(); return; }
        if (event.target.closest('[data-wizard-load-later]')) { await loadLaterMonths(); return; }
        if (event.target.closest('[data-wizard-schedule-now]')) { state.wizard.step = 3; renderWizard(); return; }
        if (event.target.closest('[data-wizard-tba]')) { state.wizard.draft.schedule_later = true; state.wizard.draft.appointment_date = ''; state.wizard.draft.appointment_time = ''; commitWizard(); return; }
        const date = event.target.closest('[data-wizard-date]'); if (date) { await chooseDate(date.dataset.wizardDate); return; }
        const time = event.target.closest('[data-wizard-time]'); if (time) { state.wizard.draft.appointment_time = time.dataset.wizardTime; state.wizard.draft.schedule_later = false; commitWizard(); return; }
        if (event.target.closest('[data-pos-clear-customer]')) {
            state.customer = null;
            state.rewards = null;
            clearLoyalty(); clearCoupon();
            q('[data-pos-membership-feedback]').textContent = '';
            q('[data-pos-membership-feedback]').className = 'tr-pos-membership-feedback';
            renderSelectedCustomer();
            renderOrder();
            return;
        }
        if (event.target.closest('[data-pos-receipt-clear]')) { clearReceipt(); q('[data-pos-feedback]').textContent = ''; q('[data-pos-feedback]').className = 'tr-pos-feedback'; return; }
        if (event.target.closest('[data-pos-reset]')) { state.items = []; state.customer = null; state.rewards = null; clearLoyalty(); clearCoupon(); clearReceipt(); setMobileCheckoutOpen(false); renderProducts(); renderSelectedCustomer(); q('[data-pos-feedback]').textContent = ''; return; }
        if (event.target.closest('[data-pos-coupon-apply]')) { await applyCoupon(); return; }
        if (event.target.closest('[data-pos-coupon-remove]')) { clearCoupon(); renderOrder(); return; }
        if (event.target.closest('[data-pos-submit]')) await submit();
        const view = event.target.closest('[data-pos-view]'); if (view) { state.view = view.dataset.posView; root.querySelectorAll('[data-pos-view]').forEach((item) => item.classList.toggle('is-active', item === view)); renderProducts(); }
    });
    q('[data-pos-loyalty-points]').addEventListener('input', (event) => { state.loyalty.input = event.target.value; state.loyalty.error = ''; });
    q('[data-pos-loyalty-max]').addEventListener('click', () => { state.loyalty.input = String(loyaltyMaxPoints()); applyLoyaltyPoints(state.loyalty.input); renderOrder(); });
    q('[data-pos-loyalty-apply]').addEventListener('click', () => { applyLoyaltyPoints(q('[data-pos-loyalty-points]').value); renderOrder(); });
    q('[data-pos-loyalty-remove]').addEventListener('click', () => { clearLoyalty(); renderOrder(); });
    q('[data-pos-coupon-input]').addEventListener('input', (event) => { state.coupon.input = event.target.value; state.coupon.error = ''; });
        q('[data-pos-search]').addEventListener('input', (event) => { state.search = event.target.value; resetCatalogPage(); renderProducts(); });
    q('[data-pos-payment-receipt]').addEventListener('change', (event) => acceptReceipt(event.target.files?.[0]));
    const receiptZone = q('[data-pos-receipt-dropzone]');
    ['dragenter', 'dragover'].forEach((name) => receiptZone.addEventListener(name, (event) => { event.preventDefault(); receiptZone.classList.add('is-dragging'); }));
    ['dragleave', 'drop'].forEach((name) => receiptZone.addEventListener(name, (event) => { event.preventDefault(); receiptZone.classList.remove('is-dragging'); }));
    receiptZone.addEventListener('drop', (event) => acceptReceipt(event.dataTransfer?.files?.[0]));
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if (!q('[data-pos-wizard]').hidden) { closeWizard(); return; }
        setMobileCheckoutOpen(false);
    });

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
            const payload = await json(apiUrl(`/membership-lookup?membership_id=${encodeURIComponent(code)}`));
            state.customer = {
                ...(payload.data.customer || {}),
                membership_id: payload.data.membership_id || code,
            };
            const activeStamps = payload.data.stamp_cards?.active || [];
            state.rewards = { points: payload.data.points, points_value_rm: payload.data.points_value_rm, point_value_rm: payload.data.point_value_rm, max_redeem_percent: payload.data.max_redeem_percent, tier: payload.data.tier, stamps_ready: payload.data.stamp_cards?.ready_to_redeem || 0, stamps_active: activeStamps.reduce((total, card) => total + Number(card.earned || 0), 0) };
            clearCoupon();
            clearLoyalty();
            feedback.textContent = '';
            feedback.className = 'tr-pos-membership-feedback';
            renderSelectedCustomer();
            renderOrder();
        } catch (error) { state.customer = null; state.rewards = null; clearCoupon(); renderSelectedCustomer(); renderOrder(); feedback.textContent = error.message; feedback.className = 'tr-pos-membership-feedback is-error'; }
    };
    q('[data-pos-membership-lookup]').addEventListener('click', lookupMembership);
    q('[data-pos-membership-id]').addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); lookupMembership(); } });
    q('[data-pos-customer-results]').addEventListener('click', async (event) => { const item = event.target.closest('[data-pos-customer]'); if (!item) return; q('[data-pos-customer-results]').innerHTML = ''; if (item.dataset.posMembership) { q('[data-pos-membership-id]').value = item.dataset.posMembership; await lookupMembership(); return; } state.customer = { id: Number(item.dataset.posCustomer), name: item.querySelector('strong').textContent, phone: item.querySelector('small').textContent }; state.rewards = null; clearCoupon(); renderSelectedCustomer(); renderOrder(); });
    init();
}
