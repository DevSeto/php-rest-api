<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserRoles extends Model
{
    protected $table    = 'user_roles';
    protected $fillable = ['name', 'display_name', 'default_permissions_ids', 'permissions_ids'];
}
