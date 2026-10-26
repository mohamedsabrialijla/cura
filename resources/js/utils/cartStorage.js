const CART_KEY = "ks_cart_items";
const CART_TOKEN_KEY = "ks_cart_token";
export const CART_CHANGE_EVENT = "ks-cart-change";

function emitCartChange() {
    if (typeof window === "undefined") {
        return;
    }
    window.dispatchEvent(new CustomEvent(CART_CHANGE_EVENT));
}

function clampQtyToStock(qty, maxStock) {
    const n = Math.max(1, Number(qty || 1));
    const cap = Number(maxStock);
    if (Number.isFinite(cap) && cap > 0) {
        return Math.min(n, Math.floor(cap));
    }
    return n;
}

function normalizeItem(item) {
    const id = item.id || item.sku || `${Date.now()}-${Math.random()}`;
    const maxStock = item.maxStock != null ? Number(item.maxStock) : null;
    const price = Number(item.price || 0);
    const rawOld =
        item.price_old != null
            ? item.price_old
            : item.old_price != null
              ? item.old_price
              : item.priceOld;
    const priceOld = Number(rawOld || 0);
    return {
        id,
        sku: item.sku || id,
        title: item.title || "Goalkeeper Product",
        subtitle: item.subtitle || "",
        price,
        price_old: priceOld > price ? priceOld : 0,
        qty: clampQtyToStock(item.qty, maxStock),
        image: item.image || "",
        maxStock: Number.isFinite(maxStock) && maxStock > 0 ? Math.floor(maxStock) : null,
        inStock: item.inStock !== false,
    };
}

export function getOrCreateCartToken() {
    try {
        let token = localStorage.getItem(CART_TOKEN_KEY);
        if (!token) {
            token =
                typeof crypto !== "undefined" && crypto.randomUUID
                    ? crypto.randomUUID()
                    : `cart-${Date.now()}-${Math.random().toString(36).slice(2)}`;
            localStorage.setItem(CART_TOKEN_KEY, token);
        }
        return token;
    } catch (_) {
        return `cart-${Date.now()}`;
    }
}

export function getCartItems() {
    try {
        const raw = localStorage.getItem(CART_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) return [];
        return parsed.map(normalizeItem).filter((item) => Number(item.qty || 0) > 0);
    } catch (_) {
        return [];
    }
}

let syncTimer = null;

export function syncCartToServer(items = getCartItems()) {
    if (typeof window === "undefined") return;

    clearTimeout(syncTimer);
    syncTimer = setTimeout(async () => {
        try {
            const { api } = await import("@/axiosUser");
            await api.post("cart/sync", {
                cart_token: getOrCreateCartToken(),
                items: items.map((row) => ({
                    id: row.id,
                    sku: row.sku,
                    title: row.title,
                    subtitle: row.subtitle,
                    price: row.price,
                    price_old: row.price_old,
                    qty: row.qty,
                    image: row.image,
                })),
            });
        } catch (_) {
            // Silent fail — cart still works locally until checkout is enabled.
        }
    }, 350);
}

export function saveCartItems(items) {
    const normalized = (Array.isArray(items) ? items : [])
        .map(normalizeItem)
        .filter((item) => Number(item.qty || 0) > 0);

    try {
        localStorage.setItem(CART_KEY, JSON.stringify(normalized));
    } catch (_) {
        return false;
    }

    syncCartToServer(normalized);
    emitCartChange();
    return true;
}

export function clearCartStorage() {
    try {
        localStorage.setItem(CART_KEY, JSON.stringify([]));
    } catch (_) {
        return false;
    }
    syncCartToServer([]);
    emitCartChange();
    return true;
}

export function addToCartStorage(item) {
    const nextItem = normalizeItem(item);
    const items = getCartItems();
    const existingIndex = items.findIndex(
        (row) => row.id === nextItem.id || row.sku === nextItem.sku
    );

    if (existingIndex >= 0) {
        const existing = items[existingIndex];
        const maxStock =
            nextItem.maxStock != null
                ? nextItem.maxStock
                : existing.maxStock != null
                  ? existing.maxStock
                  : null;
        const mergedQty = Number(existing.qty || 0) + Number(nextItem.qty || 0);
        const cappedQty = clampQtyToStock(mergedQty, maxStock);

        if (cappedQty <= 0) {
            return { ok: false, items };
        }

        items[existingIndex] = normalizeItem({
            ...existing,
            qty: cappedQty,
            price: Number(nextItem.price || existing.price || 0),
            price_old: nextItem.price_old ?? existing.price_old ?? null,
            image: nextItem.image || existing.image || "",
            subtitle: nextItem.subtitle || existing.subtitle || "",
            title: nextItem.title || existing.title || "",
            sku: nextItem.sku || existing.sku || nextItem.id,
            maxStock,
            inStock: nextItem.inStock !== false && existing.inStock !== false,
        });
    } else {
        if (nextItem.maxStock != null && nextItem.qty <= 0) {
            return { ok: false, items };
        }
        if (nextItem.inStock === false) {
            return { ok: false, items };
        }
        items.push(nextItem);
    }

    const saved = saveCartItems(items);
    return { ok: saved, items: getCartItems() };
}

export function getCartCount(items = getCartItems()) {
    return items.reduce((sum, item) => sum + Number(item.qty || 0), 0);
}

export function getCartSubtotal(items = getCartItems()) {
    return items.reduce(
        (sum, item) => sum + Number(item.price || 0) * Number(item.qty || 0),
        0
    );
}

/** مجموع التوفير من فرق السعر قبل الخصم (price_old − price) × الكمية */
export function getCartProductSavings(items = getCartItems()) {
    return items.reduce((sum, item) => {
        const price = Number(item.price || 0);
        const oldPrice = Number(item.price_old || 0);
        const qty = Number(item.qty || 0);
        if (!(oldPrice > price) || qty <= 0) {
            return sum;
        }
        return sum + (oldPrice - price) * qty;
    }, 0);
}

export function maxQtyForCartItem(item, delta = 0) {
    const next = Math.max(1, Number(item.qty || 1) + delta);
    return clampQtyToStock(next, item.maxStock);
}
