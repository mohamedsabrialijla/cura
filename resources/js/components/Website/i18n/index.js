import { reactive } from "vue";
import messages from "./messages.js";

export const LOCALES = ["ar", "en"];
export const DEFAULT_LOCALE = "en";
const STORAGE_KEY = "site_lang";

const state = reactive({ locale: DEFAULT_LOCALE });

export function savedLocale() {
    const saved = localStorage.getItem(STORAGE_KEY);
    return LOCALES.includes(saved) ? saved : DEFAULT_LOCALE;
}

export function currentLocale() {
    return state.locale;
}

export function resolveLocale(route) {
    const fromRoute = route?.params?.locale;
    if (LOCALES.includes(fromRoute)) return fromRoute;
    return LOCALES.includes(state.locale) ? state.locale : savedLocale();
}

export function localePath(route, suffix = "") {
    const extra = suffix ? `/${String(suffix).replace(/^\/+/, "")}` : "";
    return `/${resolveLocale(route)}${extra}`;
}

export function t(key) {
    const dict = messages[state.locale] || messages[DEFAULT_LOCALE];
    return dict[key] ?? messages[DEFAULT_LOCALE][key] ?? key;
}

export function applyLocale(locale) {
    if (!LOCALES.includes(locale)) return;
    state.locale = locale;
    localStorage.setItem(STORAGE_KEY, locale);

    const isAr = locale === "ar";
    const html = document.documentElement;
    html.lang = locale;
    html.dir = isAr ? "rtl" : "ltr";

    const bootstrapCss = document.getElementById("bootstrap-css");
    if (bootstrapCss) {
        const href = isAr
            ? "/site/assets/vendor/bootstrap/css/bootstrap.rtl.min.css"
            : "/site/assets/vendor/bootstrap/css/bootstrap.min.css";
        if (bootstrapCss.getAttribute("href") !== href) bootstrapCss.setAttribute("href", href);
    }

    document.title = t("meta.title");
}

export default {
    install(app) {
        app.config.globalProperties.$t = t;
        app.config.globalProperties.$locale = () => state.locale;
    },
};
