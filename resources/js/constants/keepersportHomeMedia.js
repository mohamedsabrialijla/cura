/**
 * CDN URLs scraped from https://www.keepersport.it/ (images.keepersport.net).
 * Resize tokens match the live site; change width in query if needed.
 */
export const KS_HOME_MEDIA = {
    planetEdition:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL3VobHNwb3J0XC9IZWFkZXJcL0hFQURFUl9EXzMwMDB4MTAwMF91aGxzcG9ydF9DWUJFUlRFQ19QTEFORVRfRURJVElPTi5wbmciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjI1MDB9fX0=",
    /** EOSS hero — mobile (keepersport CMS, ~600px wide). */
    planetEditionMobile:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1jbXMtZmlsZXMiLCJrZXkiOiJFT1NTX0hlYWRlcl9Nb2JpbGVfdWhsc3BvcnQucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo2MDB9fX0=",
    /** EOSS hero — desktop (keepersport CMS, ~2000px wide). */
    planetEditionDesktop:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1jbXMtZmlsZXMiLCJrZXkiOiJFT1NTX0hlYWRlcl9EZXNrdG9wX3VobHNwb3J0LnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MjAwMH19fQ==",
    bannerDeals:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL1NUQVJUU0VJVEVcL2Jhbm5lci1kZWFscy5wbmciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjE1MDB9fX0=",
    dealOfTheWeek:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0RFQUwgb2YgdGhlIFdFRUtcL0ZUV18xOTYweDEyMDlfR0tfUGFudHNfVW5QYWRkZWQucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjoxMTUwfX19",
    megaSale:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0hPIFNPQ0NFUlwvRlRXXC9GVFdfMTk2MHgxMjA5X18xNF8ucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjoxMTUwfX19",
    latestNewsAdidas:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL2FkaWRhc1wvRlRXXC9GVFdfMTk2MHgxMjA5LnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MTE1MH19fQ==",
    latestNewsGloveBanner:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL1NUQVJUU0VJVEVcL2Jhbm5lci1ndWFudGktbm92aXRhLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MTUwMH19fQ==",
    /** Second “latest news” tile on keepersport.it (Reusch Attrakt 2026). */
    latestNewsReuschAttrakt:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL3JldXNjaFwvRlRXXC9GVFdfMTk2MHgxMjA5Xy5wbmciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjExNTB9fX0=",
    whiteout:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9IRUFERVJcL0hFQURFUl9EXzMwMDB4MTAwMC5wbmciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjI1MDB9fX0=",
    freddoAbbigliamento:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9hYmJpZ2xpYW1lbnRvLXRlY25pY28tYmFubmVyLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MTUwMH19fQ==",
    keeperBattleBanner:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9LRUVQRVJiYXR0bGVcL2Jhbm5lci1rZWVwZXJiYXR0bGUucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjoxNTAwfX19",
    keeperBattleHeader:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9LRUVQRVJiYXR0bGVcL2Jhbm5lci1rZWVwZXJiYXR0bGUucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjoyNTAwfX19",
    keeperTraining:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL0tFRVBFUnNwb3J0XC9IZWFkZXJcL0hFQURFUl9LRUVQRVJfSU5TSUdIVF9ELnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MjUwMH19fQ==",
    teaserBePro:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiIyMDIxXC9TdGFydHBhZ2VcL05FVyBTdGFydHBhZ2UgRmlsZXNcL0tTX1NTX1RpbGVzX3dlZWtseV9HTE9WRV9HVUlERV9fMV8ucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo1MDB9fX0=",
    teaserCircleOfPros:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiIyMDIxXC9TdGFydHBhZ2VcL05FVyBTdGFydHBhZ2UgRmlsZXNcL0tTX1NTX1RpbGVzX3dlZWtseV9Db1AucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo1MDB9fX0=",
    teaserRisparmia:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJGVFNfMjUwMHgyNTAwX05MLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NTAwfX19",
    teaserSocial:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiIyMDIxXC9TdGFydHBhZ2VcL05FVyBTdGFydHBhZ2UgRmlsZXNcL0tTX1NTX1RpbGVzX3dlZWtseV9GT0xMT1dfVVMucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo1MDB9fX0=",
    teaserCura:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJleUppZFdOclpYUWlPaUpyWldWd1pYSnpjRzl5ZEMxamRYTjBiMjB0Y0hKdlpIVmpkQzFwYldGblpYTWlMQ0pyWlhraU9pSkxSVVZRUlZKaVlXZHpYM054ZFdGeVpUZ3VjRzVuSWl3aVpXUnBkSE1pT25zaWNtVnphWHBsSWpwN0luZHBaSFJvSWpvNE1EQjlmWDBfLndlYnAiLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjUwMH19fQ==",
    teaserGoalkeeperDay:
        "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJGVFNfMjUwMHgyNTAwLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NTAwfX19",
};

export const KS_BASE = "";

/** Official site favicon (from settings or local asset). */
export const KS_HEADER_LOGO = "/assets_website/logos/logo.webp";

/** @deprecated Internal search — use site header search */
export const KS_HEADER_SEARCH_URL = "/search";

/** @deprecated Internal cart */
export const KS_HEADER_CART_URL = "/cart";

/**
 * Category strip (top nav labels on keepersport.it).
 * Images: homepage / editorial assets from the same CDN.
 */
export const KS_HOME_CATEGORY_STRIP = [
    {
        label: "Guanti da portiere",
        path: "/guanti-da-portiere",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL1NUQVJUU0VJVEVcL2Jhbm5lci1ndWFudGktbm92aXRhLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NjAwfX19",
    },
    {
        label: "Scarpe da calcio",
        path: "/scarpe-da-calcio",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL2FkaWRhc1wvRlRXXC9GVFdfMTk2MHgxMjA5LnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NjAwfX19",
    },
    {
        label: "Abbigliamento da portiere",
        path: "/abbigliamento-da-portiere",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9hYmJpZ2xpYW1lbnRvLXRlY25pY28tYmFubmVyLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NjAwfX19",
    },
    {
        label: "Equipaggiamento per portiere",
        path: "/equipaggiamento-per-portiere",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0tFRVBFUnNwb3J0XC9IRUFERVJcL0hFQURFUl9EXzMwMDB4MTAwMC5wbmciLCJlZGl0cyI6eyJyZXNpemUiOnsid2lkdGgiOjYwMH19fQ==",
    },
    {
        label: "Teamsport",
        path: "/abbigliamento",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiIyMDIxXC9TdGFydHBhZ2VcL05FVyBTdGFydHBhZ2UgRmlsZXNcL0tTX1NTX1RpbGVzX3dlZWtseV9Db1AucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo1MDB9fX0=",
    },
    {
        label: "Saldi",
        path: "/sale",
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJGVFNfMjUwMHgyNTAwX05MLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NTAwfX19",
    },
];

/** Technical apparel section default link */
export const KS_SHOP_TECH_URL = "/goalkeeper-apparel";

/** Uhlsport planet slider — default shop category */
export const KS_UHLSPORT_PLANET_SLIDER_SHOP_URL = "/goalkeeper-gloves";

/**
 * Product cards under planet cover (layout like keepersport.it).
 * Images: keepersport CDN assets (glove/editorial); titles & prices representative.
 */
export const KS_UHLSPORT_PLANET_SLIDER_PRODUCTS = [
    {
        title: "Cybertec Supersoft HN Night Edition",
        priceWas: "70,00 €",
        priceNow: "59,95 €",
        discount_pct: 14,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL1NUQVJUU0VJVEVcL2Jhbm5lci1ndWFudGktbm92aXRhLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NDgwfX19",
    },
    {
        title: "Dynamic Impulse Advance W02",
        priceWas: "65,00 €",
        priceNow: "54,95 €",
        discount_pct: 15,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJGVFNfMjUwMHgyNTAwLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NDgwfX19",
    },
    {
        title: "Dynamic Impulse Superflash HN",
        priceWas: "65,00 €",
        priceNow: "54,95 €",
        discount_pct: 15,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiIyMDIxXC9TdGFydHBhZ2VcL05FVyBTdGFydHBhZ2UgRmlsZXNcL0tTX1NTX1RpbGVzX3dlZWtseV9HTE9WRV9HVUlERV9fMV8ucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo0ODB9fX0=",
    },
    {
        title: "Absolutgrip HN Titanium",
        priceWas: "75,00 €",
        priceNow: "64,95 €",
        discount_pct: 13,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjVcL1NUQVJUU0VJVEVcL2Jhbm5lci1ndWFudGktbm92aXRhLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NDgwfX19",
    },
    {
        title: "Supergrip+ HN Bionik+",
        priceWas: "80,00 €",
        priceNow: "69,95 €",
        discount_pct: 13,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC13ZWJzaXRlLWFzc2V0cy11cy1lYXN0LTEiLCJrZXkiOiJzaXRlc1wvaXRcLzIwMjZcL0hPIFNPQ0NFUlwvRlRXXC9GVFdfMTk2MHgxMjA5X18xNF8ucG5nIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjo0ODB9fX0=",
    },
    {
        title: "Pure Alliance Absolutgrip HN",
        priceWas: "70,00 €",
        priceNow: "59,95 €",
        discount_pct: 14,
        img: "https://images.keepersport.net/eyJidWNrZXQiOiJrZWVwZXJzcG9ydC1wcm9kdWN0LWltYWdlcy11cy1lYXN0LTEiLCJrZXkiOiJGVFNfMjUwMHgyNTAwLnBuZyIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6NDgwfX19",
    },
];

/**
 * Paths for homepage manifesto links (Saudi site — internal routes).
 * Join with locale prefix in Landing.vue.
 */
export const KS_MANIFESTO_PATHS = {
    gloves: "/goalkeeper-gloves",
    cutNegative: "/goalkeeper-gloves",
    cutRegular: "/goalkeeper-gloves",
    cutRollFinger: "/goalkeeper-gloves",
    cutHybrid: "/goalkeeper-gloves",
    glovesWithStays: "/goalkeeper-gloves",
    varan: "/shop/keepersport-goalkeeper-gloves",
    shoes: "/football-boots",
    textiles: "/goalkeeper-apparel",
    jerseys: "/shop/goalkeeper-jerseys",
    pants: "/shop/goalkeeper-pants",
    underwear: "/shop/goalkeeper-underwear",
    keeperdummy: "/goalkeeper-equipment",
    gloveGuide: "/glove-care",
};
