<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class Notes extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'notes';
    protected $fillable = ['ticket_id', 'note', 'author_id'];
    protected $dates = ['deleted_at'];

    public function author()
    {
        return $this->hasOne('App\Models\User', 'id', 'author_id');
    }

    public function ticket()
    {
        return $this->hasOne('App\Models\Tickets', 'id', 'ticket_id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\TicketFiles', 'note_id', 'id')->withTrashed();
    }

}
