<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;

class ConditionValuesList extends Model
{
    protected $fillable = ['id', 'value'];
    protected $table = 'condition_values_lists';
}
