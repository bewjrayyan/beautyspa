import Chart from "chart.js/auto";

async function initSalesTrendChart() {
    const canvas = document.getElementById("br-sales-trend-chart");

    if (!canvas || !window.BeauticianReportCharts?.salesTrendUrl) {
        return;
    }

    const response = await axios.get(window.BeauticianReportCharts.salesTrendUrl);
    const data = response.data;

    new Chart(canvas, {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: "Sales",
                    data: data.amounts,
                    borderColor: "#4f46e5",
                    backgroundColor: "rgba(79, 70, 229, 0.12)",
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: "#4f46e5",
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
                            const formatted = data.formatted[context.dataIndex] ?? "";
                            const orders = data.orders[context.dataIndex] ?? 0;

                            return [`Sales: ${formatted}`, `Orders: ${orders}`];
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

function initSalesByBeauticianChart() {
    const canvas = document.getElementById("br-sales-by-beautician-chart");
    const chartData = window.BeauticianReportCharts?.byBeautician;

    if (!canvas || !chartData?.labels?.length) {
        return;
    }

    new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    data: chartData.amounts,
                    backgroundColor: [
                        "#4f46e5",
                        "#7c3aed",
                        "#a855f7",
                        "#ec4899",
                        "#f43f5e",
                        "#f97316",
                        "#eab308",
                        "#22c55e",
                    ],
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

initSalesTrendChart();
initSalesByBeauticianChart();
