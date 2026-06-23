import "./google_reviews";

window.admin.removeSubmitButtonOffsetOn([
    "#logo",
    "#footer",
    "#newsletter",
    "#product_page",
    "#slider_banners",
    "#three_column_full_width_banners",
    "#brands",
    "#two_column_banners",
    "#three_column_banners",
    "#one_column_banner",
    "#google_reviews",
    "#mobile_home_promo",
]);

$("#storefront_theme_color").on("change", (e) => {
    if (e.currentTarget.value === "custom_color") {
        $("#custom-theme-color").removeClass("hide");
    } else {
        $("#custom-theme-color").addClass("hide");
    }
});

$("#storefront_mail_theme_color").on("change", (e) => {
    if (e.currentTarget.value === "custom_color") {
        $("#custom-mail-theme-color").removeClass("hide");
    } else {
        $("#custom-mail-theme-color").addClass("hide");
    }
});

$("#storefront-settings-edit-form").on("click", ".panel-image", (e) => {
    let picker = new MediaPicker({ type: "image" });

    picker.on("select", (file) => {
        const target = $(e.currentTarget);

        target.find("i").remove();
        target.find("img").attr("src", file.path).removeClass("hide");
        target.find(".banner-file-id").val(file.id);

        if (target.find("button.remove-image").length === 0) {
            target.append(
                `<button type="button" class="btn remove-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M6.00098 17.9995L17.9999 6.00053" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.9999 17.9995L6.00098 6.00055" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>`
            );
        }
    });
});

$("#storefront-settings-edit-form").on("click", ".remove-image", (e) => {
    e.stopPropagation();

    const target = $(e.currentTarget);

    target.parent().prepend('<i class="fa fa-picture-o"></i>');
    target.parent().find("img").removeAttr("src").addClass("hide");
    target.parent().find("input").attr("value", "");
    target.remove();
});

$(".product-type").on("change", (e) => {
    let categoryProducts = $(e.currentTarget)
        .parents(".form-group")
        .siblings(".category-products");
    let productsLimit = $(e.currentTarget)
        .parents(".form-group")
        .siblings(".products-limit");
    let customProducts = $(e.currentTarget)
        .parents(".form-group")
        .siblings(".custom-products");

    categoryProducts.addClass("hide");
    productsLimit.addClass("hide");
    customProducts.addClass("hide");

    if (e.currentTarget.value === "category_products") {
        categoryProducts.removeClass("hide");
    }

    if (
        e.currentTarget.value === "latest_products" ||
        e.currentTarget.value === "recently_viewed_products" ||
        e.currentTarget.value === "category_products"
    ) {
        productsLimit.removeClass("hide");
    }

    if (e.currentTarget.value === "custom_products") {
        customProducts.removeClass("hide");
    }
});

function toggleMobilePromoMediaFields() {
    const type = $('input[name="storefront_mobile_home_promo_media_type"]:checked').val();

    $(".mobile-promo-image-fields").toggleClass("hide", type !== "image");
    $(".mobile-promo-video-fields").toggleClass("hide", type !== "video");
}

$('input[name="storefront_mobile_home_promo_media_type"]').on("change", toggleMobilePromoMediaFields);

function renderMobilePromoVideoPreview($field, file) {
    const inputName = $field.data("inputName");
    const isVideo = (file.mime || "").startsWith("video/");
    const mediaMarkup = isVideo
        ? `<video src="${file.path}" controls playsinline preload="metadata"></video>`
        : `<div class="mobile-promo-video-preview__placeholder"><i class="fa fa-file-video-o" aria-hidden="true"></i><span>${file.filename || "Video"}</span></div>`;

    $field.find(".mobile-promo-video-dropzone").addClass("hide");
    $field.find(".mobile-promo-video-preview").removeClass("hide").html(`
        <div class="ac-media-preview__inner mobile-promo-video-preview__inner">
            ${mediaMarkup}
            <button type="button" class="ac-media-preview__remove remove-video" data-input-name="${inputName}" aria-label="Remove">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
            <div class="ac-media-preview__overlay">
                <button type="button" class="btn btn-default btn-sm video-picker-browse" data-input-name="${inputName}">
                    <i class="fa fa-refresh" aria-hidden="true"></i>
                    Replace
                </button>
            </div>
            <input type="hidden" name="${inputName}" value="${file.id}">
        </div>
    `);
    $field.find(".ac-media-field__canvas").addClass("is-filled");
}

$("#storefront-settings-edit-form").on("click", ".video-picker-browse", (e) => {
    e.preventDefault();
    e.stopPropagation();

    const inputName = $(e.currentTarget).data("inputName");
    const $field = $(`.mobile-promo-video-picker[data-input-name="${inputName}"]`);
    const picker = new MediaPicker({ type: "video" });

    picker.on("select", (file) => {
        renderMobilePromoVideoPreview($field, file);
    });
});

$("#storefront-settings-edit-form").on("click", ".remove-video", (e) => {
    e.preventDefault();
    e.stopPropagation();

    const inputName = $(e.currentTarget).data("inputName");
    const $field = $(`.mobile-promo-video-picker[data-input-name="${inputName}"]`);

    $field.find(".mobile-promo-video-preview").addClass("hide").empty();
    $field.find(".mobile-promo-video-dropzone").removeClass("hide");
    $field.find(".ac-media-field__canvas").removeClass("is-filled");
});

$(function () {
    if ($("#logo").hasClass("active")) {
        $("#logo")
            .parent()
            .find('button[type="submit"]')
            .parent()
            .removeClass("col-md-offset-2");
    }
});
