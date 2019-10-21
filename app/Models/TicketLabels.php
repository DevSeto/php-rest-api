<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Iatstuti\Database\Support\CascadeSoftDeletes;


class TicketLabels extends Model
{
    use SoftDeletes, CascadeSoftDeletes;

    protected $table = 'ticket_labels';
    protected $fillable = ['ticket_id', 'label_id'];
    protected $dates = ['deleted_at'];

    public function label()
    {
        return $this->hasOne('App\Models\Label', 'id', 'label_id');
    }

}
