<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;


class Team extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'lead_id'];
    protected $dates = ['deleted_at'];

    public function members()
    {
        return $this->hasMany('App\Models\TeamUsers', 'team_id', 'id')->with('user');
    }
}
