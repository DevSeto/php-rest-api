<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageVersions extends Model
{
    protected $fillable = ['key', 'en', 'fr', 'nl', 'description', 'print_screen_url'];
}
