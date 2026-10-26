function upsertMetaTag(selector, attr, key, value) {
    if (!value) {
        return;
    }
    let el = document.head.querySelector(`${selector}[${attr}="${key}"]`);
    if (!el) {
        el = document.createElement("meta");
        el.setAttribute(attr, key);
        document.head.appendChild(el);
    }
    el.setAttribute("content", value);
}

export function applySiteShareMeta(meta = {}) {
    if (!meta || typeof meta !== "object") {
        return;
    }
    if (meta.title) {
        document.title = meta.title;
    }
    if (meta.description) {
        upsertMetaTag("meta", "name", "description", meta.description);
    }
    if (meta.canonical) {
        let link = document.head.querySelector('link[rel="canonical"]');
        if (!link) {
            link = document.createElement("link");
            link.setAttribute("rel", "canonical");
            document.head.appendChild(link);
        }
        link.setAttribute("href", meta.canonical);
    }

    const ogMap = {
        "og:locale": meta.og_locale,
        "og:type": meta.og_type,
        "og:title": meta.og_title || meta.title,
        "og:description": meta.og_description || meta.description,
        "og:url": meta.og_url || meta.canonical,
        "og:site_name": meta.og_site_name,
        "og:image": meta.og_image,
    };
    Object.entries(ogMap).forEach(([key, value]) => {
        if (value) {
            upsertMetaTag("meta", "property", key, value);
        }
    });

    const twMap = {
        "twitter:card": meta.twitter_card,
        "twitter:title": meta.twitter_title || meta.title,
        "twitter:description": meta.twitter_description || meta.description,
        "twitter:image": meta.twitter_image || meta.og_image,
    };
    Object.entries(twMap).forEach(([key, value]) => {
        if (value) {
            upsertMetaTag("meta", "name", key, value);
        }
    });
}

export function applyProductShareMeta(product, locale = "en") {
    if (!product?.name) {
        return;
    }
    const site = locale === "ar" ? "KEEPERsport السعودية" : "KEEPERsport Saudi Arabia";
    const name = String(product.name).trim();
    const description =
        locale === "ar"
            ? `اشتري ${name} من ${site}. قفازات حراس المرمى، أحذية، ملابس ومعدات — توصيل داخل السعودية.`
            : `Shop ${name} at ${site}. Goalkeeper gloves, boots, apparel & equipment with delivery across Saudi Arabia.`;
    const image =
        product.image ||
        product.main_image ||
        (Array.isArray(product.images) ? product.images[0] : "") ||
        "";

    applySiteShareMeta({
        title: `${name} | ${site}`,
        description,
        og_title: `${name} | ${site}`,
        og_description: description,
        twitter_title: `${name} | ${site}`,
        twitter_description: description,
        og_image: image,
        twitter_image: image,
        og_site_name: site,
    });
}
