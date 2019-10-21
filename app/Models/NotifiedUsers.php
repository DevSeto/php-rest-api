<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class NotifiedUsers extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['notification_id', 'user_id', 'is_viewed'];
    protected $dates = ['deleted_at'];

    public function notifications()
    {
        return $this->hasOne('App\Models\Notifications', 'id', 'notification_id')->with(['author', 'customer','assigned', 'mentioned']);
    }
}
