import { createRouter, createWebHistory } from "vue-router";
import { applyLocale, savedLocale } from "./i18n/index.js";
import Landing from "./pages/Landing.vue";

const routes = [
    { path: "/", redirect: () => `/${savedLocale()}` },
    { path: "/:locale(ar|en)", name: "home", component: Landing },
    { path: "/:pathMatch(.*)*", redirect: () => `/${savedLocale()}` },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        if (to.name === from.name) return false;
        if (to.hash) return false;
        return { top: 0 };
    },
});

router.beforeEach((to) => {
    applyLocale(to.params.locale);
});

export default router;
