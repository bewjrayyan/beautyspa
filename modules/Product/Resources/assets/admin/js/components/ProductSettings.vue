<template>
    <div class="box-header">
        <h5>
            {{ trans("product::products.group.settings") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="form-group row">
            <label for="brand-id" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.brand_id") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="brand_id"
                    id="brand-id"
                    class="form-control custom-select-black"
                    v-model="form.brand_id"
                >
                    <option value="">
                        {{ trans("admin::admin.form.please_select") }}
                    </option>

                    <option
                        v-for="(brand, index) in brands"
                        :key="index"
                        :value="brand.value"
                    >
                        {{ brand.name }}
                    </option>
                </select>

                <span
                    class="help-block text-red"
                    v-if="errors.has('brand_id')"
                    v-text="errors.get('brand_id')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="categories" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.categories") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="categories"
                    id="categories"
                    multiple
                    v-model="form.categories"
                    ref="categoriesField"
                >
                    <option
                        v-for="(category, index) in categories"
                        :key="index"
                        :value="category.value"
                    >
                        {{ category.name }}
                    </option>
                </select>

                <span
                    class="help-block text-red"
                    v-if="errors.has('categories')"
                    v-text="errors.get('categories')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="tags" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.tags") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="tags"
                    id="tags"
                    multiple
                    v-model="form.tags"
                    ref="tagsField"
                >
                    <option
                        v-for="(tag, index) in tags"
                        :key="index"
                        :value="tag.value"
                    >
                        {{ tag.name }}
                    </option>
                </select>

                <span
                    class="help-block text-red"
                    v-if="errors.has('tags')"
                    v-text="errors.get('tags')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="tax-class-id" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.tax_class_id") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="tax_class_id"
                    id="tax-class-id"
                    class="form-control custom-select-black"
                    v-model="form.tax_class_id"
                >
                    <option value="">
                        {{ trans("admin::admin.form.please_select") }}
                    </option>

                    <option
                        v-for="(taxClass, index, key) in taxClasses"
                        :key="key"
                        :value="index"
                    >
                        {{ taxClass }}
                    </option>
                </select>

                <span
                    class="help-block text-red"
                    v-if="errors.has('tax_class_id')"
                    v-text="errors.get('tax_class_id')"
                ></span>
            </div>
        </div>

        <div
            v-if="form.is_virtual && treatmentCategories.length"
            class="form-group row"
        >
            <label for="treatment-category-id" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.treatment_category_id") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="treatment_category_id"
                    id="treatment-category-id"
                    class="form-control custom-select-black"
                    v-model="form.treatment_category_id"
                >
                    <option value="">
                        {{ trans("admin::admin.form.please_select") }}
                    </option>

                    <option
                        v-for="(category, index) in treatmentCategories"
                        :key="index"
                        :value="category.value"
                    >
                        {{ category.name }}
                    </option>
                </select>

                <span class="help-block text-muted">
                    {{ trans("product::products.form.treatment_category_help") }}
                </span>

                <span
                    class="help-block text-red"
                    v-if="errors.has('treatment_category_id')"
                    v-text="errors.get('treatment_category_id')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="is-virtual" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.is_virtual") }}
            </label>

            <div class="col-sm-12">
                <div class="switch">
                    <input
                        type="checkbox"
                        name="is_virtual"
                        id="is-virtual"
                        v-model="form.is_virtual"
                    />

                    <label
                        for="is-virtual"
                        v-html="
                            trans(
                                'product::products.form.the_product_won\'t_be_shipped'
                            )
                        "
                    >
                    </label>
                </div>

                <span
                    class="help-block text-red"
                    v-if="errors.has('is_virtual')"
                    v-text="errors.get('is_virtual')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="is-active" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.is_active") }}
            </label>

            <div class="col-sm-12">
                <div class="switch">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is-active"
                        v-model="form.is_active"
                    />

                    <label for="is-active">
                        {{
                            trans("product::products.form.enable_the_product")
                        }}
                    </label>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('is_active')"
                        v-text="errors.get('is_active')"
                    ></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useForm } from "../composables/useForm";

const brands = ref(AestheticCart.data["brands"] ?? {});
const treatmentCategories = ref(AestheticCart.data["treatment-categories"] ?? []);
const categories = ref(AestheticCart.data["categories"] ?? {});
const categoriesField = ref(null);
const taxClasses = AestheticCart.data["tax-classes"] ?? {};
const tags = ref(AestheticCart.data["tags"] ?? {});
const tagsField = ref(null);

const { form, shouldResetForm, errors } = useForm();

function initCategoriesSelectize() {
    $(categoriesField.value).selectize({
        plugins: ["remove_button"],
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        hideSelected: false,
        allowEmptyOption: true,
        onChange: (values) => {
            form.categories = values;
        },
        onItemAdd(value) {
            this.getItem(value)[0].innerHTML = this.getItem(
                value
            )[0].innerHTML.replace(/¦––\s/g, "");
        },
        onItemRemove(value) {
            const element = [...this.$dropdown_content.children()].find(
                (el) => el.getAttribute("data-value") === value
            );

            if (element) {
                element.classList.remove("selected");
            }
        },
        onInitialize() {
            $("#categories")
                .next()
                .find("[data-value]")
                .each((_, el) => {
                    $(el).html(
                        $(el).text().slice(0, -1).replace(/¦––\s/g, "") +
                            '<a href="javascript:void(0)" class="remove" tabindex="-1">×</a>'
                    );
                });
        },
    });
}

function initTagsSelectize() {
    $(tagsField.value).selectize({
        plugins: ["remove_button"],
        delimiter: ",",
        persist: true,
        selectOnTab: true,
        hideSelected: true,
        allowEmptyOption: true,
        onChange: (values) => {
            form.tags = values;
        },
    });
}

function resetFields() {
    if (categoriesField.value?.selectize) {
        categoriesField.value.selectize.clear();
    }

    if (tagsField.value?.selectize) {
        tagsField.value.selectize.clear();
    }

    if (categoriesField.value?.selectize) {
        [
            ...categoriesField.value.selectize.$dropdown_content.children(),
        ].forEach((el) => {
            if (el.classList.contains("selected")) {
                el.classList.remove("selected");
            }
        });
    }
}

watch(shouldResetForm, () => {
    resetFields();
});

onMounted(() => {
    initCategoriesSelectize();
    initTagsSelectize();
});
</script>
