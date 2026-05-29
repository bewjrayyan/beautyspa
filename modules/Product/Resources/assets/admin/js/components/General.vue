<template>
    <div class="box">
        <div class="box-header">
            <h5>{{ trans("product::products.group.general") }}</h5>
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
import { ref, watch, onMounted } from "vue";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";
import tinyMCE from "@admin/js/wysiwyg";

const textEditor = ref(null);

const { form, shouldResetForm, errors, focusField } = useForm();
const { setProductSlug } = useProductMethods();

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
