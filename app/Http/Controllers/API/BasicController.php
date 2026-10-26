<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ItemsCategories;
use App\Models\Setting;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Notification;



use App\Mail\WelcomeUserMail;
use App\Mail\SendUserPasswordMail;

use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use DB;

 

class BasicController extends Controller
{
  

    
    public function getCountries(Request $request)
    {

        $items = ItemsCategories::where('status','active');

        if(isset($request->country_id) && $request->country_id != ''){

            $items = $items->where('type_id',29)->where('country',$request->country_id)->get();

            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    'logo' => $item->logo,
                ];
            });

        }else{

            $items = $items->where('type_id',28)->get();


            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    'code' => $item->code ?? '',
                    'slug' => $item->slug ?? '',
                    'logo' => $item->logo ?? '',
                ];
            });

        }


       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }



    



    public function getCategories(Request $request)
    {
        $items = ItemsCategories::where('status','active');

        if(isset($request->category_id) && $request->category_id != ''){

            $items = $items->where('type_id',30)->where('category',$request->category_id)->get();

            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            });

        }else{

            $items = $items->where('type_id',2)->get();


            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    
                ];
            });

        }


       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }


    public function getMetals(Request $request)
    {
        $items = ItemsCategories::where('status','active');

        if(isset($request->metal_id) && $request->metal_id != ''){

            $items = $items->where('type_id',35)->where('category',$request->metal_id)->get();

            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            });

        }else{

            $items = $items->where('type_id',34)->get();


            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    
                ];
            });

        }


       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }


    public function getPieces(Request $request)
    {
        $items = ItemsCategories::where('status','active');

            $items = $items->where('type_id',33)->get();

            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    
                ];
            });
       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }


    public function getSetting(Request $request)
    {
        $items = Setting::query()->first();

        $about = Page::whereTranslationLike('slug', 'about')->first();
        $terms = Page::whereTranslationLike('slug', 'conditions')->first(); 
        $payments = ItemsCategories::where('type_id', '1')->get();
        $items->about = $about;
        $items->terms = $terms;
        $items->payments = $payments;

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items', '');
    }






    public function getReasons(Request $request)
    {

        $items = ItemsCategories::where('status','active');


            $items = $items->where('type_id',32)->get();

            $items = $items->map(function ($item) {
                // جلب الترجمة العربية والإنجليزية
                $name_ar = optional($item->translations->firstWhere('locale', 'ar'))->name;
                $name_en = optional($item->translations->firstWhere('locale', 'en'))->name;

                return [
                    'id'      => $item->id,
                    'name'    => $item->name,    // الاسم الافتراضي
                    'name_ar' => $name_ar ?? $item->name,  // fallback إذا ما فيه ترجمة
                    'name_en' => $name_en ?? $item->name,
                ];
            });



       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }





    public function contact(Request $request)
    {

        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $ad = new Contact;
        $ad->user_id = auth()->id();
        $ad->name = $user->name;
        $ad->email = $user->email;
        $ad->mobile = $user->mobile;
      
        $ad->message = $request->message;
        $ad->save();

       
        return mainResponse(true, __('common.create'), $ad , 200, 'items', '');
       
    }



   


    public function report(Request $request)
    {

        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'userid_report' => 'required',
            // 'reasons_ids' => 'required',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $ad = new Contact;
        $ad->user_id = auth()->id();
        $ad->name = $user->name;
        $ad->email = $user->email;
        $ad->mobile = $user->mobile;
      
        $ad->message = $request->message;
        $ad->user_id_report = $request->userid_report;  // report form user id 
        $ad->reasons_ids = $request->reasons_ids;  
        $ad->save();

       
        return mainResponse(true, __('common.create'), $ad , 200, 'items', '');
       
    }




    public function getBanner(Request $request)
    {
        $items = ItemsCategories::where('status','active')->where('type_id',31)->orderBy('order')->get();


            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                    'logo' => $item->logo,
                    
                ];
            });

        


        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }




    public function getPages(Request $request)
    {
        $items = Page::query();

        if ($request->slug && $request->slug != '') {
            $items->whereHas('translations', function ($query) use ($request) {
                $query->where('slug', 'like', '%' . $request->slug . '%');
            });
        }

        $items = $items->get();

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items', '');
    }




    public function getNotifications(Request $request)
    {
        $user = auth('sanctum')->user(); 
        $now = Carbon::now();

        $items = Notification::with('currentUser')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($notif) {
            $notif->read = $notif->currentUser->first()?->pivot?->read ?? 0;
            // unset($notif->currentUser);
            return $notif;
        })
        ->groupBy(function ($item) use ($now) {
            $createdAt = Carbon::parse($item->created_at);

            if ($createdAt->greaterThanOrEqualTo($now->copy()->subDay())) {
                return 'now';
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subWeek())) {
                return 'one_week';
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subMonth())) {
                return 'one_month';
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subMonths(6))) {
                return 'six_months';
            } else {
                return 'older';
            }
        });


        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items', '');
    }




    public function readNotification(Request $request)
    {
        $user = auth('sanctum')->user();


        DB::table('notification_user')
            ->where('user_id', $user->id)
            ->update(['read' => true, 'updated_at' => now()]);


        $message = __('common.return');
        return mainResponse(true, $message,'', 200, 'items', '');
        
    }



  

}




   