import sarSymbolArUrl from "@/assets/currency/sar-symbol-ar.png";
import { formatWebsiteCurrency, formatWebsiteCurrencyParts, WEBSITE_NUMBER_LOCALE } from "@/utils/westernNumerals";

export { sarSymbolArUrl };

export function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

export function currencySuffixHtml(locale) {
    if (locale === "ar") {
        const src = escapeHtml(sarSymbolArUrl);
        return `<img class="ks-currency-icon" src="${src}" alt="ر.س" width="22" height="16" loading="lazy" decoding="async" />`;
    }
    return '<span class="ks-currency-label" lang="en">SAR</span>';
}

/** عربي: الرقم يمين + شعار ر.س يسار (dir=rtl) */
export function wrapArabicPriceHtml(amountHtml, locale = "ar") {
    return (
        `<span class="ks-price-with-currency ks-price-with-currency--ar" dir="rtl" lang="ar">` +
        `<span class="ks-price-amount" dir="ltr" lang="en">${amountHtml}</span>` +
        `${currencySuffixHtml(locale)}</span>`
    );
}

/** إنجليزي: الرقم ثم SAR على يمينه (dir=ltr) */
export function wrapEnglishPriceHtml(amountHtml) {
    return (
        `<span class="ks-price-with-currency ks-price-with-currency--en" dir="ltr" lang="en">` +
        `<span class="ks-price-amount" dir="ltr" lang="en">${amountHtml}</span>` +
        `${currencySuffixHtml("en")}</span>`
    );
}

function formatPlainAmountHtml(amount, currency = "SAR", options = {}, classNames = {}) {
    const amountHtml = priceAmountPartsHtml(amount, currency, options, classNames);
    if (amountHtml) {
        return amountHtml;
    }
    const plain = new Intl.NumberFormat(WEBSITE_NUMBER_LOCALE, {
        minimumFractionDigits: options.minimumFractionDigits ?? 2,
        maximumFractionDigits: options.maximumFractionDigits ?? 2,
    }).format(Number(amount || 0));
    return escapeHtml(plain);
}

/**
 * أجزاء السعر بدون رمز العملة (للعربي) — أرقام لاتينية مع كسور علوية اختيارية.
 */
export function priceAmountPartsHtml(amount, currency = "SAR", options = {}, classNames = {}) {
    const majorClass = classNames.majorClass || "ks-price-major";
    const minorClass = classNames.minorClass || "ks-price-minor";
    const parts = formatWebsiteCurrencyParts(Number(amount || 0), currency || "SAR", {
        maximumFractionDigits: 2,
        ...options,
    });
    if (!parts.length) {
        return null;
    }

    let html = "";
    let supOpen = false;
    for (const p of parts) {
        if (p.type === "currency") {
            continue;
        }
        if (p.type === "integer" || p.type === "group" || p.type === "major") {
            if (supOpen) {
                html += "</sup>";
                supOpen = false;
            }
            html += `<span class="${majorClass}">${escapeHtml(p.value)}</span>`;
        } else if (p.type === "decimal" || p.type === "fraction") {
            if (!supOpen) {
                html += `<sup class="${minorClass}">`;
                supOpen = true;
            }
            html += escapeHtml(p.value);
        } else if (p.type === "literal" || p.type === "minusSign") {
            html += escapeHtml(p.value);
        }
    }
    if (supOpen) {
        html += "</sup>";
    }
    return html;
}

/** سعر كامل HTML — عربي: شعار يسار الرقم | إنجليزي: الرقم ثم SAR يمينه */
export function formatWebsitePriceHtml(amount, currency = "SAR", locale = "en", options = {}) {
    const classNames = { majorClass: "ks-price-major", minorClass: "ks-price-minor" };
    if (locale === "ar") {
        const amountHtml = priceAmountPartsHtml(amount, currency, options, classNames);
        if (!amountHtml) {
            return wrapArabicPriceHtml(
                escapeHtml(formatWebsiteCurrency(amount, currency, options)),
                locale
            );
        }
        return wrapArabicPriceHtml(amountHtml, locale);
    }
    return wrapEnglishPriceHtml(formatPlainAmountHtml(amount, currency, options, classNames));
}

/** PLP / صفحة المنتج — عملة + رقم كبير + كسور علوية */
export function pricePromoHtml(amount, currency = "SAR", locale = "en", options = {}) {
    const parts = formatWebsiteCurrencyParts(Number(amount || 0), currency || "SAR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        ...options,
    });
    let html = "";
    let supOpen = false;
    for (const p of parts) {
        if (p.type === "currency") {
            continue;
        }
        if (p.type === "integer" || p.type === "group" || p.type === "major") {
            if (supOpen) {
                html += "</sup>";
                supOpen = false;
            }
            html += `<span class="ks-sale-major">${escapeHtml(p.value)}</span>`;
        } else if (p.type === "decimal" || p.type === "fraction") {
            if (!supOpen) {
                html += '<sup class="ks-sale-minor">';
                supOpen = true;
            }
            html += escapeHtml(p.value);
        } else if (p.type === "literal" || p.type === "minusSign") {
            html += escapeHtml(p.value);
        }
    }
    if (supOpen) {
        html += "</sup>";
    }
    if (locale === "ar") {
        return wrapArabicPriceHtml(html, locale);
    }
    return wrapEnglishPriceHtml(html);
}
