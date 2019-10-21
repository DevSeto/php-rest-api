<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloudflareSubdomains extends Model
{
    protected $fillable = ['user_id', 'company_url', 'cloudflare_subdomain_id', 'subdomain_details'];
}
