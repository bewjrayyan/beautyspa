export function initJobTitleSelectize(titleSelect) {
    if (!titleSelect || titleSelect.selectize || !window.jQuery?.fn?.selectize) {
        return titleSelect?.selectize ?? null;
    }

    const $title = window.jQuery(titleSelect);

    $title.selectize({
        create: true,
        allowEmptyOption: true,
        selectOnTab: true,
        openOnFocus: true,
        copyClassesToDropdown: false,
        plugins: ["remove_button", "restore_on_backspace"],
    });

    const control = $title[0].selectize;

    control.$control.removeClass("form-control custom-select-black");

    const $wrapper = control.$control.closest(".selectize-control");
    const $caret = window.jQuery(
        '<button type="button" class="bp-job-title-caret" tabindex="-1" aria-label="Toggle job titles">'
            + '<i class="fa fa-chevron-down"></i></button>'
    );

    $wrapper.append($caret);

    $caret.on("mousedown", (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (control.isOpen) {
            control.close();
        } else {
            control.open();
        }
    });

    control.on("dropdown_open", () => {
        $caret.find("i").removeClass("fa-chevron-down").addClass("fa-chevron-up");
    });

    control.on("dropdown_close", () => {
        $caret.find("i").removeClass("fa-chevron-up").addClass("fa-chevron-down");
    });

    return control;
}
