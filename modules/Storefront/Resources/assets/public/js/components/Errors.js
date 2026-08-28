export default class {
    constructor() {
        this.errors = {};
    }

    record(errors, replace = false) {
        if (replace) {
            this.errors = errors;
        } else {
            Object.assign(this.errors, errors);
        }
    }

    any() {
        return Object.keys(this.errors).length > 0;
    }

    has(key) {
        const normalized = this.normalizeKey(key);
        return Object.prototype.hasOwnProperty.call(this.errors, normalized)
            || Object.prototype.hasOwnProperty.call(this.errors, key);
    }

    get(key) {
        const normalized = this.normalizeKey(key);
        const value = this.errors[normalized] ?? this.errors[key];

        if (!value) {
            return undefined;
        }

        return Array.isArray(value) ? value[0] : value;
    }

    clear(key) {
        if (key === undefined) {
            return;
        }

        const normalized = this.normalizeKey(key);
        delete this.errors[normalized];
        delete this.errors[key];
    }

    reset() {
        this.errors = {};
    }

    normalizeKey(key) {
        let keyParts = key.replace("[]", "").split("[");

        if (keyParts.length === 1) {
            return key;
        }

        return keyParts.join(".").slice(0, -1);
    }
}
