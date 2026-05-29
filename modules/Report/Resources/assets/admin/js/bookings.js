$("form").on("submit", (e) => {
    $(e.currentTarget)
        .find(":input")
        .filter((i, el) => !el.value)
        .attr("disabled", "disabled");
});

$("#report-type").on("change", (e) => {
    const form = e.currentTarget.closest("form");

    if (form) {
        form.submit();
    }
});
