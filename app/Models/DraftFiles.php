<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftFiles extends Model
{
    protected $fillable = ['draft_id', 'name', 'path', 'type'];
}
