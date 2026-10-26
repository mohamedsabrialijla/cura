import { createApp } from "vue";
import App from "./layout/App.vue";
import router from "./router.js";
import i18n from "./i18n/index.js";

window.AOS?.init({ duration: 600, easing: "ease-in-out", once: true, mirror: false });

createApp(App).use(i18n).use(router).mount("#app");
