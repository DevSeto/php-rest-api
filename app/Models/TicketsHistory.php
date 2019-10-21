<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;

class TicketsHistory extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'ticket_history';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'ticket_id',
        'author_id',
        'customer_id',
        'type',
        'merged_tickets',
        'status_to',
        'assigned_to',
        'merged_with'
    ];

    public function author()
    {
        return $this->hasOne('App\Models\User', 'id', 'author_id');
    }

    public function customer()
    {
        return $this->hasOne('App\Models\Customers', 'id', 'customer_id');
    }

    public function assignedToUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'assigned_to');
    }

    public function ticket()
    {
        return $this->hasOne('App\Models\Tickets', 'id', 'ticket_id')->with('customerData');
    }

}
