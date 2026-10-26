<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use DB;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'national_id',
        'nafath_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $appends = ['average_rating', 'account_age', 'posted_count'];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'online' => 'boolean',
        ];
    }


  


   public function getLogoAttribute($value)
    {
        return $value ? url($value) : null;
    }



    public function getCreatedAtAttribute($value){
        return date('Y-m-d', strtotime($value));
    }

    public function getAccountAgeAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }


    public function favorites()
    {
        return $this->belongsToMany(Ad::class, 'favorites')->withTimestamps();
    }



    public function ratings()
    {
        return $this->hasMany(Rating::class, 'seller_id'); 
    }


    public function getAverageRatingAttribute()
    {
        if (! Schema::hasTable('ratings')) {
            return 0;
        }

        try {
            return round((float) $this->ratings()->avg('rating'), 2) ?: 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }


 

    public function latestRating()
    {
        return $this->hasOne(Rating::class, 'user_id')->latestOfMany();
    }


    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
 



    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function country()
    {
        return $this->belongsTo(ItemsCategories::class,'country_id');
    }


    public function city()
    {
        return $this->belongsTo(ItemsCategories::class,'city_id');
    }



    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user')
                    ->withPivot('read')
                    ->withTimestamps();
    }



    public function getPostedCountAttribute()
    {
        if (! Schema::hasTable('ads')) {
            return 0;
        }

        try {
            return (int) DB::table('ads')->where('user_id', $this->id)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function ads() {
        return $this->hasMany(Ad::class);
    }


    // public function averageRating()
    // {
    //     return $this->ratings()->avg('rating');
    // }



}
