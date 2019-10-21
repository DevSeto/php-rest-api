<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class FilesOfNotification extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['notification_id', 'file_name'];
    protected $dates    = ['deleted_at'];
}
