<?php

namespace App\Http\Controllers\Web\Setting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Language;
use App\Models\Setting;
use App\Services\SettingCoverService;
use App\Services\LandingSectionCardsService;
use App\Services\LandingTechnicSectionService;
use App\Services\HeaderTickerService;
use App\Services\WebsitePricingService;



class SettingController extends Controller
{
    protected $coverService;
    protected $landingSectionCardsService;
    protected $landingTechnicSectionService;
    protected $headerTickerService;
   
     public function __construct() {
        // $this->middleware('auth', ['except' => ['login', 'register']]);

        $this->locales = Language::all();
        view()->share([
            'locales' => $this->locales,

        ]);

        $this->coverService = app(SettingCoverService::class);
        $this->landingSectionCardsService = app(LandingSectionCardsService::class);
        $this->landingTechnicSectionService = app(LandingTechnicSectionService::class);
        $this->headerTickerService = app(HeaderTickerService::class);
    }

    public function image_extensions(){
        return array('jpg','png','jpeg','gif','bmp');
    }

   


    public function edit(Request $request)
    {
       // dd($request->all());
        if (is_string($request->input('landing_cards'))) {
            $decodedCards = json_decode((string) $request->input('landing_cards'), true);
            if (is_array($decodedCards)) {
                $request->merge(['landing_cards' => $decodedCards]);
            }
        }

        $locales = Language::all()->pluck('lang');
        $roles = [
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'cover_image_mobile' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'cover_link' => 'nullable|url|max:500',
            'whiteout_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'whiteout_image_mobile' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'whiteout_link' => 'nullable|url|max:500',
            'training_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'training_image_mobile' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'training_link' => 'nullable|url|max:500',
            'technic_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'technic_image_mobile' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'technic_link' => 'nullable|url|max:500',
            'technic_title_white_ar' => 'nullable|string|max:255',
            'technic_title_gold_ar' => 'nullable|string|max:255',
            'technic_description_ar' => 'nullable|string|max:2000',
            'technic_title_white_en' => 'nullable|string|max:255',
            'technic_title_gold_en' => 'nullable|string|max:255',
            'technic_description_en' => 'nullable|string|max:2000',
            'ticker_enabled' => 'nullable|boolean',
            'ticker_prefix_en' => 'nullable|string|max:500',
            'ticker_prefix_ar' => 'nullable|string|max:500',
            'ticker_cta_en' => 'nullable|string|max:120',
            'ticker_cta_ar' => 'nullable|string|max:120',
            'ticker_link_en' => 'nullable|string|max:500',
            'ticker_link_ar' => 'nullable|string|max:500',
            'ticker_bg_color' => 'nullable|string|max:20',
            'landing_cards' => 'nullable|array|size:4',
            'email' => 'required|email',
            'mobile' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:500',
            'instagram' => 'nullable|string|max:500',
            'facebook' => 'nullable|string|max:500',
            'tiktok' => 'nullable|string|max:500',
            'snapchat' => 'nullable|string|max:500',
            'vat' => 'required|numeric|min:0',
            'eur_to_sar_rate' => 'nullable|numeric|min:0|max:99999',
           
        ];
        foreach (range(0, 3) as $idx) {
            $roles["landing_cards.$idx.title_ar"] = 'nullable|string|max:255';
            $roles["landing_cards.$idx.title_en"] = 'nullable|string|max:255';
            $roles["landing_cards.$idx.link"] = 'nullable|url|max:500';
            $roles["landing_card_image_$idx"] = 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096';
        }

       
        foreach ($locales as $locale) {
            $roles['name_' . $locale] = 'required';
            $roles['key_words_' . $locale] = 'required';
            $roles['condition1_' . $locale] = 'required';
        }


        $this->validate($request, $roles);


        $setting = Setting::query()->findOrFail(1);
        $setting->email = trim($request->get('email'));
        $setting->mobile = trim($request->get('mobile'));
        $setting->whatsapp = trim($request->get('whatsapp'));
        $setting->instagram = $this->normalizeSocialUrl('instagram', $request->input('instagram', ''));
        $setting->facebook = $this->normalizeSocialUrl('facebook', $request->input('facebook', ''));
        $setting->tiktok = $this->normalizeSocialUrl('tiktok', $request->input('tiktok', ''));
        $setting->snapchat = $this->normalizeSocialUrl('snapchat', $request->input('snapchat', ''));
        $setting->vat = trim($request->get('vat'));
        $setting->eur_to_sar_rate = $request->input('eur_to_sar_rate', 0);




         if($request->file("logo")&&$request->file("logo")!= '' )
        {
            if ($request->file("logo")->isValid())
            {
                $destinationPath=public_path('uploads/settings');

                $extension=strtolower($request->file("logo")->getClientOriginalExtension());
                //dd($extension);
              
                    $fileName_logo=uniqid().'.'.$extension;
                    $request->file("logo")->move($destinationPath, $fileName_logo);
                
            }
        }

        if($request->file("cover_image")&&$request->file("cover_image")!= '' )
        {
            if ($request->file("cover_image")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("cover_image")->getClientOriginalExtension());
                $fileName_cover=uniqid().'_cover.'.$extension;
                $request->file("cover_image")->move($destinationPath, $fileName_cover);
            }
        }
        if($request->file("cover_image_mobile")&&$request->file("cover_image_mobile")!= '' )
        {
            if ($request->file("cover_image_mobile")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("cover_image_mobile")->getClientOriginalExtension());
                $fileName_cover_mobile=uniqid().'_cover_mobile.'.$extension;
                $request->file("cover_image_mobile")->move($destinationPath, $fileName_cover_mobile);
            }
        }
        if($request->file("whiteout_image")&&$request->file("whiteout_image")!= '' )
        {
            if ($request->file("whiteout_image")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("whiteout_image")->getClientOriginalExtension());
                $fileName_whiteout=uniqid().'_whiteout.'.$extension;
                $request->file("whiteout_image")->move($destinationPath, $fileName_whiteout);
            }
        }
        if($request->file("whiteout_image_mobile")&&$request->file("whiteout_image_mobile")!= '' )
        {
            if ($request->file("whiteout_image_mobile")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("whiteout_image_mobile")->getClientOriginalExtension());
                $fileName_whiteout_mobile=uniqid().'_whiteout_mobile.'.$extension;
                $request->file("whiteout_image_mobile")->move($destinationPath, $fileName_whiteout_mobile);
            }
        }
        if($request->file("training_image")&&$request->file("training_image")!= '' )
        {
            if ($request->file("training_image")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("training_image")->getClientOriginalExtension());
                $fileName_training=uniqid().'_training.'.$extension;
                $request->file("training_image")->move($destinationPath, $fileName_training);
            }
        }
        if($request->file("training_image_mobile")&&$request->file("training_image_mobile")!= '' )
        {
            if ($request->file("training_image_mobile")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("training_image_mobile")->getClientOriginalExtension());
                $fileName_training_mobile=uniqid().'_training_mobile.'.$extension;
                $request->file("training_image_mobile")->move($destinationPath, $fileName_training_mobile);
            }
        }
        if($request->file("technic_image")&&$request->file("technic_image")!= '' )
        {
            if ($request->file("technic_image")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("technic_image")->getClientOriginalExtension());
                $fileName_technic=uniqid().'_technic.'.$extension;
                $request->file("technic_image")->move($destinationPath, $fileName_technic);
            }
        }
        if($request->file("technic_image_mobile")&&$request->file("technic_image_mobile")!= '' )
        {
            if ($request->file("technic_image_mobile")->isValid())
            {
                $destinationPath=public_path('uploads/settings');
                $extension=strtolower($request->file("technic_image_mobile")->getClientOriginalExtension());
                $fileName_technic_mobile=uniqid().'_technic_mobile.'.$extension;
                $request->file("technic_image_mobile")->move($destinationPath, $fileName_technic_mobile);
            }
        }




        foreach ($locales as $locale) {
            $setting->translate($locale)->name = trim(ucwords($request->get('name_' . $locale)));
            $setting->translate($locale)->key_words = trim(ucwords($request->get('key_words_' . $locale)));
            $setting->translate($locale)->condition1 = ucwords($request->get('condition1_' . $locale));
        }
        if(isset($fileName_logo)){$setting->logo= $fileName_logo;}
        $pricingChanged = $setting->isDirty(['vat', 'eur_to_sar_rate']);
        $setting->save();
        if ($pricingChanged) {
            app(WebsitePricingService::class)->bumpPricingCacheRevision();
        }

        $this->coverService->save([
            'cover_link' => $request->get('cover_link'),
            'cover_image' => $fileName_cover ?? null,
            'cover_image_mobile' => $fileName_cover_mobile ?? null,
            'whiteout_link' => $request->get('whiteout_link'),
            'whiteout_image' => $fileName_whiteout ?? null,
            'whiteout_image_mobile' => $fileName_whiteout_mobile ?? null,
            'training_link' => $request->get('training_link'),
            'training_image' => $fileName_training ?? null,
            'training_image_mobile' => $fileName_training_mobile ?? null,
            'technic_link' => $request->get('technic_link'),
            'technic_image' => $fileName_technic ?? null,
            'technic_image_mobile' => $fileName_technic_mobile ?? null,
        ]);

        $cardsInput = $request->input('landing_cards', []);
        $existingCards = $this->landingSectionCardsService->getAll();
        $savedCards = [];
        foreach (range(0, 3) as $idx) {
            $row = is_array($cardsInput[$idx] ?? null) ? $cardsInput[$idx] : [];
            $existing = $existingCards[$idx] ?? [];
            $image = (string) ($existing['image'] ?? '');
            if ($request->file("landing_card_image_$idx") && $request->file("landing_card_image_$idx")->isValid()) {
                $destinationPath = public_path('uploads/settings');
                $extension = strtolower($request->file("landing_card_image_$idx")->getClientOriginalExtension());
                $fileName_card = uniqid() . "_landing_card_$idx." . $extension;
                $request->file("landing_card_image_$idx")->move($destinationPath, $fileName_card);
                $image = $fileName_card;
            } else {
                $incoming = (string) ($row['image'] ?? '');
                if ($incoming !== '') {
                    $image = $this->normalizeStoredFilename($incoming, (string) ($existing['image'] ?? ''));
                }
            }

            $savedCards[] = [
                'id' => $existing['id'] ?? "card_$idx",
                'title_ar' => (string) ($row['title_ar'] ?? ($existing['title_ar'] ?? '')),
                'title_en' => (string) ($row['title_en'] ?? ($existing['title_en'] ?? '')),
                'link' => (string) ($row['link'] ?? ($existing['link'] ?? '')),
                'image' => $image,
            ];
        }
        $this->landingSectionCardsService->saveAll($savedCards);

        $this->landingTechnicSectionService->save([
            'title_white_ar' => $request->input('technic_title_white_ar'),
            'title_gold_ar' => $request->input('technic_title_gold_ar'),
            'description_ar' => $request->input('technic_description_ar'),
            'title_white_en' => $request->input('technic_title_white_en'),
            'title_gold_en' => $request->input('technic_title_gold_en'),
            'description_en' => $request->input('technic_description_en'),
        ]);

        $this->headerTickerService->save([
            'ticker_enabled' => $request->boolean('ticker_enabled', true),
            'ticker_prefix_en' => $request->input('ticker_prefix_en'),
            'ticker_prefix_ar' => $request->input('ticker_prefix_ar'),
            'ticker_cta_en' => $request->input('ticker_cta_en'),
            'ticker_cta_ar' => $request->input('ticker_cta_ar'),
            'ticker_link_en' => $request->input('ticker_link_en'),
            'ticker_link_ar' => $request->input('ticker_link_ar'),
            'ticker_bg_color' => $request->input('ticker_bg_color'),
        ]);

        $message = __('common.success');
        return mainResponse(true, $message, $setting, 200, 'items', '');    

    }


    private function normalizeSocialUrl(string $platform, mixed $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }
        $handle = ltrim($raw, '@');
        if ($handle === '') {
            return '';
        }

        return match ($platform) {
            'instagram' => "https://www.instagram.com/{$handle}",
            'facebook' => "https://www.facebook.com/{$handle}",
            'tiktok' => "https://www.tiktok.com/@{$handle}",
            'snapchat' => "https://www.snapchat.com/add/{$handle}",
            default => $raw,
        };
    }

    private function normalizeStoredFilename(string $incoming, string $fallback = ''): string
    {
        $incoming = trim($incoming);
        if ($incoming === '') {
            return $fallback;
        }
        if (preg_match('~/uploads/settings/([^/?#]+)~i', $incoming, $m)) {
            return $m[1];
        }
        if (! str_contains($incoming, '/')) {
            return $incoming;
        }

        return $fallback;
    }


    public function getById(Request $request)
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
        $technicSection = $this->landingTechnicSectionService->get();
        $items->technic_title_white_ar = $technicSection['title_white_ar'];
        $items->technic_title_gold_ar = $technicSection['title_gold_ar'];
        $items->technic_description_ar = $technicSection['description_ar'];
        $items->technic_title_white_en = $technicSection['title_white_en'];
        $items->technic_title_gold_en = $technicSection['title_gold_en'];
        $items->technic_description_en = $technicSection['description_en'];
        $ticker = $this->headerTickerService->get();
        $items->ticker_enabled = $ticker['is_enabled'];
        $items->ticker_prefix_en = $ticker['prefix_en'];
        $items->ticker_prefix_ar = $ticker['prefix_ar'];
        $items->ticker_cta_en = $ticker['cta_en'];
        $items->ticker_cta_ar = $ticker['cta_ar'];
        $items->ticker_link_en = $ticker['link_en'];
        $items->ticker_link_ar = $ticker['link_ar'];
        $items->ticker_bg_color = $ticker['bg_color'];
        $items->header_ticker = $ticker;
        $message ="success return";
        return mainResponse(true, $message , $items, 200, 'items',''); 
    }


    

}