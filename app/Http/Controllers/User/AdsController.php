<?php

namespace App\Http\Controllers\User;

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
use App\Models\Image;
use App\Services\FcmNotificationService;
use App\Models\Rating;
use Illuminate\Support\Facades\Cache;
use App\Models\AdView;
use App\Models\Comment;
use App\Models\Language;
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
        $isAr = app()->getLocale() === 'ar';
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
        $isAr = app()->getLocale() === 'ar';
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


    
    public function create(Request $request)
    {

        // dd($request->all());
        
        $user = auth()->user();


        $rules = [
            'title' => 'required',
            'category_id' => 'required|integer',
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'product_type' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
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
        $ad->user_id = auth()->id();
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
        // dd($request->all());

        $user = auth()->user();

        $rules = [
             'title' => 'required',
             'ad_id' => 'required',
             'category_id' => 'required|integer',
            'country_id' => 'required|integer',
            'city_id' => 'required|integer',
            'product_type' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:255',
            'price' => 'nullable|string|max:255',
            'invoice' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
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



    public function deleteImg(Request $request)
    {
        $img_id = $request->img_id ;
        // dd($img_id);
        $img = Image::where('id', $img_id)->first();
        if(isset($img) && $img != '' ){
            $img->delete();
        }else{

            return mainResponse(true, __('common.Not_Found'), '', 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), '', 200, 'items', '');
    }



    public function deleteInvoice(Request $request)
    {
        $ad_id = $request->ad_id ;
        // dd($ad_id);
        $img = Ad::where('id', $ad_id)->first();
        if(isset($img) && $img != '' ){
            $img->invoice = null;
            $img->save();
        }else{

            return mainResponse(true, __('common.Not_Found'), $img, 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), '', 200, 'items', '');
    }
 

  

    public function getAllItems(Request $request)
    {

        $user = auth('sanctum')->user();
        // dd($user);

        if ($request->filled('ad_id')) {
            $ad = Ad::with('images', 'user', 'category', 'brand', 'country', 'city', 'piece', 'metal', 'caliber')
                    ->where('status','active')
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

            // ✅ أضف my_ad بناءً على المستخدم
            $ad->my_ad = $user && $ad->user_id === $user->id;

            $message = __('common.return');
            return mainResponse(true, $message, $ad, 200, 'items', '');
        }

        // استعلام الإعلانات المتعددة
        $items = Ad::with('images', 'user', 'category', 'brand', 'country', 'city')
            ->where('status','active')
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        // ✅ أضف my_ad لكل إعلان
        // if ($user) {
        //     $items->map(function ($ad) use ($user) {
        //         $ad->my_ad = $ad->user_id === $user->id;
        //         return $ad;
        //     });
        // } else {
        //     $items->map(function ($ad) {
        //         $ad->my_ad = false;
        //         return $ad;
        //     });
        // }

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items', '');
    }



 
    public function getAllItemsMyAds()
    {
        $user = auth('sanctum')->user();
        $items = Ad::where('user_id',$user->id)->where('status','active')->with('user','country','city')->orderBy('updated_at','DESC')->get();
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }



    public function getAllItemsSellerAds(Request $request)
    {
        $user_id = $request->user_id;
        $items = Ad::where('user_id',$user_id)->where('status','active')->with('user','country','city')->orderBy('updated_at','DESC')->get();
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    public function getAllItemsMyAdsWithTrash()
    {
        $user = auth()->user();
        // dd($user);
        $items = Ad::withTrashed()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
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





    public function refresh(Request $request)
    {

        $user = auth()->user();
        $id = $request->id ;


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
        $query = Ad::query()->with(['category', 'brand', 'country', 'city','user']);

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

        $user = auth()->user();


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



    public function toggleFavorite(Request $request)
    {
        $user = auth()->user();

        $adId = $request->ad_id ;

        $ad = Ad::findOrFail($adId);

        if ($user->favorites()->where('ad_id', $adId)->exists()) {
            $user->favorites()->detach($adId);

            $message = (app()->getLocale() == 'ar') ? 'تم الازالة من المفضلة' : 'Removed from favorites.';

            return mainResponse(true, $message, null, 200, 'items', '');
        } else {
            $user->favorites()->attach($adId);
            $message = (app()->getLocale() == 'ar') ? 'تمت الاضافة للمفضلة' : 'Added to favorites.';
            return mainResponse(true, $message, null, 200, 'items', '');
        }
    }



    public function favorites()
    {
        $user = auth()->user();
        $favorites = $user->favorites()->with('category', 'brand','country','city','user')->get();

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

        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'body' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'ad_id' => $request->ad_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        $ad = Ad::find($request->ad_id);
        if ($ad) {
            $commenterIds = Comment::where('ad_id', $ad->id)->distinct()->pluck('user_id')->toArray();
            $followUpRaw = $ad->getRawOriginal('follow_up') ?? $ad->attributes['follow_up'] ?? '';
            $followerIds = $followUpRaw ? array_map('intval', array_filter(explode(',', trim($followUpRaw)))) : [];
            $adOwnerId = (int) $ad->user_id;
            $recipientIds = array_unique(array_merge($commenterIds, $followerIds, [$adOwnerId]));
            $actorId = auth()->id();
            $commenterName = auth()->user()->name ?? 'Someone';
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
        $comments = Comment::where('user_id',auth()->id())->where('id', $comment_id)->first();
        if(isset($comments) && $comments != '' ){
            $comments->delete();
        }else{

            return mainResponse(true, __('common.Not_Found'), '', 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), '', 200, 'items', '');
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
        // dd(auth()->id());
        $ad = Ad::where('user_id',auth()->id())->where('id', $ad_id)->first();
        if(isset($ad) && $ad != '' ){
            $ad->delete();
        }else{

            return mainResponse(true, __('common.Not_Found'), '', 200, 'items', '');
        }

        return mainResponse(true, __('common.delete'), '', 200, 'items', '');
    }



    public function getAlldeletedAd(Request $request)
    {
        $id = auth::id();

        $items = Ad::where('user_id', $id)->withTrashed()->paginate(10);

        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    public function Calculate(Request $request){

        $setting = Setting::query()->first();

        $pay = $request->price * ($setting->vat/100) ;

        return mainResponse(true, __('common.return'), $pay, 200, 'items', '');

    }


    public function DataSerach(Request $request)
    {
        $locale = App()->getLocale();
        $query = Ad::query()
            ->whereHas('translations', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            })
            ->with(['category', 'brand', 'country', 'city','user'])
            ->latest('updated_at')
            ->paginate(10);

        $message = (app()->getLocale() == 'ar') ? 'تم استرجاع البيانات بنجاح' : 'Return Info successfully.';
        return mainResponse(true, $message, $query, 200, 'items', '');
    }



    public function trust(Request $request)
    {

         
        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'id_no' => 'required',
            'birth_date' => 'required|integer',
            'mobile_trust' => 'required|string|max:255',
        ]);

       
        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }


        //here you are payment method 
        // after success go to 3alam api and go save data based in return response  

        $user->id_no = $request->id_no;
        $user->mobile_trust = $request->mobile_trust;
        $user->birth_date = $request->birth_date;
        $user->save();
 
        return mainResponse(true, __('common.create'), $ad , 200, 'items', '');
       
    }








    












  

}




   