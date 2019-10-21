<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Notifications extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['type', 'author_id', 'customer_id', 'ticket_id', 'assigned_to', 'comment_id', 'note_id', 'mentioned'];
    protected $dates = ['deleted_at'];

    public function author()
    {
        return $this->hasOne('App\Models\User', 'id', 'author_id');
    }

    public function assigned()
    {
        return $this->hasOne('App\Models\User', 'id', 'assigned_to');
    }

    public function mentioned()
    {
        return $this->hasOne('App\Models\User', 'id', 'mentioned');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customers', 'id', 'customer_id');
    }

    function files()
    {
        return $this->hasOne('App\Models\FilesOfNotification', 'id', 'notification_id');
    }
}
