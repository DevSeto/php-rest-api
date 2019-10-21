<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryCodes extends Model
{
    protected $fillable = ['id', 'iso', 'name', 'nicename', 'iso3', 'numcode', 'phonecode', 'img'];
}
