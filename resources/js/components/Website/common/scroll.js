export function scrollToSection(id, behavior = "smooth") {
    const section = document.getElementById(id);
    if (!section) return false;
    const margin = parseInt(getComputedStyle(section).scrollMarginTop, 10) || 0;
    window.scrollTo({ top: section.offsetTop - margin, behavior });
    return true;
}

/**
 * Same-page section link: scrolls on the home page, otherwise navigates home with the hash.
 */
export function goToSection(router, route, id) {
    if (route.name === "home" && scrollToSection(id)) {
        history.replaceState(history.state, "", `#${id}`);
        return;
    }
    const locale = route.params.locale === "ar" || route.params.locale === "en" ? route.params.locale : "en";
    router.push({ path: `/${locale}`, hash: `#${id}` });
}
