/**
 * قائمة الجوال (☰) على الهاتف والأجهزة اللمسية بدون فأرة.
 * اللابتوب (حتى مع شاشة لمس): شريط تنقل أفقي — بدون ☰.
 */

/** مؤشر دقيق + hover = بيئة سطح مكتب (لابتوب/ماوس)، وليس هاتفاً. */
export function isDesktopPointerEnvironment() {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
        return false;
    }
    return window.matchMedia("(hover: hover) and (pointer: fine)").matches;
}

function hasTouchCapability() {
    if (typeof window === "undefined") {
        return false;
    }
    return (
        (typeof navigator !== "undefined" && (navigator.maxTouchPoints || 0) > 0) ||
        "ontouchstart" in window
    );
}

function physicalShortSide() {
    if (typeof window === "undefined") {
        return 0;
    }
    const screenW = window.screen?.width ?? 0;
    const screenH = window.screen?.height ?? 0;
    return screenW > 0 && screenH > 0 ? Math.min(screenW, screenH) : 0;
}

function isPhoneUserAgent() {
    const ua = typeof navigator !== "undefined" ? navigator.userAgent || "" : "";
    if (/iPhone|iPod/i.test(ua)) {
        return true;
    }
    if (/Android/i.test(ua) && /Mobile/i.test(ua)) {
        return true;
    }
    if (/Windows Phone/i.test(ua)) {
        return true;
    }
    return false;
}

/** هاتف حقيقي (ليس لابتوب 1366×768 ولا تابلت عريض). */
export function isPhysicalPhoneDevice() {
    if (!hasTouchCapability()) {
        return false;
    }

    const short = physicalShortSide();
    if (short <= 0) {
        return false;
    }

    if (isPhoneUserAgent()) {
        return short <= 900;
    }

    return short <= 480;
}

/** هاتف حقيقي حتى لو «مصمم للكمبيوتر» غيّر العرض (User-Agent + لمس). */
export function isLikelyPhoneDevice() {
    if (typeof window === "undefined") {
        return false;
    }

    if (isPhysicalPhoneDevice()) {
        return true;
    }

    if (isPhoneUserAgent()) {
        return true;
    }

    const layoutW = window.innerWidth || 0;
    const short = physicalShortSide();

    // عرض واسع مصطنع (desktop site) مع شاشة فيزيائية صغيرة — هاتف فقط
    if (hasTouchCapability() && layoutW >= 960 && short > 0 && short <= 520) {
        return true;
    }

    if (isPhoneUserAgent() && hasTouchCapability() && layoutW >= 960 && short > 0 && short <= 900) {
        return true;
    }

    return false;
}

export function isMobileNavContext() {
    if (typeof window === "undefined") {
        return false;
    }

    if (isLikelyPhoneDevice()) {
        return true;
    }

    if (isDesktopPointerEnvironment()) {
        return false;
    }

    const layoutW = window.innerWidth || 0;

    if (layoutW > 1024) {
        return false;
    }

    if (hasTouchCapability()) {
        return true;
    }

    if (typeof window.matchMedia !== "function") {
        return layoutW <= 1024;
    }

    return (
        window.matchMedia("(max-width: 1024px)").matches &&
        (window.matchMedia("(pointer: coarse)").matches ||
            window.matchMedia("(hover: none)").matches)
    );
}

export function watchMobileNavContext(onChange) {
    if (typeof window === "undefined") {
        return () => {};
    }

    const handler = () => onChange(isMobileNavContext());

    const mqs =
        typeof window.matchMedia === "function"
            ? [
                  window.matchMedia("(max-width: 1024px)"),
                  window.matchMedia("(pointer: coarse)"),
                  window.matchMedia("(hover: none)"),
                  window.matchMedia("(pointer: fine)"),
                  window.matchMedia("(hover: hover)"),
              ]
            : [];

    mqs.forEach((mq) => {
        if (typeof mq.addEventListener === "function") {
            mq.addEventListener("change", handler);
        } else if (typeof mq.addListener === "function") {
            mq.addListener(handler);
        }
    });

    window.addEventListener("resize", handler, { passive: true });
    window.addEventListener("orientationchange", handler, { passive: true });
    if (window.visualViewport) {
        window.visualViewport.addEventListener("resize", handler, { passive: true });
    }

    handler();

    return () => {
        mqs.forEach((mq) => {
            if (typeof mq.removeEventListener === "function") {
                mq.removeEventListener("change", handler);
            } else if (typeof mq.removeListener === "function") {
                mq.removeListener(handler);
            }
        });
        window.removeEventListener("resize", handler);
        window.removeEventListener("orientationchange", handler);
        if (window.visualViewport) {
            window.visualViewport.removeEventListener("resize", handler);
        }
    };
}
