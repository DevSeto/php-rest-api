<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddedMailboxes extends Model
{
    protected $fillable = [
        'domain',
        'company_id'
    ];
}
