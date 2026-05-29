import Chart from "chart.js/auto";

function initMemberActivityChart() {
    const canvas = document.getElementById("loyalty-member-activity-chart");
    const data = window.LoyaltyMemberAnalytics;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: data.bookings_label,
                    data: data.bookings,
                    backgroundColor: "rgba(124, 58, 237, 0.75)",
                    borderRadius: 6,
                    stack: "activity",
                },
                {
                    label: data.purchases_label,
                    data: data.purchases,
                    backgroundColor: "rgba(0, 104, 225, 0.75)",
                    borderRadius: 6,
                    stack: "activity",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { boxWidth: 12, padding: 16 },
                },
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                },
            },
        },
    });
}

function initMemberSpendChart() {
    const canvas = document.getElementById("loyalty-member-spend-chart");
    const data = window.LoyaltyMemberAnalytics;

    if (!canvas || !data?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: data.spend_label,
                    data: data.spend,
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
                            return data.spend_formatted?.[context.dataIndex] ?? "";
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

export function initMemberPurchaseAnalytics() {
    if (!document.querySelector(".loyalty-member-analytics")) {
        return;
    }

    initMemberActivityChart();
    initMemberSpendChart();
}

document.addEventListener("DOMContentLoaded", initMemberPurchaseAnalytics);
