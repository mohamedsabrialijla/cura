import { createRouter, createWebHistory } from "vue-router";
import adminRoutes from "./adminRoutes";

async function syncApiLocale(locale, path) {
    if (path.includes("/dashboard") || path.includes("/admin")) {
        const { setLanguage } = await import("./axios");
        setLanguage(locale);
        return;
    }

    const { setLanguage } = await import("./axiosUser");
    setLanguage(locale);
}

const routes = [
    ...adminRoutes,
    {
        path: "/dashboard/:pathMatch(.*)*",
        redirect: (to) => {
            const locale = localStorage.getItem("locale") || "ar";
            const rest = to.params.pathMatch
                ? `/${[].concat(to.params.pathMatch).join("/")}`
                : "";
            return `/${locale}/dashboard${rest}`;
        },
    },
    {
        path: "/dashboard",
        redirect: () => `/${localStorage.getItem("locale") || "ar"}/dashboard`,
    },
    {
        path: "/admin/:pathMatch(.*)*",
        redirect: (to) => {
            const locale = localStorage.getItem("locale") || "ar";
            const rest = to.params.pathMatch
                ? `/${[].concat(to.params.pathMatch).join("/")}`
                : "";
            return `/${locale}/admin${rest}`;
        },
    },
    {
        path: "/",
        redirect: "/ar",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }
        if (to.hash) {
            return { el: to.hash, top: 0, behavior: "smooth" };
        }
        return { top: 0, left: 0 };
    },
});

router.afterEach((to) => {
    if (typeof window === "undefined" || to.hash) {
        return;
    }
    window.scrollTo({ top: 0, left: 0, behavior: "auto" });
});

router.beforeEach(async (to, from, next) => {
    const locale = to.params.locale || localStorage.getItem("locale") || "ar";

    if (to.params.locale && !["en", "ar"].includes(to.params.locale)) {
        return next("/ar");
    }

    localStorage.setItem("locale", locale);
    await syncApiLocale(locale, to.path);

    // التحقق فقط لمسارات واجهة المستخدم (app)
    const isAppRoute = to.path.includes("/app");

    if (isAppRoute && to.meta.requiresAuth) {
        const token = localStorage.getItem("authToken");
        if (!token) {
            return next({ path: `/${locale}/login` });
        }

        try {
            const { api } = await import("./axiosUser");
            const response = await api.get("check-auth", {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });

            if (response.status !== 200) {
                localStorage.removeItem("authToken");
                return next({ path: `/${locale}/login` });
            }
        } catch (error) {
            localStorage.removeItem("authToken");
            return next({ path: `/${locale}/login` });
        }
    }

    next();
});

export default router;
