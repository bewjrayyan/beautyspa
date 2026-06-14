import Chart from "chart.js/auto";

function getAnalyticsRoot() {
    return document.getElementById("tr-analytics");
}

function getAnalyticsLabels() {
    const root = getAnalyticsRoot();

    return {
        revenue: root?.dataset.chartLabelRevenue || "Revenue",
        bookings: root?.dataset.chartLabelBookings || "Bookings",
        empty: root?.dataset.chartEmpty || "No data for this period yet.",
        revenueTrendEmpty: root?.dataset.chartRevenueTrendEmpty || "No completed revenue in this period yet.",
    };
}

function showChartEmptyState(canvas, message) {
    const container = canvas?.closest(".tr-analytics-chart__canvas, .tr-crm-chart__canvas");

    if (!container) {
        return;
    }

    container.classList.add("is-empty");

    let emptyState = container.querySelector(".tr-analytics-chart__empty");

    if (!emptyState) {
        emptyState = document.createElement("div");
        emptyState.className = "tr-analytics-chart__empty tr-crm-chart__empty";
        container.appendChild(emptyState);
    }

    emptyState.innerHTML = `
        <i class="fa fa-bar-chart" aria-hidden="true"></i>
        <p>${message}</p>
    `;
    emptyState.hidden = false;
    canvas.hidden = true;
}

function clearChartEmptyState(canvas) {
    const container = canvas?.closest(".tr-analytics-chart__canvas, .tr-crm-chart__canvas");

    if (!container) {
        return;
    }

    container.classList.remove("is-empty");
    container.querySelector(".tr-analytics-chart__empty")?.remove();
    canvas.hidden = false;
}

function hasChartValues(values = []) {
    return Array.isArray(values) && values.some((value) => Number(value) > 0);
}

function initRevenueTrendChart() {
    const canvas = document.getElementById("tr-revenue-trend-chart");
    const data = window.TRAnalytics?.revenueTrend;
    const labels = getAnalyticsLabels();

    if (!canvas || !data?.labels?.length) {
        return;
    }

    if (!hasChartValues(data.amounts)) {
        showChartEmptyState(canvas, labels.revenueTrendEmpty);

        return;
    }

    clearChartEmptyState(canvas);

    new Chart(canvas, {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: labels.revenue,
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
                            return `${data.currency || ""}${value}`;
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
    const labels = getAnalyticsLabels();

    if (!canvas || !data?.labels?.length) {
        return;
    }

    if (!hasChartValues(data.counts)) {
        showChartEmptyState(canvas, labels.empty);

        return;
    }

    clearChartEmptyState(canvas);

    new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.counts,
                    backgroundColor: ["#ea580c", "#4338ca", "#047857", "#94a3b8"],
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "62%",
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
    const labels = getAnalyticsLabels();
    const titleEl = canvas?.closest(".tr-analytics-chart")?.querySelector("h5");

    if (!canvas) {
        return;
    }

    if (titleEl && data?.title) {
        titleEl.textContent = data.title;
    }

    if (!data?.labels?.length) {
        showChartEmptyState(canvas, labels.empty);

        return;
    }

    if (!hasChartValues(data.amounts)) {
        showChartEmptyState(canvas, labels.empty);

        return;
    }

    clearChartEmptyState(canvas);

    const isRevenueMetric = data.metric !== "bookings";
    const datasetLabel = isRevenueMetric ? labels.revenue : labels.bookings;

    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: datasetLabel,
                    data: data.amounts,
                    backgroundColor: isRevenueMetric ? "#4338ca" : "#0ea5e9",
                    borderRadius: 8,
                    maxBarThickness: 56,
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
                            const formatted = data.formatted?.[context.dataIndex];

                            if (formatted) {
                                return `${datasetLabel}: ${formatted}`;
                            }

                            return `${datasetLabel}: ${context.parsed.y ?? 0}`;
                        },
                        afterLabel(context) {
                            if (!isRevenueMetric || !data.bookingCounts?.length) {
                                return "";
                            }

                            const count = data.bookingCounts[context.dataIndex] ?? 0;

                            return `${labels.bookings}: ${count}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 0,
                        autoSkip: false,
                        callback(value, index) {
                            const label = data.labels[index] || "";

                            return label.length > 16 ? `${label.slice(0, 15)}…` : label;
                        },
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: isRevenueMetric ? undefined : 0,
                        callback(value) {
                            if (isRevenueMetric) {
                                return `${data.currency || ""}${value}`;
                            }

                            return value;
                        },
                    },
                },
            },
        },
    });
}

export function initTreatmentAnalytics() {
    if (!getAnalyticsRoot() || !window.TRAnalytics) {
        return;
    }

    initRevenueTrendChart();
    initStatusBreakdownChart();
    initRevenueByBeauticianChart();
}
