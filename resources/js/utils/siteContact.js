/**
 * Contact details from dashboard settings (mobile, email, whatsapp).
 */
export function splitContactList(value) {
    if (value == null || value === "") {
        return [];
    }
    return String(value)
        .split(/[,;\n]+/)
        .map((part) => part.trim())
        .filter(Boolean);
}

export function resolveSiteContact(setting) {
    const src = setting || {};
    return {
        mobile: String(src.mobile || "").trim(),
        email: String(src.email || "").trim(),
        whatsapp: String(src.whatsapp || "").trim(),
        mobiles: splitContactList(src.mobile),
        emails: splitContactList(src.email),
    };
}

export function resolveSiteContactFromStore(store) {
    if (!store) {
        return resolveSiteContact(null);
    }
    const getter = store.getters?.getSetting;
    const setting = typeof getter === "function" ? getter() : store.state?.setting;
    return resolveSiteContact(setting);
}

export function telHref(phone) {
    const raw = String(phone || "").trim();
    if (!raw) {
        return "";
    }
    const digits = raw.replace(/[^\d+]/g, "");
    return digits ? `tel:${digits}` : "";
}

export function mailtoHref(email) {
    const raw = String(email || "").trim();
    return raw ? `mailto:${raw}` : "";
}

export function normalizeWhatsappHref(url) {
    const raw = String(url || "").trim();
    if (!raw) {
        return "";
    }
    if (/^https?:\/\//i.test(raw)) {
        return raw;
    }
    const digits = raw.replace(/\D/g, "");
    return digits ? `https://wa.me/${digits}` : raw;
}
