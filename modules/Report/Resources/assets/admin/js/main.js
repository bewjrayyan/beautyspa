import Chart from "chart.js/auto";
import "./report-product-select.js";

$("form").on("submit", (e) => {
    $(e.currentTarget)
        .find(":input")
        .filter((i, el) => {
            return !el.value;
        })
        .attr("disabled", "disabled");
});

$("#report-type").on("change", (e) => {
    const form = e.currentTarget.closest("form");

    if (form) {
        form.submit();
    }
});

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
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: "bottom" } },
        },
    });
}

if (charts.enabled && charts.salesTrend?.labels?.length) {
    initLineChart(
        "report-store-sales-chart",
        charts.salesTrend,
        "#475aff",
        "rgba(71, 90, 255, 0.12)"
    );
}

if (charts.enabled && charts.hasBeautician && charts.treatmentSalesTrend?.labels?.length) {
    initLineChart(
        "report-treatment-sales-chart",
        charts.treatmentSalesTrend,
        "#7c3aed",
        "rgba(124, 58, 237, 0.12)"
    );
    initDoughnutChart();
}
