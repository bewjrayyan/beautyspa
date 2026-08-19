import Chart from "chart.js/auto";

const BRANCH_COLORS = [
    { bg: "rgba(99, 102, 241, 0.7)",  hover: "rgba(99, 102, 241, 0.9)"  },
    { bg: "rgba(236, 72, 153, 0.7)",  hover: "rgba(236, 72, 153, 0.9)"  },
    { bg: "rgba(16, 185, 129, 0.7)",  hover: "rgba(16, 185, 129, 0.9)"  },
    { bg: "rgba(245, 158, 11, 0.7)",  hover: "rgba(245, 158, 11, 0.9)"  },
    { bg: "rgba(59, 130, 246, 0.7)",  hover: "rgba(59, 130, 246, 0.9)"  },
    { bg: "rgba(139, 92, 246, 0.7)",  hover: "rgba(139, 92, 246, 0.9)"  },
    { bg: "rgba(20, 184, 166, 0.7)",  hover: "rgba(20, 184, 166, 0.9)"  },
    { bg: "rgba(239, 68, 68, 0.7)",   hover: "rgba(239, 68, 68, 0.9)"   },
    { bg: "rgba(107, 114, 128, 0.55)",hover: "rgba(107, 114, 128, 0.75)"},
];

async function fetchSalesAnalyticsData() {
    const response = await axios.get("/sales-analytics");
    const payload = response.data;

    if (payload.branches) {
        initBranchChart(payload.labels, payload.branches);
    } else {
        let data = { labels: payload.labels, sales: [], formatted: [], totalOrders: [] };
        for (let item of payload.data) {
            data.sales.push(item.total.amount);
            data.formatted.push(item.total.formatted);
            data.totalOrders.push(item.total_orders);
        }
        initSalesAnalyticsChart(data);
    }
}

fetchSalesAnalyticsData();

function initBranchChart(labels, branches) {
    const ctx = document.querySelector(".sales-analytics .chart");

    const datasets = branches.map((branch, i) => {
        const color = BRANCH_COLORS[i % BRANCH_COLORS.length];
        return {
            label: branch.branch_name,
            data: branch.amounts,
            borderRadius: 4,
            backgroundColor: color.bg,
            hoverBackgroundColor: color.hover,
            barPercentage: 0.7,
            categoryPercentage: 0.8,
        };
    });

    new Chart(ctx, {
        type: "bar",
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                    labels: {
                        boxWidth: 14,
                        boxHeight: 14,
                        borderRadius: 3,
                        useBorderRadius: true,
                        padding: 16,
                        font: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    },
                },
                tooltip: {
                    callbacks: {
                        label(item) {
                            const branch = branches[item.datasetIndex];
                            const orders = `${trans("admin::dashboard.sales_analytics.orders")}: ${branch.orders[item.dataIndex]}`;
                            const sales = `${trans("admin::dashboard.sales_analytics.sales")}: ${branch.formatted[item.dataIndex]}`;
                            return [item.dataset.label, orders, sales];
                        },
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 11 }, maxRotation: 45 },
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    grid: { color: "rgba(0, 0, 0, 0.04)" },
                    ticks: {
                        font: { size: 11 },
                        callback(value) {
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
                            let orders = `${trans("admin::dashboard.sales_analytics.orders")}: ${data.totalOrders[item.dataIndex]}`;
                            let sales = `${trans("admin::dashboard.sales_analytics.sales")}: ${data.formatted[item.dataIndex]}`;
                            return [orders, sales];
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, maxRotation: 45 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: "rgba(0, 0, 0, 0.04)" },
                    ticks: {
                        font: { size: 11 },
                        callback(value) {
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
