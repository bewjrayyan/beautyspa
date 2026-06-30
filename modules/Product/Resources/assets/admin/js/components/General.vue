<template>
    <div class="box">
        <div class="box-header product-general-header">
            <h5>{{ trans("product::products.group.general") }}</h5>

            <a
                v-if="storefrontProductUrl"
                :href="storefrontProductUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="product-view-live-btn"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shop-window" viewBox="0 0 16 16">
                    <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h12V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5m2 .5a.5.5 0 0 1 .5.5V13h8V9.5a.5.5 0 0 1 1 0V13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5a.5.5 0 0 1 .5-.5"/>
                </svg>

                {{ trans("product::products.view_live") }}
            </a>
        </div>

        <div class="box-body">
            <div class="form-group row">
                <label for="name" class="col-sm-12 control-label text-left">
                    {{ trans("product::attributes.name") }}
                    <span class="text-red">*</span>
                </label>

                <div class="col-sm-12">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        v-model="form.name"
                        @change="
                            if (
                                window.location.pathname.endsWith(
                                    'products/create'
                                )
                            ) {
                                setProductSlug($event.target.value);
                            }
                        "
                    />

                    <span
                        class="help-block text-red"
                        v-if="errors.has('name')"
                        v-text="errors.get('name')"
                    ></span>
                </div>
            </div>

            <div class="form-group row">
                <label
                    for="description"
                    class="col-sm-12 control-label text-left"
                    @click="focusEditor"
                >
                    {{ trans("product::attributes.description") }}
                    <span class="text-red">*</span>
                </label>

                <div class="col-sm-12">
                    <textarea
                        name="description"
                        id="description"
                        class="form-control wysiwyg"
                        v-model="form.description"
                    >
                    </textarea>

                    <span
                        class="help-block text-red"
                        v-if="errors.has('description')"
                        v-text="errors.get('description')"
                    ></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";
import tinyMCE from "@admin/js/wysiwyg";

const textEditor = ref(null);

const { form, shouldResetForm, errors, focusField } = useForm();
const { setProductSlug } = useProductMethods();

const storefrontProductUrl = computed(() => {
    const template = AestheticCart.data?.storefront_product_url_template;

    if (!form.id || !form.slug || !template) {
        return null;
    }

    return template.replace("__SLUG__", form.slug);
});

function focusEditor() {
    textEditor.value.get("description").focus();
}

function initTextEditor() {
    textEditor.value = tinyMCE({
        setup: (editor) => {
            editor.on("change", () => {
                editor.save();
                editor.getElement().dispatchEvent(new Event("input"));

                errors.clear("description");
            });
        },
    });
}

function resetFields() {
    textEditor.value.get("description").setContent("");
    textEditor.value.get("description").execCommand("mceCancel");
}

watch(shouldResetForm, () => {
    resetFields();

    focusField({
        selector: "#name",
    });
});

onMounted(() => {
    initTextEditor();
});
</script>
