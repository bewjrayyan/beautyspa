import MediaPicker from "./MediaPicker";

export default class ImagePicker {
    constructor() {
        this.bindBrowse();
        this.bindRemove();
        this.bindDropzones();
        this.sortable();
    }

    bindBrowse() {
        $(document).on("click", ".image-picker-browse, .image-picker", (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.pickImage(e);
        });
    }

    bindRemove() {
        $(document).on("click", ".image-holder-wrapper .remove-image, .ac-media-preview .remove-image", (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.removeImage(e);
        });
    }

    bindDropzones() {
        $(document).on("dragenter dragover", ".ac-media-dropzone", (e) => {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).addClass("is-dragover");
        });

        $(document).on("dragleave dragend drop", ".ac-media-dropzone", (e) => {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).removeClass("is-dragover");
        });

        $(document).on("drop", ".ac-media-dropzone", (e) => {
            const files = e.originalEvent?.dataTransfer?.files;

            if (! files?.length) {
                return;
            }

            const $field = $(e.currentTarget).closest(".ac-media-field");
            const multiple = $field.hasClass("ac-media-field--multiple");

            if (multiple) {
                Array.from(files).forEach((file) => this.uploadFile(file, $field, true));
            } else {
                this.uploadFile(files[0], $field, false);
            }
        });

        $(document).on("click", ".ac-media-dropzone", (e) => {
            if ($(e.target).closest(".image-picker-browse").length) {
                return;
            }

            $(e.currentTarget).find(".ac-media-dropzone__file").trigger("click");
        });

        $(document).on("keydown", ".ac-media-dropzone", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                $(e.currentTarget).find(".ac-media-dropzone__file").trigger("click");
            }
        });

        $(document).on("change", ".ac-media-dropzone__file", (e) => {
            const files = e.currentTarget.files;

            if (! files?.length) {
                return;
            }

            const $field = $(e.currentTarget).closest(".ac-media-field");
            const multiple = $field.hasClass("ac-media-field--multiple");

            if (multiple) {
                Array.from(files).forEach((file) => this.uploadFile(file, $field, true));
            } else {
                this.uploadFile(files[0], $field, false);
            }

            e.currentTarget.value = "";
        });
    }

    pickImage(e) {
        const $trigger = $(e.currentTarget);
        const inputName = $trigger.data("inputName");
        const multiple = $trigger.is("[data-multiple]");

        const picker = new MediaPicker({ type: "image", multiple });

        picker.on("select", (file) => {
            if (multiple) {
                this.addImage(inputName, file, true, $trigger[0]);
                return;
            }

            const $field = $trigger.closest(".ac-media-field");

            if ($field.length) {
                this.setSingleImage($field, file);
                return;
            }

            this.addImage(inputName, file, false, $trigger[0]);
        });
    }

    uploadFile(file, $field, multiple) {
        if (! this.isAllowedImage(file)) {
            this.notifyError(trans("media::media.invalid_image_type"));

            return;
        }

        const $dropzone = $field.find(".ac-media-dropzone").first();
        $dropzone.addClass("is-uploading");

        const formData = new FormData();
        formData.append("file", file);

        $.ajax({
            url: `${AestheticCart.baseUrl}/admin/media`,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-CSRF-TOKEN": AestheticCart.csrfToken,
                Accept: "application/json",
            },
            success: (response) => {
                if (multiple) {
                    this.addImage($field.data("inputName"), response, true, $field[0]);
                } else {
                    this.setSingleImage($field, response);
                }

                if (typeof window.scheduleSettingsFormBaseline === "function") {
                    window.scheduleSettingsFormBaseline(300);
                }
            },
            error: (xhr) => {
                const message =
                    xhr.responseJSON?.message
                    || xhr.responseJSON?.errors?.file?.[0]
                    || trans("core::messages.something_went_wrong");

                this.notifyError(message);
            },
            complete: () => {
                $dropzone.removeClass("is-uploading");
            },
        });
    }

    isAllowedImage(file) {
        return /^image\/(jpeg|png|gif|webp|svg\+xml)$/i.test(file.type);
    }

    setSingleImage($field, file) {
        const inputName = $field.data("inputName");
        const html = this.getTemplate(inputName, file);

        $field.find(".ac-media-field__canvas").addClass("is-filled");
        $field.find(".ac-media-dropzone").addClass("hide");
        $field.find(".ac-media-preview, .single-image.image-holder-wrapper").removeClass("hide").html(html);
    }

    addImage(inputName, file, multiple, target) {
        const html = this.getTemplate(inputName, file);

        if (multiple) {
            const $wrapper = $(target).closest(".ac-media-field, .multiple-images-wrapper");
            const $grid = $wrapper.find(".image-list, .ac-media-preview-grid");

            $grid.find(".ac-media-preview-grid__empty, .image-holder.placeholder").remove();
            $grid.append(html);

            return;
        }

        const $field = $(target).closest(".ac-media-field");

        if ($field.length) {
            this.setSingleImage($field, file);

            return;
        }

        $(target).siblings(".single-image").html(html);
    }

    getTemplate(inputName, file) {
        return `
            <div class="ac-media-preview__inner image-holder">
                <img src="${file.path}" alt="">

                <button
                    type="button"
                    class="ac-media-preview__remove remove-image"
                    data-input-name="${inputName}"
                    aria-label="${trans("media::media.remove_image")}"
                >
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>

                <div class="ac-media-preview__overlay">
                    <button type="button" class="btn btn-default btn-sm image-picker-browse" data-input-name="${inputName}">
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                        ${trans("media::media.replace_image")}
                    </button>
                </div>

                <input type="hidden" name="${inputName}" value="${file.id}">
            </div>
        `;
    }

    removeImage(e) {
        const $button = $(e.currentTarget);
        const inputName = $button.data("inputName");
        const $field = $button.closest(".ac-media-field");

        if ($field.hasClass("ac-media-field--multiple")) {
            const $grid = $button.closest(".image-list, .ac-media-preview-grid");

            $button.closest(".ac-media-preview__inner, .image-holder").remove();

            if ($grid.find(".ac-media-preview__inner, .image-holder").not(".placeholder").length === 0) {
                $grid.html(this.getMultiplePlaceholder());
            }

            return;
        }

        if ($field.length) {
            $field.find(".ac-media-field__canvas").removeClass("is-filled");
            $field.find(".ac-media-dropzone").removeClass("hide");
            $field.find(".ac-media-preview, .single-image.image-holder-wrapper").addClass("hide").empty();

            if (typeof window.scheduleSettingsFormBaseline === "function") {
                window.scheduleSettingsFormBaseline(300);
            }

            return;
        }

        const imageHolderWrapper = $button.closest(".image-holder-wrapper");

        if (imageHolderWrapper.find(".image-holder").length === 1) {
            imageHolderWrapper.html(this.getImagePlaceholder(inputName));
        }

        $button.closest(".ac-media-preview__inner, .image-holder").remove();
    }

    getImagePlaceholder(inputName) {
        return `
            <div class="image-holder placeholder cursor-auto">
                <i class="fa fa-picture-o" aria-hidden="true"></i>
                <input type="hidden" name="${inputName}" value="">
            </div>
        `;
    }

    getMultiplePlaceholder() {
        return `
            <div class="image-holder placeholder cursor-auto ac-media-preview-grid__empty">
                <i class="fa fa-picture-o" aria-hidden="true"></i>
            </div>
        `;
    }

    sortable() {
        $(".image-list, .ac-media-preview-grid").each((_, element) => {
            if (element.dataset.sortableBound === "1") {
                return;
            }

            Sortable.create(element, { animation: 150, draggable: ".ac-media-preview__inner, .image-holder:not(.placeholder)" });
            element.dataset.sortableBound = "1";
        });
    }

    notifyError(message) {
        if (typeof window.error === "function") {
            window.error(message);

            return;
        }

        window.alert(message);
    }
}
