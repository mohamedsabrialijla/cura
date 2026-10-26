/**
 * Social profile URLs from dashboard settings.
 */
export const SOCIAL_SVGS = {
    instagram: `<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4A5.8 5.8 0 0 1 16.2 22H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2zm0 2A3.8 3.8 0 0 0 4 7.8v8.4A3.8 3.8 0 0 0 7.8 20h8.4a3.8 3.8 0 0 0 3.8-3.8V7.8A3.8 3.8 0 0 0 16.2 4H7.8zm9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>`,
    facebook: `<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M13.5 22v-8.2h2.8l.4-3.2h-3.2V8.9c0-.9.3-1.6 1.7-1.6h1.7V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H8.2v3.2h2.9V22h2.4z"/></svg>`,
    tiktok: `<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M16.6 5.82s.51.5 0 0A4.278 4.278 0 0 1 15.54 3h-3.09v12.4a2.592 2.592 0 0 1-2.59 2.5c-1.42 0-2.6-1.16-2.6-2.6 0-1.72 1.66-3.01 3.37-2.48V9.66c-3.45-.46-6.47 2.22-6.47 5.64 0 3.33 2.76 5.7 5.69 5.7 3.14 0 5.69-2.55 5.69-5.7V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3s-1.88.09-3.24-1.48z"/></svg>`,
    snapchat: `<svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>`,
};

export function normalizeSocialHref(platform, value) {
    const raw = String(value || "").trim();
    if (!raw) {
        return "";
    }
    if (/^https?:\/\//i.test(raw)) {
        return raw;
    }
    const handle = raw.replace(/^@+/, "");
    if (!handle) {
        return "";
    }
    switch (platform) {
        case "instagram":
            return `https://www.instagram.com/${handle}`;
        case "facebook":
            return `https://www.facebook.com/${handle}`;
        case "tiktok":
            return `https://www.tiktok.com/@${handle}`;
        case "snapchat":
            return `https://www.snapchat.com/add/${handle}`;
        default:
            return raw;
    }
}

export function resolveSocialLinks(setting) {
    const src = setting || {};

    return [
        { id: "instagram", href: normalizeSocialHref("instagram", src.instagram), label: "Instagram" },
        { id: "facebook", href: normalizeSocialHref("facebook", src.facebook), label: "Facebook" },
        { id: "tiktok", href: normalizeSocialHref("tiktok", src.tiktok), label: "TikTok" },
        { id: "snapchat", href: normalizeSocialHref("snapchat", src.snapchat), label: "Snapchat" },
    ].filter((row) => row.href !== "");
}

export function resolveSocialLinksFromStore(store, locale) {
    if (!store) {
        return [];
    }
    const getter = store.getters?.getSetting;
    const setting = typeof getter === "function" ? getter() : store.state?.setting;
    const links = resolveSocialLinks(setting);
    const lang =
        locale === "ar" || locale === "en"
            ? locale
            : store.state?.currentLanguage === "ar"
              ? "ar"
              : "en";
    const labels = {
        instagram: { ar: "إنستغرام", en: "Instagram" },
        facebook: { ar: "فيسبوك", en: "Facebook" },
        tiktok: { ar: "تيك توك", en: "TikTok" },
        snapchat: { ar: "سناب شات", en: "Snapchat" },
    };
    return links.map((row) => ({
        ...row,
        label: labels[row.id]?.[lang] || row.label,
    }));
}

export function socialIconSvg(id) {
    return SOCIAL_SVGS[id] || "";
}
