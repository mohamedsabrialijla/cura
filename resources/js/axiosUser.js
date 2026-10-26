import axios from "axios";
import router from "./routes";
import store from "./storeUser";

const baseOrigin =
    (import.meta.env.VITE_API_BASE_URL || "").trim().replace(/\/+$/, "") ||
    window.location.origin.replace(/\/+$/, "");

let locale = localStorage.getItem("locale") || "en";
delete axios.defaults.headers.common["X-Requested-With"];

// axios.defaults.baseURL = `https://www.brandy.sa/${locale}/user/`;
// axios.defaults.baseURL = `https://brandy.sa/${locale}/user/`;
axios.defaults.baseURL = `${baseOrigin}/${locale}/user/`;

axios.defaults.headers.common["Accept-Language"] = locale;



const token = localStorage.getItem("authToken");
if (token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
} 




const api = axios.create({
    // baseURL: `https://www.brandy.sa/${locale}/user/`,
    // baseURL: `https://brandy.sa/${locale}/user/`,
    baseURL: `${baseOrigin}/${locale}/user/`,
    headers: {
        "Accept-Language": locale,
    },

}); 

function setLanguage(newLocale) {
    locale = newLocale;
    localStorage.setItem("locale", newLocale);

    axios.defaults.headers.common["Accept-Language"] = newLocale;
    api.defaults.headers["Accept-Language"] = newLocale;

    // const newBaseURL = `https://www.brandy.sa/${newLocale}/user/`;
    // const newBaseURL = `https://brandy.sa/${newLocale}/user/`;
    const newBaseURL = `${baseOrigin}/${newLocale}/user/`;



    axios.defaults.baseURL = newBaseURL;
    api.defaults.baseURL = newBaseURL;
}

const authApi = (config = {}) => {
    const token = localStorage.getItem("authToken") || localStorage.getItem("token");
    if (token) {
        config.headers = {
            ...config.headers,
            Authorization: `Bearer ${token}`,
            "Accept-Language": locale,
        };
    } else {
        config.headers = {
            ...config.headers,
            "Accept-Language": locale,
        };
    }
    return api(config);
};

authApi.get = (url, config = {}) =>
    authApi({
        ...config,
        method: "get",
        url,
    });

authApi.post = (url, data, config = {}) =>
    authApi({
        ...config,
        method: "post",
        url,
        data,
    });

authApi.put = (url, data, config = {}) =>
    authApi({
        ...config,
        method: "put",
        url,
        data,
    });

authApi.delete = (url, config = {}) =>
    authApi({
        ...config,
        method: "delete",
        url,
    });

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            router.push({ path: `/${locale}/login` });
            // const currentPath = router.currentRoute.value.path;
            // if (currentPath.includes("/app")) {
            //     router.push({ path: `/login` });
            // } else {
            //     router.push({ path: `/${locale}/login` });
            // }
        }
        return Promise.reject(error);
    }
);

export { api, authApi, setLanguage };