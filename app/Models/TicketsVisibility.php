<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class TicketsVisibility extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'tickets_visibilities';
    protected $fillable = ['ticket_id','user_id'];

}
