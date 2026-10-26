/**
 * Site logo from dashboard settings (bootstrapped in landing.blade.php or loaded via getSetting).
 */
export function resolveSiteLogo(setting) {
    const logo = setting?.logo;
    if (typeof logo === "string" && logo.trim() !== "") {
        return logo.trim();
    }
    return "";
}

export function resolveSiteLogoFromStore(store) {
    if (!store) {
        return "";
    }
    const getter = store.getters?.getSetting;
    const setting = typeof getter === "function" ? getter() : store.state?.setting;
    return resolveSiteLogo(setting);
}
