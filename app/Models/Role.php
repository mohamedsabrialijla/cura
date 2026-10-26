<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{

    protected $appends = ['by' , 'Permission_check','permissions'];

    public function getByAttribute() {
       $f_name= User::where('id',$this->created_by)->pluck('name')->first();
    
       return $f_name ;
    }
	
    public function getCreatedAtAttribute($value){
        return date('Y-m-d H:i', strtotime($value));
    }


    public function getPermissionCheckAttribute() {
       $ides_permision =  role_has_permissions::where('role_id',$this->id)->pluck('permission_id');
       return $ides_permision;
    }


     public function getpermissionsAttribute() {
       
        $all_permissions = Permission::query()
            ->whereNull('parent_id')
            ->where('admin', 1)
            ->orderBy('id')
            ->get();

        $role_permissions = $this->permission_check;

        return $all_permissions->map(function ($permission) use ($role_permissions) {
            $permission->checked = $role_permissions->contains($permission->id);

            return $permission;
        });
    
    }



}
