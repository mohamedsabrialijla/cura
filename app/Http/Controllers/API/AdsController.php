<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
use App\Models\Comment;
use App\Models\Image;
use App\Services\FcmNotificationService;
use App\Models\Rating;
use Illuminate\Support\Facades\Cache;
use App\Models\AdView;
use App\Models\Language;
use App\Models\Payment;
use DB;

use Intervention\Image\ImageManager; 
use Intervention\Image\Drivers\Gd\Driver; // لو بتستخدم GD





 

class AdsController extends Controller
{
  
    public function __construct() {
        $this->locales = Language::all();
        view()->share([
            'locales' => $this->locales,

        ]);
    }

    /**
     * رسائل التحقق المخصصة للإعلان (عربي/إنجليزي)
     */
    protected function getAdValidationMessages(): array
    {
        $isAr = $this->isArabicLocale();
        if ($isAr) {
            return [
                'required' => 'حقل :attribute مطلوب.',
                'integer' => 'حقل :attribute يجب أن يكون رقماً صحيحاً.',
                'string' => 'حقل :attribute يجب أن يكون نصاً.',
                'max' => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',
                'image' => 'حقل :attribute يجب أن يكون صورة (jpg, jpeg, png).',
                'mimes' => 'حقل :attribute يجب أن يكون ملفاً من نوع: :values.',
                'array' => 'حقل :attribute يجب أن يكون مصفوفة.',
                'min' => [
                    'array' => 'يجب إرفاق صورة واحدة على الأقل للإعلان.',
                ],
            ];
        }
        return [
            'required' => 'The :attribute field is required.',
            'integer' => 'The :attribute must be an integer.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'image' => 'The :attribute must be an image (jpg, jpeg, png).',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'array' => 'The :attribute must be an array.',
            'min' => [
                'array' => 'At least one image is required for the ad.',
            ],
        ];
    }

    /**
     * أسماء الحقول للتحقق (عربي/إنجليزي)
     */
    protected function getAdValidationAttributes(): array
    {
        $isAr = $this->isArabicLocale();
        if ($isAr) {
            return [
                'title' => 'العنوان',
                'category_id' => 'التصنيف',
                'country_id' => 'الدولة',
                'city_id' => 'المدينة',
                'brand_id' => 'العلامة التجارية',
                'product_type' => 'نوع المنتج',
                'mobile' => 'رقم الجوال',
                'price' => 'السعر',
                'images' => 'الصور',
                'images.*' => 'الصور',
                'piece_id' => 'نوع القطعة',
                'metal_id' => 'المعدن',
                'caliber_id' => 'العيار',
                'weight' => 'الوزن/الحجم',
                'ad_id' => 'رقم الإعلان',
                'invoice' => 'الفاتورة',
            ];
        }
        return [
            'title' => 'title',
            'category_id' => 'category',
            'country_id' => 'country',
            'city_id' => 'city',
            'brand_id' => 'brand',
            'product_type' => 'product type',
            'mobile' => 'mobile',
            'price' => 'price',
            'images' => 'images',
            'images.*' => 'images',
            'piece_id' => 'piece type',
            'metal_id' => 'metal',
            'caliber_id' => 'caliber',
            'weight' => 'weight/size',
            'ad_id' => 'ad ID',
            'invoice' => 'invoice',
        ];
    }

    protected function isArabicLocale(): bool
    {
        $locale = request()->header('Accept-Language') ?? request()->input('lang') ?? app()->getLocale();
        return str_starts_with((string) $locale, 'ar');
    }


    
    public function create(Request $request)
    {

        // dd($request->all());
        
        $user = auth('sanctum')->user();


        $rules = [
            'category_id' => 'required|integer',
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'product_type' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'title' => 'required',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ];
        $isGold = (int) $request->category_id === 6;
        if ($isGold) {
            $rules['brand_id'] = 'nullable|integer';
            $rules['piece_id'] = 'required|integer';
            $rules['metal_id'] = 'required|integer';
            $rules['caliber_id'] = 'required|integer';
            $rules['weight'] = 'required|string|max:50';
        } else {
            $rules['brand_id'] = 'required|integer';
            $rules['piece_id'] = 'nullable|integer';
            $rules['metal_id'] = 'nullable|integer';
            $rules['caliber_id'] = 'nullable|integer';
            $rules['weight'] = 'nullable|string|max:50';
        }
        $messages = $this->getAdValidationMessages();
        $attributes = $this->getAdValidationAttributes();
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $ad = new Ad($request->except(['images', 'invoice']));
         foreach ($this->locales as $locale) {
            $ad->translateOrNew($locale->lang)->title = $request->get('title');
            $ad->translateOrNew($locale->lang)->description = $request->get('description');
        }
        $ad->user_id = auth('sanctum')->id();
        $ad->save();

        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $destinationPath = public_path('uploads/invoice');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $fileName);

            $ad->invoice = 'uploads/invoice/' . $fileName;
            $ad->save();
        }


        if ($request->hasFile('images')) {
            $manager = new ImageManager(Driver::class);

            foreach ($request->file('images') as $imageFile) {
                if (!$imageFile->isValid() || !in_array($imageFile->extension(), ['jpg', 'jpeg', 'png'])) {
                    return mainResponse(false, 'الملف المرفوع ليس صورة صالحة', null, 422, 'items', '');
                }

                $destinationPath = public_path('uploads/ads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // ✅ قراءة الصورة
                $img = $manager->read($imageFile->get());
                $img = $img->cover(500, 500); // مربع

                // ✅ قراءة وإضافة الشعار
                $logoPath = public_path('assets/media/svg/brand-logos/logo_b.png');
                $logo = $manager->read($logoPath);
                $logo = $logo->scaleDown(50, 50);
                $img = $img->place($logo, 'bottom-left', 10, 10);

                // ✅ حفظ الصورة
                $img->save($destinationPath . '/' . $fileName);

                // ✅ ربط الصورة مع الإعلان
                $ad->images()->create(['url' => 'uploads/ads/' . $fileName]);
            }
        }

        $allUserIds = User::pluck('id')->toArray();
        $adOwnerId = (int) $ad->user_id;
        $adTitle = $ad->translate(app()->getLocale())?->title ?? $ad->translate('en')?->title ?? $ad->translate('ar')?->title ?? 'Ad';
        $title = app()->getLocale() === 'ar' ? 'إعلان جديد' : 'New ad';
        $body = (app()->getLocale() === 'ar' ? 'إعلان جديد: ' . $adTitle : 'New ad: ' . $adTitle);
        FcmNotificationService::sendAdNotification($allUserIds, $ad->id, $adOwnerId, $title, $body, $adOwnerId);

        return mainResponse(true, __('common.create'), $ad, 200, 'items', '');
    }


    public function update(Request $request)
    {
        $user = auth('sanctum')->user();

        $rules = [
            'ad_id' => 'required',
            'category_id' => 'required|integer',
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'product_type' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'title' => 'required',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ];
        $isGold = (int) $request->category_id === 6;
        if ($isGold) {
            $rules['brand_id'] = 'nullable|integer';
            $rules['piece_id'] = 'required|integer';
            $rules['metal_id'] = 'required|integer';
            $rules['caliber_id'] = 'required|integer';
            $rules['weight'] = 'required|string|max:50';
        } else {
            $rules['brand_id'] = 'required|integer';
            $rules['piece_id'] = 'nullable|integer';
            $rules['metal_id'] = 'nullable|integer';
            $rules['caliber_id'] = 'nullable|integer';
            $rules['weight'] = 'nullable|string|max:50';
        }
        $messages = $this->getAdValidationMessages();
        $attributes = $this->getAdValidationAttributes();
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $ad = Ad::where('id', $request->ad_id)->where('user_id', $user->id)->first();

        if (!$ad) {
            $message = (app()->getLocale() == 'ar') ? 'الاعلان غير موجود ' : 'Ad not found or not authorized';

            return mainResponse(false, $message, null, 404, 'items', '');
        }

        $ad->fill($request->except(['images', 'invoice']));
        foreach ($this->locales as $locale) {
            $ad->translateOrNew($locale->lang)->title = $request->get('title');
            $ad->translateOrNew($locale->lang)->description = $request->get('description');
        }
        $ad->save();

        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');
            $fileName = time() . '_' . $file->getClientOriginalName();

            $destinationPath = public_path('uploads/invoice');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $fileName);
            $ad->invoice = 'uploads/invoice/' . $fileName;
            $ad->save();
        }


        if ($request->hasFile('images')) {
            $manager = new ImageManager(Driver::class);

            foreach ($request->file('images') as $imageFile) {
                if (!$imageFile->isValid() || !in_array($imageFile->extension(), ['jpg', 'jpeg', 'png'])) {
                    return mainResponse(false, 'الملف المرفوع ليس صورة صالحة', null, 422, 'items', '');
                }

                $destinationPath = public_path('uploads/ads');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $fileName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // ✅ قراءة الصورة
                $img = $manager->read($imageFile->get());
                $img = $img->cover(500, 500); // مربع

                // ✅ قراءة وإضافة الشعار
                $logoPath = public_path('assets/media/svg/brand-logos/logo_b.png');
                $logo = $manager->read($logoPath);
                $logo = $logo->scaleDown(50, 50);
                $img = $img->place($logo, 'bottom-left', 10, 10);

                // ✅ حفظ الصورة
                $img->save($destinationPath . '/' . $fileName);

                // ✅ ربط الصورة مع الإعلان
                $ad->images()->create(['url' => 'uploads/ads/' . $fileName]);
            }
        }


        return mainResponse(true, __('common.update'), $ad , 200, 'items', '');
    }




    public function getAllItems(Request $request)
    {
        $user = auth('sanctum')->user();

        // dd($user->id);

        if ($request->filled('ad_id')) {
            $ad = Ad::with('images', 'user', 'category', 'brand', 'country', 'city')->where('status','active')
                    ->findOrFail($request->ad_id);

            $ip = $request->ip();

            $alreadyViewed = AdView::where('ad_id', $ad->id)
                ->when($user, fn($q) => $q->where('user_id', $user->id))
                ->when(!$user, fn($q) => $q->where('ip', $ip))
                ->exists();

            if (!$alreadyViewed) {
                AdView::create([
                    'ad_id' => $ad->id,
                    'user_id' => $user?->id,
                    'ip' => $user ? null : $ip,
                ]);

                $ad->views = AdView::where('ad_id', $ad->id)->count();
                $ad->save();
            }

            $message = __('common.return');
            return mainResponse(true, $message, $ad, 200, 'items', '');
        }

        $items = Ad::with('images', 'user', 'category', 'brand', 'country', 'city')->where('status','active')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('updated_at', 'desc')
            ->paginate(10);


        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items', '');
    }





    public function getAllItemsMyAds()
    {
        $user = auth('sanctum')->user();
        $items = Ad::where('user_id',$user->id)->with('category','brand','country','city')->where('status','active')->get();
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }




     public function refresh(Request $request)
    {

        $user = auth('sanctum')->user();
        $id = $request->ad_id ;

        $ad = Ad::where('id', $id)->where('user_id', $user->id)->first();

        if (!$ad) {

            $message = (app()->getLocale() == 'ar') ? 'الاعلان غير موجود ' : 'Ad not found or not authorized';

            return mainResponse(false, $message, null, 404, 'items', '');
        }

        if ($ad->updated_at && $ad->updated_at->diffInHours(now()) < 24) {
            $message = (app()->getLocale() == 'ar') ? 'يمكنك تحديث هذا المنتج فقط بعد مرور 24 ساعة منذ التحديث الأخير.' : 'You can update this product only after 24 hours since last update.';

            return mainResponse(false, $message, null, 403, 'items', '');
        }

        $ad->touch();      

        $message = (app()->getLocale() == 'ar') ? 'تم التحديث بنجاح' : 'updated successfully.';

        return mainResponse(true, 'Ad updated successfully', $ad, 200, 'items', '');


    } 

    public function filter(Request $request)
    {
        // dd($request->all());
        $query = Ad::query()->with(['category', 'brand', 'country', 'city'])->where('status','active');

        if ($request->filled('category_id')) {
            $query =$query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query =$query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('country_id')) {
            $query =$query->where('country_id', $request->country_id);
        }

        if ($request->filled('city_id')) {
           $query = $query->where('city_id', $request->city_id);
        }

        if ($request->filled('invoice')) {
            if ($request->invoice === 'yes') {
              $query =  $query->whereNotNull('invoice');
            } elseif ($request->invoice === 'no') {
              $query =  $query->whereNull('invoice');
            }
        }

        if ($request->filled('product_type')) {
           $query = $query->where('product_type', $request->product_type); // 'new' or 'used'
        }

        $query = $query->latest('updated_at')->paginate(10);


        $message = (app()->getLocale() == 'ar') ? 'تم استرجاع البيانات بنجاح' : 'Return Info successfully.';

        return mainResponse(true, $message, $query, 200, 'items', '');
    }

 

    public function rateSeller(Request $request)
    {
        // dd($request->all());

        $user = auth('sanctum')->user();


        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }


        $rating = Rating::updateOrCreate(
            ['user_id' => $user->id, 'seller_id' => $request->seller_id],
            ['rating' => $request->rating]
        );


        $message = (app()->getLocale() == 'ar') ? 'تم التقييم بنجاح' : 'Rating saved.';

        $seller = User::where('id',$request->seller_id)->first();

        return mainResponse(true, $message, $seller, 200, 'items', '');
    }



    // public function rateAd(Request $request)
    // {
    //     $user = auth('sanctum')->user();


    //     $validator = Validator::make($request->all(), [
    //         'rating' => 'required|integer|min:1|max:5',
    //         'ad_id' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return mainResponse(false, '', null, 422, 'items', $validator);
    //     }


    //     $ad_id = $request->ad_id ;

    //     $ad = Ad::where('id',$ad_id)->first();

    //     $rating = Rating::updateOrCreate(
    //         ['user_id' => $user->id, 'ad_id' => $ad->id],
    //         ['rating' => $request->rating]
    //     );


    //     $message = (app()->getLocale() == 'ar') ? 'تم التقييم بنجاح' : 'Rating saved.';


    //     return mainResponse(true, $message, $rating, 200, 'items', '');
    // }



   public function toggleFavorite(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return mainResponse(false, 'Unauthenticated.', null, 401, 'items', '');
        }

        $adId = $request->ad_id;
        $ad = Ad::findOrFail($adId);

        $isFav = false;
        $message = '';

        if ($user->favorites()->where('ad_id', $adId)->exists()) {
            $user->favorites()->detach($adId);
            $message = (app()->getLocale() == 'ar') ? 'تم الازالة من المفضلة' : 'Removed from favorites.';
            $isFav = false;
        } else {
            $user->favorites()->attach($adId);
            $message = (app()->getLocale() == 'ar') ? 'تمت الاضافة للمفضلة' : 'Added to favorites.';
            $isFav = true;
        }

        return mainResponse(true, $message, ['is_fav' => $isFav], 200, 'items', '');
    }



    public function favorites()
    {
        $user = auth('sanctum')->user();
        $favorites = $user->favorites()->with('category', 'brand','city','country','user')->get();

        return mainResponse(true, $message = __('common.return'), $favorites, 200, 'items', '');
    }



    public function sortAds(Request $request)
    {
        $sortBy = $request->input('sort_by'); // one of: most_viewed, latest, more_rate, highest_price, less_price

        $ads = Ad::with(['images', 'category', 'brand', 'country', 'city'])
        ->where('status', 'active')
        ->addSelect([
            'average_rating' => function ($q) {
                $q->select(DB::raw("COALESCE(AVG(rating), 0)"))
                    ->from('ratings')
                    ->whereColumn('ratings.seller_id', 'ads.user_id');
            }
        ]);

        switch ($sortBy) {
            case 'most_viewed':
                $ads->orderByDesc('views');
                break;

            case 'latest':
                $ads->orderByDesc('updated_at');
                break;

            case 'more_rate':
                $ads->orderByDesc('average_rating');
                break;

            case 'highest_price':
                $ads->orderByRaw("price IS NULL, CAST(price AS UNSIGNED) DESC");
                break;

            case 'less_price':
                $ads->orderByRaw("price IS NULL, CAST(price AS UNSIGNED) ASC");
                break;

            default:
                $ads->orderByDesc('updated_at'); 
        }

        $results = $ads->paginate(10);

        $message = (app()->getLocale() == 'ar') ? 'تم استرجاع البيانات بنجاح' : 'Return Info successfully.';


        return mainResponse(true, $message, $results, 200, 'items', '');
    }


  


    public function addComment(Request $request)
    {
        // dd($request->all());
         
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'body' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'ad_id' => $request->ad_id,
            'user_id' => auth('sanctum')->id(),
            'body' => $request->body,
        ]);

        $ad = Ad::find($request->ad_id);
        if ($ad) {
            $commenterIds = Comment::where('ad_id', $ad->id)->distinct()->pluck('user_id')->toArray();
            $followUpRaw = $ad->getRawOriginal('follow_up') ?? $ad->attributes['follow_up'] ?? '';
            $followerIds = $followUpRaw ? array_map('intval', array_filter(explode(',', trim($followUpRaw)))) : [];
            $adOwnerId = (int) $ad->user_id;
            $recipientIds = array_unique(array_merge($commenterIds, $followerIds, [$adOwnerId]));
            $actorId = auth('sanctum')->id();
            $commenterName = auth('sanctum')->user()->name ?? 'Someone';
            $title = app()->getLocale() === 'ar' ? 'تعليق جديد' : 'New comment';
            $body = (app()->getLocale() === 'ar' ? $commenterName . ' علّق على إعلانك' : $commenterName . ' commented on an ad');
            FcmNotificationService::sendAdNotification($recipientIds, $ad->id, $adOwnerId, $title, $body, $actorId);
        }

        return mainResponse(true, __('common.create'), $comment->load('user'), 200, 'items', '');
    }



    public function getComments(Request $request)
    {
        $ad_id = $request->ad_id ;
        $comments = Comment::with('user')->where('ad_id', $ad_id)->get();

        return mainResponse(true, __('common.return'), $comments, 200, 'items', '');
    }



    public function deleteComment(Request $request)
    {
        $comment_id = $request->comment_id ;
        $comments = Comment::where('user_id',auth('sanctum')->id())->where('id', $comment_id)->first();
        if(isset($comments) && $comments != '' ){
            $comments->delete();
        }else{

            return mainResponse(true, __('common.Not_Found'), null, 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), null, 200, 'items', '');
    }



    public function addReportComment(Request $request)
    {
        $request->validate([
            'comment_id' => 'required|exists:comments,id',
            'report' => 'required|in:not serious about buying,inappropriate response,undervaluation of the commodity',
        ]);

        $comment = Comment::findOrFail($request->comment_id);
        $comment->report = $request->report;
        $comment->save();


        return mainResponse(true, __('common.create'), $comment->load('user'), 200, 'items', '');
    }



    public function deleteAd(Request $request)
    {
        $ad_id = $request->ad_id ;
        $ad = Ad::where('user_id',auth('sanctum')->id())->where('id', $ad_id)->first();
        if(isset($ad) && $ad != '' ){
            $ad->delete();
        }else{

            return mainResponse(true, __('common.Not_Found'), null, 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), null, 200, 'items', '');
    }



    public function getAlldeletedAd(Request $request)
    {
        $id = auth::id();

        $items = Ad::withTrashed()
            ->where('user_id', $id)
            ->where('status', 'active')
            ->get();

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    public function Calculate(Request $request){
        $setting = Setting::query()->first();

        $pay = $request->price * ($setting->vat/100) ;

        return mainResponse(true, __('common.return'), $pay, 200, 'items', '');
    }


    public function DataFilter(Request $request)
{
    $categories = ItemsCategories::with('brands')  
    ->where('status', 'active')
    ->where('type_id', 2)
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'brands' => $item->brands, // إذا بدك ترجع البراند مع كل عنصر
        ];
    });



    $countries = ItemsCategories::with('cities')  
    ->where('status', 'active')
    ->where('type_id', 28)
    ->get()
    ->map(function ($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'cities' => $item->cities, // إذا بدك ترجع البراند مع كل عنصر
        ];
    });


    
    $data = [
        'categories' => $categories,
        'countries' => $countries,
    ];

    $message = __('common.return');
    return mainResponse(true, $message, $data, 200, 'data', '');
}




 
    


    public function DataSerach(Request $request)
    {
        $locale = App()->getLocale();
        $query = Ad::query()
            ->whereHas('translations', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->with(['category', 'brand', 'country', 'city'])
            ->latest('updated_at')
            ->paginate(10);

        $message = (app()->getLocale() == 'ar') ? 'تم استرجاع البيانات بنجاح' : 'Return Info successfully.';
        return mainResponse(true, $message, $query, 200, 'items', '');
    }



     


    public function getAllItemsMyInvoice(Request $request)
    {
        $id = auth('sanctum')->id();

        $items = Payment::with('ad','user')->where('user_id',$id);


        if(isset($request->track_id) && $request->track_id) {
            $items = $items->where('track_id',$request->track_id )->first();

        }else{

            $items = $items->orderByDesc('id')->get();

        }




        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }




    



    public function CalculateComissionPayment(Request $request)
    {
        $price = $request->price;
        $setting = Setting::query()->first();
        $vat = $setting->vat;
        
        $value = $price * $vat / 100 ;

        return response()->json([
            'status'=> true,
            'message'=> __('common.return'),
            'items'=> $value,
            'code'=> 200,
        ]);

        // return mainResponse(true, __('common.return'), [$value], 200, 'items', '');
    }




    public function getAdSimilar(Request $request)
    {
        $category_id = Ad::where('id',$request->ad_id)->pluck('category_id')->first();
        $items = Ad::where('category_id', $category_id)->where('id', '!=', $request->ad_id)
            ->where('status', 'active')
            ->with(['user', 'category', 'brand', 'country', 'city'])
            ->latest() 
            ->take(4)   
            ->get();
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }






 

    public function FollowUp(Request $request)
    {
        $user = auth('sanctum')->user();

        $ad = Ad::where('id', $request->ad_id)->first();
        $newUserId = $user->id;

        $followUpRaw = $ad->getRawOriginal('follow_up') ?? $ad->attributes['follow_up'] ?? '';
        $userIds = $followUpRaw ? explode(',', trim($followUpRaw)) : [];

        if (!in_array((string) $newUserId, array_map('strval', $userIds))) {
            $userIds[] = $newUserId;
        }

        $ad->follow_up = implode(',', $userIds);
        $ad->save();

        $commenterIds = Comment::where('ad_id', $ad->id)->distinct()->pluck('user_id')->toArray();
        $followerIds = array_map('intval', array_filter($userIds));
        $adOwnerId = (int) $ad->user_id;
        $recipientIds = array_unique(array_merge($commenterIds, $followerIds, [$adOwnerId]));
        $followerName = $user->name ?? 'Someone';
        $title = app()->getLocale() === 'ar' ? 'متابعة إعلان' : 'Ad followed';
        $body = (app()->getLocale() === 'ar' ? $followerName . ' يتابع إعلانك' : $followerName . ' is following your ad');
        FcmNotificationService::sendAdNotification($recipientIds, $ad->id, $adOwnerId, $title, $body, $newUserId);

        $message = __('common.return');
        return mainResponse(true, $message, '', 200, 'items', '');
    }




  

}




   