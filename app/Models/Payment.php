<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'track_id',
        'payment_id',
        'ad_id',
        'amount',
        'currency',
        'status',
        'order_status',
        'raw_response',
        'payment_from',
        'payment_type',
        'user_id',
        'type'
    ];

    public const ORDER_STATUSES = [
        'new',
        'preparing',
        'shipped',
        'delivered',
        'cancelled',
    ];

    public const ORDER_STATUS_LABELS_AR = [
        'new' => 'طلب جديد',
        'preparing' => 'جاري التجهيز',
        'shipped' => 'تم الشحن',
        'delivered' => 'تم التسليم',
        'cancelled' => 'ملغي',
    ];

    public const ORDER_STATUS_LABELS_EN = [
        'new' => 'New order',
        'preparing' => 'Preparing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $casts = [
        'raw_response' => 'array', 
    ];

    public $appends = ['order_number','url_invoice','name','barCode','cart_items','is_cart_order','order_title','is_paid','order_status_label_ar','payment_status_label_ar'];

    public function getCreatedAtAttribute($value){
        return date('Y/m/d', strtotime($value));
    }

    public function getIsPaidAttribute(): bool
    {
        $s = strtolower((string) $this->status);

        return in_array($s, ['paid', 'successful', 'success'], true);
    }

    public function getOrderStatusLabelArAttribute(): string
    {
        $key = $this->order_status ?: 'new';

        return self::ORDER_STATUS_LABELS_AR[$key] ?? $key;
    }

    public function getPaymentStatusLabelArAttribute(): string
    {
        return $this->is_paid ? 'مدفوع' : 'غير مدفوعة';
    }

    public function getIsCartOrderAttribute(): bool
    {
        $raw = is_array($this->raw_response) ? $this->raw_response : [];

        return ($this->payment_type ?? '') === 'cart'
            || ($raw['source'] ?? null) === 'shopping_cart';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getCartItemsAttribute(): array
    {
        $raw = is_array($this->raw_response) ? $this->raw_response : [];
        $items = $raw['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter($items, 'is_array'));
    }

    public function getOrderTitleAttribute(): string
    {
        if ($this->is_cart_order) {
            $titles = array_values(array_filter(array_map(
                static fn ($row) => trim((string) ($row['title'] ?? $row['sku'] ?? '')),
                $this->cart_items
            )));
            if ($titles === []) {
                return 'Shopping cart order';
            }
            if (count($titles) === 1) {
                return $titles[0];
            }

            return $titles[0].' +'.(count($titles) - 1);
        }

        return (string) (optional($this->ad)->title ?? 'Order');
    }
    

    public function getOrderNumberAttribute()
    {
        return 'INV-'. (int)$this->amount. '-' . '000'. $this->id;
    }


    public function getNameAttribute()
    {
        return 'Electronic Invoice / فاتورة الكترونية - '. $this->created_at;
        // return $this->track_id .'-'. $this->created_at .'-'. $this->amount ;
    }

 
    public function ad()
    {
        return $this->belongsTo(Ad::class,'ad_id');
    }

    public function payment_type()
    {
        return $this->belongsTo(ItemsCategories::class, 'payment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUrlInvoiceAttribute()
    {
        return url(app()->getLocale().'/invoice/'.$this->track_id);
    }


    public function getBarCodeAttribute()
    {
        $raw = is_array($this->raw_response) ? $this->raw_response : [];
        $quote = is_array($raw['quote'] ?? null) ? $raw['quote'] : [];
        $vatPercent = (float) ($quote['vat_percent'] ?? 15);
        $totalAmount = number_format((float) ($quote['total'] ?? $this->amount ?? 0), 2, '.', '');
        $vatAmount = number_format((float) ($quote['tax_amount'] ?? ((float) $totalAmount * ($vatPercent / (100 + $vatPercent)))), 2, '.', '');

        $sellerName = 'KEEPERsport';
        $vatNumber = (string) (config('services.keepersport.vat_number') ?? env('KEEPERSPORT_VAT_NUMBER', '300000000000003'));
        $invoiceDate = \Carbon\Carbon::parse($this->getRawOriginal('created_at') ?? now())->toIso8601String();

        $tlvs = [
            [1, $sellerName],
            [2, $vatNumber],
            [3, $invoiceDate],
            [4, $totalAmount],
            [5, $vatAmount],
        ];

        $qrString = '';
        foreach ($tlvs as $tlv) {
            $value = (string) $tlv[1];
            $length = strlen($value);
            $qrString .= chr($tlv[0]).chr($length).$value;
        }

        $qrBase64 = base64_encode($qrString);

        return (string) \QrCode::format('svg')->size(200)->generate($qrBase64);
    }

}
