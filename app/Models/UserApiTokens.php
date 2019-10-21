<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApiTokens extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_api_tokens';
    protected $fillable = ['user_id', 'api_token', 'expires_at'];


    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
