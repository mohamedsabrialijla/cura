<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>KEEPERsport Invoice | فاتورة كيبرسبورت</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111;
            font-size: 12px;
            margin: 0;
            padding: 24px;
            background: #fff;
        }
        .head {
            display: table;
            width: 100%;
            border-bottom: 3px solid #ffcc00;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .brand, .meta { display: table-cell; vertical-align: top; width: 50%; }
        .brand strong { font-size: 18px; display: block; }
        .brand span { color: #666; }
        .meta div { margin: 4px 0; }
        .meta span { color: #666; }
        .parties {
            width: 100%;
            margin-bottom: 16px;
        }
        .parties td {
            width: 33%;
            vertical-align: top;
            padding: 8px;
            border: 1px solid #eee;
        }
        .parties h3 {
            margin: 0 0 6px;
            color: #8a7200;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            table-layout: fixed;
            direction: rtl;
        }
        table.items th {
            background: #111;
            color: #ffcc00;
            padding: 8px;
            font-weight: bold;
        }
        table.items td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        table.items .col-desc { width: 52%; text-align: right; }
        table.items .col-num { width: 16%; text-align: center; white-space: nowrap; }
        .totals {
            width: 45%;
            margin-right: auto;
            border: 1px solid #eee;
            padding: 10px;
        }
        .totals div {
            display: table;
            width: 100%;
            margin: 6px 0;
        }
        .totals span, .totals strong {
            display: table-cell;
        }
        .totals strong { text-align: left; }
        .grand { font-size: 14px; font-weight: bold; border-top: 1px solid #ddd; padding-top: 8px; }
        .foot {
            margin-top: 24px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .sku { color: #777; font-size: 10px; }
        .paid { color: #067647; font-weight: bold; }
        .unpaid { color: #b42318; font-weight: bold; }
    </style>
</head>
<body>
@php
    $raw = is_array($invoice->raw_response ?? null) ? $invoice->raw_response : [];
    $quote = is_array($raw['quote'] ?? null) ? $raw['quote'] : [];
    $contact = is_array($raw['contact'] ?? null) ? $raw['contact'] : [];
    $address = is_array($raw['address'] ?? null) ? $raw['address'] : [];
    $items = [];
    if (!empty($invoice->cart_items) && is_array($invoice->cart_items)) {
        $items = $invoice->cart_items;
    } elseif (!empty($raw['items']) && is_array($raw['items'])) {
        $items = $raw['items'];
    } elseif (!empty($invoice->ad?->title)) {
        $items = [['title' => $invoice->ad->title, 'qty' => 1, 'price' => $invoice->amount]];
    } else {
        $items = [['title' => $invoice->order_title ?? 'Order', 'qty' => 1, 'price' => $invoice->amount]];
    }
    $isSaudi = (bool) ($quote['is_saudi'] ?? true);
    $subtotal = (float) ($isSaudi ? ($quote['subtotal_incl_vat'] ?? $invoice->amount) : ($quote['subtotal_excl_vat'] ?? $invoice->amount));
    $discount = (float) ($quote['discount_amount'] ?? 0);
    $shipping = (float) ($quote['shipping_amount'] ?? 0);
    $tax = (float) ($quote['tax_amount'] ?? 0);
    $vatPercent = (float) ($quote['vat_percent'] ?? 0);
    $total = (float) ($quote['total'] ?? $invoice->amount);
    $customerName = $contact['name'] ?? ($address['full_name'] ?? ($invoice->user->name ?? '—'));
    $customerEmail = $contact['email'] ?? ($invoice->user->email ?? '');
    $customerMobile = $contact['mobile'] ?? ($address['mobile'] ?? ($invoice->user->mobile ?? ''));
    $isPaid = (bool) ($invoice->is_paid ?? in_array(strtolower((string) $invoice->status), ['paid', 'successful', 'success'], true));
    $shipMethod = is_array($quote['shipping_method'] ?? null) ? $quote['shipping_method'] : null;
    $logoUrl = null;
    $setting = \App\Models\Setting::query()->first();
    if ($setting) {
        $rawLogo = $setting->getRawOriginal('logo');
        if ($rawLogo) {
            $candidates = [
                public_path('uploads/settings/'.$rawLogo),
                public_path($rawLogo),
            ];
            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    $logoUrl = $candidate;
                    break;
                }
            }
        }
    }
@endphp

<div class="head">
    <div class="brand">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="KEEPERsport" style="height:48px;margin-bottom:8px;" />
        @endif
        <strong>KEEPERsport</strong>
        <span>فاتورة ضريبية مبسطة / Simplified Tax Invoice</span>
    </div>
    <div class="meta">
        <div><span>رقم الفاتورة / Invoice:</span> <strong>{{ $invoice->order_number }}</strong></div>
        <div><span>المرجع / Ref:</span> <strong>{{ $invoice->track_id }}</strong></div>
        <div><span>التاريخ / Date:</span> <strong>{{ $invoice->created_at }}</strong></div>
        <div>
            <span>حالة الدفع / Payment:</span>
            <strong class="{{ $isPaid ? 'paid' : 'unpaid' }}">{{ $isPaid ? 'مدفوعة / Paid' : 'غير مدفوعة / Unpaid' }}</strong>
        </div>
        <div>
            <span>حالة الطلب / Order:</span>
            <strong>{{ $invoice->order_status_label_ar ?? 'طلب جديد' }}</strong>
        </div>
    </div>
</div>

<table class="parties">
    <tr>
        <td>
            <h3>العميل / Customer</h3>
            <div><strong>{{ $customerName }}</strong></div>
            @if($customerEmail)<div>{{ $customerEmail }}</div>@endif
            @if($customerMobile)<div>{{ $customerMobile }}</div>@endif
        </td>
        <td>
            <h3>عنوان التوصيل / Delivery</h3>
            @if(!empty($address['full_name']) || !empty($address['street']))
                <div>{{ $address['full_name'] ?? '' }}</div>
                <div>{{ collect([$address['street'] ?? null, $address['district'] ?? null, $address['city'] ?? null])->filter()->implode('، ') }}</div>
                <div>{{ ($address['country_code'] ?? '') }}</div>
                @php
                    $nationalId = $address['national_id'] ?? ($address['postal_code'] ?? '');
                @endphp
                @if($nationalId)
                    <div>الرقم الوطني / National ID: {{ $nationalId }}</div>
                @endif
            @else
                <div>—</div>
            @endif
        </td>
        <td>
            <h3>البائع / Seller</h3>
            <div><strong>KEEPERsport Saudi Arabia</strong></div>
            <div>Goalkeeper equipment e-commerce</div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th class="col-desc">الوصف / Description</th>
            <th class="col-num">الكمية / Qty</th>
            <th class="col-num">السعر / Price</th>
            <th class="col-num">المجموع / Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $row)
            @php
                $qty = (int) ($row['qty'] ?? 1);
                $price = (float) ($row['price'] ?? 0);
                $line = round($price * max(1, $qty), 2);
            @endphp
            <tr>
                <td class="col-desc">
                    <strong>{{ $row['title'] ?? ($row['sku'] ?? 'Item') }}</strong>
                    @if(!empty($row['sku']))
                        <div class="sku">{{ $row['sku'] }}</div>
                    @endif
                </td>
                <td class="col-num">{{ $qty }}</td>
                <td class="col-num">{{ number_format($price, 2) }}</td>
                <td class="col-num">{{ number_format($line, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div><span>المجموع الفرعي / Subtotal</span><strong>{{ number_format($subtotal, 2) }}</strong></div>
    @if($discount > 0)
        <div><span>الخصم / Discount</span><strong>- {{ number_format($discount, 2) }}</strong></div>
    @endif
    <div><span>الشحن / Shipping</span><strong>{{ number_format($shipping, 2) }}</strong></div>
    @if($tax > 0)
        <div><span>الضريبة / VAT ({{ $vatPercent }}%)</span><strong>{{ number_format($tax, 2) }}</strong></div>
    @else
        <div><span>الضريبة / Tax</span><strong>غير مطبّقة / N/A</strong></div>
    @endif
    <div class="grand"><span>الإجمالي / Total</span><strong>{{ number_format($total, 2) }} SAR</strong></div>
    <div style="margin-top:10px;">
        طريقة الدفع / Payment: <strong>{{ strtoupper((string) ($raw['payment_method'] ?? $invoice->payment_type ?? 'neoleap')) }}</strong>
    </div>
    @if($shipMethod)
        <div>
            الشحن / Shipping method:
            <strong>{{ $shipMethod['title_ar'] ?? ($shipMethod['title_en'] ?? '') }}</strong>
        </div>
    @endif
    @if(!empty($quote['coupon']['code']))
        <div>كوبون / Coupon: <strong>{{ $quote['coupon']['code'] }}</strong></div>
    @endif
</div>

<div class="foot">
    KEEPERsport — شكراً لتسوقك معنا / Thank you for shopping with us
</div>
</body>
</html>
