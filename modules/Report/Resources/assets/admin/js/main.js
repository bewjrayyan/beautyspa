import Chart from "chart.js/auto";
import "./report-product-select.js";

function disableEmptyFields(form) {
    $(form)
        .find(":input")
        .filter((i, el) => {
            return !el.value;
        })
        .attr("disabled", "disabled");
}

$("form").on("submit", (e) => {
    disableEmptyFields(e.currentTarget);
});

$("#report-type").on("change", (e) => {
    const form = e.currentTarget.closest("form");

    if (form) {
        disableEmptyFields(form);
        form.submit();
    }
});

const salesTable = document.getElementById("sales-transaction-table");

if (salesTable) {
    salesTable.querySelectorAll("[data-sales-sort]").forEach((button) => {
        button.addEventListener("click", () => {
            const heading = button.closest("th");
            const columnIndex = Array.from(heading.parentElement.children).indexOf(heading);
            const direction = heading.getAttribute("aria-sort") === "ascending" ? "descending" : "ascending";
            const rows = Array.from(salesTable.tBodies[0]?.rows || []);

            salesTable.querySelectorAll("th[aria-sort]").forEach((column) => {
                column.setAttribute("aria-sort", "none");
                column.querySelector("i")?.classList.replace("fa-sort-up", "fa-sort");
                column.querySelector("i")?.classList.replace("fa-sort-down", "fa-sort");
            });

            heading.setAttribute("aria-sort", direction);
            const icon = button.querySelector("i");
            icon?.classList.remove("fa-sort");
            icon?.classList.add(direction === "ascending" ? "fa-sort-up" : "fa-sort-down");

            rows
                .sort((left, right) => {
                    const leftCell = left.cells[columnIndex];
                    const rightCell = right.cells[columnIndex];
                    const leftValue = leftCell?.dataset.salesValue ?? leftCell?.textContent.trim() ?? "";
                    const rightValue = rightCell?.dataset.salesValue ?? rightCell?.textContent.trim() ?? "";
                    const comparison = button.dataset.salesSort === "number"
                        ? Number(leftValue) - Number(rightValue)
                        : leftValue.localeCompare(rightValue, undefined, { numeric: true, sensitivity: "base" });

                    return direction === "ascending" ? comparison : -comparison;
                })
                .forEach((row) => salesTable.tBodies[0].appendChild(row));
        });
    });
}

const charts = window.ReportDashboardCharts || {};

function initLineChart(canvasId, dataset, borderColor, fillColor) {
    const canvas = document.getElementById(canvasId);

    if (!canvas || !dataset?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "line",
        data: {
            labels: dataset.labels,
            datasets: [
                {
                    data: dataset.amounts,
                    borderColor,
                    backgroundColor: fillColor,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback(value) {
                            return `${dataset.currency || ""}${value}`;
                        },
                    },
                },
            },
        },
    });
}

function formatChartAmount(value, currency = "") {
    return `${currency}${Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function initSalesComparisonChart() {
    const canvas = document.getElementById("report-sales-comparison-chart");
    const store = charts.salesTrend;
    const treatment = charts.treatmentSalesTrend;

    if (!canvas || !store?.labels?.length) {
        return false;
    }

    new Chart(canvas, {
        type: "line",
        data: {
            labels: store.labels,
            datasets: [
                {
                    label: canvas.dataset.storeLabel,
                    data: store.amounts,
                    borderColor: "#3182f6",
                    backgroundColor: "rgba(49, 130, 246, 0.09)",
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.32,
                    pointRadius: 2.5,
                    pointHoverRadius: 5,
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "#3182f6",
                    pointBorderWidth: 2,
                },
                {
                    label: canvas.dataset.treatmentLabel,
                    data: treatment?.amounts || [],
                    borderColor: "#7c5ce7",
                    backgroundColor: "rgba(124, 92, 231, 0.035)",
                    borderWidth: 2.5,
                    borderDash: [7, 4],
                    fill: true,
                    tension: 0.32,
                    pointStyle: "rectRot",
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: "#ffffff",
                    pointBorderColor: "#7c5ce7",
                    pointBorderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label(context) {
                            return ` ${context.dataset.label}: ${formatChartAmount(context.parsed.y, store.currency)}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: "#718096",
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 7,
                    },
                },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: "rgba(124, 135, 152, 0.14)" },
                    ticks: {
                        color: "#718096",
                        callback(value) {
                            return formatChartAmount(value, store.currency);
                        },
                    },
                },
            },
        },
    });

    return true;
}

function initDoughnutChart() {
    const canvas = document.getElementById("report-by-beautician-chart");
    const data = charts.salesByBeautician;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.amounts,
                    backgroundColor: [
                        "#4f46e5",
                        "#7c3aed",
                        "#ec4899",
                        "#f97316",
                        "#22c55e",
                        "#0ea5e9",
                    ],
                    borderColor: "#ffffff",
                    borderWidth: 3,
                    hoverOffset: 5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "68%",
            plugins: {
                legend: {
                    display: !document.querySelector(".report-beautician-share__list"),
                    position: "bottom",
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const currency = charts.treatmentSalesTrend?.currency || charts.salesTrend?.currency || "";
                            return ` ${context.label}: ${formatChartAmount(context.parsed, currency)}`;
                        },
                    },
                },
            },
        },
    });
}

const hasComparisonChart = charts.enabled && initSalesComparisonChart();

if (charts.enabled && !hasComparisonChart && charts.salesTrend?.labels?.length) {
    initLineChart(
        "report-store-sales-chart",
        charts.salesTrend,
        "#475aff",
        "rgba(71, 90, 255, 0.12)"
    );
}

if (charts.enabled && charts.hasBeautician) {
    if (!hasComparisonChart && charts.treatmentSalesTrend?.labels?.length) {
        initLineChart(
            "report-treatment-sales-chart",
            charts.treatmentSalesTrend,
            "#7c3aed",
            "rgba(124, 58, 237, 0.12)"
        );
    }

    initDoughnutChart();
}
