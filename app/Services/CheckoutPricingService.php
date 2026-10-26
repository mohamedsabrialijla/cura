<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Setting;
use App\Models\ShippingMethod;
use Illuminate\Support\Carbon;

class CheckoutPricingService
{
    public function vatPercent(): float
    {
        $setting = Setting::query()->find(1);
        $raw = $setting ? (float) ($setting->vat ?? 0) : 0.0;

        return $raw > 0 ? $raw : 0.0;
    }

    public function isSaudiCountry(?string $countryCode): bool
    {
        $code = strtoupper(trim((string) $countryCode));

        return in_array($code, ['SA', 'SAU', 'KSA'], true);
    }

    /**
     * أسعار السلة الحالية من الموقع تشمل ضريبة السعودية.
     *
     * @param  list<array<string, mixed>>  $items
     * @return array{
     *   items_count: int,
     *   subtotal_incl_vat: float,
     *   subtotal_excl_vat: float,
     *   tax_amount: float,
     *   discount_amount: float,
     *   shipping_amount: float,
     *   total: float,
     *   vat_percent: float,
     *   is_saudi: bool,
     *   coupon: array<string, mixed>|null,
     *   shipping_method: array<string, mixed>|null,
     *   currency: string
     * }
     */
    public function quote(
        array $items,
        ?string $countryCode = 'SA',
        ?string $couponCode = null,
        ?int $shippingMethodId = null,
    ): array {
        $vatPercent = $this->vatPercent();
        $isSaudi = $this->isSaudiCountry($countryCode ?: 'SA');
        $currency = 'SAR';

        $subtotalIncl = 0.0;
        $count = 0;
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            $price = (float) ($row['price'] ?? 0);
            $qty = max(1, (int) ($row['qty'] ?? 1));
            $subtotalIncl += $price * $qty;
            $count += $qty;
        }
        $subtotalIncl = $this->roundMoney($subtotalIncl);

        // أسعار الكتالوج تشمل ضريبة السعودية — نفك الضريبة أولاً
        $subtotalExcl = $vatPercent > 0
            ? $this->roundMoney($subtotalIncl / (1 + ($vatPercent / 100)))
            : $subtotalIncl;

        $discountBase = $isSaudi ? $subtotalIncl : $subtotalExcl;
        $couponPayload = null;
        $discount = 0.0;
        if ($couponCode !== null && trim($couponCode) !== '') {
            $applied = $this->resolveCoupon(trim($couponCode), $discountBase);
            if (($applied['ok'] ?? false) === true) {
                $discount = (float) $applied['discount'];
                $couponPayload = $applied['coupon'];
            } else {
                return [
                    'ok' => false,
                    'message' => (string) ($applied['message'] ?? 'Invalid coupon'),
                    'items_count' => $count,
                    'subtotal_incl_vat' => $subtotalIncl,
                    'subtotal_excl_vat' => $subtotalExcl,
                    'tax_amount' => 0.0,
                    'discount_amount' => 0.0,
                    'shipping_amount' => 0.0,
                    'total' => $isSaudi ? $subtotalIncl : $subtotalExcl,
                    'vat_percent' => $vatPercent,
                    'is_saudi' => $isSaudi,
                    'coupon' => null,
                    'shipping_method' => null,
                    'currency' => $currency,
                ];
            }
        }

        $shipping = null;
        $shippingAmount = 0.0;
        if ($shippingMethodId) {
            $method = ShippingMethod::query()
                ->where('id', $shippingMethodId)
                ->where('status', 'active')
                ->first();
            if ($method) {
                $shippingAmount = $this->roundMoney((float) $method->price);
                $shipping = [
                    'id' => $method->id,
                    'title_ar' => $method->title_ar,
                    'title_en' => $method->title_en,
                    'delivery_days' => (string) $method->delivery_days,
                    'price' => $shippingAmount,
                ];
            }
        }

        if ($isSaudi) {
            // الإجمالي شامل الضريبة − الخصم + الشحن
            $afterDiscount = max(0, $this->roundMoney($subtotalIncl - $discount));
            // إعادة حساب الضريبة المعروضة بعد الخصم
            $taxAmount = $vatPercent > 0
                ? $this->roundMoney($afterDiscount - ($afterDiscount / (1 + ($vatPercent / 100))))
                : 0.0;
            $goodsNet = $this->roundMoney($afterDiscount - $taxAmount);
            $total = $this->roundMoney($afterDiscount + $shippingAmount);

            return [
                'ok' => true,
                'message' => null,
                'items_count' => $count,
                'subtotal_incl_vat' => $subtotalIncl,
                'subtotal_excl_vat' => $goodsNet,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'shipping_amount' => $shippingAmount,
                'total' => $total,
                'vat_percent' => $vatPercent,
                'is_saudi' => true,
                'coupon' => $couponPayload,
                'shipping_method' => $shipping,
                'currency' => $currency,
            ];
        }

        // خارج السعودية: بدون ضريبة
        $afterDiscount = max(0, $this->roundMoney($subtotalExcl - $discount));
        $total = $this->roundMoney($afterDiscount + $shippingAmount);

        return [
            'ok' => true,
            'message' => null,
            'items_count' => $count,
            'subtotal_incl_vat' => $subtotalIncl,
            'subtotal_excl_vat' => $subtotalExcl,
            'tax_amount' => 0.0,
            'discount_amount' => $discount,
            'shipping_amount' => $shippingAmount,
            'total' => $total,
            'vat_percent' => $vatPercent,
            'is_saudi' => false,
            'coupon' => $couponPayload,
            'shipping_method' => $shipping,
            'currency' => $currency,
        ];
    }

    /**
     * @return array{ok: bool, message?: string, discount?: float, coupon?: array<string, mixed>}
     */
    public function resolveCoupon(string $code, float $orderAmount): array
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '') {
            return ['ok' => false, 'message' => 'Coupon code is required'];
        }

        $coupon = Coupon::query()->where('code', $normalized)->first();
        if (! $coupon) {
            return ['ok' => false, 'message' => 'Coupon not found'];
        }
        if (($coupon->status ?? '') !== 'active') {
            return ['ok' => false, 'message' => 'Coupon is inactive'];
        }
        if ($coupon->expires_at && Carbon::parse($coupon->expires_at)->isPast()) {
            return ['ok' => false, 'message' => 'Coupon has expired'];
        }
        if ($coupon->max_uses !== null && (int) $coupon->used_count >= (int) $coupon->max_uses) {
            return ['ok' => false, 'message' => 'Coupon usage limit reached'];
        }
        if ($coupon->min_order !== null && $orderAmount < (float) $coupon->min_order) {
            return [
                'ok' => false,
                'message' => 'Order amount is below the coupon minimum',
            ];
        }

        $discount = 0.0;
        if ($coupon->type === 'percentage') {
            $discount = $orderAmount * ((float) $coupon->value / 100);
        } else {
            $discount = (float) $coupon->value;
        }
        $discount = min($this->roundMoney($discount), $this->roundMoney($orderAmount));

        return [
            'ok' => true,
            'discount' => $discount,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'discount' => $discount,
            ],
        ];
    }

    public function incrementCouponUsage(?int $couponId): void
    {
        if (! $couponId) {
            return;
        }
        Coupon::query()->where('id', $couponId)->increment('used_count');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activeShippingMethods(): array
    {
        return ShippingMethod::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(static function (ShippingMethod $method) {
                return [
                    'id' => $method->id,
                    'title_ar' => $method->title_ar,
                    'title_en' => $method->title_en,
                    'delivery_days' => (string) $method->delivery_days,
                    'price' => (float) $method->price,
                    'sort_order' => (int) $method->sort_order,
                ];
            })
            ->values()
            ->all();
    }

    private function roundMoney(float $value): float
    {
        return round($value, 2);
    }
}
