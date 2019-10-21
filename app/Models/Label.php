<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Label extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'labels';
    protected $fillable = ['ticket_id', 'color', 'body'];
    protected $dates = ['deleted_at'];

}
