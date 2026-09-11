import "flatpickr";
import "mousetrap";
import "./AestheticCart";
import "./jquery.keypressAction";
import "./vendors/axios";

import Admin, { bindConfirmationModal } from "./Admin";
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

const isolatedEditorPattern = /(?:^|\/)admin\/(products|blog\/posts)\/(create|\d+\/edit)\/?$/;
const isIsolatedEditor = isolatedEditorPattern.test(window.location.pathname);

if (!isIsolatedEditor) {
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

function bootAdminNotifications() {
    bootFlashes();

    if (isIsolatedEditor) {
        bindConfirmationModal();
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bootAdminNotifications, { once: true });
} else {
    bootAdminNotifications();
}
