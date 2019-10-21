<?php

namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflows extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'type',
        'activity',
        'completed'
    ];

    protected $dates = ['deleted_at'];

    public function conditions()
    {
        return $this->hasMany('App\Models\Workflows\DataConditions', 'workflow_id', 'id');
    }

    public function actions()
    {
        return $this->hasMany('App\Models\Workflows\DataActions', 'workflow_id', 'id');
    }
}
