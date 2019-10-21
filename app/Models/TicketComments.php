<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class TicketComments extends Model
{
    use SoftDeletes, CascadeSoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ticket_id',
        'from_name',
        'from_email',
        'body',
        'author_id',
        'is_forwarded',
        'forwarding_addresses',
        'transmission_id',
        'email_status',
        'event_time'
    ];
    protected $dates = ['deleted_at'];


    public function customer()
    {
        return $this->hasOne('App\Models\User', 'email', 'from_email');
    }

    public function author()
    {
        return $this->hasOne('App\Models\User', 'id', 'author_id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\TicketFiles', 'comment_id', 'id');
    }

}
