<?php

namespace App\Exceptions;


use Illuminate\Support\Facades\Lang;
use Throwable;

class TicketNotFoundException extends \Exception
{
    protected $message;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = Lang::get('tickets.ticket_id_hash');
    }

}