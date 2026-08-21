import {
    notify as sweetNotify,
    success as sweetSuccess,
    error as sweetError,
    warning as sweetWarning,
    info as sweetInfo,
} from "./SweetNotification";

export function trans(langKey, replace = {}) {
    let line = window.AestheticCart.langs[langKey];

    if (line === undefined || line === null) {
        return langKey;
    }

    for (let key in replace) {
        line = line.replace(`:${key}`, replace[key]);
    }

    return line;
}

export function hasAccess(permission) {
    return Object.keys(AestheticCart.permissions).includes(permission);
}

export function generateUid() {
    const timestamp = Math.floor(Math.random() * Date.now()).toString(36);
    const randomPart = Math.random().toString(36).substring(2, 8);

    return (timestamp + randomPart).substring(0, 12);
}

export function fullscreenMode() {
    $(".fullscreen-mode-open").on("click", (e) => {
        e.preventDefault();

        if (!document.fullscreenElement) {
            $(".fullscreen-mode-open .fullscreen-one").removeClass(
                "exit-full-screen"
            );
            $(".fullscreen-mode-open .fullscreen-two").addClass(
                "enter-full-screen"
            );

            document.documentElement.requestFullscreen().catch((err) => {
                alert(
                    `Error attempting to enable full-screen mode: ${err.message} (${err.name})`
                );
            });
        } else {
            $(".fullscreen-mode-open .fullscreen-two").removeClass(
                "enter-full-screen"
            );
            $(".fullscreen-mode-open .fullscreen-one").addClass(
                "exit-full-screen"
            );

            document.exitFullscreen().catch((err) => {
                alert(
                    `Error attempting to disable full-screen mode: ${err.message} (${err.name})`
                );
            });
        }
    });
}

export function keypressAction(actions) {
    $(document).keypressAction({ actions });
}

export function notify(type, message, options = {}) {
    return sweetNotify(type, message, options);
}

export function info(message, duration) {
    return sweetInfo(message, typeof duration === "number" ? { duration } : duration || {});
}

export function success(message, duration) {
    return sweetSuccess(message, typeof duration === "number" ? { duration } : duration || {});
}

export function warning(message, duration) {
    return sweetWarning(message, typeof duration === "number" ? { duration } : duration || {});
}

export function error(message, duration) {
    return sweetError(message, typeof duration === "number" ? { duration } : duration || {});
}

export function generateSlug(name) {
    let slug = "";

    // Change to lower case
    const nameLower = name.toLowerCase();

    // Letter "e"
    slug = nameLower.replace(/e|é|è|ẽ|ẻ|ẹ|ê|ế|ề|ễ|ể|ệ/gi, "e");
    // Letter "a"
    slug = slug.replace(/a|á|à|ã|ả|ạ|ă|ắ|ằ|ẵ|ẳ|ặ|â|ấ|ầ|ẫ|ẩ|ậ/gi, "a");
    // Letter "o"
    slug = slug.replace(/o|ó|ò|õ|ỏ|ọ|ô|ố|ồ|ỗ|ổ|ộ|ơ|ớ|ờ|ỡ|ở|ợ/gi, "o");
    // Letter "u"
    slug = slug.replace(/u|ú|ù|ũ|ủ|ụ|ư|ứ|ừ|ữ|ử|ự/gi, "u");
    // Letter "c"
    slug = slug.replace(/ć|ĉ|č|ċ|ç/gi, "c");
    // Letter "i"
    slug = slug.replace(/î|ï|í|ī|į|ì/gi, "i");
    // Letter (/, ', ")
    slug = slug.replace(/\/|'|"|′|’|,|\?|\.|;|]|\[|\+|=|\$|%|&|<|>|:/g, " ");
    // Letter "d"
    slug = slug.replace(/đ/gi, "d");
    // Trim the last whitespace
    slug = slug.replace(/\s*$/g, "");
    // Change whitespace to "-"
    slug = slug.replace(/\s+/g, "-");

    return slug;
}

/**
 * @see https://stackoverflow.com/a/3955096
 */
if (!Array.prototype.remove) {
    Array.prototype.remove = function () {
        let what,
            a = arguments,
            L = a.length,
            ax;

        while (L && this.length) {
            what = a[--L];

            while ((ax = this.indexOf(what)) !== -1) {
                this.splice(ax, 1);
            }
        }

        return this;
    };
}

/**
 * @see https://stackoverflow.com/a/4673436
 */
if (!String.prototype.format) {
    String.prototype.format = function () {
        return this.replace(/%(\d+)%/g, (match, number) => {
            return typeof arguments[number] !== "undefined"
                ? arguments[number]
                : match;
        });
    };
}
