<?php

namespace App\Services;

use App\Models\ShoppingCart;
use Illuminate\Support\Facades\DB;

class ShoppingCartSyncService
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function sync(string $cartToken, array $items, ?int $userId = null): ?ShoppingCart
    {
        $cartToken = trim($cartToken);
        if ($cartToken === '') {
            return null;
        }

        $normalized = $this->normalizeItems($items);

        return DB::transaction(function () use ($cartToken, $normalized, $userId) {
            if ($normalized === []) {
                $existing = ShoppingCart::query()->where('cart_token', $cartToken)->first();
                if ($existing) {
                    $existing->items()->delete();
                    $existing->delete();
                }

                return null;
            }

            $cart = ShoppingCart::query()->firstOrNew(['cart_token' => $cartToken]);
            if ($userId) {
                $cart->user_id = $userId;
            }
            $cart->status = 'active';
            $cart->currency = 'SAR';
            $cart->last_activity_at = now();
            $cart->save();

            $cart->items()->delete();

            $itemsCount = 0;
            $subtotal = 0.0;

            foreach ($normalized as $row) {
                $cart->items()->create($row);
                $itemsCount += $row['qty'];
                $subtotal += $row['price'] * $row['qty'];
            }

            $cart->update([
                'items_count' => $itemsCount,
                'subtotal' => round($subtotal, 2),
            ]);

            return $cart->fresh(['items', 'user:id,name,email']);
        });
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        $out = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productKey = trim((string) ($item['id'] ?? $item['sku'] ?? ''));
            if ($productKey === '') {
                continue;
            }

            $qty = max(1, (int) ($item['qty'] ?? 1));
            $price = max(0, (float) ($item['price'] ?? 0));

            $out[] = [
                'product_key' => $productKey,
                'sku' => (string) ($item['sku'] ?? $productKey),
                'title' => (string) ($item['title'] ?? 'Product'),
                'subtitle' => (string) ($item['subtitle'] ?? ''),
                'price' => $price,
                'qty' => $qty,
                'image' => (string) ($item['image'] ?? ''),
            ];
        }

        return $out;
    }
}
