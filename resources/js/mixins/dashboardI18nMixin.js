import { dashboardCopy, dashboardBreadcrumb } from "../constants/dashboardI18n.js";

export default {
    computed: {
        dashboardLocale() {
            return this.$route?.params?.locale === "ar" ? "ar" : "en";
        },
        dt() {
            return dashboardCopy(this.dashboardLocale);
        },
        dashboardIsRtl() {
            return this.dashboardLocale === "ar";
        },
    },
    methods: {
        dbCrumb(key, param) {
            return dashboardBreadcrumb(this.dashboardLocale, key, param);
        },
        dtPath(path) {
            const parts = String(path).split(".");
            let node = this.dt;
            for (const p of parts) {
                if (!node || typeof node !== "object") return path;
                node = node[p];
            }
            return typeof node === "string" ? node : path;
        },
        canMenu(permissionName) {
            const permissions = this.$store?.state?.user?.role;

            if (!Array.isArray(permissions) || permissions.length === 0) {
                return false;
            }

            return permissions.includes(permissionName);
        },
    },
};
