<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class UserNotifications extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['user_id', 'condition_id', 'selected', 'opened', 'browser', 'email', 'mailbox_id'];
    protected $dates = ['deleted_at'];

    function notification()
    {
        return $this->hasOne('App\Models\NotificationsList', 'id', 'condition_id');
    }

    function mailbox()
    {
        return $this->hasOne('App\Models\Mailbox', 'id', 'mailbox_id')->select(['name', 'id']);
    }

}
