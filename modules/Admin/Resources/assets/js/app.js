import "flatpickr";
import "mousetrap";
import "./AestheticCart";
import "./jquery.keypressAction";
import "./vendors/axios";

import Admin from "./Admin";
import Form from "./Form";
import DataTable from "./DataTable";
import {
    trans,
    keypressAction,
    notify,
    info,
    success,
    warning,
    error,
} from "./functions";
import SweetNotification, { bootFlashes } from "./SweetNotification";

const regex =
    /^\/[a-z]{2}\/admin\/(products|blog\/posts)\/(create|(\d+)\/edit)$/;

if (!window.location.pathname.match(regex)) {
    window.admin = new Admin();
}

window.form = new Form();
window.DataTable = DataTable;

window.trans = trans;
window.keypressAction = keypressAction;
window.SweetNotification = SweetNotification;
window.notify = Object.assign(notify, {
    success,
    error,
    warning,
    info,
    alert: SweetNotification.alert,
    confirm: SweetNotification.confirm,
    confirmDelete: SweetNotification.confirmDelete,
});
window.confirmDelete = SweetNotification.confirmDelete;
window.info = info;
window.success = success;
window.warning = warning;
window.error = error;

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => bootFlashes());
} else {
    bootFlashes();
}
