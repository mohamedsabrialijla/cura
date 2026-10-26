import axios from "axios";

const baseOrigin =
    (import.meta.env.VITE_API_BASE_URL || "").trim().replace(/\/+$/, "") ||
    window.location.origin.replace(/\/+$/, "");

let locale = localStorage.getItem("locale") || "en";

// مسارات لوحة التحكم API: /{locale}/web/...
axios.defaults.baseURL = `${baseOrigin}/${locale}/web/`;

delete axios.defaults.headers.common["X-Requested-With"];

// إعداد رمز التفويض
const token = localStorage.getItem("authToken") || localStorage.getItem("token");
if (token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
} else {
    delete axios.defaults.headers.common["Authorization"];
}

const api = axios.create({
    baseURL: `${baseOrigin}/${locale}/web/`,
    headers: {
        "Accept-Language": locale,
    },
});
 
function setLanguage(newLocale) {
    locale = newLocale;
    localStorage.setItem("locale", newLocale);

    axios.defaults.headers.common["Accept-Language"] = newLocale;
    api.defaults.headers["Accept-Language"] = newLocale;

    const newBaseURL = `${baseOrigin}/${newLocale}/web/`;
    axios.defaults.baseURL = newBaseURL;
    api.defaults.baseURL = newBaseURL;
}
 
// إعداد اللغة
axios.defaults.headers.common["Accept-Language"] = locale;

// اعتراض استجابات Axios
axios.interceptors.response.use(
    (response) => {
        return response; // تمرير الاستجابة الأصلية
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            const currentPath = window.location.pathname || "";
            const loc = localStorage.getItem("locale") || locale || "ar";

            if (currentPath.includes("/admin/login")) {
                return Promise.reject(error);
            }

            if (currentPath.includes("/dashboard") || currentPath.includes("/admin")) {
                window.location.assign(`/${loc}/admin/login`);
            } else {
                window.location.assign(`/${loc}/login`);
            }
        }
        return Promise.reject(error);
    }
);

export { api, setLanguage };
