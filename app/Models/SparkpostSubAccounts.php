<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SparkpostSubAccounts extends Model
{
    protected $fillable = ['user_id', 'sub_account_name', 'sub_account_id', 'key'];
}
