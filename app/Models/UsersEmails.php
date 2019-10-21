<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class UsersEmails extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table    = 'users_emails';
    protected $fillable = ['company_id', 'email', 'role_id'];
    protected $dates    = ['deleted_at'];

    public function company()
    {
        return $this->hasOne('App\Models\Subdomains',  'id', 'company_id');
    }
}
