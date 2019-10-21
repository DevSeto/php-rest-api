<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'logo',
        'logo_full_path',
        'company_name',
        'website',
        'subdomain',
        'country',
        'country_code',
        'flag',
        'phone',
        'timezone',
        'last_trash_clear',
        'timezone_offset'
    ];
    protected $dates = ['deleted_at'];
}
