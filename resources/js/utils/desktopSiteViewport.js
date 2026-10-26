/**
 * «موقع سطح المكتب» على الجوال (Android Chrome وغيره).
 * على iOS: لا نغيّر meta viewport — WebKit/Firefox iOS يتعطل أو يعيد التحميل بلا نهاية.
 * iPhone يعرض «سطح المكتب» شبه «الجوال» أصلاً (سلوك Apple، مثل keepersport.net).
 */
import {
    isDesktopPointerEnvironment,
    isLikelyPhoneDevice,
} from "./mobileNavContext.js";

const DEFAULT_VIEWPORT =
    "width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover";

const DESKTOP_LAYOUT_WIDTH = 1024;

let lastAppliedViewport = null;
let viewportHandlerRaf = null;

export function isIOSDevice() {
    if (typeof navigator === "undefined") {
        return false;
    }
    const ua = navigator.userAgent || "";
    if (/iPad|iPhone|iPod/i.test(ua)) {
        return true;
    }
    return navigator.platform === "MacIntel" && (navigator.maxTouchPoints || 0) > 1;
}

export function isDesktopSiteOnMobile() {
    if (typeof window === "undefined") {
        return false;
    }

    // iOS: لا نفعّل وضع desktop-site viewport — المتصفح يتعامل معه بنفسه
    if (isIOSDevice()) {
        return false;
    }

    // لابتوب/ماوس: لا نطبّق وضع «جوال + موقع سطح المكتب»
    if (isDesktopPointerEnvironment()) {
        return false;
    }

    // فقط الهاتف الحقيقي (ليس لابتوب 1366×768 ولا تابلت)
    if (!isLikelyPhoneDevice()) {
        return false;
    }

    const screenW = window.screen?.width ?? 0;
    const screenH = window.screen?.height ?? 0;
    const physicalShortSide =
        screenW > 0 && screenH > 0 ? Math.min(screenW, screenH) : 0;
    const layoutW = window.innerWidth || 0;

    if (physicalShortSide > 0 && physicalShortSide <= 900 && layoutW >= 980) {
        return true;
    }

    return false;
}

function viewportMeta() {
    return document.querySelector('meta[name="viewport"]');
}

function deviceViewportWidth() {
    const screenW = window.screen?.width ?? 0;
    const inner = window.innerWidth || 0;
    return Math.max(320, Math.round(screenW || inner || 390));
}

function desktopSiteInitialScale() {
    const deviceW = deviceViewportWidth();
    const scale = deviceW / DESKTOP_LAYOUT_WIDTH;
    return Math.max(0.25, Math.min(1, scale));
}

function buildViewportContent(active) {
    if (!active) {
        return DEFAULT_VIEWPORT;
    }
    const scaleStr = String(Math.round(desktopSiteInitialScale() * 1000) / 1000);
    return `width=${DESKTOP_LAYOUT_WIDTH}, initial-scale=${scaleStr}, minimum-scale=0.25, maximum-scale=5, user-scalable=yes, viewport-fit=cover`;
}

function setViewportContent(meta, content) {
    if (!meta || content === lastAppliedViewport) {
        return;
    }
    lastAppliedViewport = content;
    meta.setAttribute("content", content);
}

export function applyDesktopSiteViewportFix() {
    if (typeof document === "undefined") {
        return;
    }

    const root = document.documentElement;
    const meta = viewportMeta();
    const active = isDesktopSiteOnMobile();

    root.classList.toggle("ks-desktop-site-mobile", active);

    if (!meta || isIOSDevice()) {
        return;
    }

    setViewportContent(meta, buildViewportContent(active));
}

function scheduleViewportFix(onChange) {
    applyDesktopSiteViewportFix();
    if (viewportHandlerRaf) {
        return;
    }
    viewportHandlerRaf = requestAnimationFrame(() => {
        viewportHandlerRaf = null;
        applyDesktopSiteViewportFix();
        if (typeof onChange === "function") {
            onChange(isDesktopSiteOnMobile());
        }
    });
}

export function watchDesktopSiteViewport(onChange) {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") {
        return () => {};
    }

    if (isIOSDevice()) {
        applyDesktopSiteViewportFix();
        if (typeof onChange === "function") {
            onChange(false);
        }
        return () => {
            document.documentElement.classList.remove("ks-desktop-site-mobile");
        };
    }

    const mqs = [
        window.matchMedia("(max-width: 1024px)"),
        window.matchMedia("(pointer: fine)"),
        window.matchMedia("(hover: hover)"),
    ];

    const handler = () => scheduleViewportFix(onChange);

    mqs.forEach((mq) => {
        if (typeof mq.addEventListener === "function") {
            mq.addEventListener("change", handler);
        } else if (typeof mq.addListener === "function") {
            mq.addListener(handler);
        }
    });

    window.addEventListener("resize", handler, { passive: true });
    window.addEventListener("orientationchange", handler, { passive: true });

    handler();

    return () => {
        if (viewportHandlerRaf) {
            cancelAnimationFrame(viewportHandlerRaf);
            viewportHandlerRaf = null;
        }
        mqs.forEach((mq) => {
            if (typeof mq.removeEventListener === "function") {
                mq.removeEventListener("change", handler);
            } else if (typeof mq.removeListener === "function") {
                mq.removeListener(handler);
            }
        });
        window.removeEventListener("resize", handler);
        window.removeEventListener("orientationchange", handler);
        document.documentElement.classList.remove("ks-desktop-site-mobile");
        const meta = viewportMeta();
        if (meta) {
            lastAppliedViewport = null;
            meta.setAttribute("content", DEFAULT_VIEWPORT);
        }
    };
}
