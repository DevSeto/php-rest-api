<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes, CascadeSoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'company_url',
        'api_token',
        'push_notification_status',
        'role_id',
        'display_user_role',
        'title',
        'phone',
        'country_code',
        'country',
        'flag',
        'alternate_email',
        'time_zone',
        'avatar',
        'avatar_full_path',
        'avatar_url',
        'step'
    ];

    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['tickets', 'notified'];

    use Notifiable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get ticket notes
     *
     * @return object
     */
    public function avatar()
    {
        return $this->hasOne('App\Models\Profile', 'user_id', 'id');
    }

    /**
     * Get merged tickets data from 'merged_tickets' table
     *
     */
    public function userRoles()
    {
        return $this->hasOne('App\Models\UserRoles', 'id', 'role_id');
    }

    public function mailboxes()
    {
        return $this->hasOne('App\Models\MailboxUserPermissions', 'user_id', 'id');
    }

    public function mailbox_names()
    {
        return $this->hasMany('App\Models\MailboxUserPermissions', 'user_id', 'id')->with('mailbox');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Tickets', 'owner_id', 'id');
    }

    public function notified()
    {
        return $this->hasMany('App\Models\NotifiedUsers', 'user_id', 'id');
    }

    public function preferences()
    {
        return $this->hasOne('App\Models\UserPreferences', 'user_id', 'id');
    }

}
