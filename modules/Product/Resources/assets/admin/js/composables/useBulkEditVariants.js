import { ref, reactive } from "vue";
import { useForm } from "./useForm";
import { normalizeScheduleDate } from "../support/normalizeScheduleDate";
import { toaster } from "@admin/js/Toaster";

const bulkEditVariantsUid = ref("");
const bulkEditVariantsField = ref("");

const bulkEditVariants = reactive({ ...bulkEditvariantsDefaultData() });

function bulkEditvariantsDefaultData() {
    return {
        is_active: true,
        media: [],
        price: null,
        special_price: null,
        special_price_type: "fixed",
        special_price_start: null,
        special_price_end: null,
        manage_stock: 0,
        qty: null,
        in_stock: 1,
    };
}

function normalizeBulkScheduleDates() {
    bulkEditVariants.special_price_start = normalizeScheduleDate(
        bulkEditVariants.special_price_start,
    );
    bulkEditVariants.special_price_end = normalizeScheduleDate(
        bulkEditVariants.special_price_end,
    );
}

function clearVariantsSpecialPriceErrors(errors, uid) {
    Object.keys(errors.errors).forEach((key) => {
        if (key.startsWith(`variants.${uid}`) && key.includes("special_price")) {
            errors.clear(key);
        }
    });
}

function updateVariantsField(variant, { key, value }, errors) {
    variant[key] = value;

    errors.clear(`variants.${variant.uid}.${key}`);
}

function updateVariantsStatus(variant, { key, value }, errors) {
    if (variant.is_default === true) {
        return;
    }

    variant[key] = value;

    errors.clear(`variants.${variant.uid}.${key}`);
}

function updateVariantsSpecialPrice(
    variant,
    { key, value },
    bulkData,
    errors,
) {
    variant[key] = value;
    variant.special_price_type = bulkData.special_price_type;
    variant.special_price_start = normalizeScheduleDate(
        bulkData.special_price_start,
    );
    variant.special_price_end = normalizeScheduleDate(
        bulkData.special_price_end,
    );

    clearVariantsSpecialPriceErrors(errors, variant.uid);
}

function updateVariantsSpecialPriceDates(variant, bulkData, errors) {
    variant.special_price_start = normalizeScheduleDate(
        bulkData.special_price_start,
    );
    variant.special_price_end = normalizeScheduleDate(
        bulkData.special_price_end,
    );

    clearVariantsSpecialPriceErrors(errors, variant.uid);
}

function updateVariantsManageStock(variant, { key, value }, bulkData, errors) {
    variant[key] = value;
    variant.qty = bulkData.qty;

    errors.clear([
        `variants.${variant.uid}.${key}`,
        `variants.${variant.uid}.qty`,
    ]);
}

function callUpdateVariantsMethodByField(key) {
    return {
        media: updateVariantsField,
        sku: updateVariantsField,
        is_active: updateVariantsStatus,
        price: updateVariantsField,
        special_price: updateVariantsSpecialPrice,
        special_price_dates: updateVariantsSpecialPriceDates,
        manage_stock: updateVariantsManageStock,
        in_stock: updateVariantsField,
    }[key];
}

export function useBulkEditVariants() {
    const { form, errors } = useForm();

    function resetBulkEditVariantFields() {
        bulkEditVariantsUid.value = "";

        resetVariantsSelection();
        resetBulkEditVariantsField();
    }

    function resetVariantsSelection() {
        form.variants.forEach((variant) => {
            variant.is_selected = false;
        });
    }

    function resetBulkEditVariantsField() {
        bulkEditVariantsField.value = "";

        resetBulkEditVariants();
    }

    function resetBulkEditVariants() {
        Object.assign(bulkEditVariants, {
            ...bulkEditvariantsDefaultData(),
        });
    }

    function applyBulkEdit({ resetAfter = true, showToast = true } = {}) {
        if (!bulkEditVariantsUid.value || !bulkEditVariantsField.value) {
            return false;
        }

        normalizeBulkScheduleDates();

        const fieldKey = bulkEditVariantsField.value;

        const field = {
            key: fieldKey,
            value:
                fieldKey === "special_price_dates"
                    ? null
                    : bulkEditVariants[fieldKey],
        };

        const updater = callUpdateVariantsMethodByField(field.key);

        if (!updater) {
            return false;
        }

        form.variants.forEach((variant) => {
            if (!variant.is_selected) {
                return;
            }

            if (field.key === "special_price_dates") {
                updater(variant, bulkEditVariants, errors);

                return;
            }

            if (field.key === "special_price") {
                updater(variant, field, bulkEditVariants, errors);

                return;
            }

            if (field.key === "manage_stock") {
                updater(variant, field, bulkEditVariants, errors);

                return;
            }

            updater(variant, field, errors);
        });

        if (resetAfter) {
            resetBulkEditVariantFields();
        }

        if (showToast) {
            toaster(trans("product::products.variants.bulk_variants_updated"), {
                type: "default",
            });
        }

        return true;
    }

    function applyPendingBulkEdit() {
        if (!bulkEditVariantsUid.value || !bulkEditVariantsField.value) {
            return false;
        }

        if (
            bulkEditVariantsField.value === "special_price_dates" ||
            bulkEditVariantsField.value === "special_price"
        ) {
            if (
                !bulkEditVariants.special_price_start &&
                !bulkEditVariants.special_price_end &&
                bulkEditVariantsField.value === "special_price_dates"
            ) {
                return false;
            }
        }

        return applyBulkEdit({ resetAfter: false, showToast: false });
    }

    return {
        bulkEditVariantsUid,
        bulkEditVariantsField,
        bulkEditVariants,
        resetBulkEditVariantFields,
        resetVariantsSelection,
        resetBulkEditVariantsField,
        resetBulkEditVariants,
        applyBulkEdit,
        applyPendingBulkEdit,
    };
}
