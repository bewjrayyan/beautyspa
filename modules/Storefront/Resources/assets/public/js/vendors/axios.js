import axios from "axios";

window.axios = axios;

axios.defaults.baseURL = FleetCart.baseUrl;
axios.defaults.headers.common["X-CSRF-TOKEN"] = FleetCart.csrfToken;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

axios.interceptors.request.use((config) => {
    const url = config.url;

    if (
        typeof url === "string" &&
        url.startsWith("/") &&
        typeof FleetCart?.url === "function"
    ) {
        config.url = FleetCart.url(url);
    }

    return config;
});
