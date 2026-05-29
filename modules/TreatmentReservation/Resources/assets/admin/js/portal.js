import { initBeauticianAvatarLightbox, renderKanbanBeautician } from "./kanban-helpers.js";

const TR_KANBAN_STATUS_ACCENT = {
    pending: "#ea580c",
    in_progress: "#4338ca",
    completed: "#047857",
};

class BeauticianPortalApp {
    static statusAccentColor(status) {
        return TR_KANBAN_STATUS_ACCENT[status] || "#94a3b8";
    }

    constructor(root) {
        this.root = root;
        this.kanbanUrl = root.dataset.kanbanUrl;
        this.statusUrlTemplate = root.dataset.statusUrl;
        this.loadKanban();
    }

    async loadKanban() {
        const response = await axios.get(this.kanbanUrl);
        const columns = response.data.columns || {};

        Object.keys(columns).forEach((status) => {
            const container = document.getElementById(`tr-kanban-${status}`);
            const cards = columns[status] || [];

            container.innerHTML = cards.length
                ? cards.map((card) => this.renderKanbanCard(card)).join("")
                : `<p class="tr-kanban-empty">${this.emptyLabel()}</p>`;

            const countEl = document.querySelector(`[data-count="${status}"]`);

            if (countEl) {
                countEl.textContent = cards.length;
            }

            this.makeSortable(container);
        });
    }

    emptyLabel() {
        return "No tasks";
    }

    renderKanbanCard(card) {
        const template = document.getElementById("tr-kanban-card-template");
        const node = template.content.cloneNode(true);
        const el = node.querySelector(".tr-kanban-card");

        el.dataset.id = card.id;
        el.querySelector(".tr-kanban-card-accent").removeAttribute("style");
        el.querySelector(".tr-kanban-card-customer").textContent = card.customer_name;

        const productName = el.querySelector(".tr-kanban-card-product__name");
        const treatmentLine = el.querySelector(".tr-kanban-card-treatment-line");
        const treatmentValue = el.querySelector(".tr-kanban-card-treatment-line__value");
        const displayName = card.product_name || card.treatment_name || "—";

        if (productName) {
            productName.textContent = displayName;
        } else {
            el.querySelector(".tr-kanban-card-treatment").textContent = displayName;
        }

        if (treatmentLine && treatmentValue) {
            const selection = (card.treatment_selection || "").trim();

            if (selection !== "") {
                treatmentValue.textContent = selection;
                treatmentLine.hidden = false;
            } else {
                treatmentValue.textContent = "";
                treatmentLine.hidden = true;
            }
        }
        el.querySelector(".tr-kanban-card-date span").textContent = card.appointment_date || "";
        el.querySelector(".tr-kanban-card-time-slot span").textContent = card.appointment_time || "";
        renderKanbanBeautician(el, card);

        if (card.order_url) {
            const link = el.querySelector(".tr-kanban-card-link");

            link.href = card.order_url;
            link.hidden = false;
        }

        const wrapper = document.createElement("div");

        wrapper.appendChild(node);

        return wrapper.innerHTML;
    }

    makeSortable(container) {
        if (!window.Sortable || container.dataset.sortableInit) {
            return;
        }

        container.dataset.sortableInit = "1";

        Sortable.create(container, {
            group: "tr-portal-kanban",
            animation: 150,
            draggable: ".tr-kanban-card",
            onEnd: (evt) => this.handleKanbanMove(evt),
        });
    }

    async handleKanbanMove(evt) {
        const card = evt.item;
        const bookingId = card.dataset.id;
        const newStatus = evt.to.closest(".tr-kanban-column")?.dataset?.status;
        const oldStatus = evt.from.closest(".tr-kanban-column")?.dataset?.status;

        if (!bookingId || !newStatus || newStatus === oldStatus) {
            return;
        }

        const url = this.statusUrlTemplate.replace("__ID__", bookingId);

        try {
            await axios.patch(url, { status: newStatus });
            card.querySelector(".tr-kanban-card-accent")?.removeAttribute("style");
            this.updateKanbanCounts();
        } catch (error) {
            evt.from.appendChild(card);
            alert("Failed to update status");
        }
    }

    updateKanbanCounts() {
        ["pending", "in_progress", "completed"].forEach((status) => {
            const container = document.getElementById(`tr-kanban-${status}`);
            const countEl = document.querySelector(`[data-count="${status}"]`);

            if (container && countEl) {
                countEl.textContent = container.querySelectorAll(".tr-kanban-card").length;
            }
        });
    }
}

const root = document.getElementById("tr-portal-app");

if (root) {
    new BeauticianPortalApp(root);
}

initBeauticianAvatarLightbox();
