import axios from "axios";

window.axios = axios;

axios.defaults.baseURL = AestheticCart.baseUrl;
axios.defaults.headers.common["X-CSRF-TOKEN"] = AestheticCart.csrfToken;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

axios.interceptors.request.use((config) => {
    const url = config.url;

    if (
        typeof url === "string" &&
        url.startsWith("/") &&
        typeof AestheticCart?.url === "function"
    ) {
        config.url = AestheticCart.url(url);
    }

    return config;
});
