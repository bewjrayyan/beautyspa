import Chart from "chart.js/auto";

function initRevenueTrendChart() {
    const canvas = document.getElementById("tr-revenue-trend-chart");
    const data = window.TRAnalytics?.revenueTrend;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: "Revenue",
                    data: data.amounts,
                    borderColor: "#047857",
                    backgroundColor: "rgba(4, 120, 87, 0.12)",
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: "#047857",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return data.formatted[context.dataIndex] ?? "";
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback(value) {
                            return `${data.currency}${value}`;
                        },
                    },
                },
            },
        },
    });
}

function initStatusBreakdownChart() {
    const canvas = document.getElementById("tr-status-breakdown-chart");
    const data = window.TRAnalytics?.statusBreakdown;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.counts,
                    backgroundColor: ["#ea580c", "#4338ca", "#047857", "#94a3b8"],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                },
            },
        },
    });
}

function initRevenueByBeauticianChart() {
    const canvas = document.getElementById("tr-revenue-by-beautician-chart");
    const data = window.TRAnalytics?.revenueByBeautician;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: "Revenue",
                    data: data.amounts,
                    backgroundColor: "#4338ca",
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return data.formatted[context.dataIndex] ?? "";
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
}

export function initTreatmentAnalytics() {
    if (!document.getElementById("tr-analytics")) {
        return;
    }

    initRevenueTrendChart();
    initStatusBreakdownChart();
    initRevenueByBeauticianChart();
}
