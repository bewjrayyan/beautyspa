@php
    use Modules\Loyalty\Support\LoyaltyLang;

    $lt = fn (string $key): string => LoyaltyLang::get($key);
    $selection = $eligibleSelection ?? ['category_ids' => [], 'products' => []];
    $selectedCategoryIds = old('eligible_category_ids', $selection['category_ids'] ?? []);
    $selectedProducts = old('eligible_product_ids', collect($selection['products'] ?? [])->pluck('id')->all());
    $selectedProductOptions = collect($selection['products'] ?? []);
    $filterCategoryId = old('product_filter_category_id', '');

    if (old('eligible_product_ids') !== null) {
        $selectedProductOptions = collect(old('eligible_product_ids'))->map(fn ($id) => ['id' => (int) $id, 'name' => '#'.$id]);
    }

    $stampProgramSelection = [
        'category_ids' => array_values(array_map('intval', (array) $selectedCategoryIds)),
        'product_ids' => array_values(array_map('intval', (array) $selectedProducts)),
    ];
@endphp

<div
    class="loyalty-stamp-products-picker"
    id="stamp-program-products"
    data-search-url="{{ route('admin.loyalty.stamp_programs.products.search') }}"
    data-category-products-url="{{ url('admin/loyalty/stamp-programs/categories') }}"
>
    <div class="loyalty-stamp-products-picker__grid">
        <div class="loyalty-tier-form__field loyalty-stamp-products-picker__field">
            <label for="eligible_category_ids" class="loyalty-tier-form__label">
                {{ $lt('stamp_programs.form.products.categories') }}
            </label>
            <select
                name="eligible_category_ids[]"
                id="eligible_category_ids"
                class="form-control selectize prevent-creation loyalty-stamp-products-picker__categories"
                multiple
                placeholder="{{ $lt('stamp_programs.form.products.categories_placeholder') }}"
            >
                @foreach ($categories ?? [] as $categoryId => $categoryName)
                    <option
                        value="{{ $categoryId }}"
                        @selected(in_array((int) $categoryId, array_map('intval', (array) $selectedCategoryIds), true))
                    >
                        {{ $categoryName }}
                    </option>
                @endforeach
            </select>
            <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.products.categories_help') }}</p>
        </div>

        <div class="loyalty-tier-form__field loyalty-stamp-products-picker__field">
            <label for="product_filter_category_id" class="loyalty-tier-form__label">
                {{ $lt('stamp_programs.form.products.filter_category') }}
            </label>
            <select
                id="product_filter_category_id"
                class="form-control custom-select-black loyalty-stamp-products-picker__filter"
            >
                <option value="">{{ $lt('stamp_programs.form.products.all_categories') }}</option>
                @foreach ($categories ?? [] as $categoryId => $categoryName)
                    <option value="{{ $categoryId }}" @selected((string) $filterCategoryId === (string) $categoryId)>
                        {{ $categoryName }}
                    </option>
                @endforeach
            </select>
            <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.products.filter_category_help') }}</p>
        </div>
    </div>

    <div class="loyalty-tier-form__field loyalty-stamp-products-picker__field loyalty-stamp-products-picker__field--products">
        <div class="loyalty-stamp-products-picker__products-head">
            <label for="eligible_product_ids" class="loyalty-tier-form__label">
                {{ $lt('stamp_programs.form.products.products') }}
            </label>
            <button
                type="button"
                class="btn btn-default btn-sm"
                id="stamp-program-add-category-products"
                data-need-category="{{ $lt('stamp_programs.form.products.need_filter_category') }}"
                data-load-failed="{{ $lt('stamp_programs.form.products.load_category_failed') }}"
            >
                <i class="fa fa-plus" aria-hidden="true"></i>
                {{ $lt('stamp_programs.form.products.add_category_products') }}
            </button>
        </div>

        <select
            name="eligible_product_ids[]"
            id="eligible_product_ids"
            class="form-control selectize prevent-creation loyalty-stamp-products-picker__products"
            multiple
            data-url="{{ route('admin.loyalty.stamp_programs.products.search') }}"
            placeholder="{{ $lt('stamp_programs.form.products.search_placeholder') }}"
        >
            @foreach ($selectedProductOptions as $product)
                <option value="{{ $product['id'] }}" selected>{{ $product['name'] ?? ('#'.$product['id']) }}</option>
            @endforeach
        </select>
        <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.products.products_help') }}</p>
    </div>
</div>

@include('admin::partials.selectize_remote')

@push('globals')
    <script>
        AestheticCart.data['loyalty.stamp_program.selection'] = @json($stampProgramSelection);
    </script>
@endpush

@push('scripts')
    @vite(['modules/Loyalty/Resources/assets/admin/js/stamp-program-products.js'])
@endpush
