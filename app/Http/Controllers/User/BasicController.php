<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ItemsCategories;
use App\Models\ItemsTypes;
use App\Models\Setting;
use App\Models\Contact;
use App\Models\Notification;
use App\Services\LandingSliderProductsService;
use App\Services\TechnicSliderProductsService;
use App\Services\SettingCoverService;
use App\Services\LandingSectionCardsService;
use App\Services\LandingCategoryStripService;
use App\Services\LandingBrandSliderService;
use App\Services\LandingTechnicSectionService;
use App\Services\HeaderTickerService;




use App\Mail\WelcomeUserMail;
use App\Mail\SendUserPasswordMail;

use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use DB;


 

class BasicController extends Controller
{
    protected $sliderService;
    protected $technicSliderService;
    protected $coverService;
    protected $landingSectionCardsService;
    protected $landingCategoryStripService;
    protected $landingBrandSliderService;
    protected $landingTechnicSectionService;
    protected $headerTickerService;

    public function __construct(
        LandingSliderProductsService $sliderService,
        TechnicSliderProductsService $technicSliderService,
        SettingCoverService $coverService,
        LandingSectionCardsService $landingSectionCardsService,
        LandingCategoryStripService $landingCategoryStripService,
        LandingBrandSliderService $landingBrandSliderService,
        LandingTechnicSectionService $landingTechnicSectionService,
        HeaderTickerService $headerTickerService
    )
    {
        $this->sliderService = $sliderService;
        $this->technicSliderService = $technicSliderService;
        $this->coverService = $coverService;
        $this->landingSectionCardsService = $landingSectionCardsService;
        $this->landingCategoryStripService = $landingCategoryStripService;
        $this->landingBrandSliderService = $landingBrandSliderService;
        $this->landingTechnicSectionService = $landingTechnicSectionService;
        $this->headerTickerService = $headerTickerService;
    }
  

    
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


    /** نوع القطع (للذهب) - من الأدمن */
    public function getPieces(Request $request)
    {
        $typeId = ItemsTypes::where('name', 'piece')->value('id');
        if (!$typeId) {
            return mainResponse(true, __('common.return'), [], 200, 'items', '');
        }
        $items = ItemsCategories::where('status', 'active')
            ->where('type_id', $typeId)
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return ['id' => $item->id, 'name' => $item->name];
            });
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    /** المعادن (للذهب) - من الأدمن */
    public function getMetals(Request $request)
    {
        $typeId = ItemsTypes::where('name', 'metal')->value('id');
        if (!$typeId) {
            return mainResponse(true, __('common.return'), [], 200, 'items', '');
        }
        $items = ItemsCategories::where('status', 'active')
            ->where('type_id', $typeId)
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return ['id' => $item->id, 'name' => $item->name];
            });
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    /** العيار (مرتبط بالمعدن) - من الأدمن - العمود قد يكون metal أو category حسب إعداد الأدمن */
    public function getCalibers(Request $request)
    {
        if (!$request->filled('metal_id')) {
            return mainResponse(true, __('common.return'), [], 200, 'items', '');
        }
        $metalId = (int) $request->metal_id;
        $typeId = ItemsTypes::where('name', 'caliber')->value('id');
        if (!$typeId) {
            return mainResponse(true, __('common.return'), [], 200, 'items', '');
        }
        // الأدمن يحفظ المعدن الأب في عمود category (form.append('category', metal.id))
        $query = ItemsCategories::where('status', 'active')
            ->where('type_id', $typeId)
            ->where('category', $metalId);
        $items = $query->orderBy('id')->get()->map(function ($item) {
            return ['id' => $item->id, 'name' => $item->name];
        });
        return mainResponse(true, __('common.return'), $items, 200, 'items', '');
    }


    public function getSetting(Request $request)
    {
        $items = Setting::query()->with('translations')->first();
        $cover = $this->coverService->get();
        $items->cover_link = $cover['cover_link'] ?? '';
        $items->cover_image = $cover['cover_image'] ?? null;
        $items->cover_image_mobile = $cover['cover_image_mobile'] ?? null;
        $items->whiteout_link = $cover['whiteout_link'] ?? '';
        $items->whiteout_image = $cover['whiteout_image'] ?? null;
        $items->whiteout_image_mobile = $cover['whiteout_image_mobile'] ?? null;
        $items->training_link = $cover['training_link'] ?? '';
        $items->training_image = $cover['training_image'] ?? null;
        $items->training_image_mobile = $cover['training_image_mobile'] ?? null;
        $items->technic_link = $cover['technic_link'] ?? '';
        $items->technic_image = $cover['technic_image'] ?? null;
        $items->technic_image_mobile = $cover['technic_image_mobile'] ?? null;
        $items->landing_section_cards = $this->landingSectionCardsService->getAll();
        $items->landing_category_strip = $this->landingCategoryStripService->getAll();
        $items->landing_brand_slider = $this->landingBrandSliderService->getAll();
        $items->landing_technic_section = $this->landingTechnicSectionService->get();
        $items->header_ticker = $this->headerTickerService->get();
        
        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }

    public function getLandingSliderProducts(Request $request)
    {
        $message = __('common.return');

        return mainResponse(true, $message, $this->sliderService->getPayload(), 200, 'items', '');
    }

    public function getTechnicalSliderProducts(Request $request)
    {
        $message = __('common.return');

        return mainResponse(true, $message, $this->technicSliderService->getPayload(), 200, 'items', '');
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

        // dd($request->all());
        $user = auth()->user();


        $validator = Validator::make($request->all(), [
            'message' => 'required',
            'user_id_report' => 'required',
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
        $ad->user_id_report = $request->user_id_report;  // report form user id 
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







    public function getReasons(Request $request)
    {

        $items = ItemsCategories::where('status','active')->where('type_id',32)->get();

            $items = $items->map(function ($item) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            });

        


       

        $message = __('common.return');
        return mainResponse(true, $message, $items, 200, 'items','');
    }




   public function getNotifications(Request $request)
{
    $user = auth()->user(); 
    $now = Carbon::now();

    $items = Notification::with('currentUser')
        ->where('created_at', '>=', $user->created_at) // 🔹 فقط من يوم إنشاء الحساب
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($notif) {
            $notif->read = $notif->currentUser->first()?->pivot?->read ?? 0;
            return $notif;
        })
        ->groupBy(function ($item) use ($now, $user) {
            $createdAt = Carbon::parse($item->created_at);

            if ($createdAt->greaterThanOrEqualTo($now->copy()->subDay())) {
                return __('common.now');
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subWeek())) {
                return __('common.week');
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subMonth())) {
                return __('common.month');
            } elseif ($createdAt->greaterThanOrEqualTo($now->copy()->subMonths(6))) {
                return __('common.six_months');
            } else {
                return __('common.older');
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




    public function create2(Request $request)
    {



        $validator = Validator::make($request->all(), [
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return mainResponse(false, '', null, 422, 'items', $validator);
        }

        $ad = new Contact;
        $ad->user_id = auth()->id();
        $ad->name = $request->name;
        $ad->email = $request->email;
        $ad->mobile = $request->mobile;
        // $ad->subject = $request->subject;
      
        $ad->message = $request->message;
        $ad->save();

       
        return mainResponse(true, __('common.create'), $ad , 200, 'items', '');
       
    }


  

}




   