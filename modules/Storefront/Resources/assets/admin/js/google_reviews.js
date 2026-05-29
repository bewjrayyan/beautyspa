function readJsonInput(id) {
    const el = document.getElementById(id);

    if (!el?.value) {
        return [];
    }

    try {
        return JSON.parse(el.value);
    } catch {
        return [];
    }
}

function writeJsonInput(id, data) {
    const el = document.getElementById(id);

    if (el) {
        el.value = JSON.stringify(data);
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function reviewItemTemplate(item, index) {
    return `
        <div class="google-reviews-item box m-b-15" data-index="${index}">
            <div class="row">
                <div class="col-sm-4">
                    <label>Author</label>
                    <input type="text" class="form-control gr-item-author" value="${escapeHtml(item.author || "")}">
                </div>
                <div class="col-sm-3">
                    <label>Date</label>
                    <input type="text" class="form-control gr-item-date" value="${escapeHtml(item.date || "")}" placeholder="18 APR 2025">
                </div>
                <div class="col-sm-2">
                    <label>Rating</label>
                    <input type="number" class="form-control gr-item-rating" min="1" max="5" value="${item.rating || 5}">
                </div>
                <div class="col-sm-2">
                    <label>Likes</label>
                    <input type="number" class="form-control gr-item-likes" min="0" value="${item.likes || 0}">
                </div>
                <div class="col-sm-1 text-right">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block gr-item-remove"><i class="fa fa-trash"></i></button>
                </div>
                <div class="col-sm-12 m-t-10">
                    <label>Review text</label>
                    <textarea class="form-control gr-item-text" rows="3">${escapeHtml(item.text || "")}</textarea>
                </div>
            </div>
        </div>`;
}

function metricItemTemplate(item, index) {
    return `
        <div class="google-reviews-metric box m-b-10" data-index="${index}">
            <div class="row">
                <div class="col-sm-5">
                    <label>Label</label>
                    <input type="text" class="form-control gr-metric-label" value="${escapeHtml(item.label || "")}">
                </div>
                <div class="col-sm-3">
                    <label>Percent</label>
                    <input type="number" class="form-control gr-metric-percent" min="0" max="100" value="${item.percent || 0}">
                </div>
                <div class="col-sm-3">
                    <label>Sentiment</label>
                    <input type="text" class="form-control gr-metric-sentiment" value="${escapeHtml(item.sentiment || "")}" placeholder="Great">
                </div>
                <div class="col-sm-1 text-right">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block gr-metric-remove"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        </div>`;
}

function renderReviewItems(items) {
    const list = document.getElementById("google-reviews-items-list");

    if (!list) {
        return;
    }

    list.innerHTML = items.map((item, index) => reviewItemTemplate(item, index)).join("");
}

function renderMetrics(items) {
    const list = document.getElementById("google-reviews-metrics-list");

    if (!list) {
        return;
    }

    list.innerHTML = items.map((item, index) => metricItemTemplate(item, index)).join("");
}

function collectReviews() {
    return [...document.querySelectorAll(".google-reviews-item")].map((row) => ({
        author: row.querySelector(".gr-item-author")?.value?.trim() || "",
        date: row.querySelector(".gr-item-date")?.value?.trim() || "",
        rating: parseInt(row.querySelector(".gr-item-rating")?.value || "5", 10),
        likes: parseInt(row.querySelector(".gr-item-likes")?.value || "0", 10),
        text: row.querySelector(".gr-item-text")?.value?.trim() || "",
    }));
}

function collectMetrics() {
    return [...document.querySelectorAll(".google-reviews-metric")].map((row) => ({
        label: row.querySelector(".gr-metric-label")?.value?.trim() || "",
        percent: parseInt(row.querySelector(".gr-metric-percent")?.value || "0", 10),
        sentiment: row.querySelector(".gr-metric-sentiment")?.value?.trim() || "",
    }));
}

function syncReviews() {
    writeJsonInput("google-reviews-items-json", collectReviews());
}

function syncMetrics() {
    writeJsonInput("google-reviews-metrics-json", collectMetrics());
}

function bindGoogleReviewsAdmin() {
    const form = document.getElementById("storefront-settings-edit-form");

    if (!form || form.dataset.googleReviewsBound === "1") {
        return;
    }

    form.dataset.googleReviewsBound = "1";

    document.getElementById("google-reviews-add-item")?.addEventListener("click", () => {
        const reviews = collectReviews();

        reviews.push({ author: "", date: "", rating: 5, likes: 0, text: "" });
        renderReviewItems(reviews);
        syncReviews();
    });

    document.getElementById("google-reviews-add-metric")?.addEventListener("click", () => {
        const metrics = collectMetrics();

        metrics.push({ label: "", percent: 50, sentiment: "Good" });
        renderMetrics(metrics);
        syncMetrics();
    });

    document.getElementById("google-reviews-items-list")?.addEventListener("input", syncReviews);
    document.getElementById("google-reviews-items-list")?.addEventListener("click", (e) => {
        if (e.target.closest(".gr-item-remove")) {
            e.target.closest(".google-reviews-item")?.remove();
            syncReviews();
        }
    });

    document.getElementById("google-reviews-metrics-list")?.addEventListener("input", syncMetrics);
    document.getElementById("google-reviews-metrics-list")?.addEventListener("click", (e) => {
        if (e.target.closest(".gr-metric-remove")) {
            e.target.closest(".google-reviews-metric")?.remove();
            syncMetrics();
        }
    });

    form.addEventListener("submit", () => {
        syncReviews();
        syncMetrics();
    });
}

function initGoogleReviewsFromDomIfEmpty() {
    const list = document.getElementById("google-reviews-items-list");

    if (list && list.children.length === 0) {
        renderReviewItems(readJsonInput("google-reviews-items-json"));
    }

    const metricsList = document.getElementById("google-reviews-metrics-list");

    if (metricsList && metricsList.children.length === 0) {
        renderMetrics(readJsonInput("google-reviews-metrics-json"));
    }
}

function bootGoogleReviewsAdmin() {
    initGoogleReviewsFromDomIfEmpty();
    bindGoogleReviewsAdmin();
    syncReviews();
    syncMetrics();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootGoogleReviewsAdmin);
} else {
    bootGoogleReviewsAdmin();
}

$(document).on("shown.bs.tab", 'a[href="#google_reviews"]', bootGoogleReviewsAdmin);
