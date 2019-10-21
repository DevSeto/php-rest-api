<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class MergedTickets extends Model
{
    use SoftDeletes;

    protected $table = 'merged_tickets';
    protected $fillable = ['ticket_id', 'master_ticket_id', 'batch'];
    protected $dates = ['deleted_at'];

    public function mergedTickets()
    {
        return $this->belongsTo('App\Models\Tickets', 'ticket_id', 'id');
    }

    public function notes()
    {
        return $this->hasMany('App\Models\Notes', 'ticket_id', 'ticket_id');
    }

    /**
     * Get merged (deleted  ) tickets
     *
     */
    public function ticket()
    {
        return $this->belongsTo('App\Models\Tickets', 'ticket_id', 'id')->withTrashed();
    }
}
