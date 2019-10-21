<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundDomains extends Model
{
    protected $fillable = ['user_id', 'inbound_domain', 'token', 'webhook_id'];
}
