<template>
    <div class="box-header">
        <h5>
            {{ trans("product::products.group.seo") }}
        </h5>

        <div class="drag-handle">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
        </div>
    </div>

    <div class="box-body">
        <div class="form-group row">
            <label for="slug" class="col-sm-12 control-label text-left">
                {{ trans("product::attributes.slug") }}

                <span
                    v-if="window.location.pathname.endsWith('/edit')"
                    class="text-red"
                    >*</span
                >
            </label>

            <div class="col-sm-12">
                <input
                    type="text"
                    name="slug"
                    id="slug"
                    class="form-control"
                    @change="setProductSlug($event.target.value)"
                    v-model="form.slug"
                />

                <span
                    class="help-block text-red"
                    v-if="errors.has('slug')"
                    v-text="errors.get('slug')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="meta-title" class="col-sm-12 control-label text-left">
                {{ trans("meta::attributes.meta_title") }}
            </label>

            <div class="col-sm-12">
                <input
                    type="text"
                    name="meta.meta_title"
                    id="meta-title"
                    class="form-control"
                    v-model="form.meta.meta_title"
                />

                <span
                    class="help-block text-red"
                    v-if="errors.has('meta.meta_title')"
                    v-text="errors.get('meta.meta_title')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label
                for="meta-description"
                class="col-sm-12 control-label text-left"
            >
                {{ trans("meta::attributes.meta_description") }}
            </label>

            <div class="col-sm-12">
                <textarea
                    name="meta.meta_description"
                    rows="6"
                    cols="10"
                    id="meta-description"
                    class="form-control"
                    v-model="form.meta.meta_description"
                ></textarea>

                <span
                    class="help-block text-red"
                    v-if="errors.has('meta.meta_description')"
                    v-text="errors.get('meta.meta_description')"
                ></span>
            </div>
        </div>

        <div class="form-group row">
            <label for="meta-robots" class="col-sm-12 control-label text-left">
                {{ trans("meta::attributes.meta_robots") }}
            </label>

            <div class="col-sm-12">
                <select
                    name="meta.meta_robots"
                    id="meta-robots"
                    class="form-control"
                    v-model="form.meta.meta_robots"
                >
                    <option value="index, follow">
                        {{ trans("meta::attributes.meta_robots_index") }}
                    </option>
                    <option value="noindex, follow">
                        {{ trans("meta::attributes.meta_robots_noindex") }}
                    </option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-12 control-label text-left">
                {{ trans("meta::attributes.og_image") }}
            </label>

            <div class="col-sm-12">
                <div v-if="form.meta.og_image_path" class="product-seo-image">
                    <img :src="form.meta.og_image_path" :alt="trans('meta::attributes.og_image')" />
                    <button type="button" class="btn btn-default" @click="removeOgImage">
                        {{ trans("meta::attributes.og_image_remove") }}
                    </button>
                </div>

                <button v-else type="button" class="btn btn-default" @click="selectOgImage">
                    <i class="fa fa-image" aria-hidden="true"></i>
                    {{ trans("meta::attributes.og_image_select") }}
                </button>

                <span class="help-block">
                    {{ trans("meta::attributes.og_image_hint") }}
                </span>
            </div>
        </div>

        <section
            class="product-social-preview"
            aria-labelledby="product-social-preview-title"
        >
            <div class="product-social-preview__header">
                <div>
                    <span class="product-social-preview__eyebrow">
                        {{ trans("meta::attributes.social_preview_live") }}
                    </span>
                    <h6 id="product-social-preview-title">
                        {{ trans("meta::attributes.social_preview") }}
                    </h6>
                </div>

                <div class="product-social-preview__platforms" aria-hidden="true">
                    <span title="Facebook"><i class="fa fa-facebook"></i></span>
                    <span title="WhatsApp"><i class="fa fa-whatsapp"></i></span>
                    <span title="X"><i class="fa fa-twitter"></i></span>
                </div>
            </div>

            <div class="product-social-preview__card">
                <div class="product-social-preview__media">
                    <img
                        v-if="previewImage"
                        :src="previewImage"
                        :alt="previewTitle"
                    />
                    <div v-else class="product-social-preview__placeholder">
                        <i class="fa fa-image" aria-hidden="true"></i>
                        <span>{{ trans("meta::attributes.social_preview_no_image") }}</span>
                    </div>
                </div>

                <div class="product-social-preview__content">
                    <span class="product-social-preview__domain">{{ previewDomain }}</span>
                    <strong>{{ previewTitle }}</strong>
                    <p>{{ previewDescription }}</p>
                </div>
            </div>

            <p class="product-social-preview__hint">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                {{ trans("meta::attributes.social_preview_hint") }}
            </p>
        </section>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { useForm } from "../composables/useForm";
import { useProductMethods } from "../composables/useProductMethods";

const { form, errors } = useForm();
const { setProductSlug } = useProductMethods();

const previewImage = computed(
    () => form.meta.og_image_path || form.media?.[0]?.path || null
);

const previewTitle = computed(
    () =>
        plainText(form.meta.meta_title) ||
        plainText(form.name) ||
        trans("meta::attributes.social_preview_title_fallback")
);

const previewDescription = computed(
    () =>
        plainText(form.meta.meta_description) ||
        plainText(form.short_description) ||
        plainText(form.description) ||
        trans("meta::attributes.social_preview_description_fallback")
);

const previewUrl = computed(() => {
    const template = AestheticCart.data?.storefront_product_url_template;

    return template?.replace("__SLUG__", form.slug || "product") || window.location.origin;
});

const previewDomain = computed(() => {
    try {
        return new URL(previewUrl.value, window.location.origin).hostname;
    } catch {
        return window.location.hostname;
    }
});

function plainText(value) {
    return String(value || "")
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
}

function selectOgImage() {
    const picker = new MediaPicker({ type: "image" });

    picker.on("select", ({ id, path }) => {
        form.meta.og_image_id = +id;
        form.meta.og_image_path = path;
    });
}

function removeOgImage() {
    form.meta.og_image_id = null;
    form.meta.og_image_path = null;
}
</script>
