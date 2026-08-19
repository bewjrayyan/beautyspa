import Chart from "chart.js/auto";

async function fetchSalesAnalyticsData() {
    const response = await axios.get("/sales-analytics");

    let data = {
        labels: response.data.labels,
        sales: [],
        formatted: [],
        totalOrders: [],
    };

    for (let item of response.data.data) {
        data.sales.push(item.total.amount);
        data.formatted.push(item.total.formatted);
        data.totalOrders.push(item.total_orders);
    }

    initSalesAnalyticsChart(data);
}

fetchSalesAnalyticsData();

function initSalesAnalyticsChart(data) {
    const ctx = document.querySelector(".sales-analytics .chart");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.sales,
                    borderRadius: 6,
                    backgroundColor: "rgba(99, 102, 241, 0.7)",
                    hoverBackgroundColor: "rgba(99, 102, 241, 0.9)",
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: false,
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label(item) {
                            let orders = `${trans(
                                "admin::dashboard.sales_analytics.orders"
                            )}: ${data.totalOrders[item.dataIndex]}`;

                            let sales = `${trans(
                                "admin::dashboard.sales_analytics.sales"
                            )}: ${data.formatted[item.dataIndex]}`;

                            return [orders, sales];
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: { size: 11 },
                        maxRotation: 45,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0, 0, 0, 0.04)",
                    },
                    ticks: {
                        font: { size: 11 },
                        callback: function (value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + "M";
                            if (value >= 1000) return (value / 1000).toFixed(0) + "K";
                            return value;
                        },
                    },
                },
            },
        },
    });
}
