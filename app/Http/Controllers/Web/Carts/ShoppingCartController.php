<?php

namespace App\Http\Controllers\Web\Carts;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function getAll(Request $request)
    {
        $query = ShoppingCart::query()
            ->with(['user:id,name,email', 'items'])
            ->where('status', 'active')
            ->where('items_count', '>', 0);

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('cart_token', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
            });
        }

        if ((int) $request->input('pagination', 1) === 1) {
            $items = $query->orderByDesc('last_activity_at')->paginate(15);
        } else {
            $items = $query->orderByDesc('last_activity_at')->get();
        }

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }

    public function getById(Request $request)
    {
        $request->validate([
            'ID' => 'required|integer',
        ]);

        $item = ShoppingCart::query()
            ->with(['user:id,name,email', 'items'])
            ->where('id', $request->input('ID'))
            ->first();

        if (! $item) {
            return mainResponse(false, __('common.error'), null, 404, 'item', '');
        }

        return mainResponse(true, __('common.return'), $item, 200, 'item', '');
    }

    public function deleteItem(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $cart = ShoppingCart::query()->find($request->input('id'));
        if (! $cart) {
            return mainResponse(false, __('common.error'), null, 404, 'item', '');
        }

        $cart->items()->delete();
        $cart->delete();

        return mainResponse(true, __('common.success'), null, 200, 'item', '');
    }
}
