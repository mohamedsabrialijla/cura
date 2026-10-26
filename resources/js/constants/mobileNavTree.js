/** فرع أحذية كرة القدم — نفس FOOTBALL_BOOT_SHOP_LEAF_SLUGS في KeeperSportCatalogService */
const FOOTBALL_BOOT_LEAVES = [
    { slug: "hard-court", en: "Hard court", ar: "ملعب صلب" },
    { slug: "firm-ground", en: "Firm ground", ar: "أرضية صلبة" },
    { slug: "soft-ground", en: "Soft ground", ar: "أرضية طرية" },
    { slug: "artificial-grass", en: "Artificial grass", ar: "عشب صناعي" },
    { slug: "turf", en: "Turf", ar: "تورف" },
    { slug: "multi-ground", en: "Multi ground", ar: "متعدد الأسطح" },
    { slug: "street", en: "Street", ar: "شارع" },
    { slug: "cleats", en: "Cleats", ar: "مسامير" },
    { slug: "accessories", en: "Accessories", ar: "إكسسوارات" },
    { slug: "kids-football-boots", en: "Kids football boots", ar: "أحذية أطفال" },
    { slug: "leisure-kids", en: "Leisure kids", ar: "أطفال ترفيهي" },
    { slug: "indoor", en: "Indoor", ar: "صالات" },
    { slug: "sandals", en: "Sandals", ar: "صنادل" },
];

const GK_APPAREL_LEAVES = [
    { slug: "goalkeeper-underwear", en: "Goalkeeper underwear", ar: "ملابس داخلية لحارس المرمى" },
    { slug: "goalkeeper-pants", en: "Goalkeeper pants", ar: "بناطيل حارس المرمى" },
    { slug: "goalkeeper-jerseys", en: "Goalkeeper jerseys", ar: "قمصان حارس المرمى" },
    { slug: "goalkeeper-set", en: "Goalkeeper set", ar: "طقم حارس المرمى" },
    { slug: "goalkeeper-training-tops", en: "Goalkeeper training tops", ar: "قمصان تدريب حارس المرمى" },
    { slug: "goalkeeper-socks", en: "Goalkeeper socks", ar: "جوارب حارس المرمى" },
];

/** نفس TEAMSPORT_SUBCATEGORIES في الخادم */
const TEAMSPORT_SUBS = [
    {
        slug: "tops",
        label: "Tops",
        ar: "قمصان وملابس علوية",
        children: [
            { slug: "jerseys", label: "Jerseys", ar: "قمصان" },
            { slug: "training-tops", label: "Training tops", ar: "قمصان تدريب" },
            { slug: "t-shirt", label: "T-Shirt", ar: "تيشيرت" },
            { slug: "tank-tops", label: "Tank tops", ar: "سترات بدون أكمام" },
            { slug: "jackets", label: "Jackets", ar: "جاكيتات" },
            { slug: "sweatshirts", label: "Sweatshirts", ar: "سويتشيرت" },
            { slug: "polo-shirts", label: "Polo shirts", ar: "بولو" },
        ],
    },
    {
        slug: "pants",
        label: "Pants",
        ar: "بناطيل",
        children: [
            { slug: "shorts", label: "Shorts", ar: "شورت" },
            { slug: "long-pants", label: "Long pants", ar: "بناطيل طويلة" },
            { slug: "short-pants", label: "Short pants", ar: "بناطيل قصيرة" },
        ],
    },
    {
        slug: "underwear",
        label: "Underwear",
        ar: "ملابس داخلية",
        children: [
            { slug: "underpants", label: "Underpants", ar: "سروال داخلي" },
            { slug: "boxer-shorts", label: "Boxer shorts", ar: "بوكسر" },
            { slug: "sport-bras", label: "Sport Bras", ar: "حمالات رياضية" },
        ],
    },
    { slug: "tracksuits", label: "Tracksuits", ar: "بدلات رياضية", children: [] },
    {
        slug: "socks",
        label: "Socks",
        ar: "جوارب",
        children: [
            { slug: "knee-high-socks", label: "Knee-high socks", ar: "جوارب طويلة" },
            { slug: "functional-socks", label: "Functional Socks", ar: "جوارب وظيفية" },
        ],
    },
    { slug: "footballs", label: "Footballs", ar: "كرات قدم", children: [] },
];

const SECTION_ALIASES = {
    "goalkeeper-gloves": ["goalkeeper-gloves", "guanti-da-portiere", "torwarthandschuhe"],
    "football-boots": ["football-boots", "scarpe-da-calcio", "fussballschuhe"],
    "goalkeeper-apparel": ["goalkeeper-apparel", "abbigliamento-da-portiere", "torwartbekleidung"],
    "goalkeeper-equipment": ["goalkeeper-equipment", "equipaggiamento-per-portiere", "torwartausruestung"],
    teamsport: ["teamsport", "abbigliamento", "teamwear"],
};

/** معدات الحارس — عند غياب أبناء من API (مثل keepersport.net) */
const GK_EQUIPMENT_FALLBACK = [
    { internalPath: "/glove-care", en: "Goalkeeper glove care", ar: "عناية قفازات الحارس" },
    { slug: "gk-bag", en: "GK-Bag", ar: "حقيبة حارس المرمى" },
    { slug: "protection", en: "Protection", ar: "حماية" },
    { slug: "training-equipment", en: "Training equipment", ar: "معدات تدريب" },
    { slug: "medicine-tapes", en: "Medicine / Tapes", ar: "طبية / شريط" },
    { slug: "key-rings", en: "Key Rings", ar: "حلقات مفاتيح", leaf: true },
];

const BRAND_FALLBACKS = {
    "goalkeeper-gloves": [
        { key: "reusch", en: "REUSCH", ar: "REUSCH" },
        { key: "uhlsport", en: "uhlsport", ar: "uhlsport" },
        { key: "adidas", en: "adidas", ar: "adidas" },
        { key: "puma", en: "Puma", ar: "Puma" },
        { key: "keepersport", en: "KEEPERsport", ar: "KEEPERsport" },
        { key: "one-glove", en: "One Glove", ar: "One Glove" },
    ],
};

/** قائمة قفازات الحارس — نفس keepersport.net (مسطح: ماركات + Bundle Deals) */
const GOALKEEPER_GLOVES_LEAVES = [
    { slug: "uhlsport-goalkeeper-gloves", en: "uhlsport", ar: "uhlsport" },
    { slug: "adidas-goalkeeper-gloves", en: "adidas", ar: "adidas" },
    { slug: "reusch-goalkeeper-gloves", en: "REUSCH", ar: "REUSCH" },
    { slug: "puma-goalkeeper-gloves", en: "Puma", ar: "Puma" },
    { slug: "keepersport-goalkeeper-gloves", en: "KEEPERsport", ar: "KEEPERsport" },
    { queryBrand: "one-glove", en: "One Glove", ar: "One Glove" },
    { slug: "elite-sport-goalkeeper-gloves", en: "Elite Sport", ar: "Elite Sport" },
    { slug: "rehab-goalkeeper-gloves", en: "rehab", ar: "rehab" },
    { slug: "erima-goalkeeper-gloves", en: "erima", ar: "erima" },
    { slug: "packs-sets-goalkeeper-gloves", en: "Bundle Deals", ar: "Bundle Deals" },
    { slug: "nike-goalkeeper-gloves", en: "NIKE", ar: "NIKE" },
    { slug: "gloveglu-goalkeeper-gloves", en: "gloveglu", ar: "gloveglu" },
    { slug: "ho-soccer-goalkeeper-gloves", en: "Ho Soccer", ar: "Ho Soccer" },
    { slug: "jako-goalkeeper-gloves", en: "Jako", ar: "Jako" },
];

/** معدات الحارس — نفس keepersport.net (مع فروع فرعية) */
const GOALKEEPER_EQUIPMENT_NAV = [
    {
        slug: "goalkeeper-glove-care",
        en: "Goalkeeper glove care",
        ar: "عناية قفازات الحارس",
        children: [
            { slug: "glove-wash", en: "Glove Wash", ar: "Glove Wash" },
            { slug: "care", en: "Care", ar: "Care" },
            { slug: "grip-enhancer", en: "Grip enhancer", ar: "Grip enhancer" },
            { slug: "towels", en: "Towels", ar: "Towels" },
        ],
    },
    { slug: "gk-bag", en: "GK-Bag", ar: "GK-Bag" },
    {
        slug: "protection",
        en: "Protection",
        ar: "Protection",
        children: [
            { slug: "elbow-pads", en: "Elbow Pads", ar: "Elbow Pads" },
            { slug: "shin-pads", en: "Shin Pads", ar: "Shin Pads" },
            { slug: "knee-pads", en: "Knee Pads", ar: "Knee Pads" },
            { slug: "helmets", en: "Helmets", ar: "Helmets" },
            { slug: "mouth", en: "Mouth", ar: "Mouth" },
            { slug: "genital", en: "Genital", ar: "Genital" },
        ],
    },
    {
        slug: "training-equipment",
        en: "Training equipment",
        ar: "Training equipment",
        children: [
            { slug: "water-bottles", en: "Water Bottles", ar: "Water Bottles" },
            { slug: "studs", en: "Studs", ar: "Studs" },
            { slug: "training-aids", en: "Training aids", ar: "Training aids" },
        ],
    },
    {
        slug: "medicine-tapes",
        en: "Medicine / Tapes",
        ar: "Medicine / Tapes",
        children: [
            { slug: "bandages", en: "Bandages", ar: "Bandages" },
            { slug: "cooling", en: "Cooling", ar: "Cooling" },
            { slug: "tapes-pavings", en: "Tapes / Pavings", ar: "Tapes / Pavings" },
        ],
    },
    { slug: "key-rings", en: "Key Rings", ar: "Key Rings", leaf: true },
    { slug: "under-gloves", en: "Under gloves", ar: "Under gloves" },
    {
        slug: "multimedia",
        en: "Multimedia",
        ar: "Multimedia",
        children: [
            { slug: "online-goalkeeper-training", en: "Online goalkeeper training", ar: "Online goalkeeper training" },
            { slug: "books", en: "Books", ar: "Books" },
        ],
    },
    { slug: "recovery", en: "Recovery", ar: "Recovery" },
];

function leafLabel(row, ar) {
    return ar ? row.ar || row.label : row.en || row.label;
}

function footballBootChildren(ar) {
    return FOOTBALL_BOOT_LEAVES.map((row) => ({
        slug: row.slug,
        name: leafLabel(row, ar),
    }));
}

function gkApparelChildren(ar) {
    return GK_APPAREL_LEAVES.map((row) => ({
        slug: row.slug,
        name: leafLabel(row, ar),
    }));
}

function teamsportChildren(ar) {
    return TEAMSPORT_SUBS.map((sub) => ({
        slug: `teamsport/${sub.slug}`,
        id: sub.slug,
        name: leafLabel(sub, ar),
        filterIds: true,
        children: (sub.children || []).map((gc) => ({
            slug: gc.slug,
            name: leafLabel(gc, ar),
            label: leafLabel(gc, ar),
            parentSlug: sub.slug,
        })),
    }));
}

function findSectionRoot(sectionSlug, categories) {
    const aliases = SECTION_ALIASES[sectionSlug] || [sectionSlug];
    return (categories || []).find((r) => {
        const s = String(r.slug || "").toLowerCase();
        return aliases.some((a) => s === a);
    });
}

function mapTreeChildren(nodes, ar) {
    return (nodes || [])
        .map((ch) => {
            const slug = String(ch.slug || ch.id || "").replace(/_/g, "-");
            const nested = ch.children?.length ? mapTreeChildren(ch.children, ar) : [];
            return {
                slug,
                name: ch.name || ch.slug || "",
                ...(nested.length ? { children: nested } : {}),
            };
        })
        .filter((ch) => ch.slug && ch.name);
}

function gkEquipmentFallback(ar) {
    return GK_EQUIPMENT_FALLBACK.map((row) => ({
        slug: row.slug || "",
        internalPath: row.internalPath || "",
        name: leafLabel(row, ar),
        leaf: Boolean(row.leaf),
    }));
}

function mapNavBranch(rows, ar) {
    return (rows || []).map((row) => {
        const nested = row.children?.length ? mapNavBranch(row.children, ar) : [];
        return {
            slug: row.slug || "",
            id: row.slug || row.queryBrand || "",
            queryBrand: row.queryBrand || "",
            internalPath: row.internalPath || "",
            name: leafLabel(row, ar),
            label: leafLabel(row, ar),
            leaf: Boolean(row.leaf) || nested.length === 0,
            ...(nested.length ? { children: nested } : {}),
        };
    });
}

export function goalkeeperGlovesNavChildren(ar = false) {
    return mapNavBranch(GOALKEEPER_GLOVES_LEAVES, ar);
}

const GK_GLOVE_BRAND_SHOP_SUFFIX = "-goalkeeper-gloves";

/** ماركة من مسار /shop/{brand}-goalkeeper-gloves (مثل erima-goalkeeper-gloves → erima). */
export function goalkeeperGloveBrandFromShopSlug(slug) {
    const s = String(slug || "")
        .trim()
        .replace(/_/g, "-")
        .toLowerCase();
    if (!s.endsWith(GK_GLOVE_BRAND_SHOP_SUFFIX)) {
        return "";
    }
    const brand = s.slice(0, -GK_GLOVE_BRAND_SHOP_SUFFIX.length);
    if (!brand || brand === "packs-sets") {
        return "";
    }
    return brand;
}

/** تصنيف الـ API: أوراق الماركات تُعامل كقفازات حارس + فلتر brand. */
export function catalogApiCategoryFromSlug(slug) {
    if (goalkeeperGloveBrandFromShopSlug(slug)) {
        return "goalkeeper-gloves";
    }
    return String(slug || "")
        .trim()
        .replace(/_/g, "-")
        .toLowerCase();
}

export function goalkeeperEquipmentNavChildren(ar = false) {
    return mapNavBranch(GOALKEEPER_EQUIPMENT_NAV, ar);
}

function fallbackBrandChildren(mainSlug, ar) {
    const rows = BRAND_FALLBACKS[mainSlug] || [];
    return rows.map((b) => ({
        id: `brand-${b.key}`,
        slug: "",
        queryBrand: b.key,
        name: leafLabel(b, ar),
    }));
}

/**
 * أبناء القسم للقائمة الجانبية (موبايل).
 */
export function resolveMobileNavChildren(mainSlug, { categories = [], ar = false } = {}) {
    switch (mainSlug) {
        case "football-boots":
            return footballBootChildren(ar);
        case "goalkeeper-apparel":
            return gkApparelChildren(ar);
        case "goalkeeper-gloves":
            return goalkeeperGlovesNavChildren(ar);
        case "goalkeeper-equipment":
            return goalkeeperEquipmentNavChildren(ar);
        case "teamsport":
            return teamsportChildren(ar);
        default:
            return [];
    }
}

/**
 * مسار Vue Router لعنصر فرعي.
 */
export function mobileNavChildRoute(locale, mainSlug, ch) {
    const loc = String(locale || "en");
    if (!ch) {
        return { path: `/${loc}` };
    }

    const internalPath = String(ch.internalPath || "").trim();
    if (internalPath) {
        return { path: internalPath.startsWith("/") ? `/${loc}${internalPath}` : `/${loc}/${internalPath}` };
    }

    if (ch.queryBrand) {
        return {
            path: `/${loc}/${mainSlug}`,
            query: { brand: String(ch.queryBrand) },
        };
    }

    if (mainSlug === "goalkeeper-gloves") {
        const raw = String(ch.slug || ch.id || "").trim().replace(/_/g, "-").toLowerCase();
        if (raw.endsWith("-goalkeeper-gloves")) {
            return { path: `/${loc}/shop/${raw}` };
        }
    }

    if (mainSlug === "goalkeeper-equipment") {
        const raw = String(ch.slug || ch.id || "").trim().replace(/_/g, "-").toLowerCase();
        if (raw) {
            return { path: `/${loc}/shop/${raw}` };
        }
    }

    if (ch.filterIds) {
        const sub = String(ch.slug || ch.id || "").replace(/^teamsport\//, "");
        return {
            path: `/${loc}/teamsport`,
            query: sub ? { teamsport_sub: sub } : {},
        };
    }

    if (ch.parentSlug && ch.slug) {
        return {
            path: `/${loc}/teamsport`,
            query: { teamsport_sub: `${ch.parentSlug}/${ch.slug}` },
        };
    }

    const raw = String(ch.slug || ch.id || "").trim().replace(/_/g, "-").toLowerCase();
    if (!raw) {
        return { path: `/${loc}/shop/` };
    }

    if (
        mainSlug === "football-boots" &&
        raw &&
        raw !== "football-boots" &&
        !raw.includes("/")
    ) {
        return { path: `/${loc}/football-boots-${raw}` };
    }

    return { path: `/${loc}/shop/${raw}` };
}

export function buildMobileNavMainItems(htNav, { categories = [], ar = false } = {}) {
    return (htNav || []).map((item) => {
        const mainSlug = String(item.internal || "")
            .replace(/^\//, "")
            .toLowerCase();
        const isSale = mainSlug === "sale";
        const children = isSale ? [] : resolveMobileNavChildren(mainSlug, { categories, ar });
        return {
            ...item,
            mainSlug,
            isSale,
            children,
        };
    });
}
