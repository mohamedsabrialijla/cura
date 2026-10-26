<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\ShoppingCartSyncService;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CartController extends Controller
{
    public function __construct(
        protected ShoppingCartSyncService $cartSyncService
    ) {
    }

    public function sync(Request $request)
    {
        if (is_string($request->input('items'))) {
            $decoded = json_decode((string) $request->input('items'), true);
            if (is_array($decoded)) {
                $request->merge(['items' => $decoded]);
            }
        }

        $request->validate([
            'cart_token' => 'required|string|max:64',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|string|max:128',
            'items.*.sku' => 'nullable|string|max:128',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.subtitle' => 'nullable|string|max:255',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|integer|min:1',
            'items.*.image' => 'nullable|string|max:2000',
        ]);

        $cart = $this->cartSyncService->sync(
            (string) $request->input('cart_token'),
            $request->input('items', []),
            $this->resolveUserId($request)
        );

        return mainResponse(true, __('common.success'), $cart, 200, 'item', '');
    }

    private function resolveUserId(Request $request): ?int
    {
        $user = $request->user('sanctum');
        if ($user) {
            return (int) $user->id;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable_id ? (int) $accessToken->tokenable_id : null;
    }
}
