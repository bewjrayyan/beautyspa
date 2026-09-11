import flatpickr from "flatpickr";
import { buildStandardDatepickerOptions } from "../../../../Storefront/Resources/assets/public/js/lib/flatpickrLocale.js";
import { fullscreenMode } from "./functions";
import NProgress from "nprogress";
import { bootModernPhoneInputs } from "../../../../Storefront/Resources/assets/public/js/lib/modernPhoneInput";

export function bindConfirmationModal() {
    const confirmationModal = $("#confirmation-modal");

    if (
        confirmationModal.length === 0 ||
        confirmationModal.data("sweet-confirmation-bound")
    ) {
        return;
    }

    confirmationModal.data("sweet-confirmation-bound", true);

    let sweetConfirmPending = false;

    const resolveConfirmText = () => {
        const custom = confirmationModal.data("confirm-text");

        if (custom) {
            return custom;
        }

        const bodyText = confirmationModal
            .find(".modal-body .default-message")
            .text()
            .trim();

        return bodyText || undefined;
    };

    const openSweetConfirm = () => {
        if (sweetConfirmPending) {
            return;
        }

        const confirmDelete = window.SweetNotification?.confirmDelete;

        if (typeof confirmDelete !== "function") {
            confirmationModal.modal("show");
            return;
        }

        sweetConfirmPending = true;

        confirmDelete(resolveConfirmText())
            .then((confirmed) => {
                if (confirmed) {
                    confirmationModal.find("form").trigger("submit");
                }
            })
            .finally(() => {
                sweetConfirmPending = false;
                confirmationModal.removeData("confirm-text");
                confirmationModal.find("button.delete").prop("disabled", false);
            });
    };

    confirmationModal.on("show.bs.modal.acSweetConfirm", (event) => {
        if (
            sweetConfirmPending ||
            typeof window.SweetNotification?.confirmDelete !== "function"
        ) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        setTimeout(openSweetConfirm, 0);
    });

    confirmationModal
        .find("form")
        .on("submit.acSweetConfirm", () => {
            confirmationModal.find("button.delete").prop("disabled", true);
        });

    confirmationModal.on("hidden.bs.modal.acSweetConfirm", () => {
        confirmationModal.find("button.delete").prop("disabled", false);
    });

    confirmationModal.on("shown.bs.modal.acSweetConfirm", () => {
        confirmationModal.find("button.delete").focus();
    });
}

export default class {
    constructor() {
        this.selectize();
        this.dateTimePicker();
        bootModernPhoneInputs();
        this.changeAccordionTabState();
        this.preventChangingCurrentTab();
        this.buttonLoading();
        this.confirmationModal();
        this.tooltip();
        this.shortcuts();
        this.nprogress();
        this.setActiveAccordionTabQueryParam();
        this.setDefaultActiveAccordionTabQueryParam();
        this.stopDropdownPropagation();

        fullscreenMode();
    }

    stopDropdownPropagation() {
        $(".dropdown-menu").on("click", (e) => {
            e.stopPropagation();
        });
    }

    selectize() {
        let selects = $("select.selectize").removeClass(
            "form-control custom-select-black"
        );

        let options = _.merge(
            {
                valueField: "id",
                labelField: "name",
                searchField: "name",
                delimiter: ",",
                persist: true,
                selectOnTab: true,
                hideSelected: true,
                allowEmptyOption: true,
                onItemAdd(value) {
                    this.getItem(value)[0].innerHTML = this.getItem(
                        value
                    )[0].innerHTML.replace(/¦––\s/g, "");
                },
                onInitialize() {
                    for (let index in this.options) {
                        let label = this.options[index].name;
                        let value = this.options[index].id;

                        this.$control
                            .find(`.item[data-value="${value}"]`)
                            .html(
                                label.replace(/¦––\s/g, "") +
                                    '<a href="javascript:void(0)" class="remove" tabindex="-1">×</a>'
                            );
                    }
                },
            },
            ...AestheticCart.selectize
        );

        for (let select of selects) {
            select = $(select);

            let create = true;
            let plugins = ["remove_button", "restore_on_backspace"];

            if (select.hasClass("prevent-creation")) {
                create = false;

                plugins.remove("restore_on_backspace");
            }

            select.selectize(_.merge(options, { create, plugins }));
        }
    }

    dateTimePicker(elements) {
        elements = elements || $(".datetime-picker");

        elements = elements instanceof jQuery ? elements : $(elements);

        for (let el of elements) {
            if (el._flatpickr) {
                continue;
            }

            flatpickr(el, buildStandardDatepickerOptions(el));
        }
    }

    changeAccordionTabState() {
        $('.accordion-box [data-toggle="tab"]').on("click", (e) => {
            if (!$(e.currentTarget).parent().hasClass("active")) {
                $(".accordion-tab li.active").removeClass("active");
            }
        });
    }

    preventChangingCurrentTab() {
        $('[data-toggle="tab"]').on("click", (e) => {
            let targetElement = $(e.currentTarget);

            if (targetElement.parent().hasClass("active")) {
                return false;
            }
        });
    }

    removeSubmitButtonOffsetOn(tabs, tabsSelector = null) {
        tabs = Array.isArray(tabs) ? tabs : [tabs];
        $(tabsSelector || ".accordion-tab li > a").on("click", (e) => {
            if (tabs.includes(e.currentTarget.getAttribute("href"))) {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .removeClass("col-md-offset-2");
                }, 150);
            } else {
                setTimeout(() => {
                    $("button[type=submit]")
                        .parent()
                        .addClass("col-md-offset-2");
                }, 150);
            }
        });
    }

    buttonLoading() {
        $(document).on("click", "[data-loading]", (e) => {
            let button = $(e.currentTarget);

            button
                .data("loading-text", button.html())
                .addClass("btn-loading")
                .button("loading");
        });
    }

    stopButtonLoading(button) {
        button = button instanceof jQuery ? button : $(button);

        button
            .data("loading-text", button.html())
            .removeClass("btn-loading")
            .button("reset");
    }

    confirmationModal() {
        bindConfirmationModal();
    }

    tooltip() {
        $('[data-toggle="tooltip"]')
            .tooltip({ trigger: "hover" })
            .on("click", (e) => {
                $(e.currentTarget).tooltip("hide");
            });
    }

    shortcuts() {
        Mousetrap.bind("?", () => {
            $("#keyboard-shortcuts-modal").modal();
        });
    }

    nprogress() {
        NProgress.configure({ showSpinner: false });

        $(document).ajaxStart(() => NProgress.start());
        $(document).ajaxComplete(() => NProgress.done());
    }

    setActiveAccordionTabQueryParam() {
        $('.accordion-box a[data-toggle="tab"]').on("click", (e) => {
            const target = $(e.currentTarget);
            const targetForm = target.closest("form");
            const queryParam = `?tab=${target.attr("href").slice(1)}`;

            targetForm.attr(
                "action",
                `${targetForm.attr("action").split("?")[0]}${queryParam}`
            );

            history.replaceState(
                null,
                null,
                `${window.location.pathname}${queryParam}`
            );
        });
    }

    setDefaultActiveAccordionTabQueryParam() {
        const activeAccordionTab = $(".accordion-tab li.active a");

        if (activeAccordionTab.length !== 0) {
            const activeAccordionTabForm = activeAccordionTab.closest("form");
            const queryParam = `?tab=${activeAccordionTab
                .attr("href")
                .slice(1)}`;

            activeAccordionTabForm.attr(
                "action",
                `${activeAccordionTabForm.attr("action")}${queryParam}`
            );

            history.replaceState(
                null,
                null,
                `${window.location.pathname}${queryParam}`
            );
        }
    }
}
