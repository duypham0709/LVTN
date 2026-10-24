<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];


    // 1 Role có nhiều User
    public function users()
    {
        return $this->hasMany(User::class);
    }


    // 1 Role có nhiều Permission thông qua bảng trung gian role_permissions
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }


}
