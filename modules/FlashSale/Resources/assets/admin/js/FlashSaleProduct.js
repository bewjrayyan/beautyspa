export default class {
    constructor(data) {
        this.productPanelNumber = data.productPanelNumber;
        this.productPanelHtml = this.getProductPanelHtml(data);
    }

    getProductPanelHtml(data) {
        data.product = this.normalizeAttributes(data.product);

        let template = _.template($('#flash-sale-product-template').html());

        return $(template(data));
    }

    normalizeAttributes(product) {
        if ($.isEmptyObject(product)) {
            return {
                id: null,
                name: null,
                is_virtual: true,
                catalog_price_formatted: null,
                has_options: false,
                has_variants: false,
                pivot: { end_date: null, price: { amount: null }, qty: 0, sold: 0 },
            };
        }

        if (! $.isEmptyObject(AestheticCart.errors['flash_sale.products'])) {
            return {
                id: product.id,
                name: product.name,
                is_virtual: product.is_virtual ?? true,
                catalog_price_formatted: product.catalog_price_formatted ?? null,
                has_options: product.has_options ?? false,
                has_variants: product.has_variants ?? false,
                pivot: {
                    end_date: product.end_date ?? product.campaign_end,
                    price: { amount: this.formatPriceAmount(this.resolvePriceAmount(product)) },
                    qty: product.qty ?? 0,
                    sold: product.sold ?? 0,
                },
            };
        }

        product.is_virtual = product.is_virtual ?? product.isVirtual ?? true;
        product.has_options = product.has_options ?? false;
        product.has_variants = product.has_variants ?? false;

        if (! product.pivot) {
            product.pivot = {
                end_date: null,
                price: { amount: null },
                qty: 0,
                sold: 0,
            };
        }

        product.pivot.price = {
            amount: this.formatPriceAmount(this.resolvePriceAmount(product)),
        };

        product.pivot.sold = parseInt(product.pivot.sold ?? 0, 10) || 0;

        if (product.pivot.qty === null || product.pivot.qty === '') {
            product.pivot.qty = product.is_virtual ? 0 : 1;
        }

        if (product.pivot.end_date && typeof product.pivot.end_date === 'object' && product.pivot.end_date.date) {
            product.pivot.end_date = product.pivot.end_date.date;
        }

        return product;
    }

    resolvePriceAmount(product) {
        if (product.pivot?.price?.amount !== undefined && product.pivot.price.amount !== null) {
            return product.pivot.price.amount;
        }

        if (product.pivot?.price !== undefined && product.pivot.price !== null) {
            return product.pivot.price;
        }

        return product.price ?? null;
    }

    formatPriceAmount(amount) {
        if (amount === null || amount === '') {
            return '';
        }

        const value = parseFloat(String(amount).replace(/,/g, ''));

        if (Number.isNaN(value)) {
            return '';
        }

        return value.toFixed(2);
    }

    render() {
        this.attachEventListeners();
        this.syncQtyHelp(this.productPanelHtml.data('isVirtual') == 1);
        this.formatPriceField();

        window.admin.dateTimePicker(this.productPanelHtml.find('.datetime-picker'));

        return this.productPanelHtml;
    }

    attachEventListeners() {
        this.productPanelHtml.find('.delete-product-panel').on('click', () => {
            this.productPanelHtml.remove();
            $(document).trigger('flash-sale:products-changed');
        });

        const $select = this.productPanelHtml.find('.flash-sale-product-select');

        $select.on('change', () => {
            const productId = $select.val();

            if (! productId) {
                return;
            }

            const showUrl = `${AestheticCart.data['flash_sale.product_show_url']}/${productId}`;

            axios.get(showUrl).then((response) => {
                this.applyProductMeta(response.data);
            });
        });

        this.productPanelHtml.find('.flash-sale-qty-input').on('input change', () => {
            $(document).trigger('flash-sale:products-changed');
        });

        this.productPanelHtml.find('.flash-sale-price-input__field').on('blur', () => {
            this.formatPriceField();
        });
    }

    formatPriceField() {
        const $price = this.productPanelHtml.find('.flash-sale-price-input__field');
        const formatted = this.formatPriceAmount($price.val());

        if (formatted !== '') {
            $price.val(formatted);
        }
    }

    applyProductMeta(product) {
        const isVirtual = !! product.is_virtual;

        this.productPanelHtml.attr('data-is-virtual', isVirtual ? 1 : 0);
        this.productPanelHtml.data('isVirtual', isVirtual ? 1 : 0);

        const $badge = this.productPanelHtml.find('.flash-sale-virtual-badge');

        if (isVirtual) {
            if ($badge.length === 0) {
                const label = AestheticCart.langs['product::products.table.virtual_treatment'] ?? 'Virtual/Treatment';

                this.productPanelHtml
                    .find('.flash-sale-item__sold')
                    .first()
                    .before(
                        `<span class="flash-sale-item__badge flash-sale-virtual-badge"><i class="fa fa-heart" aria-hidden="true"></i> ${label}</span>`
                    );
            }
        } else {
            $badge.remove();
        }

        const $qty = this.productPanelHtml.find('.flash-sale-qty-input');

        if (! $qty.val() || $qty.val() === '0') {
            $qty.val(product.default_qty ?? (isVirtual ? 0 : 1));
        }

        const $price = this.productPanelHtml.find('.flash-sale-price-input__field');

        if (product.suggested_price !== undefined && ! $price.val()) {
            $price.val(this.formatPriceAmount(product.suggested_price));
        }

        if (product.catalog_price_formatted) {
            this.productPanelHtml
                .find('.flash-sale-item__catalog-price-value')
                .text(product.catalog_price_formatted);
        }

        const $optionsNote = this.productPanelHtml.find('.flash-sale-item__price-note');

        if (product.has_options) {
            $optionsNote.show();
        } else {
            $optionsNote.hide();
        }

        const displayName = (product.name || '').split(' · ')[0].trim();

        if (displayName) {
            this.productPanelHtml.find('.flash-sale-item__heading-text').text(displayName);
            this.productPanelHtml.find('[id$="-name"]').val(displayName);
        }

        this.syncQtyHelp(isVirtual);
        $(document).trigger('flash-sale:products-changed');
    }

    syncQtyHelp(isVirtual) {
        const $help = this.productPanelHtml.find('.flash-sale-qty-help--virtual');

        if (isVirtual) {
            $help.show();
        } else {
            $help.hide();
        }
    }
}
