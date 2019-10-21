<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdomains extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'subdomains';
    protected $fillable = ['company_url', 'webhook_id'];

}
