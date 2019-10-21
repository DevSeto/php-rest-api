<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UsersReports extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['user_id', 'ticket_id', 'reply_time', 'first_reply_time', 'handle_time', 'replies'];
    protected $dates = ['deleted_at'];

}
