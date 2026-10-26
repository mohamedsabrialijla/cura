/** ترتيب تسميات المقاسات من الأصغر إلى الأكبر (قفازات، أحذية، ملابس). */
const SIZE_RANK = {
    xxxs: 0,
    xxs: 1,
    xs: 2,
    s: 3,
    m: 4,
    l: 5,
    xl: 6,
    xxl: 7,
    "2xl": 8,
    "3xl": 9,
    "4xl": 10,
    "5xl": 11,
    xxxl: 12,
};

function sizeSortKey(size) {
    const raw = String(size ?? "").trim();
    if (!raw) {
        return [3, Number.POSITIVE_INFINITY, ""];
    }
    const lower = raw.toLowerCase();

    const leadingNum = lower.match(/^(\d+(?:[.,]\d+)?)/);
    if (leadingNum) {
        return [0, parseFloat(leadingNum[1].replace(",", ".")), lower];
    }

    const compact = lower.replace(/\s+/g, "");
    if (Object.prototype.hasOwnProperty.call(SIZE_RANK, compact)) {
        return [1, SIZE_RANK[compact], lower];
    }

    const embeddedNum = lower.match(/(\d+(?:[.,]\d+)?)/);
    if (embeddedNum && !/[a-z]{2,}/i.test(lower.replace(embeddedNum[0], ""))) {
        return [0, parseFloat(embeddedNum[1].replace(",", ".")), lower];
    }

    if (/^(one\s*size|onesize|os|ua)$/i.test(lower)) {
        return [2, 9999, lower];
    }

    return [2, 0, lower];
}

export function compareSizeLabels(a, b) {
    const ka = sizeSortKey(a);
    const kb = sizeSortKey(b);
    for (let i = 0; i < 3; i += 1) {
        if (ka[i] < kb[i]) return -1;
        if (ka[i] > kb[i]) return 1;
    }
    return 0;
}

/** @param {Array<{ size?: string }>} variants */
export function sortVariantsBySize(variants) {
    if (!Array.isArray(variants) || variants.length < 2) {
        return Array.isArray(variants) ? [...variants] : [];
    }
    return [...variants].sort((a, b) => compareSizeLabels(a?.size, b?.size));
}
