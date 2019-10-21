<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;

class ConditionsOperatorsList extends Model
{
    protected $fillable = ['id', 'operator'];
    protected $table = 'conditions_operators_lists';

}
