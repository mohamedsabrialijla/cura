/**
 * توحيد روابط صور كتالوج Keepersport وتقليل الفشل عند hotlinking أو روابط // بدون بروتوكول.
 */
export function normalizeCatalogImageUrl(url) {
    if (url == null) return "";
    const s = String(url).trim();
    if (!s) return "";
    if (s.startsWith("//")) return `https:${s}`;
    return s;
}
