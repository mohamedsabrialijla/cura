import { api } from "@/axiosUser";

const DEFAULT_CATALOG_LOCALE = "en";
/** تجنب شاشة loading بلا نهاية إذا علق GraphQL محلياً */
const CATALOG_HTTP_TIMEOUT_MS = 120_000;
const CATEGORIES_HTTP_TIMEOUT_MS = 20_000;

let categoriesCacheKey = null;
let categoriesCacheData = null;
let categoriesInflight = null;

function buildCategoriesCacheKey(params = {}) {
    return JSON.stringify({
        base: api.defaults.baseURL,
        catalog_locale: DEFAULT_CATALOG_LOCALE,
        ...params,
    });
}

export function invalidateCatalogCategoriesCache() {
    categoriesCacheKey = null;
    categoriesCacheData = null;
    categoriesInflight = null;
}

export async function fetchCatalogCategories(params = {}) {
    const cacheKey = buildCategoriesCacheKey(params);
    if (categoriesCacheKey === cacheKey && Array.isArray(categoriesCacheData)) {
        return categoriesCacheData;
    }
    if (categoriesInflight?.key === cacheKey) {
        return categoriesInflight.promise;
    }

    const promise = api
        .get("catalog/categories", {
            params: { catalog_locale: DEFAULT_CATALOG_LOCALE, ...params },
            timeout: CATEGORIES_HTTP_TIMEOUT_MS,
        })
        .then(({ data }) => {
            const categories = data?.categories || [];
            categoriesCacheKey = cacheKey;
            categoriesCacheData = categories;
            return categories;
        })
        .finally(() => {
            if (categoriesInflight?.key === cacheKey) {
                categoriesInflight = null;
            }
        });

    categoriesInflight = { key: cacheKey, promise };
    return promise;
}

export async function fetchCatalogProducts(params = {}) {
    const { data } = await api.get("catalog/products", {
        params: { catalog_locale: DEFAULT_CATALOG_LOCALE, ...params },
        timeout: CATALOG_HTTP_TIMEOUT_MS,
    });
    return {
        items: data?.items || [],
        facets: data?.facets || { brands: [], sizes: [], colors: [], attributes: [] },
        pagination: data?.pagination || { page: 1, per_page: 48, total: 0, last_page: 1 },
        meta: data?.meta || {},
    };
}

/** تصنيفات + منتجات في طلب واحد (أقل ضغط على الكاش البارد من طلبين متتابعين). */
export async function fetchCatalogShopListing(params = {}) {
    const { data } = await api.get("catalog/shop", {
        params: { catalog_locale: DEFAULT_CATALOG_LOCALE, ...params },
        timeout: CATALOG_HTTP_TIMEOUT_MS,
    });
    return {
        categories: data?.categories || [],
        teamsport_subcategories: data?.teamsport_subcategories || [],
        items: data?.items || [],
        facets: data?.facets || { brands: [], sizes: [], colors: [], attributes: [] },
        pagination: data?.pagination || { page: 1, per_page: 48, total: 0, last_page: 1 },
        meta: data?.meta || {},
    };
}

const productBySlugCache = new Map();
const productBySlugInflight = new Map();

export async function fetchCatalogProductBySlug(slug, params = {}) {
    const normalizedSlug = String(slug || "").trim().replace(/\.html$/i, "");
    if (!normalizedSlug) {
        return null;
    }

    const cacheKey = `${api.defaults.baseURL}|${normalizedSlug}|${JSON.stringify({
        catalog_locale: DEFAULT_CATALOG_LOCALE,
        ...params,
    })}`;

    if (productBySlugCache.has(cacheKey)) {
        return productBySlugCache.get(cacheKey);
    }
    if (productBySlugInflight.has(cacheKey)) {
        return productBySlugInflight.get(cacheKey);
    }

    const promise = api
        .get(`catalog/products/${normalizedSlug}`, {
            params: { catalog_locale: DEFAULT_CATALOG_LOCALE, ...params },
            timeout: 45_000,
        })
        .then(({ data }) => {
            const product = data?.product || null;
            if (product) {
                productBySlugCache.set(cacheKey, product);
            }
            return product;
        })
        .finally(() => {
            productBySlugInflight.delete(cacheKey);
        });

    productBySlugInflight.set(cacheKey, promise);
    return promise;
}

/** تحميل مسبق عند المرور على بطاقة منتج — يفتح PDP أسرع */
export function prefetchCatalogProductBySlug(slug, params = {}) {
    fetchCatalogProductBySlug(slug, params).catch(() => {});
}

/** منتجات موصى بها — طلب منفصل حتى لا يبطئ فتح صفحة المنتج */
export async function fetchCatalogProductRecommended(slug, params = {}) {
    const { data } = await api.get(`catalog/products/${slug}/recommended`, {
        params: { catalog_locale: DEFAULT_CATALOG_LOCALE, ...params },
        timeout: 60_000,
    });
    return Array.isArray(data?.items) ? data.items : [];
}

