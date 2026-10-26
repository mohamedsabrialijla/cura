<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ItemsCategories;
use App\Models\Setting;



use App\Mail\WelcomeUserMail;
use App\Mail\SendUserPasswordMail;

use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use App\Models\Ad;
use Illuminate\Support\Facades\Cache;

use App\Models\Language;
use App\Models\Payment;

use DB;




 

class PaymentController extends Controller
{
  
    public function __construct() {
        $this->locales = Language::all();
        view()->share([
            'locales' => $this->locales,

        ]);
    }

  
 

    public function generateUrwayPaymentLink(Request $request)
    {
        $user = auth('sanctum')->user();
        $trackId = uniqid();
        $amount   = number_format((float) $request->amount, 2, '.', '');
        $currency = 'SAR';

        $payment = Payment::create([
            'track_id'      => $trackId,
            'ad_id'         => $request->ad_id,
            'amount'        => $amount,
            'currency'      => $currency,
            'status'        => 'pending',
            'payment_from'  => $request->payment_from ?? 'web',
            'payment_type'  => 'commission',
            'user_id'       => $user?->id,
        ]);

        $paymentUrl = url('/neoleap/pay?payment_id=' . $payment->track_id);
        return response()->json([
            'status'       => true,
            'payment_url'  => $paymentUrl,
            'payment_id'   => $payment->track_id,
        ]);
    }

    /**
     * دفع سلة التسوق → إنشاء دفعة وربط NeoLeap (بدون صفحة عمولة براندي).
     */
    public function generateCartPaymentLink(Request $request)
    {
        if (is_string($request->input('items'))) {
            $decoded = json_decode((string) $request->input('items'), true);
            if (is_array($decoded)) {
                $request->merge(['items' => $decoded]);
            }
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'items' => 'nullable|array',
            'items.*.sku' => 'nullable|string|max:128',
            'items.*.title' => 'nullable|string|max:255',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.qty' => 'nullable|integer|min:1',
            'cart_token' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first() ?: 'Invalid cart payment request',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth('sanctum')->user();
        $amount = number_format((float) $request->input('amount'), 2, '.', '');
        $trackId = str_replace('.', '', uniqid('cart', true));

        $payment = Payment::create([
            'track_id' => $trackId,
            'ad_id' => null,
            'amount' => $amount,
            'currency' => 'SAR',
            'status' => 'pending',
            'payment_from' => $request->input('payment_from', 'web'),
            'payment_type' => 'cart',
            'user_id' => $user?->id,
            'raw_response' => [
                'source' => 'shopping_cart',
                'cart_token' => $request->input('cart_token'),
                'items' => $request->input('items', []),
            ],
        ]);

        return response()->json([
            'status' => true,
            'payment_url' => url('/neoleap/pay?payment_id=' . $payment->track_id),
            'payment_id' => $payment->track_id,
        ]);
    }



  

 
   

   public function handleCallback(Request $request)
    {
        $paymentResult = $request->all();
                // dd($paymentResult);

        \Log::info('URWAY CALLBACK RECEIVED', $paymentResult);

        $payment = Payment::where('track_id', $paymentResult['TrackId'] ?? null)->first();

        if ($payment) {
            $status = ($paymentResult['Result'] ?? '') === 'Successful' ? 'paid' : 'unpaid';

            $payment->update([
                'status'       => $status,
                'amount'       => $paymentResult['amount'] ?? $payment->amount,
                'raw_response' => $paymentResult,
                'payment_id'   => $paymentResult['PaymentId'] ?? $payment->payment_id,
                'payment_type' => $paymentResult['cardBrand'] ?? 'not_found',
            ]);

            // 👇 هنا التفرقة بين الويب والتطبيق
            if ($payment->payment_from === 'app') {
                return response()->json([
                    'status' => $status === 'paid',
                    'message' => $status === 'paid' ? 'Payment successful' : 'Payment failed',
                    'payment' => $payment,
                ]);
            }

            

            // هذا استدعاء من الويب
            if ($status === 'paid') {

               

                if ($payment->type === 'trust') {
                    $user = User::where('id',$payment->user_id)->first();

                    $user->trusted = 1;
                    $user->save();
                }

                return redirect()->to(app()->getLocale() ."/payment/success?track_id=" . $payment->track_id);
            } else {
                return redirect()->to(app()->getLocale() ."/payment/failed?track_id=" . $payment->track_id);
            }
        }

        // لم يتم العثور على عملية دفع
        return response()->json(['status' => false, 'message' => 'Payment not found'], 404);
    }



    public function handleError(Request $request)
    {
        return redirect()->to('/payment/failed?track_id=' . $payment->track_id);
    }



     public function getAllItems(Request $request)
    {
        $track_id = $request->trackId;
        $payment = Payment::with('ad', 'user')->where('track_id', $track_id)->first();

        return mainResponse(true, __('common.create'), $payment, 200, 'items', '');
    }



    public function generateUrwayPaymentLinkTrust(Request $request)
    {
        $rules = [
            'id_no'        => 'required|string|max:20',
            'birth_date'   => 'required|date',
            'mobile_trust' => 'required|string|max:15',
        ];
        $messages = [
            'ar' => [
                'id_no.required'        => 'الرقم الوطني مطلوب',
                'birth_date.required'   => 'تاريخ الميلاد مطلوب',
                'mobile_trust.required' => 'رقم الجوال مطلوب',
            ],
            'en' => [
                'id_no.required'        => 'ID number is required',
                'birth_date.required'   => 'Birth date is required',
                'mobile_trust.required' => 'Mobile number is required',
            ]
        ];
        $locale = app()->getLocale();
        $validator = Validator::make($request->all(), $rules, $messages[$locale] ?? []);
        if ($validator->fails()) {
            return mainResponse(false, '', null, 203, 'items', $validator);
        }

        $user = auth('sanctum')->user();
        $trackId = uniqid();
        $amount   = number_format((float) $request->amount, 2, '.', '');
        $currency = 'SAR';

        $user->id_no = $request->id_no;
        $user->birth_date = $request->birth_date;
        $user->mobile_trust = $request->mobile_trust;
        $user->save();

        $payment = Payment::create([
            'track_id'      => $trackId,
            'ad_id'         => $request->ad_id,
            'amount'        => $amount,
            'currency'      => $currency,
            'status'        => 'pending',
            'payment_from'  => 'web',
            'payment_type'  => 'Card',
            'type'          => 'trust',
            'user_id'       => $user->id,
        ]);

        $paymentUrl = url('/neoleap/pay?payment_id=' . $payment->track_id);
        return response()->json([
            'status'       => true,
            'payment_url'  => $paymentUrl,
            'payment_id'   => $payment->track_id,
        ]);
    }







 




  

}




   