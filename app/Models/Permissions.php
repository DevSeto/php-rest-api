<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Permissions extends Model
{
    protected $table = 'permissions';
    protected $fillable = ['name'];

}
