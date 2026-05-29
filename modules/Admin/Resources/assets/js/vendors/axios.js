import axios from "axios";

window.axios = axios;

axios.defaults.baseURL = `${AestheticCart.baseUrl}/admin`;
axios.defaults.headers.common["X-CSRF-TOKEN"] = AestheticCart.csrfToken;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
