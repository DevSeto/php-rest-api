<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class MailboxAvailableHours extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mailbox_id',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ];
    protected $dates = ['deleted_at'];
}
