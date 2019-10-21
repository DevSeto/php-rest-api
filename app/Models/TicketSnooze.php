<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSnooze extends Model
{
    protected $fillable = [ 'ticket_id_hash', 'sub_domain', 'snooze'];

}
