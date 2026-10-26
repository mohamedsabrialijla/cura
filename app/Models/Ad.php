<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Astrotomic\Translatable\Translatable;
use Illuminate\Support\Facades\App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Support\Arr;

class Ad extends Model
{

     use SoftDeletes,Translatable;

    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'country_id',
        'city_id',
        'invoice',
        'product_type',

        'mobile',
        'price',
        'status',
        'views',

        'piece_id',
        'metal_id',
        'caliber_id',
        'weight',
    ];


    public $translatedAttributes = ['title','description'];
    public $appends = ['image_basic','updated_at_human','is_favorite','my_ad','ad_number','url_ad','follow_up','product_type_translated'];


    protected $casts = [
        'user_id'     => 'integer',
        'category_id' => 'integer',
        'brand_id'    => 'integer',
        'country_id'  => 'integer',
        'city_id'     => 'integer',
        'piece_id'    => 'integer',
        'metal_id'    => 'integer',
        'caliber_id'  => 'integer',
    ];

 

  
    public function getCreatedAtAttribute($value){
        return date('Y-m-d H:i', strtotime($value));
    }


    public function getUpdatedAtHumanAttribute()
    {
        Carbon::setLocale(App::getLocale());

        return Carbon::parse($this->updated_at)->diffForHumans();
    }
    

    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }



    public function user(){

        return $this->belongsTo(User::class, 'user_id');
    }


    public function category(){

        return $this->belongsTo(ItemsCategories::class, 'category_id');
    }


    public function brand(){

        return $this->belongsTo(ItemsCategories::class, 'brand_id');
    }


    public function piece()
    {
        return $this->belongsTo(ItemsCategories::class, 'piece_id');
    }


    public function metal()
    {
        return $this->belongsTo(ItemsCategories::class, 'metal_id');
    }


    public function caliber()
    {
        return $this->belongsTo(ItemsCategories::class, 'caliber_id');
    }


    public function country(){

        return $this->belongsTo(ItemsCategories::class, 'country_id');
    }


    public function city(){

        return $this->belongsTo(ItemsCategories::class, 'city_id');
    }


    public function getInvoiceAttribute($value){

        if($value){
            return url($value);
        }else{
            return "";
        }
        
    } 


    

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }


    public function comments()
    {
        return $this->hasMany(Comment::class)->with('user');
    }


    public function getImageBasicAttribute() {
        if ($this->images && $this->images->isNotEmpty()) {
            return $this->images->first();
        } else {
            return null;
        }
    }


    public function getUrlAdAttribute() {
        
        return url(app()->getLocale().'/'.'ad-details/'. $this->id);
    }



    public function getIsFavoriteAttribute() {
        $guard = request()->expectsJson() ? 'sanctum' : null;
        $user = auth($guard)->user();

        if(!$user || $user == null){
            return false;
        }
        $fav = DB::table('favorites')->where('ad_id',$this->id)->where('user_id', $user->id)->first();
        if(isset($fav) && $fav != ''){
            return true;
        }else{
            return false;
        }
    }



    public function getMyAdAttribute()
    {
        $guard = request()->expectsJson() ? 'sanctum' : null;
        $user = auth($guard)->user();
        // return $user;
        if(!isset($user)|| $user==null){
            return false;
        }
        $status = false;

        if($user->id == $this->user_id){
            $status = true;
        }

        return $status;
    }



    public function getAdNumberAttribute()
    {
        return '000'.$this->id;
    }




    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // $ad->payments;
    // $ad->payments()->latest()->first();
    // $isPaid = $ad->payments()->where('status', 'successful')->exists();






    public function getFollowUpAttribute()
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return false;
        }

        $followAttr = $this->attributes['follow_up'] ?? ''; // لو غير موجود يرجع ''
        $followIds = $followAttr ? explode(',', $followAttr) : [];

        return in_array($user->id, $followIds);
    }


     public function getProductTypeTranslatedAttribute()
    {
        $locale = app()->getLocale(); // 'en' أو 'ar'
        $translations = [
            'new' => ['en' => 'new', 'ar' => 'جديد'],
            'used' => ['en' => 'used', 'ar' => 'مستخدم'],
        ];

        return $translations[$this->product_type][$locale] ?? $this->product_type;
    }


   





   
    






}


