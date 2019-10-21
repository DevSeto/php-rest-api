<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPreferences extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'answer',
        'assign_after_reply',
        'take_back_after_reply',
        'assign_after_note',
        'take_back_after_note',
        'take_back_after_update',
        'delay_sending'
    ];
    protected $dates = ['deleted_at'];
}
