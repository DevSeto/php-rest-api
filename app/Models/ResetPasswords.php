<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class ResetPasswords extends Model
{
    protected $table = 'reset_passwords';
    protected $fillable = ['user_id', 'token'];
}
