<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForwardedEmailsDraft extends Model
{
    protected $fillable = ['id', 'draft_id', 'email'];
}
