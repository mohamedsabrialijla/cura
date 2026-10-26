/**
 * أرقام وأسعار الموقع العام: دائمًا أرقام لاتينية (0–9) وتنسيق en-US
 * حتى عندما تكون واجهة المستخدم عربية (RTL).
 */
export const WEBSITE_NUMBER_LOCALE = "en-US";

/**
 * @param {number|string} amount
 * @param {string} currency
 * @param {Intl.NumberFormatOptions} [options]
 */
export function formatWebsiteCurrency(amount, currency = "EUR", options = {}) {
    return new Intl.NumberFormat(WEBSITE_NUMBER_LOCALE, {
        style: "currency",
        currency: currency || "EUR",
        ...options,
    }).format(Number(amount || 0));
}

/**
 * @param {number|string} amount
 * @param {string} currency
 * @param {Intl.NumberFormatOptions} [options]
 * @returns {Intl.NumberFormatPart[]}
 */
export function formatWebsiteCurrencyParts(amount, currency = "EUR", options = {}) {
    return new Intl.NumberFormat(WEBSITE_NUMBER_LOCALE, {
        style: "currency",
        currency: currency || "EUR",
        ...options,
    }).formatToParts(Number(amount || 0));
}

/** لدمج أرقام داخل نص عربي بدون HTML: عزل اتجاه يسار-يمين للأرقام */
export function embedWesternDigits(value) {
    return `\u2066${String(value)}\u2069`;
}
