<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationsList extends Model
{
    protected $table = "notifications_list";
    protected $fillable = ["condition", "reply"];
}
