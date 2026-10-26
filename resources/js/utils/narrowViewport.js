import { watchDesktopSiteViewport } from "./desktopSiteViewport.js";
import { isLikelyPhoneDevice } from "./mobileNavContext.js";

/** عرض ضيق للجوال — صور الغلاف والبانرات. */
export const NARROW_VIEWPORT_MEDIA_QUERY = "(max-width: 1024px)";

export function isNarrowViewport() {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
        return false;
    }
    // هواتف حقيقية: غلاف الجوال دائمًا (حتى في وضع «موقع سطح المكتب»)
    if (isLikelyPhoneDevice()) {
        return true;
    }
    return window.matchMedia(NARROW_VIEWPORT_MEDIA_QUERY).matches;
}

export function watchNarrowViewport(onChange) {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
        return () => {};
    }

    const mq = window.matchMedia(NARROW_VIEWPORT_MEDIA_QUERY);
    const desktopMq = [
        window.matchMedia("(pointer: fine)"),
        window.matchMedia("(hover: hover)"),
    ];

    const handler = () => {
        if (typeof onChange === "function") {
            onChange(isNarrowViewport());
        }
    };

    if (typeof mq.addEventListener === "function") {
        mq.addEventListener("change", handler);
    } else if (typeof mq.addListener === "function") {
        mq.addListener(handler);
    }

    desktopMq.forEach((dmq) => {
        if (typeof dmq.addEventListener === "function") {
            dmq.addEventListener("change", handler);
        } else if (typeof dmq.addListener === "function") {
            dmq.addListener(handler);
        }
    });

    window.addEventListener("resize", handler, { passive: true });
    window.addEventListener("orientationchange", handler, { passive: true });

    const unwatchDesktop = watchDesktopSiteViewport(handler);

    handler();

    return () => {
        unwatchDesktop();
        if (typeof mq.removeEventListener === "function") {
            mq.removeEventListener("change", handler);
        } else if (typeof mq.removeListener === "function") {
            mq.removeListener(handler);
        }
        desktopMq.forEach((dmq) => {
            if (typeof dmq.removeEventListener === "function") {
                dmq.removeEventListener("change", handler);
            } else if (typeof dmq.removeListener === "function") {
                dmq.removeListener(handler);
            }
        });
        window.removeEventListener("resize", handler);
        window.removeEventListener("orientationchange", handler);
    };
}
