<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    protected $fillable = ['DT_RowId', 'key', 'en', 'fr', 'nl','description', 'print_screen_url'];
}
