/** حد أدنى للسحب الأفقي لتبديل صور المنتج */
export const IMAGE_SWIPE_MIN_PX = 20;

/**
 * @param {{ startX: number, startY: number }} state
 * @returns {number|null} dx سالب = سحب لليسار (التالي)
 */
export function readHorizontalSwipeDelta(state, endX, endY) {
    const dx = endX - state.startX;
    const dy = endY - state.startY;
    if (Math.abs(dx) < IMAGE_SWIPE_MIN_PX) {
        return null;
    }
    if (Math.abs(dy) >= Math.abs(dx)) {
        return null;
    }
    return dx;
}

export function touchPoint(e) {
    return e.touches?.[0] || e.changedTouches?.[0] || null;
}
