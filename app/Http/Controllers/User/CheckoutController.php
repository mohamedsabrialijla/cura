<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\UserAddress;
use App\Services\CheckoutPricingService;
use App\Services\ShoppingCartSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutPricingService $pricing,
        protected ShoppingCartSyncService $cartSync,
    ) {
    }

    public function options(Request $request)
    {
        $user = $this->resolveUser($request);
        $addresses = [];
        if ($user) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('user_addresses')) {
                    $addresses = UserAddress::query()
                        ->where('user_id', $user->id)
                        ->orderByDesc('is_default')
                        ->orderByDesc('id')
                        ->get();
                }
            } catch (\Throwable $e) {
                \Log::warning('Checkout options: addresses unavailable', [
                    'error' => $e->getMessage(),
                ]);
                $addresses = [];
            }
        }

        $shippingMethods = [];
        try {
            $shippingMethods = $this->pricing->activeShippingMethods();
        } catch (\Throwable $e) {
            \Log::error('Checkout options: shipping methods failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $paymentMethods = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('payment_methods')) {
                $paymentMethods = PaymentMethod::query()
                    ->active()
                    ->visibleOnStorefront()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['id', 'code', 'title_ar', 'title_en', 'description_ar', 'description_en', 'sort_order']);
            }
        } catch (\Throwable $e) {
            \Log::warning('Checkout options: payment methods unavailable', [
                'error' => $e->getMessage(),
            ]);
            $paymentMethods = [];
        }

        return response()->json([
            'status' => true,
            'shipping_methods' => $shippingMethods,
            'payment_methods' => $paymentMethods,
            'vat_percent' => $this->pricing->vatPercent(),
            'addresses' => $addresses,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => preg_replace('/\D+/', '', (string) ($user->getAttributes()['mobile'] ?? $user->mobile ?? '')) ?: null,
            ] : null,
        ]);
    }

    public function quote(Request $request)
    {
        if (is_string($request->input('items'))) {
            $decoded = json_decode((string) $request->input('items'), true);
            if (is_array($decoded)) {
                $request->merge(['items' => $decoded]);
            }
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|integer|min:1',
            'country_code' => 'nullable|string|max:8',
            'coupon_code' => 'nullable|string|max:64',
            'shipping_method_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $quote = $this->pricing->quote(
            $request->input('items', []),
            $request->input('country_code', 'SA'),
            $request->input('coupon_code'),
            $request->filled('shipping_method_id') ? (int) $request->input('shipping_method_id') : null,
        );

        return response()->json([
            'status' => (bool) ($quote['ok'] ?? false),
            'message' => $quote['message'] ?? null,
            'quote' => $quote,
        ], ($quote['ok'] ?? false) ? 200 : 422);
    }

    public function saveAddress(Request $request)
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('user_addresses')) {
            return response()->json([
                'status' => false,
                'message' => 'Addresses table is missing. Run: php artisan migrate',
            ], 503);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'required|string|min:8|max:32',
            'country_code' => 'required|string|max:8',
            'country_name' => 'nullable|string|max:120',
            'city' => 'required|string|max:120',
            'district' => 'nullable|string|max:120',
            'street' => 'required|string|max:255',
            'building' => 'nullable|string|max:120',
            'apartment' => 'nullable|string|max:120',
            'postal_code' => 'nullable|string|max:32',
            'national_id' => 'nullable|string|max:32',
            'notes' => 'nullable|string|max:1000',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->resolveUser($request);
        $mobile = preg_replace('/\D+/', '', (string) $request->input('mobile')) ?: (string) $request->input('mobile');
        $payload = [
            'user_id' => $user?->id,
            'full_name' => trim((string) $request->input('full_name')),
            'email' => trim((string) $request->input('email')),
            'mobile' => $mobile,
            'country_code' => strtoupper(trim((string) $request->input('country_code', 'SA'))),
            'country_name' => trim((string) $request->input('country_name', '')),
            'city' => trim((string) $request->input('city')),
            'district' => trim((string) $request->input('district', '')),
            'street' => trim((string) $request->input('street')),
            'building' => trim((string) $request->input('building', '')),
            'apartment' => trim((string) $request->input('apartment', '')),
            'postal_code' => trim((string) (
                $request->input('national_id')
                ?: $request->input('postal_code', '')
            )),
            'notes' => trim((string) $request->input('notes', '')),
            'is_default' => (bool) $request->boolean('is_default'),
        ];

        $address = null;
        if ($user && $request->filled('id')) {
            $address = UserAddress::query()
                ->where('user_id', $user->id)
                ->where('id', (int) $request->input('id'))
                ->first();
        }

        if ($address) {
            $address->update($payload);
        } else {
            $address = UserAddress::create($payload);
        }

        if ($user && $address->is_default) {
            UserAddress::query()
                ->where('user_id', $user->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        return response()->json([
            'status' => true,
            'address' => $address->fresh(),
        ]);
    }

    public function placeOrder(Request $request)
    {
        if (is_string($request->input('items'))) {
            $decoded = json_decode((string) $request->input('items'), true);
            if (is_array($decoded)) {
                $request->merge(['items' => $decoded]);
            }
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.sku' => 'nullable|string|max:128',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'country_code' => 'required|string|max:8',
            'coupon_code' => 'nullable|string|max:64',
            'shipping_method_id' => 'required|integer',
            'payment_method_id' => 'nullable|integer',
            'cart_token' => 'nullable|string|max:64',
            'payment_from' => 'nullable|string|max:32',
            'contact.name' => 'required|string|max:255',
            'contact.email' => 'required|email|max:255',
            'contact.mobile' => 'required|string|min:8|max:32',
            'address.full_name' => 'required|string|max:255',
            'address.city' => 'required|string|max:120',
            'address.street' => 'required|string|max:255',
            'address.district' => 'nullable|string|max:120',
            'address.building' => 'nullable|string|max:120',
            'address.apartment' => 'nullable|string|max:120',
            'address.postal_code' => 'nullable|string|max:32',
            'address.national_id' => 'nullable|string|max:32',
            'address.notes' => 'nullable|string|max:1000',
            'address.country_name' => 'nullable|string|max:120',
            'save_address' => 'nullable|boolean',
            'address_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->resolveUser($request);
        $items = $request->input('items', []);
        $countryCode = strtoupper(trim((string) $request->input('country_code', 'SA')));
        $quote = $this->pricing->quote(
            $items,
            $countryCode,
            $request->input('coupon_code'),
            (int) $request->input('shipping_method_id'),
        );

        if (! ($quote['ok'] ?? false)) {
            return response()->json([
                'status' => false,
                'message' => $quote['message'] ?? 'Could not calculate checkout total',
                'quote' => $quote,
            ], 422);
        }

        if ((float) ($quote['total'] ?? 0) <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid checkout total',
            ], 422);
        }

        $contact = $request->input('contact', []);
        $addressInput = $request->input('address', []);
        $mobile = preg_replace('/\D+/', '', (string) ($contact['mobile'] ?? '')) ?: (string) ($contact['mobile'] ?? '');

        $savedAddress = null;
        $canPersistAddress = \Illuminate\Support\Facades\Schema::hasTable('user_addresses');
        if ($canPersistAddress && ($request->boolean('save_address') || $user)) {
            $addrPayload = [
                'user_id' => $user?->id,
                'full_name' => trim((string) ($addressInput['full_name'] ?? $contact['name'] ?? '')),
                'email' => trim((string) ($contact['email'] ?? '')),
                'mobile' => $mobile,
                'country_code' => $countryCode,
                'country_name' => trim((string) ($addressInput['country_name'] ?? '')),
                'city' => trim((string) ($addressInput['city'] ?? '')),
                'district' => trim((string) ($addressInput['district'] ?? '')),
                'street' => trim((string) ($addressInput['street'] ?? '')),
                'building' => trim((string) ($addressInput['building'] ?? '')),
                'apartment' => trim((string) ($addressInput['apartment'] ?? '')),
                'postal_code' => trim((string) ($addressInput['national_id'] ?? $addressInput['postal_code'] ?? '')),
                'notes' => trim((string) ($addressInput['notes'] ?? '')),
                'is_default' => true,
            ];

            try {
                if ($user && $request->filled('address_id')) {
                    $savedAddress = UserAddress::query()
                        ->where('user_id', $user->id)
                        ->where('id', (int) $request->input('address_id'))
                        ->first();
                }

                if ($savedAddress) {
                    $savedAddress->update($addrPayload);
                } elseif ($request->boolean('save_address') || $user) {
                    $savedAddress = UserAddress::create($addrPayload);
                    if ($user) {
                        UserAddress::query()
                            ->where('user_id', $user->id)
                            ->where('id', '!=', $savedAddress->id)
                            ->update(['is_default' => false]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Checkout placeOrder: could not persist address', [
                    'error' => $e->getMessage(),
                ]);
                $savedAddress = null;
            }
        }

        $cartToken = trim((string) $request->input('cart_token', ''));
        if ($cartToken !== '') {
            $this->cartSync->sync($cartToken, $items, $user?->id);
        }

        $paymentMethod = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('payment_methods')) {
            $pmQuery = PaymentMethod::query()
                ->active()
                ->visibleOnStorefront()
                ->orderBy('sort_order')
                ->orderBy('id');
            if ($request->filled('payment_method_id')) {
                $paymentMethod = (clone $pmQuery)->where('id', (int) $request->input('payment_method_id'))->first();
            }
            if (! $paymentMethod) {
                $paymentMethod = $pmQuery->first();
            }
        }

        if (! $paymentMethod) {
            return response()->json([
                'status' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'لا توجد طريقة دفع مفعّلة حالياً.'
                    : 'No active payment method available.',
            ], 422);
        }

        $methodCode = strtolower((string) $paymentMethod->code);
        if ($methodCode !== 'neoleap') {
            return response()->json([
                'status' => false,
                'message' => app()->getLocale() === 'ar'
                    ? 'طريقة الدفع المحددة غير متاحة إلكترونياً حالياً. فعّل NeoLeap.'
                    : 'Selected payment method is not available online yet. Enable NeoLeap.',
            ], 422);
        }

        $trackId = str_replace('.', '', uniqid('cart', true));
        $amount = number_format((float) $quote['total'], 2, '.', '');

        $payment = Payment::create([
            'track_id' => $trackId,
            'ad_id' => null,
            'amount' => $amount,
            'currency' => 'SAR',
            'status' => 'pending',
            'order_status' => 'new',
            'payment_from' => $request->input('payment_from', 'web'),
            'payment_type' => 'cart',
            'user_id' => $user?->id,
            'raw_response' => [
                'source' => 'shopping_cart',
                'cart_token' => $cartToken !== '' ? $cartToken : null,
                'items' => array_values(array_map(static function ($row) {
                    return [
                        'sku' => $row['sku'] ?? null,
                        'title' => $row['title'] ?? null,
                        'price' => (float) ($row['price'] ?? 0),
                        'qty' => (int) ($row['qty'] ?? 1),
                        'image' => $row['image'] ?? null,
                    ];
                }, $items)),
                'contact' => [
                    'name' => trim((string) ($contact['name'] ?? '')),
                    'email' => trim((string) ($contact['email'] ?? '')),
                    'mobile' => $mobile,
                ],
                'address' => [
                    'id' => $savedAddress?->id,
                    'full_name' => trim((string) ($addressInput['full_name'] ?? '')),
                    'country_code' => $countryCode,
                    'country_name' => trim((string) ($addressInput['country_name'] ?? '')),
                    'city' => trim((string) ($addressInput['city'] ?? '')),
                    'district' => trim((string) ($addressInput['district'] ?? '')),
                    'street' => trim((string) ($addressInput['street'] ?? '')),
                    'building' => trim((string) ($addressInput['building'] ?? '')),
                    'apartment' => trim((string) ($addressInput['apartment'] ?? '')),
                    'postal_code' => trim((string) ($addressInput['national_id'] ?? $addressInput['postal_code'] ?? '')),
                    'national_id' => trim((string) ($addressInput['national_id'] ?? $addressInput['postal_code'] ?? '')),
                    'notes' => trim((string) ($addressInput['notes'] ?? '')),
                ],
                'quote' => $quote,
                'payment_method' => $methodCode,
                'payment_method_meta' => [
                    'id' => $paymentMethod->id,
                    'code' => $paymentMethod->code,
                    'title_ar' => $paymentMethod->title_ar,
                    'title_en' => $paymentMethod->title_en,
                ],
            ],
        ]);

        return response()->json([
            'status' => true,
            'payment_url' => url('/neoleap/pay?payment_id='.$payment->track_id),
            'payment_id' => $payment->track_id,
            'quote' => $quote,
        ]);
    }

    private function resolveUser(Request $request)
    {
        $user = $request->user('sanctum');
        if ($user) {
            return $user;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }
}
