<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailProviders extends Model
{
    protected $table = 'email_providers';
    protected $fillable = ['name'];

}
