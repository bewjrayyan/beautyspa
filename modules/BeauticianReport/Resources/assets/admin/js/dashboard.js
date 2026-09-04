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
                    label: window.BeauticianReportCharts.salesLabel,
                    data: data.amounts,
                    borderColor: "#ab0d58",
                    backgroundColor: "rgba(171, 13, 88, 0.10)",
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2.5,
                    pointBackgroundColor: "#ab0d58",
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

                            return [window.BeauticianReportCharts.salesLabel + ": " + formatted, window.BeauticianReportCharts.ordersLabel + ": " + orders];
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
                        "#ab0d58",
                        "#6d4088",
                        "#d15b76",
                        "#d79544",
                        "#3d8b73",
                        "#3978aa",
                        "#8e78b2",
                        "#a19aa4",
                    ],
                    borderColor: "#ffffff",
                    borderWidth: 3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: { usePointStyle: true, pointStyle: "circle", boxWidth: 7, boxHeight: 7, padding: 14 },
                },
            },
        },
    });
}

initSalesTrendChart();
initSalesByBeauticianChart();
