import axios from "axios";

window.axios = axios;

axios.defaults.withCredentials = true;
axios.defaults.xsrfCookieName = "XSRF-TOKEN";
axios.defaults.xsrfHeaderName = "X-XSRF-TOKEN";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

const token = document.querySelector('meta[name="csrf-token"]')?.content;

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token;
}
