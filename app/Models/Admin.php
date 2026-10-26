<?php

namespace App\Models;


use App\Models\Permission;
use App\Models\Subscribe;
use App\Models\ModelHasRole;
use App\Models\role_has_permissions;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;




class Admin extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable;


    protected $fillable = [
        'name', 'email', 'password', 'mobile', 'profile_image'];
        
    protected $guard_name = 'admin';    

    protected $appends = ['role','permission','role_name'];

 
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function getLogoAttribute($value)
    {
        if ($value != null)
            return url($value);
        return "";
    }
    


      public function getRoleAttribute()
    {
        $roleId = ModelHasRole::where('model_id', $this->id)
            ->where('model_type', 'like', '%Admin%')
            ->value('role_id');

        if (! $roleId || (int) $roleId <= 0) {
            return [];
        }

        $permissionIds = role_has_permissions::where('role_id', $roleId)
            ->pluck('permission_id')
            ->toArray();

        return Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
    }



    public function getPermissionAttribute()
    {
        $p = ModelHasRole::where('model_id',$this->id)->first();
        return $p ;
        
    }

    public function getRoleNameAttribute()
    {
        $p = ModelHasRole::where('model_id',$this->id)->first();
        if($p != '')
            return Role::where('id',$p->role_id)->first();
        return '';
        
    }


    public function subscribe(){

        return $this->hasOne(Subscribe::class,'company_id')->where('status','active')->orderBy('id','desc')->latest();
    }
    


     
    
}
